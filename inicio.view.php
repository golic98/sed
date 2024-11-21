<?php
require 'functions.php';
permisos(['Administrador', 'Profesor']);
?>
<!DOCTYPE html>
<html>
<head>
<title>SED - Inicio</title>
    <meta name="description" content="Registro de Notas del Centro Escolar Profesor Lennin" />
    <link rel="stylesheet" href="css/style.css" />
</head>
<body>
<div class="header">
    <h1>Bienvenido al Sistema</h1>
    <h3>Usuario: <?php echo $_SESSION['username']; ?></h3>
</div>
<nav>
    <ul>
        <li><a href="inicio.view.php">Inicio</a></li>
        <li><a href="alumnos.view.php">Registro de Alumnos</a></li>
        <li><a href="notas.view.php">Registro de Notas</a></li>
        <li class="right"><a href="logout.php">Salir</a></li>
    </ul>
</nav>

<div class="body">
    <h2>Bienvenido al Sistema de Registro de Notas</h2>
</div>

<footer>
    <p>&copy; 2024 Sistema de Registro de Notas</p>
</footer>

</body>
</html>
