<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $titulo ?? 'Admin' ?> | Misioneros Integrales</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="/public/css/app.css?v=<?= filemtime(BASE_PATH . '/public/css/app.css') ?>">
    <?php if (!empty($es_app)): ?>
    <!-- Para instalar la caja en el teléfono como si fuera una app -->
    <link rel="manifest" href="/public/caja.webmanifest">
    <meta name="theme-color" content="#167a5e">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="Caja MI">
    <link rel="apple-touch-icon" href="/public/assets/icons/caja-192.png">
    <?php endif; ?>
</head>
<body class="body-admin">

    <!-- Sin navbar público ni footer — el sidebar del admin los reemplaza -->
    <?= $contenido ?>

    <script src="/public/js/app.js?v=<?= filemtime(BASE_PATH . '/public/js/app.js') ?>"></script>
</body>
</html>
