<?php
/*
 * Archivo de Conexión a la Base de Datos (db_conexion.php)
 */

// 1. Definimos las constantes de conexión
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); // Si tienes contraseña, ponla aquí
define('DB_NAME', 'reflectgit_db');

// 2. Creamos la conexión usando mysqli
$conexion = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// 3. Verificamos si hay un error de conexión
if ($conexion->connect_error) {
    // Si hay un error, devolvemos un mensaje JSON claro para Alpine
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Error de Conexión a la BD. Asegúrate que MySQL esté corriendo.']);
    exit; // Detenemos el script
}

// 4. Establecemos el charset a utf8mb4 (para emojis y tildes)
$conexion->set_charset("utf8mb4");

?>