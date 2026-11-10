<?php
/**
 * ReporteController
 * Informes de rendición de cuentas del programa.
 *
 * Dos pantallas: el índice, donde se arma el informe que se quiere, y
 * la hoja, que sale sin el panel para imprimirla o mandarla en PDF.
 * Los números salen de los mismos modelos que usa Finanzas, no de
 * consultas aparte, para que el informe nunca diga algo distinto a
 * lo que muestra el panel.
 */

require_once APP_PATH . '/controllers/Controller.php';

class ReporteController extends Controller {

    /** Los informes que se pueden sacar, en el orden en que se ofrecen */
    public const INFORMES = [
        'gerencial'  => ['fa-chart-pie',           'Resumen gerencial',
                         'Todo el movimiento del ciclo en una hoja, para la directiva de la CNBV'],
        'prestamos'  => ['fa-hand-holding-dollar', 'Préstamos recibidos',
                         'Quién prestó, cuánto se devolvió y qué queda pendiente'],
        'matriculas' => ['fa-graduation-cap',      'Matrículas por participante',
                         'Lo que paga cada quien, lo abonado y el saldo, con el detalle de cada pago'],
        'seminario'  => ['fa-school',              'Pagos al Seminario',
                         'Lo que se le debe al STBV por participante y con qué transferencia se cubrió'],
        'gastos'     => ['fa-receipt',             'Gastos por rubro',
                         'Logística, alimentación, transporte y lo demás, con su detalle'],
        'fondos'     => ['fa-bullseye',            'Fondos con destino',
                         'Lo recibido para un fin concreto y los gastos que salieron de ahí'],
    ];

    /** Estados del filtro de préstamos */
    private const ESTADOS_PRESTAMO = [
        ''           => 'Todos',
        'pendientes' => 'Solo pendientes',
        'cancelados' => 'Solo cancelados',
    ];

    // ── GET /admin/finanzas/reportes ──────────────────────────
    public function index(): void {
        $this->requireAnyRole(['admin', 'evaluador']);

        $rep = new ReporteModel();

        $this->render('admin/finanzas_reportes', [
            'titulo'     => 'Informes',
            'informes'   => self::INFORMES,
            'estados'    => self::ESTADOS_PRESTAMO,
            'categorias' => GastoModel::CATEGORIAS,
            'fondos'     => $rep->fondos(),
            'periodo'    => $rep->periodo(),
        ], 'admin');
    }

    // ── GET /admin/finanzas/reporte ───────────────────────────
    public function ver(): void {
        $this->requireAnyRole(['admin', 'evaluador']);

        $tipo = (string) ($_GET['r'] ?? 'gerencial');
        if (!isset(self::INFORMES[$tipo])) {
            $this->flash('error', 'Ese informe no existe.');
            $this->redirigir('/admin/finanzas/reportes');
        }

        $filtros = $this->filtros();
        $datos   = $this->datos($tipo, $filtros);

        $this->render('admin/reporte_imprimir', $datos + [
            'titulo'     => self::INFORMES[$tipo][1],
            'tipo'       => $tipo,
            'encabezado' => self::INFORMES[$tipo],
            'filtros'    => $filtros,
            'query'      => http_build_query(array_filter(['r' => $tipo] + $filtros)),
            'categorias' => GastoModel::CATEGORIAS,
            'origenes'   => IngresoModel::ORIGENES,
            'estados'    => self::ESTADOS_PRESTAMO,
        ], 'impresion');
    }

    // ── GET /admin/finanzas/reporte/csv ───────────────────────
    public function csv(): void {
        $this->requireAnyRole(['admin', 'evaluador']);

        $tipo = (string) ($_GET['r'] ?? '');
        if (!isset(self::INFORMES[$tipo])) {
            http_response_code(404);
            exit;
        }

        $filtros = $this->filtros();
        $datos   = $this->datos($tipo, $filtros);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=informe-' . $tipo . '-' . date('Y-m-d') . '.csv');

        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM, para que Excel en español lo abra bien

        match ($tipo) {
            'prestamos'  => $this->csvPrestamos($out, $datos),
            'matriculas' => $this->csvMatriculas($out, $datos),
            'seminario'  => $this->csvSeminario($out, $datos),
            'gastos'     => $this->csvGastos($out, $datos),
            'fondos'     => $this->csvFondos($out, $datos),
            default      => $this->csvGerencial($out, $datos),
        };
        fclose($out);
        exit;
    }

