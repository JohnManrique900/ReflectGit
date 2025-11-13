<?php
// Visión Pro: 1. Incluimos nuestro archivo de conexión.
// require_once es más seguro que 'include' porque si falla, detiene el script.
require_once 'db_conexion.php';

// 2. Establecemos la cabecera de respuesta como JSON.
header('Content-Type: application/json');

// 3. Definimos la consulta SQL
$sql = "SELECT nombre, categoria, url_insignia FROM tecnologias ORDER BY categoria, nombre";

// 4. Ejecutamos la consulta
$resultado = $conexion->query($sql);

$tecnologias_agrupadas = [];

if ($resultado->num_rows > 0) {
    // 5. Iteramos sobre los resultados
    while($fila = $resultado->fetch_assoc()) {
        $categoria = $fila['categoria'];
        
        // Visión Pro: 6. Agrupamos las tecnologías por su categoría.
        // Esto hace que sea MUCHO más fácil para nuestro frontend (Alpine.js)
        // mostrar las tecnologías en secciones (Frontend, Backend, etc.).
        if (!isset($tecnologias_agrupadas[$categoria])) {
            $tecnologias_agrupadas[$categoria] = [];
        }
        
        $tecnologias_agrupadas[$categoria][] = [
            'nombre' => $fila['nombre'],
            'url_insignia' => $fila['url_insignia']
        ];
    }
}

// 7. Cerramos la conexión a la base de datos
$conexion->close();

// 8. Enviamos la respuesta JSON final.
echo json_encode($tecnologias_agrupadas);

?>