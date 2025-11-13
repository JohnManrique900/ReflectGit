<?php
/*
 * Archivo de Conexión a la Base de Datos (db_conexion.php)
 */

// 1. Definimos las constantes de conexión
define('DB_HOST', 'localhost'); // El servidor de XAMPP (casi siempre 'localhost')
define('DB_USER', 'root');      // El usuario por defecto de XAMPP
define('DB_PASS', '');          // La contraseña por defecto de XAMPP (vacía)
define('DB_NAME', 'reflectgit_db'); // El nombre de nuestra base de datos

// 2. Creamos la conexión usando mysqli
$conexion = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// 3. Verificamos si hay un error de conexión
if ($conexion->connect_error) {
    // Si hay un error, matamos el script y mostramos el error
    die("Error de Conexión: " . $conexion->connect_error);
}

// 4. Establecemos el charset a utf8mb4 (para emojis y tildes)
$conexion->set_charset("utf8mb4");

?>