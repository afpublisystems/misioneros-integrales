<?php $titulo = 'Estadísticas de Impacto'; ?>

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
                <h1>Estadísticas de Impacto</h1>
                <p>Contadores visibles en la página pública de Impacto</p>
            </div>
            <a href="/impacto" target="_blank" class="btn btn--outline">
                <i class="fas fa-external-link-alt"></i> Ver en sitio público
            </a>
        </div>

        <?php if ($_SESSION['usuario_rol'] !== 'admin'): ?>
        <div class="alerta alerta--info">
            <i class="fas fa-info-circle"></i>
            Solo los administradores pueden editar estos valores.
        </div>
        <?php endif; ?>

        <!-- Previsualización pública ─────────────────────── -->
        <div class="admin-panel" style="margin-bottom: 1.5rem;">
            <div class="admin-panel__header">
                <h2><i class="fas fa-eye"></i> Vista previa — Como lo ve el público</h2>
            </div>
            <div class="stats-preview-grid">
                <?php foreach ($stats as $s): ?>
                <?php if (!$s['activo']) continue; ?>
                <div class="stats-preview-card">
                    <div class="stats-preview-card__icono">
                        <i class="fas <?= htmlspecialchars($s['icono'] ?? 'fa-chart-bar') ?>"></i>
                    </div>
                    <div class="stats-preview-card__valor" id="prev-<?= $s['id'] ?>">
                        <?= number_format($s['valor']) ?>
                    </div>
                    <div class="stats-preview-card__label">
                        <?= htmlspecialchars($s['etiqueta']) ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Formulario de edición ──────────────────────── -->
        <?php if ($_SESSION['usuario_rol'] === 'admin'): ?>
        <form method="POST" action="/admin/estadisticas">
            <?= csrf_field() ?>

            <div class="admin-panel">
                <div class="admin-panel__header">
                    <h2><i class="fas fa-edit"></i> Editar Contadores</h2>
                </div>

                <div class="stats-edit-grid">
                    <?php foreach ($stats as $s): ?>
                    <div class="stats-edit-card <?= !$s['activo'] ? 'stats-edit-card--inactiva' : '' ?>">
                        <div class="stats-edit-card__icono">
                            <i class="fas <?= htmlspecialchars($s['icono'] ?? 'fa-chart-bar') ?>"></i>
                        </div>
                        <div class="stats-edit-card__info">
                            <label class="stats-edit-card__label">
                                <?= htmlspecialchars($s['etiqueta']) ?>
                            </label>
                            <input
                                type="number"
                                name="valor[<?= $s['id'] ?>]"
                                value="<?= (int) $s['valor'] ?>"
                                min="0"
                                max="99999"
                                class="stats-edit-card__input"
                                oninput="actualizarPreview(<?= $s['id'] ?>, this.value)"
                                <?= !$s['activo'] ? 'disabled' : '' ?>
                            >
                            <?php if (!$s['activo']): ?>
                            <small class="texto-gris">Inactivo — no visible al público</small>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="stats-edit-footer">
                    <small class="texto-gris">
                        <i class="fas fa-info-circle"></i>
                        Los cambios se reflejan inmediatamente en el sitio público al guardar.
                    </small>
                    <button type="submit" class="btn btn--verde">
                        <i class="fas fa-save"></i> Guardar estadísticas
                    </button>
                </div>
            </div>

        </form>
        <?php endif; ?>

        <!-- Historial / info ──────────────────────────── -->
        <div class="admin-panel">
            <div class="admin-panel__header">
                <h2><i class="fas fa-info-circle"></i> Sobre estas estadísticas</h2>
            </div>
            <div class="stats-info-content">
                <p>Estos contadores representan el <strong>impacto histórico</strong> del programa Misioneros Integrales y se actualizan manualmente al finalizar cada ciclo.</p>
                <ul class="stats-info-list">
                    <li><i class="fas fa-user-graduate"></i> <strong>Misioneros Capacitados:</strong> Total de graduados en todos los ciclos del programa.</li>
                    <li><i class="fas fa-church"></i> <strong>Iglesias Plantadas:</strong> Congregaciones nuevas fundadas por egresados del programa.</li>
                    <li><i class="fas fa-briefcase"></i> <strong>Microempresas Misioneras:</strong> Emprendimientos sostenibles que financian obra misionera.</li>
                    <li><i class="fas fa-map-marker-alt"></i> <strong>Estados Alcanzados:</strong> Estados de Venezuela donde hay presencia activa de misioneros egresados.</li>
                </ul>
                <p class="stats-ultima-actualizacion">
                    <i class="fas fa-clock"></i>
                    Última modificación del sistema: <?= date('d/m/Y H:i') ?>
                </p>
            </div>
        </div>

    </main>
