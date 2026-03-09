<?php $ruta_actual = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH); ?>
<footer class="footer">
    <div class="footer__container">

        <div class="footer__brand">
            <img src="/public/assets/logos/logo-mi-t.png" alt="Misioneros Integrales"
                 style="height:50px;width:auto;margin-bottom:1rem;border-radius:8px;"
                 onerror="this.style.display='none'">
            <p class="footer__lema">"De la formación a la misión:<br>Transforma, Multiplica e Impacta"</p>
            <p class="footer__versiculo">— Mateo 13:23</p>
        </div>

        <div class="footer__links">
            <h4>Programa</h4>
            <ul>
                <li><a href="/programa">El Programa</a></li>
                <li><a href="/requisitos">Requisitos</a></li>
                <li><a href="/galeria">Galería</a></li>
                <li><a href="/impacto">Impacto</a></li>
                <li><a href="/contacto">Contacto</a></li>
            </ul>
        </div>

        <div class="footer__contacto">
            <h4>Contacto</h4>
            <p><i class="fas fa-user"></i> José F. Ramos — 0424-5886540</p>
            <p><i class="fas fa-user"></i> Yohanna de Ramos — 0424-5905392</p>
            <p><i class="fas fa-envelope"></i> misionerosintegrales.cnbv@gmail.com</p>
            <div style="margin-top:1.25rem; display:flex; gap:0.75rem; align-items:center; flex-wrap:wrap;">
                <img src="/public/assets/logos/logo-cnbv-t.png" alt="CNBV"
                     style="height:32px;filter:brightness(0) invert(1);opacity:0.8"
                     onerror="this.style.display='none'">
                <img src="/public/assets/logos/logo-dime-t.png" alt="DIME"
                     style="height:32px;filter:brightness(0) invert(1);opacity:0.8"
                     onerror="this.style.display='none'">
            </div>
        </div>

        <div class="footer__postulate">
            <h4>¿Sientes el llamado?</h4>
            <p style="font-size:0.85rem;color:rgba(255,255,255,0.7);margin-bottom:1rem;line-height:1.5">
                Postúlate para el Ciclo 1<br>que inicia en <strong style="color:var(--dorado)">Julio 2026</strong>
            </p>
            <a href="/registro" class="btn btn--naranja btn--block">
                <i class="fas fa-user-plus"></i> Postularme
            </a>
        </div>

    </div>

    <div class="footer__bottom">
        <div class="footer__bottom-inner">
            <span>© <?= date('Y') ?> Programa de Formación Misioneros Integrales · CNBV / DIME · Venezuela</span>
            <span class="footer__credits">
                Diseñado y desarrollado por
                <a href="#" style="color:var(--dorado);font-weight:700">AF Publi-Systems</a>
            </span>
            <a href="/login" class="footer__admin-link">
                <i class="fas fa-lock"></i> Acceso Administradores
            </a>
        </div>
    </div>
</footer>

<style>
.footer__admin-link {
    font-size: 0.72rem; color: rgba(255,255,255,0.3);
    display: flex; align-items: center; gap: 0.3rem;
    transition: color 0.2s;
}
.footer__admin-link:hover { color: rgba(255,255,255,0.7); }
</style>

<style>
.footer__bottom-inner {
    display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;
}
.footer__credits { font-size: 0.8rem; }
.footer__container {
    grid-template-columns: 1.8fr 1fr 1.5fr 1.2fr !important;
}
@media(max-width:768px) {
    .footer__bottom-inner { flex-direction: column; text-align: center; }
}
</style>
