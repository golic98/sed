<?php
// Iniciamos la sesión
session_start();
// Validación de sesión activa con cookies
if (isset($_COOKIE["activo"]) && isset($_SESSION['username'])) {
    setcookie("activo", 1, time() + 3600);
} else {
    http_response_code(403);
    header('location:index.php?err=2');
}
// Importamos el archivo que contiene la variable de conexión a la base de datos
require 'conn/connection.php';

// Para verificar que tiene acceso a un archivo
function permisos($permisos) {
    if (!in_array($_SESSION['rol'], $permisos)) {
        http_response_code(403);
        header('location:inicio.view.php?err=1');
    }
}

// Verificar si ya existe una nota para un alumno y materia específicos
function existeNota($id_alumno, $id_materia, $conn) {
    $nota = $conn->prepare("SELECT * FROM notas WHERE id_materia = :id_materia AND id_alumno = :id_alumno");
    $nota->bindParam(':id_materia', $id_materia);
    $nota->bindParam(':id_alumno', $id_alumno);
    $nota->execute();
    // Si devuelve una fila, significa que la nota ya existe
    return $nota->rowCount();
}

// Verificar las credenciales desencriptando la contraseña
function verificarCredenciales($username, $password, $conn) {
    try {
        // Buscar al usuario en la base de datos
        $stmt = $conn->prepare("SELECT * FROM usuarios WHERE username = :username LIMIT 1");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Si se encuentra un usuario, verificar la contraseña
        if ($user && password_verify($password, $user['password'])) {
            return $user; // Devuelve el registro del usuario si es válido
        } else {
            return false; // Credenciales inválidas
        }
    } catch (PDOException $e) {
        // Manejar errores de conexión o consulta
        return false;
    }
}
?>