</div>

<script>
function actualizarPreview(id, valor) {
    const el = document.getElementById('prev-' + id);
    if (el) {
        const num = parseInt(valor) || 0;
        el.textContent = num.toLocaleString('es-VE');
    }
}
</script>

<style>
/* Estadísticas — previsualización pública */
.stats-preview-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 1.5rem;
    padding: 1.5rem;
    background: linear-gradient(135deg, var(--verde-dark) 0%, var(--verde) 100%);
    border-radius: 0.5rem;
    margin: 1rem;
}
.stats-preview-card {
    text-align: center;
    color: white;
}
.stats-preview-card__icono {
    font-size: 2rem;
    margin-bottom: 0.5rem;
    opacity: 0.85;
}
.stats-preview-card__valor {
    font-size: 2.5rem;
    font-weight: 900;
    line-height: 1;
    color: var(--dorado);
}
.stats-preview-card__label {
    font-size: 0.8rem;
    opacity: 0.9;
    margin-top: 0.4rem;
    line-height: 1.3;
}

/* Estadísticas — formulario edición */
.stats-edit-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 1rem;
    padding: 1rem 1.5rem;
}
.stats-edit-card {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    border: 1px solid #e2e8f0;
    border-radius: 0.5rem;
    background: #f8fafc;
    transition: border-color 0.2s;
}
.stats-edit-card:focus-within { border-color: var(--verde); }
.stats-edit-card--inactiva { opacity: 0.5; }
.stats-edit-card__icono {
    width: 2.5rem;
    height: 2.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--verde-light);
    color: var(--verde);
    border-radius: 0.4rem;
    font-size: 1.1rem;
    flex-shrink: 0;
}
.stats-edit-card__info { flex: 1; }
.stats-edit-card__label {
    display: block;
    font-size: 0.78rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.35rem;
}
.stats-edit-card__input {
    width: 100%;
    padding: 0.45rem 0.65rem;
    border: 1px solid #d1d5db;
    border-radius: 0.35rem;
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--verde-dark);
    background: white;
}
.stats-edit-card__input:focus {
    outline: none;
    border-color: var(--verde);
    box-shadow: 0 0 0 3px rgba(22,163,74,0.1);
}
.stats-edit-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem 1.5rem;
    border-top: 1px solid #f1f5f9;
    margin-top: 0.5rem;
}

/* Info */
.stats-info-content {
    padding: 1rem 1.5rem;
    font-size: 0.9rem;
    line-height: 1.7;
}
.stats-info-list {
    list-style: none;
    padding: 0;
    margin: 0.75rem 0;
}
.stats-info-list li {
    padding: 0.4rem 0;
    border-bottom: 1px solid #f1f5f9;
}
.stats-info-list li:last-child { border-bottom: none; }
.stats-info-list li i {
    color: var(--verde);
    width: 1.2rem;
    margin-right: 0.5rem;
}
.stats-ultima-actualizacion {
    font-size: 0.78rem;
    color: var(--gris);
    margin-top: 1rem;
}
</style>
