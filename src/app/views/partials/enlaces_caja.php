<?php
/**
 * Enlaces para que coordinadores y responsables carguen gastos sin cuenta.
 * Va al pie de la Caja del admin. El enlace completo solo se ve justo al
 * crearlo o regenerarlo: del token se guarda el hash.
 */
?>
<div class="admin-panel enlaces-caja" id="enlaces">
    <h3 class="caja-titulo">Enlaces para cargar gastos</h3>
    <p class="caja-vacio" style="margin-bottom:1rem">
        Cada persona recibe el suyo. Con él solo puede cargar gastos y ver lo que cargó en la semana;
        no ve saldos ni nada más del sistema.
    </p>

    <?php if (!empty($enlace_nuevo)): ?>
    <div class="enlace-nuevo">
        <strong>Enlace de <?= htmlspecialchars($enlace_nuevo['nombre']) ?></strong>
        <span>Cópialo ahora: por seguridad no se vuelve a mostrar. Si se pierde, se regenera.</span>
        <input type="text" readonly value="<?= htmlspecialchars($enlace_nuevo['url']) ?>" id="enlace-url" onclick="this.select()">
        <div class="enlace-botones">
            <button type="button" class="btn btn--sm btn--outline" id="enlace-copiar">
                <i class="fas fa-copy"></i> Copiar
            </button>
            <a class="btn btn--sm btn--primario" target="_blank" rel="noopener"
               href="https://wa.me/?text=<?= rawurlencode("Este es tu enlace para cargar los gastos del programa Misioneros Integrales. Ábrelo en el teléfono y guárdalo en la pantalla de inicio:\n" . $enlace_nuevo['url']) ?>">
                <i class="fab fa-whatsapp"></i> Mandar por WhatsApp
            </a>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($enlaces): ?>
    <ul class="caja-lista">
        <?php foreach ($enlaces as $e): ?>
        <li class="<?= $e['activo'] ? '' : 'caja-no-cuenta' ?>">
            <i class="fas fa-link"></i>
            <div>
                <strong><?= htmlspecialchars($e['nombre']) ?><?= $e['activo'] ? '' : ' · desactivado' ?></strong>
                <span>
                    <?= (int) $e['gastos'] ?> gasto<?= (int) $e['gastos'] === 1 ? '' : 's' ?> · <?= usd($e['total_usd']) ?>
                    · <?= $e['ultimo_uso'] ? 'último uso ' . date('d/m H:i', strtotime($e['ultimo_uso'])) : 'sin usar' ?>
                </span>
            </div>
            <form method="POST" action="/admin/caja/enlace" class="enlace-acciones">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int) $e['id'] ?>">
                <button name="accion" value="regenerar" class="btn btn--xs btn--outline" title="Enlace nuevo; el anterior deja de servir"
                        onclick="return confirm('El enlace actual de <?= htmlspecialchars(addslashes($e['nombre']), ENT_QUOTES) ?> dejará de servir. ¿Generar uno nuevo?')">
                    <i class="fas fa-rotate"></i>
                </button>
                <?php if ($e['activo']): ?>
                <button name="accion" value="desactivar" class="btn btn--xs btn--outline" title="Desactivar"
                        onclick="return confirm('<?= htmlspecialchars(addslashes($e['nombre']), ENT_QUOTES) ?> no podrá cargar más gastos. ¿Desactivar?')">
                    <i class="fas fa-ban"></i>
                </button>
                <?php else: ?>
                <button name="accion" value="activar" class="btn btn--xs btn--outline" title="Volver a activar">
                    <i class="fas fa-check"></i>
                </button>
                <?php endif; ?>
            </form>
        </li>
        <?php endforeach; ?>
    </ul>
    <?php endif; ?>

    <form method="POST" action="/admin/caja/enlace" class="enlace-crear">
        <?= csrf_field() ?>
        <input type="hidden" name="accion" value="crear">
        <input type="text" name="nombre" required maxlength="100" placeholder="Nombre: Yohanna, responsable de traslados…">
        <button type="submit" class="btn btn--sm btn--primario"><i class="fas fa-plus"></i> Crear enlace</button>
    </form>
</div>

<style>
.enlaces-caja { margin-top: 1rem; }
.enlace-nuevo {
    background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px;
    padding: .9rem; margin-bottom: 1rem; display: grid; gap: .5rem;
}
.enlace-nuevo span  { font-size: .8rem; color: var(--gris); }
.enlace-nuevo input { width: 100%; font-size: .8rem; padding: .5rem; border: 1px solid #d1d5db; border-radius: 6px; }
.enlace-botones { display: flex; gap: .5rem; flex-wrap: wrap; }
.enlace-acciones { display: flex; gap: .3rem; }
.enlaces-caja .btn--xs {
    padding: .3rem .6rem; font-size: .75rem;
    display: inline-flex; align-items: center; gap: .3rem;
}
.enlace-crear { display: flex; gap: .5rem; margin-top: 1rem; }
.enlace-crear input { flex: 1; min-width: 0; padding: .5rem .7rem; border: 1px solid #d1d5db; border-radius: 6px; font: inherit; }
</style>

<?php if (!empty($enlace_nuevo)): ?>
<script>
document.getElementById('enlace-copiar').addEventListener('click', function () {
    const campo = document.getElementById('enlace-url');
    campo.select();
    (navigator.clipboard ? navigator.clipboard.writeText(campo.value) : Promise.reject())
        .catch(() => document.execCommand('copy'))
        .finally(() => { this.innerHTML = '<i class="fas fa-check"></i> Copiado'; });
});
</script>
<?php endif; ?>