    // ── Armado de cada informe ────────────────────────────────

    /** Lo que vive en la URL y se arrastra entre la hoja y el CSV */
    private function filtros(): array {
        [$desde, $hasta] = ReporteModel::rango($_GET);

        return [
            'desde'     => $desde ?? '',
            'hasta'     => $hasta ?? '',
            'estatus'   => isset(self::ESTADOS_PRESTAMO[$_GET['estatus'] ?? '']) ? (string) $_GET['estatus'] : '',
            'categoria' => isset(GastoModel::CATEGORIAS[$_GET['categoria'] ?? '']) ? (string) $_GET['categoria'] : '',
            'cobertura' => in_array($_GET['cobertura'] ?? '', ['presupuesto', 'adicional'], true) ? (string) $_GET['cobertura'] : '',
            'fondo'     => (int) ($_GET['fondo'] ?? 0),
        ];
    }

    private function datos(string $tipo, array $filtros): array {
        $rep = new ReporteModel();

        return match ($tipo) {
            'prestamos'  => $this->datosPrestamos($rep, $filtros),
            'matriculas' => $this->datosMatriculas($rep),
            'seminario'  => $this->datosSeminario($rep),
            'gastos'     => $this->datosGastos($rep, $filtros),
            'fondos'     => $this->datosFondos($rep, $filtros),
            default      => $this->datosGerencial($rep),
        };
    }

    private function datosPrestamos(ReporteModel $rep, array $filtros): array {
        $lista = (new PrestamoModel())->listar();

        // El saldo manda: un préstamo "activo" sin saldo ya está cancelado
        $filtradas = [];
        foreach ($lista as $p) {
            $p['saldo_usd'] = (float) $p['monto_usd'] - (float) $p['devuelto_usd'];
            $vivo = $p['estatus'] !== 'anulado' && $p['saldo_usd'] > 0.001;

            if ($filtros['estatus'] === 'pendientes' && !$vivo) { continue; }
            if ($filtros['estatus'] === 'cancelados' && ($vivo || $p['estatus'] === 'anulado')) { continue; }
            $filtradas[] = $p;
        }

        return [
            'prestamos'    => $filtradas,
            'devoluciones' => $rep->devoluciones(),
            'totales'      => $rep->totalesPrestamos(),
        ];
    }

    private function datosMatriculas(ReporteModel $rep): array {
        $pagos = new PagoModel();
        return [
            'estudiantes' => $pagos->resumenPorEstudiante(),
            'pagos'       => $rep->pagosMatricula(),
            'totales'     => $pagos->totalesMatricula(),
        ];
    }

    private function datosSeminario(ReporteModel $rep): array {
        $sem = new SeminarioModel();
        return [
            'estudiantes' => $sem->resumenPorEstudiante(),
            'cargos'      => $rep->cargosSeminario(),
            'pagos'       => $rep->pagosSeminario(),
            'totales'     => $sem->totales(),
        ];
    }

    private function datosGastos(ReporteModel $rep, array $filtros): array {
        return [
            'rubros'  => $rep->gastosPorRubro($filtros),
            'detalle' => $rep->gastosDetalle($filtros),
        ];
    }

    private function datosFondos(ReporteModel $rep, array $filtros): array {
        $fondos = $rep->fondos();
        $uno    = null;

        foreach ($fondos as $f) {
            if ((int) $f['id'] === $filtros['fondo']) { $uno = $f; }
        }

        return [
            'fondos'      => $uno ? [$uno] : $fondos,
            'movimientos' => $uno ? [$uno['id'] => $rep->movimientosFondo((int) $uno['id'])] : [],
            'uno'         => $uno,
        ];
    }

