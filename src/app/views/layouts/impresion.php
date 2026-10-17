<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $titulo ?? 'Documento' ?> | Misioneros Integrales</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        /* Hoja suelta para imprimir o guardar en PDF: sin sidebar
           ni nada de la aplicación, solo el documento. */
        * { box-sizing: border-box; }
        body {
            font-family: 'Montserrat', system-ui, sans-serif;
            color: #1f2937; background: #f1f5f9;
            margin: 0; padding: 1.5rem; font-size: 13px;
        }
        .hoja {
            background: #fff; max-width: 950px; margin: 0 auto;
            padding: 2.5rem; border-radius: 6px;
            box-shadow: 0 1px 3px rgba(0,0,0,.12);
        }
        .hoja__acciones {
            max-width: 950px; margin: 0 auto 1rem;
            display: flex; gap: .5rem; justify-content: flex-end;
        }
        .hoja__acciones a, .hoja__acciones button {
            font-family: inherit; font-size: .8rem; font-weight: 600;
            padding: .5rem .9rem; border-radius: 5px; cursor: pointer;
            text-decoration: none; border: 1px solid #cbd5e1;
            background: #fff; color: #334155;
            display: inline-flex; align-items: center; gap: .4rem;
        }
        .hoja__acciones .primario { background: #167a5e; border-color: #167a5e; color: #fff; }

        @media print {
            body { background: #fff; padding: 0; font-size: 11px; }
            .hoja { box-shadow: none; max-width: none; padding: 0; border-radius: 0; }
            .hoja__acciones { display: none; }
            table { page-break-inside: auto; }
            tr    { page-break-inside: avoid; }
            thead { display: table-header-group; }
        }
    </style>
</head>
<body>
    <?= $contenido ?>
</body>
</html>
