<?php
declare(strict_types=1);

/**
 * Evaluacion - Modelo de la tabla Evaluaciones, incluye la escala 0-20
 * y el estatus motivacional definidos en config.json (academic.evaluation).
 */
final class Evaluacion
{
    public static function byEntrega(int $entregaId): ?array
    {
        return Database::row(
            'SELECT * FROM Evaluaciones WHERE id_entrega = :e LIMIT 1',
            [':e' => $entregaId]
        );
    }

    /**
     * Escala de letra según config: A (18-20), B (15-17), C (12-14), D (0-11).
     */
    public static function escala(float $puntaje): string
    {
        $scales = Config::get('academic.evaluation.scales', []);
        foreach ($scales as $letra => $range) {
            if ($puntaje >= (float)$range['min'] && $puntaje <= (float)$range['max']) {
                return (string)$letra;
            }
        }
        return 'D';
    }

    /**
     * Estatus motivacional según config (thresholds decrecientes):
     * Sobresaliente (>= 18), Buen estudiante (>= 12), Vamos a mejorar.
     */
    public static function estatus(float $puntaje): string
    {
        $statuses = Config::get('academic.evaluation.status_motivacional', []);
        $result = 'Vamos a mejorar';
        foreach ($statuses as $nombre => $cfg) {
            if ($puntaje >= (float)$cfg['threshold']) {
                $result = $nombre;
                break;
            }
        }
        return $result;
    }

    public static function create(int $entregaId, float $puntaje, ?string $observaciones): int
    {
        $puntaje = max(0.0, min((float)Config::get('academic.evaluation.max_score', 20), $puntaje));
        return (int) Database::execute(
            'INSERT INTO Evaluaciones (id_entrega, puntaje, escala_letra, observaciones, estatus)
             VALUES (:e, :p, :l, :o, :s)',
            [
                ':e' => $entregaId,
                ':p' => $puntaje,
                ':l' => self::escala($puntaje),
                ':o' => $observaciones !== '' ? $observaciones : null,
                ':s' => self::estatus($puntaje),
            ]
        );
    }

    public static function update(int $idEvaluacion, float $puntaje, ?string $observaciones): void
    {
        $puntaje = max(0.0, min((float)Config::get('academic.evaluation.max_score', 20), $puntaje));
        Database::execute(
            'UPDATE Evaluaciones
             SET puntaje = :p, escala_letra = :l, observaciones = :o, estatus = :s
             WHERE id_evaluacion = :id',
            [
                ':p' => $puntaje,
                ':l' => self::escala($puntaje),
                ':o' => $observaciones !== '' ? $observaciones : null,
                ':s' => self::estatus($puntaje),
                ':id' => $idEvaluacion,
            ]
        );
    }

    /** Promedio de un estudiante (o null si no tiene evaluaciones). */
    public static function promedioDeEstudiante(int $estudianteId): ?float
    {
        $v = Database::scalar(
            'SELECT AVG(ev.puntaje)
             FROM Evaluaciones ev JOIN Entregas_Taller et ON et.id_entrega = ev.id_entrega
             WHERE et.id_estudiante = :s',
            [':s' => $estudianteId]
        );
        return $v === null || $v === false ? null : round((float)$v, 1);
    }

    public static function lastByStudent(int $estudianteId, int $limit): array
    {
        return Database::rows(
            'SELECT ev.puntaje, ev.escala_letra, ev.estatus, ev.observaciones, ev.fecha_evaluacion,
                    t.titulo AS taller_titulo, a.titulo AS asignatura_titulo
             FROM Evaluaciones ev
             JOIN Entregas_Taller et ON et.id_entrega = ev.id_entrega
             JOIN Talleres t ON t.id_taller = et.id_taller
             JOIN Asignaturas a ON a.id_asignatura = t.id_asignatura
             WHERE et.id_estudiante = :s
             ORDER BY ev.fecha_evaluacion DESC LIMIT ' . max(1, (int)$limit),
            [':s' => $estudianteId]
        );
    }
}