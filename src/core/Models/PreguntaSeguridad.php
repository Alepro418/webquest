<?php
declare(strict_types=1);

/**
 * PreguntaSeguridad - Modelo de la tabla Preguntas_Seguridad.
 */
final class PreguntaSeguridad
{
    /**
     * Preguntas disponibles en el formulario de registro (valores del <select>).
     */
    public const PREGUNTAS = [
        'mascota'    => '¿Cómo se llama tu mascota?',
        'comida'     => '¿Cuál es tu comida favorita?',
        'juego'      => '¿Cuál es tu juego favorito?',
        'superheroe' => '¿Quién es tu superhéroe favorito?',
        'color'      => '¿Cuál es tu color favorito?',
        'libro'      => '¿Cuál es tu libro favorito?',
    ];

    public static function forUser(int $userId): ?array
    {
        return Database::row(
            'SELECT * FROM Preguntas_Seguridad WHERE id_usuario = :id LIMIT 1',
            [':id' => $userId]
        );
    }

    /**
     * Crea las 5 preguntas de un estudiante.
     * $items: array de ['pregunta' => valor, 'respuesta' => hash].
     */
    public static function create(int $userId, array $items): void
    {
        Database::execute(
            'INSERT INTO Preguntas_Seguridad
                (id_usuario, pregunta_1, respuesta_1, pregunta_2, respuesta_2,
                 pregunta_3, respuesta_3, pregunta_4, respuesta_4, pregunta_5, respuesta_5)
             VALUES (:id, :p1, :r1, :p2, :r2, :p3, :r3, :p4, :r4, :p5, :r5)',
            [
                ':id' => $userId,
                ':p1' => $items[0]['pregunta'], ':r1' => $items[0]['respuesta'],
                ':p2' => $items[1]['pregunta'], ':r2' => $items[1]['respuesta'],
                ':p3' => $items[2]['pregunta'], ':r3' => $items[2]['respuesta'],
                ':p4' => $items[3]['pregunta'], ':r4' => $items[3]['respuesta'],
                ':p5' => $items[4]['pregunta'], ':r5' => $items[4]['respuesta'],
            ]
        );
    }

    /** Devuelve las 5 preguntas como lista indexada 1..5 (sin respuestas). */
    public static function questionsOfUser(int $userId): array
    {
        $row = self::forUser($userId);
        if ($row === null) {
            return [];
        }
        $questions = [];
        for ($i = 1; $i <= 5; $i++) {
            $questions[$i] = $row["pregunta_{$i}"];
        }
        return $questions;
    }

    /**
     * Verifica las 5 respuestas (en texto plano) contra los hashes almacenados.
     */
    public static function verifyAnswers(int $userId, array $answers): bool
    {
        $row = self::forUser($userId);
        if ($row === null || count($answers) !== 5) {
            return false;
        }
        for ($i = 1; $i <= 5; $i++) {
            $given = strtolower(trim((string)($answers[$i] ?? '')));
            $hash  = (string)$row["respuesta_{$i}"];
            if (!password_verify($given, $hash)) {
                return false;
            }
        }
        return true;
    }
}