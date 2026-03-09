<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $titulo ?? 'Admin' ?> | Misioneros Integrales</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="/public/css/app.css">
</head>
<body class="body-admin">

    <!-- Sin navbar público ni footer — el sidebar del admin los reemplaza -->
    <?= $contenido ?>

    <script src="/public/js/app.js"></script>
</body>
</html>
