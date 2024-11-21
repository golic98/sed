<?php
require 'functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recuperar datos enviados desde el formulario
    $username = htmlentities($_POST['username']);
    $password = htmlentities($_POST['password']);

    try {
        // Consultar el usuario en la base de datos
        $stmt = $conn->prepare("SELECT * FROM usuarios WHERE username = :username LIMIT 1");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verificar si se encontró un usuario
        if ($user) {
            // Verificar la contraseña encriptada
            if (password_verify($password, $user['password'])) {
                // Iniciar sesión
                session_start();
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role']; // Asume que hay una columna `role` en la tabla
                header('Location: inicio.view.php');
                exit();
            } else {
                // Contraseña incorrecta
                header('Location: index.php?err=1');
                exit();
            }
        } else {
            // Usuario no encontrado
            header('Location: index.php?err=1');
            exit();
        }
    } catch (PDOException $e) {
        // Error en la consulta
        header('Location: index.php?err=1');
        exit();
    }
} else {
    // Si el usuario accede directamente al archivo
    header('Location: index.php');
    exit();
}
