<?php
// Visión Pro: 1. Incluimos nuestro archivo de conexión.
require_once 'db_conexion.php';

// 2. Establecemos la cabecera de respuesta como JSON.
header('Content-Type: application/json');

// 3. CONFIGURACIÓN DE CACHÉ
$cache_file = 'tech_cache.json';
$cache_time = 3600; // La caché dura 1 hora (3600 segundos)

// 4. LÓGICA DE CACHÉ
// Si el archivo de caché existe y no ha expirado, lo devolvemos inmediatamente.
if (file_exists($cache_file) && (time() - filemtime($cache_file) < $cache_time)) {
    echo file_get_contents($cache_file);
    exit;
}

// 5. Consulta a la Base de Datos (Solo si la caché no existe o expiró)
$sql = "SELECT nombre, categoria, url_insignia FROM tecnologias ORDER BY categoria, nombre";
$resultado = $conexion->query($sql);

$tecnologias_agrupadas = [];

if ($resultado->num_rows > 0) {
    while($fila = $resultado->fetch_assoc()) {
        $categoria = $fila['categoria'];
        
        if (!isset($tecnologias_agrupadas[$categoria])) {
            $tecnologias_agrupadas[$categoria] = [];
        }
        
        $tecnologias_agrupadas[$categoria][] = [
            'nombre' => $fila['nombre'],
            'url_insignia' => $fila['url_insignia']
        ];
    }
}

// 6. Cerramos la conexión a la base de datos
$conexion->close();

// 7. Preparamos el JSON de salida y lo guardamos en la caché.
$json_output = json_encode($tecnologias_agrupadas);
file_put_contents($cache_file, $json_output);

// 8. Enviamos la respuesta JSON final.
echo $json_output;

?>