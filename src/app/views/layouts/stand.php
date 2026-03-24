<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Registro Rápido | Misioneros Integrales</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- CSS Principal (variables y componentes base) -->
    <link rel="stylesheet" href="/public/css/app.css">

    <style>
        /* ── Reset kiosk ─────────────────────────────── */
        *, *::before, *::after { box-sizing: border-box; }

        html, body {
            margin: 0; padding: 0;
            height: 100%; overflow: hidden;
            font-family: 'Montserrat', sans-serif;
            background: var(--verde-dark, #083c2b);
        }

        /* ── Layout principal ─────────────────────────── */
        .stand-wrap {
            display: grid;
            grid-template-columns: 420px 1fr;
            height: 100vh;
        }

        /* ── Panel izquierdo (branding) ───────────────── */
        .stand-brand {
            position: relative;
            background:
                linear-gradient(160deg, rgba(8,60,43,0.97) 0%, rgba(22,122,94,0.92) 100%),
                url('/public/assets/logos/logo-mi-completo-t.png') center/cover no-repeat;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 3rem 2.5rem;
            gap: 2rem;
            text-align: center;
            overflow: hidden;
        }
        .stand-brand::before {
            content: '';
            position: absolute; inset: 0;
            background: radial-gradient(ellipse at 50% 40%, rgba(206,162,55,0.08) 0%, transparent 70%);
            pointer-events: none;
        }

        .stand-brand__logo {
            width: 240px;
            filter: drop-shadow(0 6px 24px rgba(0,0,0,0.4));
            position: relative;
        }

        .stand-brand__call {
            position: relative;
        }
        .stand-brand__call h1 {
            font-size: 1.5rem;
            font-weight: 900;
            color: #fff;
            margin: 0 0 0.5rem;
            line-height: 1.2;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .stand-brand__call p {
            font-size: 0.9rem;
            color: rgba(255,255,255,0.7);
            margin: 0;
            line-height: 1.5;
        }

        /* ── Contador de cupos ────────────────────────── */
        .stand-cupos {
            position: relative;
            background: rgba(255,255,255,0.07);
            border: 1px solid rgba(206,162,55,0.35);
            border-radius: 20px;
            padding: 1.5rem 2rem;
            width: 100%;
        }
        .stand-cupos__label {
            font-size: 0.72rem;
            font-weight: 700;
            color: rgba(255,255,255,0.5);
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 0.5rem;
        }
        .stand-cupos__num {
            font-size: 3.5rem;
            font-weight: 900;
            color: var(--dorado, #cea237);
            line-height: 1;
        }
        .stand-cupos__sub {
            font-size: 0.82rem;
            color: rgba(255,255,255,0.6);
            margin-top: 0.3rem;
        }
        .stand-cupos__bar {
            margin-top: 1rem;
            height: 8px;
            border-radius: 4px;
            background: rgba(255,255,255,0.12);
            overflow: hidden;
        }
        .stand-cupos__bar-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--dorado, #cea237), #f5c842);
            border-radius: 4px;
            transition: width 0.6s ease;
        }
        .stand-cupos--urgente .stand-cupos__num { color: #f87171; }
        .stand-cupos--urgente .stand-cupos__bar-fill { background: linear-gradient(90deg, #ef4444, #f87171); }

        /* ── QR / instrucción ─────────────────────────── */
        .stand-qr {
            position: relative;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 16px;
            padding: 1.2rem 1.5rem;
            width: 100%;
        }
        .stand-qr p {
            margin: 0;
            font-size: 0.8rem;
            color: rgba(255,255,255,0.6);
            line-height: 1.5;
        }
        .stand-qr strong {
            display: block;
            color: rgba(255,255,255,0.9);
            font-size: 0.85rem;
            margin-bottom: 0.3rem;
        }

        .stand-brand__orgs {
            display: flex;
            gap: 1.5rem;
            align-items: center;
            position: relative;
        }
        .stand-brand__orgs img {
            height: 26px;
            filter: brightness(0) invert(1);
            opacity: 0.5;
        }

        /* ── Panel derecho (formulario) ───────────────── */
        .stand-form-panel {
            background: var(--crema, #faf8f3);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow-y: auto;
            padding: 2.5rem 3rem;
        }

        .stand-form-wrap {
            width: 100%;
            max-width: 580px;
        }

        .stand-form__header {
            margin-bottom: 2rem;
        }
        .stand-form__header h2 {
            font-size: 2rem;
            font-weight: 900;
            color: var(--verde, #167a5e);
            margin: 0 0 0.4rem;
        }
        .stand-form__header p {
            font-size: 1rem;
            color: var(--gris, #6b7280);
            margin: 0;
        }

        /* Inputs grandes para tablet */
        .stand-form .form-grupo {
            margin-bottom: 1.25rem;
        }
        .stand-form .form-grupo label {
            font-size: 0.9rem;
            font-weight: 700;
            color: var(--gris-dark, #374151);
            margin-bottom: 0.4rem;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }
        .stand-form .form-grupo label i {
            color: var(--verde, #167a5e);
        }
        .stand-form input[type="text"],
        .stand-form input[type="email"],
        .stand-form input[type="password"] {
            width: 100%;
            height: 56px;
            font-size: 1rem;
            padding: 0 1.1rem;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            background: #fff;
            font-family: inherit;
            font-weight: 500;
            color: var(--gris-dark, #374151);
            transition: border-color 0.2s, box-shadow 0.2s;
            -webkit-appearance: none;
        }
        .stand-form input:focus {
            outline: none;
            border-color: var(--verde, #167a5e);
            box-shadow: 0 0 0 4px rgba(22,122,94,0.12);
        }

        .stand-form .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .stand-form .input-pass-wrap {
            position: relative;
        }
        .stand-form .input-pass-wrap input {
            padding-right: 3.5rem;
        }
        .stand-form .toggle-pass {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: var(--gris, #6b7280);
            font-size: 1.1rem;
            padding: 0.5rem;
            transition: color 0.2s;
        }
        .stand-form .toggle-pass:hover { color: var(--verde, #167a5e); }

        /* Aviso movilidad */
        .stand-movilidad {
            display: flex;
            gap: 0.85rem;
            align-items: flex-start;
            background: rgba(206,162,55,0.1);
            border: 1px solid rgba(206,162,55,0.35);
            border-left: 4px solid var(--dorado, #cea237);
            border-radius: 12px;
            padding: 1rem 1.1rem;
            font-size: 0.88rem;
            color: var(--gris-dark, #374151);
            margin-bottom: 1.25rem;
        }
        .stand-movilidad i { color: var(--dorado, #cea237); flex-shrink: 0; margin-top: 2px; font-size: 1.1rem; }
        .stand-movilidad strong { color: #92700a; }

        /* Checkbox */
        .stand-check {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            font-size: 0.9rem;
            color: var(--gris-dark, #374151);
            cursor: pointer;
            margin-bottom: 1.5rem;
        }
        .stand-check input[type="checkbox"] {
            width: 22px;
            height: 22px;
            flex-shrink: 0;
            margin-top: 1px;
            accent-color: var(--verde, #167a5e);
            cursor: pointer;
        }
        .stand-check a { color: var(--verde, #167a5e); font-weight: 700; }

        /* Botón principal */
        .stand-btn {
            width: 100%;
            height: 64px;
            font-size: 1.15rem;
            font-weight: 800;
            font-family: inherit;
            background: var(--verde, #167a5e);
            color: #fff;
            border: none;
            border-radius: 14px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            transition: background 0.2s, transform 0.1s;
            letter-spacing: 0.5px;
        }
        .stand-btn:hover  { background: #0f5a45; }
        .stand-btn:active { transform: scale(0.98); }

        /* Login link */
        .stand-ya-cuenta {
            margin-top: 1.25rem;
            text-align: center;
            font-size: 0.9rem;
            color: var(--gris, #6b7280);
        }
        .stand-ya-cuenta a {
            color: var(--verde, #167a5e);
            font-weight: 700;
            text-decoration: none;
        }
        .stand-ya-cuenta a:hover { text-decoration: underline; }

        /* Errores */
        .stand-alerta {
            background: #fef2f2;
            border: 1px solid #fca5a5;
            border-left: 4px solid #ef4444;
            border-radius: 12px;
            padding: 1rem 1.1rem;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            color: #991b1b;
            display: flex;
            gap: 0.75rem;
            align-items: flex-start;
        }
        .stand-alerta i { flex-shrink: 0; margin-top: 2px; }
        .stand-alerta p { margin: 0; line-height: 1.5; }

        /* ── Idle reset overlay ───────────────────────── */
        .stand-idle-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(8,60,43,0.96);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 2rem;
            text-align: center;
            padding: 2rem;
        }
        .stand-idle-overlay.visible { display: flex; }
        .stand-idle-overlay h2 {
            font-size: 2.2rem;
            font-weight: 900;
            color: #fff;
            margin: 0;
        }
        .stand-idle-overlay p {
            font-size: 1.1rem;
            color: rgba(255,255,255,0.7);
            margin: 0;
        }
        .stand-idle-countdown {
            font-size: 5rem;
            font-weight: 900;
            color: var(--dorado, #cea237);
            line-height: 1;
        }
        .stand-idle-btn {
            padding: 1rem 3rem;
            font-size: 1.1rem;
            font-weight: 800;
            font-family: inherit;
            background: var(--verde, #167a5e);
            color: #fff;
            border: none;
            border-radius: 14px;
            cursor: pointer;
            transition: background 0.2s;
        }
        .stand-idle-btn:hover { background: #0f5a45; }
    </style>
</head>
<body>

<div class="stand-wrap">
    <?= $contenido ?>
</div>

<!-- Overlay de inactividad -->
<div class="stand-idle-overlay" id="idleOverlay">
    <img src="/public/assets/logos/logo-mi-completo-t.png" alt="Misioneros Integrales" style="width:200px;filter:drop-shadow(0 4px 16px rgba(0,0,0,0.4))">
    <div>
        <h2>¿Sigues ahí?</h2>
        <p>El formulario se reiniciará en</p>
    </div>
    <div class="stand-idle-countdown" id="idleCount">10</div>
    <button class="stand-idle-btn" onclick="resetIdle()">
        <i class="fas fa-hand-pointer"></i> Continuar
    </button>
</div>

<!-- JS Principal -->
<script src="/public/js/app.js"></script>

<script>
// ── Toggle contraseña ─────────────────────────────────
document.querySelectorAll('.toggle-pass').forEach(btn => {
    btn.addEventListener('click', () => {
        const input = btn.previousElementSibling;
        input.type  = input.type === 'password' ? 'text' : 'password';
        btn.querySelector('i').className = input.type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
    });
});

// ── Idle reset (3 minutos de inactividad) ─────────────
let idleTimer, countdownTimer;
const IDLE_MS = 3 * 60 * 1000; // 3 min
const overlay  = document.getElementById('idleOverlay');
const countEl  = document.getElementById('idleCount');

function startIdle() {
    clearTimeout(idleTimer);
    idleTimer = setTimeout(showIdleOverlay, IDLE_MS);
}

function showIdleOverlay() {
    overlay.classList.add('visible');
    let remaining = 10;
    countEl.textContent = remaining;
    countdownTimer = setInterval(() => {
        remaining--;
        countEl.textContent = remaining;
        if (remaining <= 0) {
            clearInterval(countdownTimer);
            window.location.reload();
        }
    }, 1000);
}

function resetIdle() {
    clearInterval(countdownTimer);
    overlay.classList.remove('visible');
    startIdle();
}

// Escuchar interacción del usuario
['touchstart', 'mousedown', 'keypress', 'scroll'].forEach(ev => {
    document.addEventListener(ev, () => {
        if (!overlay.classList.contains('visible')) startIdle();
    });
});

startIdle();

// ── Cupos: refresco cada 60 seg ───────────────────────
function refrescarCupos() {
    fetch('/api/cupos')
        .then(r => r.ok ? r.json() : null)
        .then(data => {
            if (!data) return;
            const numEl = document.getElementById('cuposNum');
            const barEl = document.getElementById('cuposBar');
            const subEl = document.getElementById('cuposSub');
            if (numEl) numEl.textContent = data.disponibles;
            if (barEl) {
                const pct = Math.round((data.inscritos / data.total) * 100);
                barEl.style.width = pct + '%';
            }
            if (subEl) subEl.textContent = `de ${data.total} cupos totales`;
            if (data.disponibles <= 5 && data.disponibles > 0) {
                document.querySelector('.stand-cupos')?.classList.add('stand-cupos--urgente');
            }
        })
        .catch(() => {});
}

setInterval(refrescarCupos, 60000);
</script>

</body>
</html>
