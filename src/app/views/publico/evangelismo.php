<?php $titulo = 'Registro de evangelismo'; ?>

<section class="evg-pub">
    <div class="evg-pub__card">

        <div class="evg-pub__header">
            <img src="/public/assets/logos/logo-mi-t.png" alt="Misioneros Integrales">
            <h1>Registro de evangelismo</h1>
            <p>Personas alcanzadas en campo</p>
        </div>

        <?php if (!empty($_SESSION['flash'])): ?>
        <div class="alerta alerta--<?= $_SESSION['flash']['tipo'] ?>">
            <i class="fas fa-<?= $_SESSION['flash']['tipo'] === 'exito' ? 'check-circle' : 'exclamation-circle' ?>"></i>
            <?= htmlspecialchars($_SESSION['flash']['msg']) ?>
        </div>
        <?php unset($_SESSION['flash']); endif; ?>

        <?php if (!$habilitado): ?>
        <p class="evg-pub__aviso">
            <i class="fas fa-lock"></i>
            El registro no está habilitado en este momento. Pídele el acceso a la coordinación del programa.
        </p>

        <?php elseif (!$dentro): ?>
        <form method="POST" action="/evangelismo/pin">
            <?= csrf_field() ?>
            <div class="form-grupo">
                <label>Tu nombre <span class="req">*</span></label>
                <input type="text" name="responsable" maxlength="150" required
                       value="<?= htmlspecialchars($responsable) ?>" placeholder="Quién está registrando">
            </div>
            <div class="form-grupo">
                <label>PIN de acceso <span class="req">*</span></label>
                <input type="password" name="pin" required autocomplete="off">
            </div>
            <button type="submit" class="btn btn--verde btn--block btn--lg">
                <i class="fas fa-unlock"></i> Entrar
            </button>
        </form>

        <?php else: ?>
        <div class="evg-pub__sesion">
            <span><i class="fas fa-user"></i> <?= htmlspecialchars($responsable) ?></span>
            <form method="POST" action="/evangelismo/salir">
                <?= csrf_field() ?>
                <button type="submit" class="evg-pub__salir">Salir</button>
            </form>
        </div>

        <form method="POST" action="/evangelismo">
            <?= csrf_field() ?>
            <?php include __DIR__ . '/../partials/evangelismo_campos.php'; ?>
            <div class="form-grupo">
                <label>Quién hizo el contacto</label>
                <input type="text" name="responsable" maxlength="150" value="<?= htmlspecialchars($responsable) ?>">
            </div>
            <button type="submit" class="btn btn--verde btn--block btn--lg">
                <i class="fas fa-save"></i> Registrar persona
            </button>
        </form>
        <?php endif; ?>

    </div>
</section>

<style>
.evg-pub {
    background: var(--gris-claro);
    padding: 2.5rem 1rem 3rem;
    min-height: calc(100vh - 73px);
}
.evg-pub__card {
    max-width: 560px; margin: 0 auto;
    background: var(--blanco); border-radius: var(--radio-lg);
    box-shadow: var(--sombra); padding: 2rem 1.5rem;
}
.evg-pub__header { text-align: center; margin-bottom: 1.5rem; }
.evg-pub__header img { display: block; height: 64px; margin: 0 auto 0.75rem; }
.evg-pub__header h1 { font-size: 1.4rem; color: var(--verde-dark); margin-bottom: 0.25rem; }
.evg-pub__header p { color: var(--gris); font-size: 0.9rem; }
.evg-pub__aviso {
    text-align: center; color: var(--gris-dark); line-height: 1.6;
    background: #f8fafc; border-radius: var(--radio); padding: 1.25rem;
}
.evg-pub__aviso i { display: block; font-size: 1.5rem; color: var(--gris); margin-bottom: 0.5rem; }
.evg-pub__sesion {
    display: flex; justify-content: space-between; align-items: center;
    padding: 0.6rem 0.9rem; margin-bottom: 1.25rem;
    background: #f8fafc; border-radius: var(--radio);
    font-size: 0.85rem; color: var(--gris-dark);
}
.evg-pub__sesion form { margin: 0; }
.evg-pub__salir {
    background: none; border: none; cursor: pointer;
    color: var(--verde); font-weight: 700; font-family: inherit;
}
@media (max-width: 640px) {
    .evg-pub { padding: 1rem 0.75rem 2rem; }
    .evg-pub__card { padding: 1.5rem 1rem; }
}
</style>
