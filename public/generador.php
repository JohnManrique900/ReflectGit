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
$biografia = $datos['biografia'] ?? ''; 
$showTrophies = $datos['showTrophies'] ?? false;
$showContribs = $datos['showContribs'] ?? false;
$socials = $datos['socials'] ?? []; // <-- ¡NUEVO!

header('Content-Type: application/json');

if (empty($nombres_tech) && empty($biografia) && empty($socials)) {
    echo json_encode(['error' => 'No se seleccionó ninguna información.']);
    exit;
}

// 4. FUNCIÓN CRÍTICA: Mapeo de Redes Sociales
function generar_insignia_social($network, $url) {
    if (empty(trim($url))) return '';
    
    // Mapeo directo: (nombre de Alpine) => [nombre del logo de Shields.io, color]
    $logos_map = [
        'linkedin' => ['LinkedIn', '0077B5'],
        'github' => ['GitHub', '181717'],
        'twitter' => ['X', '000000'],
        'youtube' => ['YouTube', 'FF0000'],
        'tiktok' => ['TikTok', '000000'],
        'discord' => ['Discord', '5865F2'],
        'telegram' => ['Telegram', '26A5E4'],
        'whatsapp' => ['WhatsApp', '25D366'],
        'facebook' => ['Facebook', '1877F2'],
        'instagram' => ['Instagram', 'E4405F'],
        'reddit' => ['Reddit', 'FF4500'],
    ];

    if (isset($logos_map[$network])) {
        list($name, $color) = $logos_map[$network];
        // Usamos la URL de la red como link, y el nombre/color para la insignia
        return "  <a href=\"$url\" target=\"_blank\"><img src=\"https://img.shields.io/badge/{$name}-{$color}.svg?style=for-the-badge&logo=" . strtolower($name) . "&logoColor=white\" alt=\"{$name}\" /></a>\n";
    }
    return '';
}

// 5. Lógica de separación y consulta a la BD (Tecnologías)
$db_tech_names = [];
$custom_tech_names = [];
$tecnologias_encontradas = [];

if (!empty($nombres_tech)) {
    $db_query_string = implode(',', array_fill(0, count($nombres_tech), '?'));
    $sql_check = "SELECT nombre FROM tecnologias WHERE nombre IN ($db_query_string)";

    $stmt = $conexion->prepare($sql_check);
    $types = str_repeat('s', count($nombres_tech));
    $stmt->bind_param($types, ...$nombres_tech);
    $stmt->execute();
    $resultado_check = $stmt->get_result();

    $nombres_en_bd = [];
    while ($fila = $resultado_check->fetch_assoc()) {
        $nombres_en_bd[] = $fila['nombre'];
    }
    $stmt->close();

    foreach ($nombres_tech as $name) {
        if (in_array($name, $nombres_en_bd)) {
            $db_tech_names[] = $name;
        } else {
            $custom_tech_names[] = $name;
        }
    }

    if (!empty($db_tech_names)) {
        $placeholders_string = implode(',', array_fill(0, count($db_tech_names), '?'));
        $sql_fetch = "SELECT nombre, url_insignia FROM tecnologias WHERE nombre IN ($placeholders_string)";
        
        $stmt_fetch = $conexion->prepare($sql_fetch);
        $types_fetch = str_repeat('s', count($db_tech_names));
        $stmt_fetch->bind_param($types_fetch, ...$db_tech_names);
        $stmt_fetch->execute();
        $resultado_fetch = $stmt_fetch->get_result();

        while ($fila = $resultado_fetch->fetch_assoc()) {
            $tecnologias_encontradas[] = $fila;
        }
        $stmt_fetch->close();
    }
}

// 6. Añadimos las tecnologías personalizadas con un logo genérico
$custom_logo_url_base = 'https://img.shields.io/badge/TECH-30363D?style=for-the-badge&logo=gear&logoColor=white';
foreach ($custom_tech_names as $name) {
    $tecnologias_encontradas[] = [
        'nombre' => $name,
        'url_insignia' => str_replace('TECH', urlencode(str_replace('-', '--', $name)), $custom_logo_url_base) // Reemplaza - para que Shields.io funcione
    ];
}


// 7. CONSTRUCCIÓN DEL MARKDOWN COMPLETO

$markdown = "### 👋 ¡Hola! Soy " . $usuario_github . "\n\n";
$markdown .= $biografia . "\n\n"; 
$markdown .= "---" . "\n\n";

// 7.1. Redes Sociales (NUEVA SECCIÓN CRÍTICA)
$socials_markdown = '';
foreach ($socials as $network => $url) {
    $socials_markdown .= generar_insignia_social($network, $url);
}

if (!empty(trim($socials_markdown))) {
    $markdown .= "## 🌐 Mis Redes y Contacto\n\n";
    $markdown .= "<p align='left'>\n";
    $markdown .= $socials_markdown;
    $markdown .= "</p>\n\n";
}


// 7.2. Stack Tecnológico
if (!empty($tecnologias_encontradas)) {
    $markdown .= "### 💻 Mi Stack Tecnológico\n\n";

    switch ($plantilla) {
        case 'corporativo':
            foreach ($tecnologias_encontradas as $tech) {
                $markdown .= "- **" . $tech['nombre'] . "**\n";
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
}

// 7.3. Estadísticas de GitHub
if ($plantilla !== 'minimalista') {
    $markdown .= "\n### 📊 Mis Estadísticas de GitHub\n\n";
    $markdown .= "<p align='center'>\n";
    
    if ($showTrophies) {
        $markdown .= "  <img src='https://github-profile-trophy.vercel.app/?username=$usuario_github&theme=radical&no-frame=false&no-bg=true&margin-w=4' alt='GitHub Trophies' />\n";
    }

    if ($showContribs) {
        $markdown .= "  <img src='https://github-contributor-stats.vercel.app/api?username=$usuario_github&limit=5&theme=radical&combine_all_yearly_contributions=true' alt='Top Contributor' />\n";
    }

    $markdown .= "  <img src='https://github-readme-stats.vercel.app/api?username=$usuario_github&theme=radical&hide_border=true&include_all_commits=true' alt='Estadísticas' />\n";
    $markdown .= "  <img src='https://github-readme-stats.vercel.app/api/top-langs/?username=$usuario_github&theme=radical&hide_border=true&layout=compact' alt='Top Lenguajes' />\n";
    $markdown .= "</p>\n";
}

// 8. Cerramos la conexión
$conexion->close();

// 9. Devolvemos el Markdown final
echo json_encode(['markdown_final' => $markdown]);

?>