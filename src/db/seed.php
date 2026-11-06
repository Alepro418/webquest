<?php
/**
 * seed.php - Datos de demostración para la Webquest.
 *
 * Escenario: año escolar 2025-2026, 5° "A", un docente y cinco estudiantes.
 *   - 1er momento: PA "Ecosistemas Terrestres" (publicado, 16/09/2025 - 15/12/2025)
 *   - 2do momento: PA "El Cuerpo Humano" (borrador, 07/01/2026 - 31/03/2026)
 *   - 3er momento: sin configurar (sirve para probar la validación del sistema)
 *
 * Uso:
 *   mysql -u root -p webquest < src/db/schema.sql   (o schema2.sql)
 *   php src/db/seed.php
 */
declare(strict_types=1);

$db = new PDO(
    'mysql:host=localhost;port=3306;dbname=webquest;charset=utf8mb4',
    'root',
    '',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$hash = fn(string $p): string => password_hash($p, PASSWORD_ARGON2ID);

// ---------------------------------------------------------------
// Usuarios
// ---------------------------------------------------------------
$insertUsuario = static function (PDO $db, array $u) : int {
    $stmt = $db->prepare(
        'INSERT INTO Usuarios (nombre_usuario, password, rol, nombre_completo, email, grado, seccion)
         VALUES (:u, :p, :r, :n, :e, :g, :s)'
    );
    $stmt->execute([
        ':u' => $u['nombre_usuario'],
        ':p' => $u['password'],
        ':r' => $u['rol'],
        ':n' => $u['nombre_completo'],
        ':e' => $u['email'] ?? null,
        ':g' => $u['grado'] ?? null,
        ':s' => $u['seccion'] ?? null,
    ]);
    return (int)$db->lastInsertId();
};

$idDocente = $insertUsuario($db, [
    'nombre_usuario'  => 'Manuel',
    'password'        => $hash('Docente2026!'),
    'rol'             => 'docente',
    'nombre_completo' => 'Manuel Gónzales',
    'email'           => 'manuelzales03@gmail.com',
]);

$estudiantes = [
    ['Barbara', 'Barbara Araujo',    5, 'A'],
    ['Daniel',  'Daniel Moreno',     5, 'A'],
    ['Pedro',   'Pedro Castillo',    5, 'A'],
    ['Sandra',  'Sandra Carrasquero',5, 'A'],
    ['Miguel',  'Miguel Suarez',     5, 'A'],
];

$idEstudiantes = [];
$passEstudiante = $hash('Estudiante2026!');
foreach ($estudiantes as [$user, $nombre, $grado, $seccion]) {
    $idEstudiantes[$user] = $insertUsuario($db, [
        'nombre_usuario'  => $user,
        'password'        => $passEstudiante,
        'rol'             => 'estudiante',
        'nombre_completo' => $nombre,
        'grado'           => $grado,
        'seccion'         => $seccion,
    ]);
}

// ---------------------------------------------------------------
// Preguntas de seguridad (las mismas para todos los demos)
// ---------------------------------------------------------------
$preguntas = [
    ['¿Cómo se llama tu mascota?', 'Rex'],
    ['¿Cuál es tu comida favorita?', 'Pasta'],
    ['¿Cuál es tu juego favorito?', 'Béisbol'],
    ['¿Quién es tu superhéroe favorito?', 'Spider'],
    ['¿Cuál es tu color favorito?', 'Verde'],
];

$stmt = $db->prepare(
    'INSERT INTO Preguntas_Seguridad
        (id_usuario, pregunta_1, respuesta_1, pregunta_2, respuesta_2, pregunta_3, respuesta_3, pregunta_4, respuesta_4, pregunta_5, respuesta_5)
     VALUES (:id, :p1, :r1, :p2, :r2, :p3, :r3, :p4, :r4, :p5, :r5)'
);

foreach ($idEstudiantes as $idE) {
    $stmt->execute([
        ':id' => $idE,
        ':p1' => $preguntas[0][0], ':r1' => $hash(strtolower($preguntas[0][1])),
        ':p2' => $preguntas[1][0], ':r2' => $hash(strtolower($preguntas[1][1])),
        ':p3' => $preguntas[2][0], ':r3' => $hash(strtolower($preguntas[2][1])),
        ':p4' => $preguntas[3][0], ':r4' => $hash(strtolower($preguntas[3][1])),
        ':p5' => $preguntas[4][0], ':r5' => $hash(strtolower($preguntas[4][1])),
    ]);
}

// ---------------------------------------------------------------
// Proyectos de Aula (momentos pedagógicos)
// ---------------------------------------------------------------
$insertProyecto = static function (PDO $db, array $p) : int {
    $stmt = $db->prepare(
        'INSERT INTO Proyectos_Aula
            (titulo, descripcion, momento, anio_escolar, grado, seccion, id_docente, fecha_inicio, fecha_fin, estado)
         VALUES (:t, :d, :m, :a, :g, :s, :doc, :fi, :ff, :e)'
    );
    $stmt->execute([
        ':t' => $p['titulo'],
        ':d' => $p['descripcion'],
        ':m' => $p['momento'],
        ':a' => $p['anio_escolar'],
        ':g' => $p['grado'],
        ':s' => $p['seccion'],
        ':doc' => $p['id_docente'],
        ':fi' => $p['fecha_inicio'],
        ':ff' => $p['fecha_fin'],
        ':e' => $p['estado'],
    ]);
    return (int)$db->lastInsertId();
};

$pa1 = $insertProyecto($db, [
    'titulo'        => 'Ecosistemas Terrestres',
    'descripcion'   => 'Proyecto de Aula orientado al estudio de los ecosistemas terrestres, sus componentes bióticos y abióticos, y su importancia para la vida en el planeta.',
    'momento'       => '1',
    'anio_escolar'  => '2025-2026',
    'grado'         => 5,
    'seccion'       => 'A',
    'id_docente'    => $idDocente,
    'fecha_inicio'  => '2025-09-16',
    'fecha_fin'     => '2025-12-15',
    'estado'        => 'publicada',
]);

$pa2 = $insertProyecto($db, [
    'titulo'        => 'El Cuerpo Humano',
    'descripcion'   => 'Proyecto de Aula orientado al estudio de los sistemas del cuerpo humano y su cuidado.',
    'momento'       => '2',
    'anio_escolar'  => '2025-2026',
    'grado'         => 5,
    'seccion'       => 'A',
    'id_docente'    => $idDocente,
    'fecha_inicio'  => '2026-01-07',
    'fecha_fin'     => '2026-03-31',
    'estado'        => 'borrador',
]);

// ---------------------------------------------------------------
// Asignaturas del 1er momento
// ---------------------------------------------------------------
$insertAsignatura = static function (PDO $db, int $proyecto, string $titulo, string $desc, string $estado) : int {
    $stmt = $db->prepare(
        'INSERT INTO Asignaturas (id_proyecto, titulo, descripcion, estado)
         VALUES (:p, :t, :d, :e)'
    );
    $stmt->execute([':p' => $proyecto, ':t' => $titulo, ':d' => $desc, ':e' => $estado]);
    return (int)$db->lastInsertId();
};

$asigEcosistema = $insertAsignatura($db, $pa1, 'Componentes del Ecosistema',
    'Estudio de los componentes bióticos y abióticos de un ecosistema terrestre.', 'publicada');
$asigAgua = $insertAsignatura($db, $pa1, 'El Ciclo del Agua',
    'Análisis de las fases del ciclo hidrológico y su importancia para la vida.', 'publicada');
$asigCadenas = $insertAsignatura($db, $pa1, 'Cadenas Alimenticias',
    'Identificación de productores, consumidores y descomponedores en un ecosistema.', 'publicada');
$asigFotosintesis = $insertAsignatura($db, $pa1, 'La Fotosíntesis',
    'Proceso mediante el cual las plantas transforman la energía solar en energía química.', 'publicada');

// ---------------------------------------------------------------
// Talleres (uno por asignatura)
// ---------------------------------------------------------------
$insertTaller = static function (PDO $db, int $asignatura, string $titulo, string $desc, string $unidad, string $limite) : int {
    $stmt = $db->prepare(
        'INSERT INTO Talleres (id_asignatura, titulo, descripcion, unidad, fecha_limite)
         VALUES (:a, :t, :d, :u, :l)'
    );
    $stmt->execute([':a'=>$asignatura, ':t'=>$titulo, ':d'=>$desc, ':u'=>$unidad, ':l'=>$limite]);
    return (int)$db->lastInsertId();
};

$talleres = [
    1 => $insertTaller($db, $asigEcosistema, 'Mapa conceptual del ecosistema',
        'Elaborar en la libreta un mapa conceptual con los componentes bióticos y abióticos de un ecosistema terrestre.', '1', '2025-09-30'),
    2 => $insertTaller($db, $asigAgua, 'Dibujo del ciclo del agua',
        'Realizar un dibujo del ciclo del agua etiquetando cada fase: evaporación, condensación, precipitación e infiltración.', '2', '2025-10-31'),
    3 => $insertTaller($db, $asigCadenas, 'Dos cadenas alimenticias',
        'Construir 2 cadenas alimenticias (una terrestre y una acuática) e indicar el rol de cada organismo.', '3', '2025-11-15'),
    4 => $insertTaller($db, $asigFotosintesis, 'Esquema de la fotosíntesis',
        'Explicar el proceso de la fotosíntesis mediante un esquema y una breve redacción.', '4', '2025-12-10'),
];

// ---------------------------------------------------------------
// Recursos
// ---------------------------------------------------------------
$insertRecurso = static function (PDO $db, int $asignatura, string $titulo, string $tipo, string $url) : void {
    $stmt = $db->prepare(
        'INSERT INTO Recursos (id_asignatura, titulo, tipo, url_o_ruta) VALUES (:a, :t, :tp, :u)'
    );
    $stmt->execute([':a'=>$asignatura, ':t'=>$titulo, ':tp'=>$tipo, ':u'=>$url]);
};

$insertRecurso($db, $asigEcosistema, 'Video: Ecosistemas terrestres explicados', 'video', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ');
$insertRecurso($db, $asigEcosistema, 'Enlace: Factores bióticos y abióticos', 'enlace', 'https://es.wikipedia.org/wiki/Ecosistema');
$insertRecurso($db, $asigEcosistema, 'Documento: Guía de ecosistemas de Venezuela', 'documento', 'public/uploads/recursos/guia-ecosistemas.pdf');
$insertRecurso($db, $asigAgua, 'Video: El ciclo del agua en 3 minutos', 'video', 'https://www.youtube.com/watch?v=al-do-HGuIk');
$insertRecurso($db, $asigAgua, 'Enlace: USGS - Water Cycle', 'enlace', 'https://www.usgs.gov/special-topics/water-science-school');
$insertRecurso($db, $asigCadenas, 'Video: Cadenas y redes tróficas', 'video', 'https://www.youtube.com/watch?v=8eY0nM6Ho3Y');
$insertRecurso($db, $asigCadenas, 'Documento: Ejercicios de cadenas alimenticias', 'documento', 'public/uploads/recursos/ejercicios-cadenas.pdf');
$insertRecurso($db, $asigFotosintesis, 'Video: Fotosíntesis paso a paso', 'video', 'https://www.youtube.com/watch?v=sQK3Yr4Sc_k');
$insertRecurso($db, $asigFotosintesis, 'Enlace: Khan Academy - Fotosíntesis', 'enlace', 'https://es.khanacademy.org/science/biology');

// ---------------------------------------------------------------
// Entregas y Evaluaciones (19 de 20 talleres entregados)
// ---------------------------------------------------------------
$insertEntrega = static function (PDO $db, int $estudiante, int $taller, string $ruta, ?string $comentario, string $fecha) : int {
    $stmt = $db->prepare(
        'INSERT INTO Entregas_Taller (id_estudiante, id_taller, ruta_fotografia, comentario, fecha_envio)
         VALUES (:e, :t, :r, :c, :f)'
    );
    $stmt->execute([':e'=>$estudiante, ':t'=>$taller, ':r'=>$ruta, ':c'=>$comentario, ':f'=>$fecha]);
    return (int)$db->lastInsertId();
};

$insertEvaluacion = static function (PDO $db, int $entrega, float $puntaje, string $letra, string $observaciones, string $estatus) : void {
    $stmt = $db->prepare(
        'INSERT INTO Evaluaciones (id_entrega, puntaje, escala_letra, observaciones, estatus)
         VALUES (:e, :p, :l, :o, :s)'
    );
    $stmt->execute([':e'=>$entrega, ':p'=>$puntaje, ':l'=>$letra, ':o'=>$observaciones, ':s'=>$estatus]);
};

$EVA = fn(float $p): string => $p >= 18 ? 'Sobresaliente' : ($p >= 12 ? 'Buen estudiante' : 'Vamos a mejorar');
$LET = fn(float $p): string => $p >= 18 ? 'A' : ($p >= 15 ? 'B' : ($p >= 12 ? 'C' : 'D'));

// [usuario, taller, ruta, comentario, fecha_envio, puntaje|null, observaciones]
$entregas = [
    ['Barbara', 1, 'public/uploads/entregas/barbara-ecosistema.jpg', 'Mapa conceptual del bosque tropical.', '2025-09-28 10:30:00', 20.0, 'Mapa conceptual impecable, con colores y excelente ortografía.'],
    ['Barbara', 2, 'public/uploads/entregas/barbara-agua.jpg', 'Ciclo del agua completo.', '2025-10-29 09:12:00', 19.0, 'Dibujo detallado, etiquetas claras y explicación completa de cada fase.'],
    ['Barbara', 3, 'public/uploads/entregas/barbara-cadenas.jpg', 'Cadena terrestre y acuática.', '2025-11-14 14:45:00', 19.0, 'Ambas cadenas correctas con 4 eslabones y análisis del impacto.'],
    ['Barbara', 4, 'public/uploads/entregas/barbara-fotosintesis.jpg', 'Esquema con fórmula química.', '2025-12-09 16:10:00', 20.0, 'Esquema impecable, fórmula química correcta y excelente redacción.'],

    ['Daniel', 1, 'public/uploads/entregas/daniel-ecosistema.jpg', 'Componentes bióticos y abióticos.', '2025-09-29 16:20:00', 19.0, 'Mapa conceptual completo, con colores y buena ortografía.'],
    ['Daniel', 2, 'public/uploads/entregas/daniel-agua.jpg', 'Dibujo de las cuatro fases.', '2025-10-29 11:00:00', 18.0, 'Dibujo detallado, etiquetas claras y buena redacción.'],
    ['Daniel', 3, 'public/uploads/entregas/daniel-cadenas.jpg', 'Cadenas con cinco eslabones.', '2025-11-14 08:02:00', 19.0, 'Ambas cadenas correctas, análisis profundo del impacto.'],
    ['Daniel', 4, 'public/uploads/entregas/daniel-fotosintesis.jpg', 'Esquema y redacción.', '2025-12-09 13:33:00', 19.0, 'Esquema muy completo, fórmula química correcta y explicación clara.'],

    ['Pedro', 1, 'public/uploads/entregas/pedro-ecosistema.jpg', 'Mapa del desierto.', '2025-09-30 09:40:00', 17.0, 'Buen mapa, aunque faltó un factor abiótico y uso de colores.'],
    ['Pedro', 2, 'public/uploads/entregas/pedro-agua.jpg', 'Ciclo del agua.', '2025-10-30 10:15:00', 16.0, 'Dibujo correcto, pero faltó explicar la infiltración con detalle.'],
    ['Pedro', 3, 'public/uploads/entregas/pedro-cadenas.jpg', 'Cadenas alimenticias.', '2025-11-13 15:20:00', 17.0, 'Cadenas completas y bien explicadas, sin análisis del impacto.'],
    ['Pedro', 4, 'public/uploads/entregas/pedro-fotosintesis.jpg', 'Esquema de la fotosíntesis.', '2025-12-08 17:05:00', 18.0, 'Esquema muy completo con fórmula química y buena redacción.'],

    ['Sandra', 1, 'public/uploads/entregas/sandra-ecosistema.jpg', 'Mapa conceptual.', '2025-09-30 18:00:00', 14.0, 'Faltaron colores y solo incluyó 2 factores bióticos.'],
    ['Sandra', 2, 'public/uploads/entregas/sandra-agua.jpg', 'Ciclo del agua básico.', '2025-10-31 12:45:00', 12.0, 'Dibujo básico, faltaron etiquetas en 2 fases.'],
    ['Sandra', 3, 'public/uploads/entregas/sandra-cadenas.jpg', 'Dos cadenas.', '2025-11-15 09:30:00', 15.0, 'Cadenas correctas, pero sin análisis del impacto.'],
    ['Sandra', 4, 'public/uploads/entregas/sandra-fotosintesis.jpg', 'Esquema incompleto.', '2025-12-10 11:00:00', 13.0, 'Esquema incompleto, faltó la fórmula química.'],

    ['Miguel', 1, 'public/uploads/entregas/miguel-ecosistema.jpg', 'Primera entrega.', '2025-09-30 20:10:00', 10.0, 'Mapa incompleto, sin colores ni ejemplos.'],
    ['Miguel', 2, 'public/uploads/entregas/miguel-agua.jpg', 'Dibujo del ciclo.', '2025-10-31 20:05:00', 8.0, 'Dibujo muy básico y sin explicaciones.'],
    ['Miguel', 3, 'public/uploads/entregas/miguel-cadenas.jpg', 'Una cadena.', '2025-11-15 19:50:00', 11.0, 'Solo una cadena y sin roles definidos.'],
    // Taller 4: Miguel no entregó (queda como "Sin entrega" en el reporte).
];

foreach ($entregas as [$usuario, $nTaller, $ruta, $comentario, $fecha, $puntaje, $observaciones]) {
    $entregaId = $insertEntrega($db, $idEstudiantes[$usuario], $talleres[$nTaller], $ruta, $comentario, $fecha);
    if ($puntaje !== null) {
        $insertEvaluacion($db, $entregaId, $puntaje, $LET($puntaje), $observaciones, $EVA($puntaje));
    }
}

echo "Seed completado.\n";
echo "  Docente:    Manuel / Docente2026!\n";
echo "  Estudiantes (5° A): Barbara, Daniel, Pedro, Sandra, Miguel / Estudiante2026!\n";
echo "  1er momento: PA 'Ecosistemas Terrestres' (publicado) con 4 asignaturas y 4 talleres.\n";
echo "  2do momento: PA 'El Cuerpo Humano' (borrador).\n";
echo "  3er momento: sin configurar.\n";
