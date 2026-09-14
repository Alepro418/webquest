<?php
declare(strict_types=1);

/**
 * Reporte - Agregaciones y estadísticas para paneles y reportes.
 */
final class Reporte
{
    /**
     * KPIs del docente: estudiantes en su cohorte, entregas de hoy,
     * promedio grupal y estudiantes "por mejorar".
     */
    public static function kpisDocente(int $docenteId): array
    {
        $students = self::studentsByDocenteCohorte($docenteId);

        $totales = count($students);
        $promedios = array_filter(array_column($students, 'promedio'));
        $promedioGrupal = count($promedios) > 0 ? round(array_sum($promedios) / count($promedios), 1) : 0.0;
        $porMejorar = 0;
        foreach ($students as $s) {
            if ($s['promedio'] !== null && $s['promedio'] < 12) {
                $porMejorar++;
            }
        }

        $entregasHoy = (int) Database::scalar(
            'SELECT COUNT(*)
             FROM Entregas_Taller et
             JOIN Talleres t ON t.id_taller = et.id_taller
             JOIN Asignaturas a ON a.id_asignatura = t.id_asignatura
             WHERE a.id_docente = :d AND DATE(et.fecha_envio) = CURDATE()',
            [':d' => $docenteId]
        );

        return [
            'total_estudiantes' => $totales,
            'entregas_hoy'      => $entregasHoy,
            'promedio_grupal'   => $promedioGrupal,
            'por_mejorar'       => $porMejorar,
            'total_evaluadas'   => count(array_filter($promedios)),
        ];
    }

    /** Estudiantes que pertenecen a las cohortes de las asignaturas del docente. */
    public static function studentsByDocenteCohorte(int $docenteId): array
    {
        $rows = Database::rows(
            'SELECT DISTINCT u.id_usuario, u.nombre_completo, u.grado, u.seccion
             FROM Asignaturas a
             JOIN Usuarios u ON u.rol = \'estudiante\' AND u.grado = a.grado AND u.seccion = a.seccion
             WHERE a.id_docente = :d
             ORDER BY u.grado, u.seccion, u.nombre_completo',
            [':d' => $docenteId]
        );

        foreach ($rows as &$row) {
            $avg = Evaluacion::promedioDeEstudiante((int)$row['id_usuario']);
            $row['promedio'] = $avg;
            $row['estatus']  = $avg !== null ? Evaluacion::estatus($avg) : null;
            $row['entregas'] = (int) Database::scalar(
                'SELECT COUNT(*) FROM Entregas_Taller WHERE id_estudiante = :s',
                [':s' => (int)$row['id_usuario']]
            );
        }
        unset($row);
        return $rows;
    }

    /**
     * Progreso de un estudiante en una asignatura pública de su cohorte:
     * talleres, entregados y promedio.
     */
    public static function progresoEstudianteEnAsignatura(int $estudianteId, array $asignatura): array
    {
        $talleres = Taller::byAsignatura((int)$asignatura['id_asignatura']);
        $entregados = 0;
        $evaluados = 0;
        $acumulado = 0.0;

        foreach ($talleres as &$taller) {
            $entrega = Entrega::byTallerAndStudent((int)$taller['id_taller'], $estudianteId);
            $taller['entrega'] = $entrega;
            $taller['evaluacion'] = $entrega ? Evaluacion::byEntrega((int)$entrega['id_entrega']) : null;
            if ($entrega !== null) {
                $entregados++;
            }
            if ($taller['evaluacion'] !== null) {
                $evaluados++;
                $acumulado += (float)$taller['evaluacion']['puntaje'];
            }
        }
        unset($taller);

        return [
            'asignatura'  => $asignatura,
            'talleres'    => $talleres,
            'total'       => count($talleres),
            'entregados'  => $entregados,
            'evaluados'   => $evaluados,
            'promedio'    => $acumulado > 0 ? round($acumulado / $evaluados, 1) : null,
        ];
    }

    /** Estatus motivacional completo (icono, colores, mensaje) según promedio. */
    public static function estatusConfig(?float $promedio): array
    {
        $list = Config::get('academic.evaluation.status_motivacional', []);
        foreach ($list as $nombre => $cfg) {
            if ($promedio !== null && $promedio >= (float)$cfg['threshold']) {
                return ['nombre' => $nombre] + (array)$cfg;
            }
        }
        $vacio = $list['Vamos a mejorar'] ?? ['icon' => '🌱', 'color' => '#9a3412', 'bg_color' => '#ffedd5', 'message' => ''];
        return ['nombre' => 'Vamos a mejorar', 'promedio' => null] + (array)$vacio;
    }
}