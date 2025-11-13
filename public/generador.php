<?php
// 1. Incluimos la conexión a la BD
require_once 'db_conexion.php';

// 2. Leemos los datos JSON
$json_data = file_get_contents('php://input');
$datos = json_decode($json_data, true);

// 3. Obtenemos TODOS los datos que necesitamos
$nombres_tech = $datos['tecnologias'] ?? [];
$usuario_github = $datos['usuario'] ?? 'tu-usuario';
$plantilla = $datos['plantilla'] ?? 'tecnologico';
$biografia = $datos['biografia'] ?? ''; // <-- NUEVA VARIABLE

header('Content-Type: application/json');

if (empty($nombres_tech)) {
    echo json_encode(['error' => 'No se seleccionaron tecnologías.']);
    exit;
}

// 4. Preparamos la consulta SQL (sin cambios)
$placeholders = implode(',', array_fill(0, count($nombres_tech), '?'));
$sql = "SELECT nombre, url_insignia FROM tecnologias WHERE nombre IN ($placeholders)";
$stmt = $conexion->prepare($sql);
$stmt->bind_param(str_repeat('s', count($nombres_tech)), ...$nombres_tech);
$stmt->execute();
$resultado = $stmt->get_result();

$tecnologias_encontradas = [];
while ($fila = $resultado->fetch_assoc()) {
    $tecnologias_encontradas[] = $fila;
}

// Visión Pro: 5. ¡NUEVA LÓGICA DE CONSTRUCCIÓN!
// Empezamos construyendo la biografía.
$markdown = "### 👋 ¡Hola! Soy " . $usuario_github . "\n\n";
$markdown .= $biografia . "\n\n"; // <-- AÑADIMOS LA BIOGRAFÍA

// 6. Construimos el Stack Tecnológico basado en la plantilla
$markdown .= "### 💻 Mi Stack Tecnológico\n\n";

switch ($plantilla) {
    case 'corporativo':
        foreach ($tecnologias_encontradas as $tech) {
            $markdown .= "- " . $tech['nombre'] . "\n";
        }
        break;

    case 'minimalista':
        $lista_nombres = array_column($tecnologias_encontradas, 'nombre');
        $markdown .= implode(' • ', $lista_nombres);
        $markdown .= "\n";
        break;
    
    case 'tecnologico':
    default:
        $markdown .= "<p align='left'>\n";
        foreach ($tecnologias_encontradas as $tech) {
            $markdown .= "  <img src='" . $tech['url_insignia'] . "' alt='" . $tech['nombre'] . "' />\n";
        }
        $markdown .= "</p>\n";
        break;
}

// 7. Añadimos las estadísticas (sin cambios)
if ($plantilla !== 'minimalista') {
    $markdown .= "\n### 📊 Mis Estadísticas de GitHub\n\n";
    $markdown .= "<p align='center'>\n";
    $markdown .= "  <img src='https://github-readme-stats.vercel.app/api?username=$usuario_github&theme=radical&hide_border=true&include_all_commits=true' alt='Estadísticas' />\n";
    $markdown .= "  <img src='https://github-readme-stats.vercel.app/api/top-langs/?username=$usuario_github&theme=radical&hide_border=true&layout=compact' alt='Top Lenguajes' />\n";
    $markdown .= "</p>\n";
}

// 8. Cerramos todo
$stmt->close();
$conexion->close();

// 9. Devolvemos el Markdown final
echo json_encode(['markdown_final' => $markdown]);

?>