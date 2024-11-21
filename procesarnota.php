<?php
if (!$_POST) {
    header('location: alumnos.view.php');
    exit();
} else {
    // Incluir el archivo para hacer la conexión
    require 'functions.php';

    // Recuperamos los valores que vamos a llenar en la BD
    $id_materia = htmlentities($_POST['id_materia'] ?? '');
    $id_grado = htmlentities($_POST['id_grado'] ?? '');
    $id_seccion = htmlentities($_POST['id_seccion'] ?? '');
    $num_eval = htmlentities($_POST['num_eval'] ?? 0);
    $num_alumnos = htmlentities($_POST['num_alumnos'] ?? 0);

    // Verificamos que la conexión exista
    if (!isset($conn) || !$conn) {
        die("Error: No se pudo establecer la conexión con la base de datos.");
    }

    // Si se presionó el botón "insertar"
    if (isset($_POST['insertar'])) {
        for ($i = 0; $i < $num_alumnos; $i++) {
            $id_alumno = htmlentities($_POST['id_alumno' . $i] ?? '');
           
            if (!$id_alumno) {
                continue; // Salta al siguiente alumno si falta el ID
            }

            // Validar si la nota ya existe
            if (existeNota($id_alumno, $id_materia, $conn) == 0) {
                for ($j = 0; $j < $num_eval; $j++) {
                    $nota = htmlentities($_POST['evaluacion' . $j . 'alumno' . $i] ?? 0);
                    $observaciones = htmlentities($_POST['observaciones' . $i] ?? '');
                   
                    $sql_insert = "INSERT INTO notas (id_alumno, id_materia, nota, observaciones) VALUES (?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql_insert);
                    $stmt->bind_param('iids', $id_alumno, $id_materia, $nota, $observaciones);
                    $stmt->execute();
                }
            } else {
                // Si la nota ya existe, actualizar
                for ($j = 0; $j < $num_eval; $j++) {
                    $id_nota = htmlentities($_POST['idnota' . $j . 'alumno' . $i] ?? '');
                    $nota = htmlentities($_POST['evaluacion' . $j . 'alumno' . $i] ?? 0);
                    $observaciones = htmlentities($_POST['observaciones' . $i] ?? '');

                    if ($id_nota) {
                        $sql_update = "UPDATE notas SET nota = ?, observaciones = ? WHERE id = ?";
                        $stmt = $conn->prepare($sql_update);
                        $stmt->bind_param('dsi', $nota, $observaciones, $id_nota);
                        $stmt->execute();
                    }
                }
            }
        }

        // Redirigir en función del resultado
        if (isset($stmt) && $stmt->affected_rows > 0) {
            header("location: notas.view.php?grado=$id_grado&materia=$id_materia&seccion=$id_seccion&revisar=1&info=1");
        } else {
            header("location: notas.view.php?grado=$id_grado&materia=$id_materia&seccion=$id_seccion&revisar=1&err=1");
        }
        exit();
    } elseif (isset($_POST['modificar'])) {
        // Capturar los valores para modificar un alumno
        $id_alumno = htmlentities($_POST['id'] ?? '');
        $num_lista = htmlentities($_POST['num_lista'] ?? '');
        $nombres = htmlentities($_POST['nombres'] ?? '');
        $apellidos = htmlentities($_POST['apellidos'] ?? '');
        $genero = htmlentities($_POST['genero'] ?? '');
        $id_grado = htmlentities($_POST['id_grado'] ?? '');
        $id_seccion = htmlentities($_POST['id_seccion'] ?? '');

        if ($id_alumno) {
            $sql_update = "UPDATE alumnos SET num_lista = ?, nombres = ?, apellidos = ?, genero = ?, id_grado = ?, id_seccion = ? WHERE id = ?";
            $stmt = $conn->prepare($sql_update);
            $stmt->bind_param('ssssiii', $num_lista, $nombres, $apellidos, $genero, $id_grado, $id_seccion, $id_alumno);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                header("location: alumnoedit.view.php?id=$id_alumno&info=1");
            } else {
                header("location: alumnoedit.view.php?id=$id_alumno&err=1");
            }
        }
        exit();
    }
}
