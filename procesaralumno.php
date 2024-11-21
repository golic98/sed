<?php
if (!$_POST) {
    header('location: alumnos.view.php');
} else {
    // Incluimos el archivo funciones que tiene la conexión
    require 'functions.php';

    // Recuperamos los valores que vamos a llenar en la BD
    $nombres = htmlentities($_POST['nombres']);
    $apellidos = htmlentities($_POST['apellidos']);
    $genero = htmlentities($_POST['genero']);
    $numlista = htmlentities($_POST['numlista']);
    $idgrado = htmlentities($_POST['grado']);
    $idseccion = htmlentities($_POST['seccion']);
    $password = htmlentities($_POST['password']); // Recuperamos la contraseña

    // Encriptar la contraseña
    $passwordHash = password_hash($password, PASSWORD_BCRYPT);

    // Insertar es el nombre del botón guardar que está en el archivo alumnos.view.php
    if (isset($_POST['insertar'])) {
        $result = $conn->query(
            "INSERT INTO alumnos (num_lista, nombres, apellidos, genero, id_grado, id_seccion, password) 
            VALUES ('$numlista', '$nombres', '$apellidos', '$genero', '$idgrado', '$idseccion', '$passwordHash')"
        );

        if ($result) {
            header('location:alumnos.view.php?info=1');
        } else {
            header('location:alumnos.view.php?err=1');
        } // Validación de registro

    // Si no, botón modificar que está en el archivo alumnoedit.view.php
    } else if (isset($_POST['modificar'])) {
        // Capturamos el id del alumno a modificar
        $id_alumno = htmlentities($_POST['id']);

        // Si también se modifica la contraseña
        if (!empty($password)) {
            $sql = "UPDATE alumnos 
                    SET num_lista = '$numlista', nombres = '$nombres', apellidos = '$apellidos', 
                        genero = '$genero', id_grado = '$idgrado', id_seccion = '$idseccion', 
                        password = '$passwordHash' 
                    WHERE id = $id_alumno";
        } else {
            // Si no se modifica la contraseña, se actualizan los demás campos
            $sql = "UPDATE alumnos 
                    SET num_lista = '$numlista', nombres = '$nombres', apellidos = '$apellidos', 
                        genero = '$genero', id_grado = '$idgrado', id_seccion = '$idseccion' 
                    WHERE id = $id_alumno";
        }

        $result = $conn->query($sql);

        if ($result) {
            header('location:alumnoedit.view.php?id=' . $id_alumno . '&info=1');
        } else {
            header('location:alumnoedit.view.php?id=' . $id_alumno . '&err=1');
        } // Validación de registro
    }
}
