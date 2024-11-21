<?php
require 'functions.php';

$permisos = ['Administrador', 'Profesor', 'Padre'];
permisos($permisos);

// consulta las materias
$materias = $conn->prepare("SELECT * FROM materias");
$materias->execute();
$materias = $materias->fetchAll();

// consulta de grados
$grados = $conn->prepare("SELECT * FROM grados");
$grados->execute();
$grados = $grados->fetchAll();

// consulta las secciones
$secciones = $conn->prepare("SELECT * FROM secciones");
$secciones->execute();
$secciones = $secciones->fetchAll();
?>

<html>
<head>
    <title>SED</title>
    <meta name="description" content="Registro de Notas del Centro Escolar Profesor Lennin" />
    <link rel="stylesheet" href="css/style.css" />
</head>
<body>
<div class="header">
    <h1>Consultas</h1>
    <h3>Usuario:  <?php echo $_SESSION["username"] ?></h3>
</div>

<nav>
    <ul>
        <li><a href="inicio.view.php">Inicio</a></li>
        <li><a href="alumnos.view.php">Registro de Alumnos</a></li>
        <li><a href="listadoalumnos.view.php">Listado de Alumnos</a></li>
        <li><a href="notas.view.php">Registro de Notas</a></li>
        <li class="active"><a href="listadonotas.view.php">Consulta de Notas</a></li>
        <li class="right"><a href="logout.php">Salir</a></li>
    </ul>
</nav>

<div class="body">
    <div class="panel">
        <h3>Consulta de Notas</h3>

        <?php
        if (!isset($_GET['consultar'])) {
            ?>
            <p>Seleccione el grado, la materia y la sección</p>
            <form method="get" class="form" action="listadonotas.view.php">
                <label>Seleccione el Grado</label><br>
                <select name="grado" required>
                    <?php foreach ($grados as $grado): ?>
                        <option value="<?php echo $grado['id'] ?>"><?php echo $grado['nombre'] ?></option>
                    <?php endforeach; ?>
                </select>
                <br><br>

                <label>Seleccione la Materia</label><br>
                <select name="materia" required>
                    <?php foreach ($materias as $materia): ?>
                        <option value="<?php echo $materia['id'] ?>"><?php echo $materia['nombre'] ?></option>
                    <?php endforeach; ?>
                </select>

                <br><br>
                <label>Seleccione la Sección</label><br><br>
                <?php foreach ($secciones as $seccion): ?>
                    <input type="radio" name="seccion" required value="<?php echo $seccion['id'] ?>">Sección <?php echo $seccion['nombre'] ?>
                <?php endforeach; ?>

                <br><br>
                <button type="submit" name="consultar" value="1">Consultar Notas</button>
                <br><br>
            </form>
            <?php
        }
        ?>

        <hr>

        <?php
        if (isset($_GET['consultar'])) {
            $id_materia = $_GET['materia'];
            $id_grado = $_GET['grado'];
            $id_seccion = $_GET['seccion'];

            // Extrayendo el número de evaluaciones para la materia seleccionada
            $num_eval = $conn->prepare("SELECT num_evaluaciones FROM materias WHERE id = :id_materia");
            $num_eval->execute([':id_materia' => $id_materia]);
            $num_eval = $num_eval->fetch();
            $num_eval = $num_eval['num_evaluaciones'];

            // Consulta los alumnos y sus notas
            $sqlalumnos = $conn->prepare("
                SELECT 
                    a.id, 
                    a.num_lista, 
                    a.apellidos, 
                    a.nombres, 
                    COALESCE(AVG(b.nota), 0) AS promedio, 
                    STRING_AGG(b.observaciones, ', ') AS observaciones
                FROM alumnos AS a
                LEFT JOIN notas AS b 
                    ON a.id = b.id_alumno
                WHERE a.id_grado = :id_grado AND a.id_seccion = :id_seccion
                GROUP BY a.id, a.num_lista, a.apellidos, a.nombres
            ");
            $sqlalumnos->execute([
                ':id_grado' => $id_grado,
                ':id_seccion' => $id_seccion
            ]);
            $alumnos = $sqlalumnos->fetchAll();
            $num_alumnos = $sqlalumnos->rowCount();
            $promediototal = 0.0;
            ?>

            <br>
            <a href="listadonotas.view.php"><strong><< Volver</strong></a>
            <br><br>

            <table class="table" cellpadding="0" cellspacing="0">
                <tr>
                    <th>No de lista</th>
                    <th>Apellidos</th>
                    <th>Nombres</th>
                    <?php
                    for ($i = 1; $i <= $num_eval; $i++) {
                        echo '<th>Nota ' . $i . '</th>';
                    }
                    ?>
                    <th>Promedio</th>
                    <th>Observaciones</th>
                </tr>

                <?php foreach ($alumnos as $alumno): ?>
                    <tr>
                        <td align="center"><?php echo $alumno['num_lista'] ?></td>
                        <td><?php echo $alumno['apellidos'] ?></td>
                        <td><?php echo $alumno['nombres'] ?></td>

                        <?php
                        // Crear array con notas vacías
                        $notas_array = array_fill(0, $num_eval, 0.00);

                        // Consultar las notas específicas del alumno
                        $notas = $conn->prepare("
                            SELECT nota 
                            FROM notas 
                            WHERE id_alumno = :id_alumno AND id_materia = :id_materia
                        ");
                        $notas->execute([
                            ':id_alumno' => $alumno['id'],
                            ':id_materia' => $id_materia
                        ]);
                        $notas = $notas->fetchAll(PDO::FETCH_COLUMN);

                        // Reemplazar valores en las posiciones correctas
                        foreach ($notas as $key => $nota) {
                            $notas_array[$key] = $nota;
                        }

                        // Imprimir las notas
                        foreach ($notas_array as $nota) {
                            echo '<td align="center">' . number_format($nota, 2) . '</td>';
                        }

                        // Imprimir promedio
                        echo '<td align="center">' . number_format($alumno['promedio'], 2) . '</td>';

                        // Imprimir observaciones
                        echo '<td>' . ($alumno['observaciones'] ? $alumno['observaciones'] : 'Sin observaciones') . '</td>';
                        ?>
                    </tr>
                <?php endforeach; ?>

                <tr>
                    <td colspan="<?php echo $num_eval + 3; ?>" align="center">
                        <?php
                        // Promedio general
                        echo number_format($promediototal / $num_alumnos, 2);
                        ?>
                    </td>
                </tr>
            </table>
            <br>
        <?php
        }
        ?>
    </div>
</div>

<footer>
    <p></p>
</footer>

</body>

<script>
    <?php
    for ($i = 0; $i < $num_eval; $i++) {
        echo 'var values' . $i . ' = [];
        var promedio' . $i . ';
        var valor' . $i . ' = 0;
        var nota' . $i . ' = document.getElementsByName("nota' . $i . '");
        for (var i = 0; i < nota' . $i . '.length; i++) {
            valor' . $i . ' += parseFloat(nota' . $i . '[i].value);
        }
        promedio' . $i . ' = (valor' . $i . ' / parseFloat(nota' . $i . '.length));
        document.getElementById("promedio' . $i . '").innerHTML = (promedio' . $i . ').toFixed(2);';
    }
    ?>
</script>

</html>
