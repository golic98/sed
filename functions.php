<?php
// Configuración de la conexión a la base de datos
$host = 'localhost';
$dbname = 'registro_notas';
$user = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// Función para verificar permisos
function permisos($roles) {
    session_start();
    if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $roles)) {
        header('Location: index.php?err=2');
        exit();
    }
}
?>
