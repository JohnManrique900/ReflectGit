<?php
// 1. Establecemos la cabecera
header('Content-Type: application/json');

// 2. Leemos los datos JSON
$json_data = file_get_contents('php://input');
$datos = json_decode($json_data, true);

$nombres_tech = $datos['tecnologias'] ?? [];

if (empty($nombres_tech)) {
    echo json_encode(['error' => 'No se seleccionaron tecnologías para la biografía.']);
    exit;
}

// 3. Simulamos un retraso de la "IA"
sleep(2);

// 4. Formateamos la frase de habilidades (sin cambios)
$total = count($nombres_tech);
$frase_habilidades = '';

if ($total === 1) {
    $frase_habilidades = $nombres_tech[0];
} else if ($total === 2) {
    $frase_habilidades = $nombres_tech[0] . ' y ' . $nombres_tech[1];
} else {
    $ultimas = array_pop($nombres_tech);
    $frase_habilidades = implode(', ', $nombres_tech) . ' y ' . $ultimas;
}

// 
// Visión Pro: 5. ¡NUEVA LÓGICA DE IA!
// Creamos un "Pool" de plantillas de IA.
//
$pool_plantillas = [
    "Desarrollador apasionado con experiencia en la creación de soluciones web modernas. Especializado en {HABILIDADES}.",
    "Profesional de la tecnología enfocado en {HABILIDADES}. Siempre buscando aprender y construir nuevos proyectos.",
    "Hola, soy desarrollador. Mi stack principal incluye {HABILIDADES}. ¡Conectemos!",
    "Entusiasta del desarrollo con un profundo interés en {HABILIDADES}. Abierto a colaborar en proyectos innovadores.",
    "Creador de soluciones web y software. Mi conjunto de herramientas principal es {HABILIDADES}.",
    "Desarrollador con experiencia demostrada en {HABILIDADES}. Buscando siempre el siguiente reto.",
    "Especialista en backend (simulado) con dominio de {HABILIDADES}. Transformando ideas en código funcional.",
    "Enfocado en la experiencia de usuario (simulado) y el desarrollo frontend, usando {HABILIDADES} para crear interfaces intuitivas."
];

// 6. Seleccionamos 3 plantillas al azar de nuestro "pool"
$claves_aleatorias = array_rand($pool_plantillas, 3);
$sugerencias = [];

foreach ($claves_aleatorias as $clave) {
    // Reemplazamos el placeholder {HABILIDADES} con nuestra frase
    $sugerencias[] = str_replace('{HABILIDADES}', $frase_habilidades, $pool_plantillas[$clave]);
}

// 7. Devolvemos las 3 opciones aleatorias como JSON
echo json_encode([
    'sugerencias' => $sugerencias
]);

?>