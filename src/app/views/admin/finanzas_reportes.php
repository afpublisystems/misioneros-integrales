<?php
/**
 * Índice de informes de rendición de cuentas.
 * Cada tarjeta arma la dirección de su hoja; la hoja sale sin el panel,
 * lista para imprimir o guardar en PDF.
 */
$titulo = 'Informes';
?>

<div class="admin-layout">

    <?php include __DIR__ . '/../partials/admin_sidebar.php'; ?>

    <main class="admin-main">

        <?php if (!empty($_SESSION['flash'])): ?>
        <div class="alerta alerta--<?= $_SESSION['flash']['tipo'] ?>">
            <i class="fas fa-<?= $_SESSION['flash']['tipo'] === 'exito' ? 'check-circle' : 'exclamation-circle' ?>"></i>
            <?= htmlspecialchars($_SESSION['flash']['msg']) ?>
        </div>
        <?php unset($_SESSION['flash']); endif; ?>

        <div class="admin-header">
            <div>
                <h1>Informes</h1>
                <p>Rendición de cuentas del programa, para imprimir o mandar en PDF</p>
            </div>
            <?php if ($periodo['desde']): ?>
            <div class="admin-header__fecha">
                <i class="fas fa-calendar-alt"></i>
                Con movimientos desde <?= date('d/m/Y', strtotime($periodo['desde'])) ?>
            </div>
            <?php endif; ?>
        </div>

        <?php include __DIR__ . '/../partials/finanzas_nav.php'; ?>

        <div class="rep-grid">

            <!-- Resumen gerencial ───────────────────────────── -->
            <section class="admin-panel rep-card rep-card--destacada">
                <div class="admin-panel__header">
                    <h2><i class="fas <?= $informes['gerencial'][0] ?>"></i> <?= $informes['gerencial'][1] ?></h2>
                </div>
                <div class="rep-card__cuerpo">
                    <p class="texto-muted"><?= $informes['gerencial'][2] ?></p>
                    <p class="fin-nota">
                        Trae de dónde vino la plata, en qué se fue, el saldo, lo que se debe por
                        préstamos, cómo va el cobro de matrículas y la cuenta del Seminario.
                    </p>
                    <div class="rep-card__pie">
                        <a href="/admin/finanzas/reporte?r=gerencial" target="_blank" class="btn btn--verde btn--sm">
                            <i class="fas fa-file-lines"></i> Generar informe
                        </a>
                        <a href="/admin/finanzas/reporte/csv?r=gerencial" class="btn btn--outline btn--sm">
                            <i class="fas fa-file-csv"></i> CSV
                        </a>
                    </div>
                </div>
            </section>

            <!-- Préstamos ───────────────────────────────────── -->
            <section class="admin-panel rep-card">
                <div class="admin-panel__header">
                    <h2><i class="fas <?= $informes['prestamos'][0] ?>"></i> <?= $informes['prestamos'][1] ?></h2>
                </div>
                <form method="GET" action="/admin/finanzas/reporte" target="_blank" class="rep-card__cuerpo">
                    <input type="hidden" name="r" value="prestamos">
                    <p class="texto-muted"><?= $informes['prestamos'][2] ?></p>
                    <div class="form-grupo">
                        <label>Qué mostrar</label>
                        <select name="estatus">
                            <?php foreach ($estados as $valor => $texto): ?>
                            <option value="<?= $valor ?>"><?= $texto ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="rep-card__pie">
                        <button type="submit" class="btn btn--verde btn--sm">
                            <i class="fas fa-file-lines"></i> Generar informe
                        </button>
                        <button type="submit" formaction="/admin/finanzas/reporte/csv" formtarget="_self"
                                class="btn btn--outline btn--sm">
                            <i class="fas fa-file-csv"></i> CSV
                        </button>
                    </div>
                </form>
            </section>

            <!-- Matrículas ──────────────────────────────────── -->
            <section class="admin-panel rep-card">
                <div class="admin-panel__header">
                    <h2><i class="fas <?= $informes['matriculas'][0] ?>"></i> <?= $informes['matriculas'][1] ?></h2>
                </div>
                <div class="rep-card__cuerpo">
                    <p class="texto-muted"><?= $informes['matriculas'][2] ?></p>
                    <p class="fin-nota">
                        Los abonos que todavía están por confirmar salen marcados aparte y no suman
                        en el total cobrado.
                    </p>
                    <div class="rep-card__pie">
                        <a href="/admin/finanzas/reporte?r=matriculas" target="_blank" class="btn btn--verde btn--sm">
                            <i class="fas fa-file-lines"></i> Generar informe
                        </a>
                        <a href="/admin/finanzas/reporte/csv?r=matriculas" class="btn btn--outline btn--sm">
                            <i class="fas fa-file-csv"></i> CSV
                        </a>
                    </div>
                </div>
            </section>

            <!-- Seminario ───────────────────────────────────── -->
            <section class="admin-panel rep-card">
                <div class="admin-panel__header">
                    <h2><i class="fas <?= $informes['seminario'][0] ?>"></i> <?= $informes['seminario'][1] ?></h2>
                </div>
                <div class="rep-card__cuerpo">
                    <p class="texto-muted"><?= $informes['seminario'][2] ?></p>
                    <div class="rep-card__pie">
                        <a href="/admin/finanzas/reporte?r=seminario" target="_blank" class="btn btn--verde btn--sm">
                            <i class="fas fa-file-lines"></i> Generar informe
                        </a>
                        <a href="/admin/finanzas/reporte/csv?r=seminario" class="btn btn--outline btn--sm">
                            <i class="fas fa-file-csv"></i> CSV
                        </a>
                    </div>
                </div>
            </section>

            <!-- Gastos ──────────────────────────────────────── -->
            <section class="admin-panel rep-card">
                <div class="admin-panel__header">
                    <h2><i class="fas <?= $informes['gastos'][0] ?>"></i> <?= $informes['gastos'][1] ?></h2>
                </div>
                <form method="GET" action="/admin/finanzas/reporte" target="_blank" class="rep-card__cuerpo">
                    <input type="hidden" name="r" value="gastos">
                    <p class="texto-muted"><?= $informes['gastos'][2] ?></p>

                    <div class="form-grid-2">
                        <div class="form-grupo">
                            <label>Desde</label>
                            <input type="date" name="desde" value="<?= htmlspecialchars($periodo['desde'] ?? '') ?>">
                        </div>
                        <div class="form-grupo">
                            <label>Hasta</label>
                            <input type="date" name="hasta" value="<?= htmlspecialchars($periodo['hasta'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="form-grupo">
                        <label>Rubro</label>
                        <select name="categoria">
                            <option value="">Todos los rubros</option>
                            <?php foreach ($categorias as $clave => $texto): ?>
                            <option value="<?= $clave ?>"><?= htmlspecialchars($texto) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-grupo">
                        <label>Cobertura</label>
                        <select name="cobertura">
                            <option value="">Presupuestado y adicional</option>
                            <option value="presupuesto">Solo lo presupuestado</option>
                            <option value="adicional">Solo lo adicional</option>
                        </select>
                    </div>

                    <div class="rep-card__pie">
                        <button type="submit" class="btn btn--verde btn--sm">
                            <i class="fas fa-file-lines"></i> Generar informe
                        </button>
                        <button type="submit" formaction="/admin/finanzas/reporte/csv" formtarget="_self"
                                class="btn btn--outline btn--sm">
                            <i class="fas fa-file-csv"></i> CSV
                        </button>
                    </div>
                </form>
            </section>

            <!-- Fondos ──────────────────────────────────────── -->
            <section class="admin-panel rep-card">
                <div class="admin-panel__header">
                    <h2><i class="fas <?= $informes['fondos'][0] ?>"></i> <?= $informes['fondos'][1] ?></h2>
                </div>
                <form method="GET" action="/admin/finanzas/reporte" target="_blank" class="rep-card__cuerpo">
                    <input type="hidden" name="r" value="fondos">
                    <p class="texto-muted"><?= $informes['fondos'][2] ?></p>

                    <div class="form-grupo">
                        <label>Fondo</label>
                        <select name="fondo">
                            <option value="0">Todos, uno debajo del otro</option>
                            <?php foreach ($fondos as $f): ?>
                            <option value="<?= $f['id'] ?>"><?= htmlspecialchars($f['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <p class="fin-nota">
                        <strong>Para que los traslados de la DIME salgan aparte</strong> hay que crear
                        su fondo en Ingresos y gastos y cargarle tanto lo que gira la DIME como los
                        pasajes y el transporte de cada salida. Lo que no tenga fondo cae en el
                        informe de gastos por rubro, no acá.
                    </p>

                    <div class="rep-card__pie">
                        <button type="submit" class="btn btn--verde btn--sm">
                            <i class="fas fa-file-lines"></i> Generar informe
                        </button>
                        <button type="submit" formaction="/admin/finanzas/reporte/csv" formtarget="_self"
                                class="btn btn--outline btn--sm">
                            <i class="fas fa-file-csv"></i> CSV
                        </button>
                    </div>
                </form>
            </section>

        </div>

    </main>
</div>

<style>
.rep-grid {
    display: grid; gap: 1.25rem;
    grid-template-columns: repeat(auto-fit, minmax(330px, 1fr));
    align-items: start;
}
.rep-card { margin: 0; display: flex; flex-direction: column; }
.rep-card--destacada { border-left: 4px solid var(--verde); }
.rep-card__cuerpo {
    padding: 1.25rem; display: flex; flex-direction: column; gap: 0.85rem; flex: 1;
}
.rep-card__cuerpo > p { margin: 0; }
.rep-card__cuerpo .form-grupo { margin: 0; }
.rep-card__pie {
    display: flex; gap: 0.5rem; flex-wrap: wrap;
    margin-top: auto; padding-top: 0.5rem;
}
</style>
