<?php

/**
 * Encabezado comun de todas las paginas: documento HTML5, metadatos,
 * logo, menu de navegacion y mensajes flash. [ABSTRACCION]
 *
 * Variables esperadas:
 * - string $titulo  titulo de la pagina
 * - string $seccion seccion activa del menu: panel | espacios | reservas | reporte
 */

$titulo = $titulo ?? 'Panel';
$seccion = $seccion ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($titulo) ?> · ReservaEspacios</title>
    <link rel="stylesheet" href="/css/estilos.css">
</head>
<body>
<header class="site-header">
    <div class="contenedor cabecera">
        <a class="logo" href="/index.php">
            <span class="logo-marca"><?= e($config['app']['institucion']) ?></span>
            <span class="logo-nombre">ReservaEspacios</span>
        </a>
        <nav class="nav-principal" aria-label="Navegacion principal">
            <ul>
                <li><a href="/index.php"<?= $seccion === 'panel' ? ' aria-current="page"' : '' ?>>Panel</a></li>
                <li><a href="/espacios/index.php"<?= $seccion === 'espacios' ? ' aria-current="page"' : '' ?>>Espacios</a></li>
                <li><a href="/reservas/index.php"<?= $seccion === 'reservas' ? ' aria-current="page"' : '' ?>>Reservas</a></li>
                <li><a href="/reporte.php"<?= $seccion === 'reporte' ? ' aria-current="page"' : '' ?>>Reporte</a></li>
            </ul>
        </nav>
    </div>
</header>

<main class="contenedor">
<?php require __DIR__ . '/../partials/mensajes.php'; ?>