    private function datosGerencial(ReporteModel $rep): array {
        $prestamos = $rep->totalesPrestamos();
        $caja      = $rep->totalesCaja();

        return [
            'caja'          => $caja,
            'por_origen'    => (new IngresoModel())->totalesPorOrigen(),
            'por_categoria' => (new GastoModel())->totalesPorCategoria(),
            'prestamos'     => $prestamos,
            'matriculas'    => (new PagoModel())->totalesMatricula(),
            'seminario'     => (new SeminarioModel())->totales(),
            'fondos'        => $rep->fondos(),
            'periodo'       => $rep->periodo(),
            // Lo prestado está en caja pero no es del programa
            'disponible'    => $caja['saldo'] - $prestamos['saldo'],
        ];
    }

    // ── Los CSV ───────────────────────────────────────────────

    private function csvPrestamos($out, array $d): void {
        fputcsv($out, ['Prestamista','Teléfono','Concepto','Fecha','Compromiso','Estado','Monto USD','Devuelto USD','Saldo USD','Notas'], ';');
        foreach ($d['prestamos'] as $p) {
            fputcsv($out, [
                $p['prestamista'], $p['telefono'], $p['concepto'],
                $p['fecha_prestamo'], $p['fecha_compromiso'], $p['estatus'],
                $p['monto_usd'], $p['devuelto_usd'], $p['saldo_usd'], $p['notas'],
            ], ';');
        }
        fputcsv($out, [], ';');
        fputcsv($out, ['Totales', '', '', '', '', '', $d['totales']['recibido'], $d['totales']['devuelto'], $d['totales']['saldo']], ';');
    }

    private function csvMatriculas($out, array $d): void {
        fputcsv($out, ['Participante','Cédula','Beca %','Costo base USD','A pagar USD','Pagado USD','Saldo USD','Fecha del pago','Monto del pago USD','Método','Banco','Referencia','Estado del pago'], ';');
        foreach ($d['estudiantes'] as $e) {
            $saldo = (float) $e['total_a_pagar'] - (float) $e['total_pagado'];
            $suyos = $d['pagos'][(int) $e['aspirante_id']] ?? [];

            if (!$suyos) {
                fputcsv($out, [$e['nombre'], $e['cedula'], $e['beca_pct'], $e['costo_base_usd'], $e['total_a_pagar'], $e['total_pagado'], $saldo, '', '', '', '', '', 'Sin abonos'], ';');
                continue;
            }
            foreach ($suyos as $p) {
                fputcsv($out, [
                    $e['nombre'], $e['cedula'], $e['beca_pct'], $e['costo_base_usd'],
                    $e['total_a_pagar'], $e['total_pagado'], $saldo,
                    $p['fecha'], $p['monto_usd'], $p['metodo_pago'], $p['banco_origen'], $p['referencia'], $p['estatus'],
                ], ';');
            }
        }
    }

    private function csvSeminario($out, array $d): void {
        fputcsv($out, ['Participante','Cédula','Trimestre','Cargo','Monto USD','Estado','Fecha de pago','Referencia del pago'], ';');
        foreach ($d['estudiantes'] as $e) {
            foreach ($d['cargos'][(int) $e['aspirante_id']] ?? [] as $c) {
                fputcsv($out, [
                    $e['nombre'], $e['cedula'] ?? '', $c['trimestre'],
                    $c['descripcion'] ?: $c['concepto'],
                    $c['monto_usd'], $c['estatus'], $c['fecha_pago'], $c['referencia'],
                ], ';');
            }
        }
    }

