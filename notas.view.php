<!DOCTYPE html>
<?php
require 'functions.php';
// arreglo de permisos
$permisos = ['Administrador', 'Profesor'];
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
        <h1>Guardar una nota</h1>
        <h3>Usuario:  <?php echo $_SESSION["username"] ?></h3>
</div>
<nav>
    <ul>
        <li><a href="inicio.view.php">Inicio</a> </li>
        <li><a href="alumnos.view.php">Registro de Alumnos</a> </li>
        <li><a href="listadoalumnos.view.php">Listado de Alumnos</a> </li>
        <li class="active"><a href="notas.view.php">Registro de Notas</a> </li>
        <li><a href="listadonotas.view.php">Consulta de Notas</a> </li>
        <li class="right"><a href="logout.php">Salir</a> </li>
    </ul>
</nav>

<div class="body">
    <div class="panel">
            <h3>Registro y Modificación Notas</h3>
           <?php
           if(!isset($_GET['revisar'])){
               ?>

            <form method="get" class="form" action="notas.view.php">
                <label>Seleccione el Grado</label><br>
                <select name="grado" required>
                    <?php foreach ($grados as $grado):?>
                        <option value="<?php echo $grado['id'] ?>"><?php echo $grado['nombre'] ?></option>
                    <?php endforeach;?>
                </select>
                <br><br>
                <label>Seleccione la Materia</label><br>
                <select name="materia" required>
                    <?php foreach ($materias as $materia):?>
                        <option value="<?php echo $materia['id'] ?>"><?php echo $materia['nombre'] ?></option>
                    <?php endforeach;?>
                </select>

                <br><br>
                <label>Seleccione la Sección</label><br>

                <?php foreach ($secciones as $seccion):?>
                    <input type="radio" name="seccion" required value="<?php echo $seccion['id'] ?>">Sección <?php echo $seccion['nombre'] ?>
                <?php endforeach;?>

                <br><br>
                <button type="submit" name="revisar" value="1">Ingresar Notas</button> <a class="btn-link" href="listadonotas.view.php">Consultar Notas</a>
                <br><br>
            </form>
        <?php
           }
        ?>
        <hr>

        <?php
        if(isset($_GET['revisar'])){
            $id_materia = $_GET['materia'];
            $id_grado = $_GET['grado'];
            $id_seccion = $_GET['seccion'];

            // Extrayendo el número de evaluaciones para esa materia seleccionada
            $num_eval = $conn->prepare("SELECT num_evaluaciones FROM materias WHERE id = :id_materia");
            $num_eval->execute([':id_materia' => $id_materia]);
            $num_eval = $num_eval->fetch();
            $num_eval = $num_eval['num_evaluaciones'];

            // Mostrando el cuadro de notas de todos los alumnos del grado seleccionado
            $sqlalumnos = $conn->prepare("
    SELECT 
        a.id, a.num_lista, a.apellidos, a.nombres, 
        AVG(b.nota) AS promedio, MAX(b.observaciones) AS observaciones
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


            ?>
            <br>
            <a href="notas.view.php"><strong><< Volver</strong></a>
            <br><br>
            <form action="procesarnota.php" method="post">

            <table class="table" cellpadding="0" cellspacing="0">
                <tr>
                    <th>No de lista</th><th>Apellidos</th><th>Nombres</th>
                    <?php
                        for($i = 1; $i <= $num_eval; $i++){
                           echo '<th>Nota '.$i .'</th>';
                        }
                    ?>
                    <th>Promedio</th>
                    <th>Observaciones</th>
                    <th>Eliminar</th>
                </tr>
                <?php foreach ($alumnos as $index => $alumno) :?>
                    <!-- campos ocultos necesarios para realizar el insert-->
                    <input type="hidden" value="<?php echo $num_alumnos ?>" name="num_alumnos">
                    <input type="hidden" value="<?php echo $alumno['id'] ?>" name="<?php echo 'id_alumno'.$index ?>">
                    <input type="hidden" value="<?php echo $num_eval ?>" name="num_eval">
                     <!-- campos para devolver los parámetros en el GET y mantener los mismos datos al hacer el header location-->
                    <input type="hidden" value="<?php echo $id_materia ?>" name="id_materia">
                    <input type="hidden" value="<?php echo $id_grado ?>" name="id_grado">
                    <input type="hidden" value="<?php echo $id_seccion ?>" name="id_seccion">
                    <tr>
                        <td align="center"><?php echo $alumno['num_lista'] ?></td><td><?php echo $alumno['apellidos'] ?></td>
                        <td><?php echo $alumno['nombres'] ?></td>
                        <?php
                           if(existeNota($alumno['id'],$id_materia,$conn) > 0){
                                // Ya tiene notas registradas
                                $notas = $conn->prepare("SELECT id, nota FROM notas WHERE id_alumno = :id_alumno AND id_materia = :id_materia");
                                $notas->execute([
                                    ':id_alumno' => $alumno['id'],
                                    ':id_materia' => $id_materia
                                ]);
                                $registrosnotas = $notas->fetchAll();
                                $num_notas = $notas->rowCount();
                                foreach ($registrosnotas as $eval => $nota){
                                    echo '<input type="hidden" value="'.$nota['id'].'" name="idnota' . $eval .'alumno' . $index . '">';
                                    echo '<td><input type="text" maxlength="5" value="'.$nota['nota'].'" name="evaluacion' . $eval . 'alumno' . $index . '" class="txtnota"></td>';
                                }
                                if($num_eval > $num_notas){
                                    $dif = $num_eval - $num_notas;

                                    for($i = $num_notas; $i < $dif + $num_notas; $i++) {
                                        echo '<input type="hidden" value="'.$nota['id'].'" name="idnota' . $i .'alumno' . $index . '">';
                                        echo '<td><input type="text" maxlength="5" value="'.$nota['nota'].'" name="evaluacion' . $i . 'alumno' . $index . '" class="txtnota"></td>';
                                    }
                                }


                            }else {
                                // Extrayendo el número de evaluaciones para esa materia seleccionada
                                for($i = 0; $i < $num_eval; $i++) {
                                    echo '<td><input type="text" maxlength="5" name="evaluacion' . $i . 'alumno' . $index . '" class="txtnota"></td>';
                                }
                            }

                            echo '<td align="center">'.number_format($alumno['promedio'], 2).'</td>';

                            if(existeNota($alumno['id'],$id_materia,$conn) > 0){
                                echo '<td><input type="text" maxlength="100" value="'.$alumno['observaciones'].'" name="observaciones' . $index . '" class="txtnota"></td>';
                            }else {
                                echo '<td><input type="text" name="observaciones' . $index . '" class="txtnota"></td>';
                            }

                        if(existeNota($alumno['id'],$id_materia,$conn) > 0){
                            echo '<td><a href="notadelete.php?idalumno='.$alumno['id'].'&idmateria='.$id_materia.'">Eliminar</a> </td>';
                        }else{
                            echo '<td>Sin notas</td>';
                        }
                        ?>
                    </tr>
                <?php endforeach;?>
                <tr></tr>
            </table>
                <br>
                <button type="submit" name="insertar">Guardar</button> <button type="reset">Limpiar</button> <a class="btn-link" href="listadonotas.view.php">Consultar Notas</a>
                <br>
            </form>
        <?php }

        ?>
                <!-- mostrando los mensajes que recibe a través de los parámetros en la URL -->
                <?php
                if(isset($_GET['err']))
                    echo '<span class="error">Error al almacenar el registro</span>';
                if(isset($_GET['info']))
                    echo '<span class="success">Registro almacenado correctamente!</span>';
                ?>
            </form>
        <?php
        if(isset($_GET['err']))
            echo '<span class="error">Error al guardar</span>';
        ?>
        </div>
</div>

<footer>
    <p</p>
</footer>

</body>

</html>
