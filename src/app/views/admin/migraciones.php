<?php
$titulo = 'Migraciones';
$error  = $_SESSION['migracion_error'] ?? null;
unset($_SESSION['migracion_error']);
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

        <?php if ($error): ?>
        <div class="admin-panel mig-error">
            <div class="admin-panel__header">
                <h2><i class="fas fa-triangle-exclamation"></i> Lo que respondió la base</h2>
            </div>
            <pre><?= htmlspecialchars($error) ?></pre>
        </div>
        <?php endif; ?>

        <div class="admin-header">
            <div>
                <h1>Migraciones</h1>
                <p>Cambios en la base de datos que se aplican desde acá cuando no hay phpMyAdmin</p>
            </div>
        </div>

        <div class="admin-panel">
            <div class="admin-panel__header">
                <h2><i class="fas fa-database"></i> Archivos en <code><?= htmlspecialchars(basename($carpeta)) ?>/</code></h2>
            </div>

            <div class="mig-aviso">
                <p>
                    <strong>Las migraciones viejas ya están aplicadas en esta base.</strong>
                    A esas márcalas con <em>Ya estaba aplicada</em>, que solo las anota. El botón
                    <em>Ejecutar</em> corre el archivo de verdad, y una migración repetida falla.
                </p>
                <p>
                    Se listan solo los archivos <code>migracion_*.sql</code>. Los demás <code>.sql</code>
                    de la carpeta no aparecen a propósito: <code>schema_produccion.sql</code> trae
                    <code>DROP TABLE</code> y borraría todo.
                </p>
            </div>

            <?php if (!$hay_carpeta): ?>
            <p class="mig-vacio">
                <i class="fas fa-folder-open"></i>
                No existe la carpeta <code><?= htmlspecialchars($carpeta) ?></code>.
                Súbela por FTP con los archivos adentro.
            </p>

            <?php elseif (empty($archivos)): ?>
            <p class="mig-vacio">
                <i class="fas fa-inbox"></i>
                La carpeta está, pero no hay ningún <code>migracion_*.sql</code> adentro.
            </p>

            <?php else: ?>
            <div class="tabla-wrap">
                <table class="tabla">
                    <thead>
                        <tr><th>Archivo</th><th>Estado</th><th></th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($archivos as $a): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($a['nombre']) ?></strong>
                                <br><small class="texto-muted"><?= number_format($a['peso'] / 1024, 1) ?> KB</small>
                            </td>
                            <td>
                                <?php if ($a['aplicada']): ?>
                                <span class="badge badge--aprobada">Aplicada</span>
                                <br><small class="texto-muted"><?= date('d/m/Y H:i', strtotime($a['aplicada'])) ?></small>
                                <?php if ($a['cambio']): ?>
                                <br><small class="mig-cambio"><i class="fas fa-triangle-exclamation"></i> El archivo cambió después</small>
                                <?php endif; ?>
                                <?php else: ?>
                                <span class="badge badge--enviada">Pendiente</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="tabla__acciones">
                                    <?php if ($a['aplicada']): ?>
                                    <form method="POST" action="/admin/migraciones/olvidar"
                                          onsubmit="return confirm('Quitar <?= htmlspecialchars($a['nombre'], ENT_QUOTES) ?> de la lista de aplicadas? Vuelve a quedar pendiente, pero lo que ya hizo en la base no se deshace.')">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="archivo" value="<?= htmlspecialchars($a['nombre']) ?>">
                                        <button type="submit" class="btn btn--sm btn--outline">Marcar pendiente</button>
                                    </form>
                                    <?php else: ?>
                                    <form method="POST" action="/admin/migraciones">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="accion" value="marcar">
                                        <input type="hidden" name="archivo" value="<?= htmlspecialchars($a['nombre']) ?>">
                                        <button type="submit" class="btn btn--sm btn--outline">Ya estaba aplicada</button>
                                    </form>
                                    <form method="POST" action="/admin/migraciones"
                                          onsubmit="return confirm('Ejecutar <?= htmlspecialchars($a['nombre'], ENT_QUOTES) ?> contra la base? Haz un respaldo antes si no lo tienes.')">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="archivo" value="<?= htmlspecialchars($a['nombre']) ?>">
                                        <button type="submit" class="btn btn--sm btn--verde"><i class="fas fa-play"></i> Ejecutar</button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>

    </main>
</div>

<style>
.mig-aviso {
    padding: 1rem 1.5rem; background: #fffbeb;
    border-bottom: 1px solid #fde68a; font-size: 0.84rem;
    color: #78350f; line-height: 1.55;
}
.mig-aviso p + p { margin-top: 0.5rem; }
.mig-aviso code, .admin-panel__header code { font-size: 0.82em; }
.mig-vacio { padding: 2rem 1.5rem; text-align: center; color: var(--gris); }
.mig-vacio i { display: block; font-size: 2rem; margin-bottom: 0.5rem; }
.mig-cambio { color: #b45309; }
.mig-error { margin-bottom: 1.5rem; border-left: 4px solid #dc2626; }
.mig-error pre {
    margin: 0; padding: 1rem 1.5rem; overflow-x: auto;
    font-size: 0.8rem; line-height: 1.5; color: #991b1b; white-space: pre-wrap;
}
.tabla__acciones form { margin: 0; }
</style>