    private function csvGastos($out, array $d): void {
        fputcsv($out, ['Fecha','Concepto','Rubro','Cobertura','Motivo','Beneficiario','Monto USD','Monto Bs','Tasa','Método','Referencia','Fondo','Cuenta','Cargado por'], ';');
        foreach ($d['detalle'] as $g) {
            fputcsv($out, [
                $g['fecha_gasto'], $g['concepto'],
                GastoModel::CATEGORIAS[$g['categoria']] ?? $g['categoria'],
                $g['cobertura'] === 'adicional' ? 'Adicional' : 'Presupuestado', $g['cobertura_motivo'],
                $g['beneficiario'], $g['monto_usd'], $g['monto_ves'], $g['tasa_cambio'],
                $g['metodo_pago'], $g['referencia'], $g['fondo'], $g['cuenta'], $g['cargado_por'],
            ], ';');
        }
    }

    private function csvFondos($out, array $d): void {
        fputcsv($out, ['Fondo','Tipo','Entradas USD','Salidas USD','Saldo USD','Movimiento','Fecha','Concepto','Contraparte','Monto USD','Referencia'], ';');
        foreach ($d['fondos'] as $f) {
            $saldo = (float) $f['entradas'] - (float) $f['salidas'];
            $movs  = $d['movimientos'][$f['id']] ?? [];

            if (!$movs) {
                fputcsv($out, [$f['nombre'], $f['tipo'], $f['entradas'], $f['salidas'], $saldo], ';');
                continue;
            }
            foreach ($movs as $m) {
                fputcsv($out, [
                    $f['nombre'], $f['tipo'], $f['entradas'], $f['salidas'], $saldo,
                    $m['tipo'] === 'ingreso' ? 'Entrada' : 'Salida',
                    $m['fecha'], $m['concepto'], $m['contraparte'], $m['monto_usd'], $m['referencia'],
                ], ';');
            }
        }
    }

    private function csvGerencial($out, array $d): void {
        fputcsv($out, ['Informe de rendición de cuentas — Programa Misioneros Integrales'], ';');
        fputcsv($out, ['Período', $d['periodo']['desde'], $d['periodo']['hasta']], ';');
        fputcsv($out, [], ';');

        fputcsv($out, ['ENTRADAS POR ORIGEN', 'Movimientos', 'USD'], ';');
        foreach (IngresoModel::ORIGENES as $clave => $etiqueta) {
            $o = $d['por_origen'][$clave] ?? null;
            if ($o) { fputcsv($out, [$etiqueta, $o['cantidad'], $o['total']], ';'); }
        }
        fputcsv($out, ['Total entradas', '', $d['caja']['ingresos']], ';');
        fputcsv($out, [], ';');

        fputcsv($out, ['SALIDAS POR RUBRO', 'Movimientos', 'USD'], ';');
        foreach ($d['por_categoria'] as $c) {
            fputcsv($out, [GastoModel::CATEGORIAS[$c['categoria']] ?? $c['categoria'], $c['cantidad'], $c['total']], ';');
        }
        fputcsv($out, ['Total salidas', '', $d['caja']['gastos']], ';');
        fputcsv($out, [], ';');

        fputcsv($out, ['POSICIÓN'], ';');
        fputcsv($out, ['Saldo en caja', '', $d['caja']['saldo']], ';');
        fputcsv($out, ['Deuda por préstamos', '', $d['prestamos']['saldo']], ';');
        fputcsv($out, ['Disponible propio', '', $d['disponible']], ';');
        fputcsv($out, [], ';');

        fputcsv($out, ['MATRÍCULAS', 'USD'], ';');
        fputcsv($out, ['Esperado', $d['matriculas']['esperado']], ';');
        fputcsv($out, ['Cobrado', $d['matriculas']['cobrado']], ';');
        fputcsv($out, ['Por cobrar', $d['matriculas']['por_cobrar']], ';');
        fputcsv($out, [], ';');

        fputcsv($out, ['SEMINARIO (STBV)', 'USD'], ';');
        fputcsv($out, ['Total de cargos', $d['seminario']['total']], ';');
        fputcsv($out, ['Pagado', $d['seminario']['pagado']], ';');
        fputcsv($out, ['Pendiente', $d['seminario']['pendiente']], ';');
    }
}
