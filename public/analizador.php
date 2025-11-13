<?php
// Visión Pro: 1. Establecemos la cabecera de respuesta como JSON.
// Esto le dice al navegador (o a Alpine.js) que estamos enviando datos, no HTML.
header('Content-Type: application/json');

// Visión Pro: 2. Obtenemos el nombre de usuario de forma segura.
// Lo leemos de la URL (ej: analizador.php?usuario=JohnManrique900)
$usuario = $_GET['usuario'] ?? '';

if (empty($usuario)) {
    // Si no nos dan usuario, devolvemos un error.
    echo json_encode(['error' => 'No se proporcionó un usuario.']);
    exit;
}

// Visión Pro: 3. Preparamos la llamada a la API de GitHub.
$url = 'https://api.github.com/users/' . urlencode($usuario) . '/repos';

// Visión Pro: 4. ¡EL TRUCO CLAVE! La API de GitHub RECHAZA peticiones
// sin un 'User-Agent'. Esto nos identifica y nos hace ver como un script legítimo.
$opciones = [
    'http' => [
        'method' => 'GET',
        'header' => "User-Agent: ReflectGit-App\r\n" .
                    "Accept: application/vnd.github.v3+json\r\n"
    ]
];
$contexto = stream_context_create($opciones);

// Visión Pro: 5. Ejecutamos la llamada a la API.
// Usamos '@' para suprimir warnings si la API falla (ej. usuario no existe)
$respuesta = @file_get_contents($url, false, $contexto);

if ($respuesta === FALSE) {
    // Si la API falla (usuario no existe, límite de peticiones, etc.)
    echo json_encode(['error' => 'No se pudo encontrar el usuario o se superó el límite de la API.']);
    exit;
}

// Visión Pro: 6. Procesamos los datos.
$repositorios = json_decode($respuesta, true);
$conteo_lenguajes = [];

foreach ($repositorios as $repo) {
    $lenguaje = $repo['language'];
    
    // Solo contamos lenguajes válidos (no 'null')
    if ($lenguaje) {
        if (!isset($conteo_lenguajes[$lenguaje])) {
            $conteo_lenguajes[$lenguaje] = 0;
        }
        $conteo_lenguajes[$lenguaje]++;
    }
}

// Visión Pro: 7. Ordenamos los lenguajes de más usado a menos usado.
arsort($conteo_lenguajes);

// Visión Pro: 8. Enviamos la respuesta JSON final.
echo json_encode([
    'usuario' => $usuario,
    'total_repos' => count($repositorios),
    'lenguajes' => $conteo_lenguajes
]);

?>