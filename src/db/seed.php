<?php
/**
 * seed.php - Datos de demostración para la Webquest.
 * Uso: php seed.php  (desde la raíz del proyecto)
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
$docente = [
    'nombre_usuario' => 'profesor',
    'password'       => $hash('Docente2026!'),
    'rol'            => 'docente',
    'nombre_completo'=> 'Juan Pérez',
    'email'          => 'juan.perez@webquest.edu',
];

$estudiantes = [
    ['maria',  'María García',  4, 'A'],
    ['carlos', 'Carlos Ruiz',   4, 'A'],
    ['luis',   'Luis Pineda',   4, 'A'],
    ['ana',    'Ana Torres',    4, 'A'],
];

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

$idDocente = $insertUsuario($db, $docente);

$idEstudiantes = [];
$passEstudiante = $hash('Estudiante2026!');
foreach ($estudiantes as [$user, $nombre, $grado, $seccion]) {
    $id = $insertUsuario($db, [
        'nombre_usuario' => $user,
        'password'       => $passEstudiante,
        'rol'            => 'estudiante',
        'nombre_completo'=> $nombre,
        'grado'          => $grado,
        'seccion'        => $seccion,
    ]);
    $idEstudiantes[$user] = $id;
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
// Asignaturas
// ---------------------------------------------------------------
$insertAsignatura = static function (PDO $db, string $titulo, string $desc, int $grado, string $seccion, string $estado, int $docente) : int {
    $stmt = $db->prepare(
        'INSERT INTO Asignaturas (titulo, descripcion, grado, seccion, estado, id_docente)
         VALUES (:t, :d, :g, :s, :e, :doc)'
    );
    $stmt->execute([':t'=>$titulo, ':d'=>$desc, ':g'=>$grado, ':s'=>$seccion, ':e'=>$estado, ':doc'=>$docente]);
    return (int)$db->lastInsertId();
};

$asigCN = $insertAsignatura(
    $db,
    'Ciencias Naturales - 4to',
    'Seres vivos, ecosistemas y cuerpo humano',
    4, 'A', 'publicada', $idDocente
);

$asigAgua = $insertAsignatura(
    $db,
    'El Ciclo del Agua',
    'Evaporación, condensación y precipitación',
    4, 'A', 'borrador', $idDocente
);

// ---------------------------------------------------------------
// Talleres
// ---------------------------------------------------------------
$insertTaller = static function (PDO $db, int $asignatura, string $titulo, string $desc, ?string $unidad, string $limite) : int {
    $stmt = $db->prepare(
        'INSERT INTO Talleres (id_asignatura, titulo, descripcion, unidad, fecha_limite)
         VALUES (:a, :t, :d, :u, :l)'
    );
    $stmt->execute([':a'=>$asignatura, ':t'=>$titulo, ':d'=>$desc, ':u'=>$unidad, ':l'=>$limite]);
    return (int)$db->lastInsertId();
};

$talleres = [];
$t = $insertTaller($db, $asigCN, 'Ecosistemas', 'Identifica los elementos de un ecosistema', '1', '2026-07-10');
$talleres[1] = $t;
$t = $insertTaller($db, $asigCN, 'Cadena Alimenticia', 'Dibuja una cadena alimenticia de tu región', '2', '2026-07-17');
$talleres[2] = $t;
$t = $insertTaller($db, $asigCN, 'La Célula', 'Dibuja y señala las partes de una célula animal', '3', '2026-07-24');
$talleres[3] = $t;
$t = $insertTaller($db, $asigCN, 'Experimentos', 'Realiza un experimento de germinación', '4', '2026-07-31');
$talleres[4] = $t;

// ---------------------------------------------------------------
// Recursos
// ---------------------------------------------------------------
$insertRecurso = static function (PDO $db, int $asignatura, string $titulo, string $tipo, string $url) : void {
    $stmt = $db->prepare(
        'INSERT INTO Recursos (id_asignatura, titulo, tipo, url_o_ruta) VALUES (:a, :t, :tp, :u)'
    );
    $stmt->execute([':a'=>$asignatura, ':t'=>$titulo, ':tp'=>$tipo, ':u'=>$url]);
};

$insertRecurso($db, $asigCN, 'Video: El ecosistema', 'video', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ');
$insertRecurso($db, $asigCN, 'Guía: La célula vegetal', 'documento', 'public/uploads/recursos/guia-celula.pdf');
$insertRecurso($db, $asigCN, 'Enlace: Ciclo del agua', 'enlace', 'https://es.wikipedia.org/wiki/Ciclo_del_agua');

// ---------------------------------------------------------------
// Entregas y Evaluaciones
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

// Maria
$e = $insertEntrega($db, $idEstudiantes['maria'], $talleres[1], 'public/uploads/entregas/maria-ecosistemas.jpg', 'Adjunto mi trabajo sobre los ecosistemas.', '2026-06-28 10:30:00');
$insertEvaluacion($db, $e, 19, $LET(19), 'Excelente trabajo, muy bien ilustrado', $EVA(19));

$e = $insertEntrega($db, $idEstudiantes['maria'], $talleres[2], 'public/uploads/entregas/maria-cadena.jpg', 'Cadena alimenticia de mi región.', '2026-07-05 09:12:00');
$insertEvaluacion($db, $e, 18, $LET(18), 'Muy buen análisis, sigue así', $EVA(18));

$e = $insertEntrega($db, $idEstudiantes['maria'], $talleres[3], 'public/uploads/entregas/maria-celula.jpg', 'Célula animal señalada.', '2026-07-18 14:45:00');

// Carlos
$e = $insertEntrega($db, $idEstudiantes['carlos'], $talleres[1], 'public/uploads/entregas/carlos-ecosistemas.jpg', 'Aquí está mi ecosistema.', '2026-06-29 16:20:00');
$insertEvaluacion($db, $e, 14, $LET(14), 'Bien, pero falta nombrar los productores', $EVA(14));

$e = $insertEntrega($db, $idEstudiantes['carlos'], $talleres[2], 'public/uploads/entregas/carlos-cadena.jpg', 'Mi cadena alimenticia.', '2026-07-06 11:00:00');

// Luis
$e = $insertEntrega($db, $idEstudiantes['luis'], $talleres[1], 'public/uploads/entregas/luis-ecosistemas.jpg', 'Primera entrega.', '2026-07-01 08:02:00');
$insertEvaluacion($db, $e, 10, $LET(10), 'Recuerda revisar los conceptos básicos, puedes mejorar', $EVA(10));

// Ana
$e = $insertEntrega($db, $idEstudiantes['ana'], $talleres[2], 'public/uploads/entregas/ana-cadena.jpg', 'Cadena alimenticia marina.', '2026-07-04 13:33:00');
$insertEvaluacion($db, $e, 16, $LET(16), 'Muy bien, buen detalle en los descomponedores', $EVA(16));

echo "Seed completado. Docente: profesor | Contraseña: Docente2026!\n";
echo "Estudiantes (maria, carlos, luis, ana) | Contraseña: Estudiante2026!\n";