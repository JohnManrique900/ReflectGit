<?php
// 1. Establecemos la cabecera de respuesta como JSON.
header('Content-Type: application/json');

// 2. Obtenemos el nombre de usuario de forma segura.
$usuario = $_GET['usuario'] ?? '';

if (empty($usuario)) {
    echo json_encode(['error' => 'No se proporcionó un usuario.']);
    exit;
}

// 3. Preparamos la llamada a la API de GitHub.
$url = 'https://api.github.com/users/' . urlencode($usuario) . '/repos?per_page=100'; // Solicitamos 100 repos por página
$opciones = [
    'http' => [
        'method' => 'GET',
        'header' => "User-Agent: ReflectGit-App\r\n" .
                    "Accept: application/vnd.github.v3+json\r\n"
    ]
];
$contexto = stream_context_create($opciones);

// 4. Ejecutamos la llamada a la API.
$respuesta = @file_get_contents($url, false, $contexto);

if ($respuesta === FALSE) {
    // Si la API falla (límite superado, o user no existe)
    echo json_encode(['error' => 'No se pudo encontrar el usuario o se superó el límite de la API.']);
    exit;
}

// 5. Procesamos los datos.
$repositorios = json_decode($respuesta, true);
$conteo_lenguajes = [];

if (empty($repositorios)) {
    // Si el usuario existe pero no tiene repositorios públicos
    echo json_encode([
        'usuario' => $usuario,
        'total_repos' => 0,
        'lenguajes' => new stdClass(), // Aseguramos objeto vacío {}
        'analisis_exitoso' => true
    ]);
    exit;
}

foreach ($repositorios as $repo) {
    $lenguaje = $repo['language'];
    if ($lenguaje) {
        if (!isset($conteo_lenguajes[$lenguaje])) {
            $conteo_lenguajes[$lenguaje] = 0;
        }
        $conteo_lenguajes[$lenguaje]++;
    }
}

// 6. Ordenamos y aseguramos que sea un objeto JSON
arsort($conteo_lenguajes);

// 7. Enviamos la respuesta JSON final.
echo json_encode([
    'usuario' => $usuario,
    'total_repos' => count($repositorios),
    'lenguajes' => $conteo_lenguajes,
    'analisis_exitoso' => true
]);

?>