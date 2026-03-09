<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $titulo ?? 'Misioneros Integrales' ?> | CNBV - DIME</title>
    <meta name="description" content="Programa de Formación de Misioneros Integrales - De la formación a la misión: Transforma, Multiplica e Impacta">

    <!-- Google Fonts: Montserrat -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- CSS Principal -->
    <link rel="stylesheet" href="/public/css/app.css">

    <?= $estilos_extra ?? '' ?>
</head>
<body class="<?= $clase_body ?? '' ?>">

    <!-- NAVBAR -->
    <?php require APP_PATH . '/views/partials/navbar.php'; ?>

    <!-- CONTENIDO PRINCIPAL -->
    <main id="contenido-principal">
        <?= $contenido ?>
    </main>

    <!-- FOOTER -->
    <?php require APP_PATH . '/views/partials/footer.php'; ?>

    <!-- JS Principal -->
    <script src="/public/js/app.js"></script>
    <?= $scripts_extra ?? '' ?>

</body>
</html>
