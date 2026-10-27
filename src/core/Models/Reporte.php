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

    /**
     * Cohortes (grado+sección) en los que imparte el docente.
     */
    public static function cohortesDeDocente(int $docenteId): array
    {
        return Database::rows(
            'SELECT DISTINCT grado, seccion
             FROM Asignaturas
             WHERE id_docente = :d
             ORDER BY grado, seccion',
            [':d' => $docenteId]
        );
    }

    /**
     * Resumen GENERAL de una sección para el docente (todas sus asignaturas de la cohorte).
     * Cada fila es un estudiante con totales y promedio global; si $estudianteId se indica,
     * solo se devuelve ese estudiante con su desglose por asignatura.
     */
    public static function resumenSeccion(int $docenteId, int $grado, string $seccion, ?int $estudianteId = null): array
    {
        $subjects = Database::rows(
            'SELECT * FROM Asignaturas
             WHERE id_docente = :d AND grado = :g AND seccion = :s
             ORDER BY titulo',
            [':d' => $docenteId, ':g' => $grado, ':s' => $seccion]
        );

        $students = Usuario::studentsByGrade($grado, $seccion);
        if ($estudianteId !== null) {
            $students = array_values(array_filter($students, fn($s) => (int)$s['id_usuario'] === $estudianteId));
        }
        usort($students, fn($a, $b) => strcasecmp($a['nombre_completo'], $b['nombre_completo']));

        $rows = [];
        foreach ($students as $st) {
            $row = [
                'id_usuario'    => (int)$st['id_usuario'],
                'nombre'        => $st['nombre_completo'],
                'asignaturas'   => count($subjects),
                'talleres'      => 0,
                'entregados'    => 0,
                'evaluados'     => 0,
                'suma'          => 0.0,
                'promedio'      => null,
                'detalle'       => [],
            ];

            foreach ($subjects as $asig) {
                $prog = self::progresoEstudianteEnAsignatura((int)$st['id_usuario'], $asig);
                $row['talleres']   += $prog['total'];
                $row['entregados'] += $prog['entregados'];
                $row['evaluados']  += $prog['evaluados'];
                foreach ($prog['talleres'] as $tall) {
                    if ($tall['evaluacion'] !== null) {
                        $row['suma'] += (float)$tall['evaluacion']['puntaje'];
                    }
                }
                $row['detalle'][] = [
                    'titulo'     => $asig['titulo'],
                    'talleres'   => $prog['total'],
                    'entregados' => $prog['entregados'],
                    'evaluados'  => $prog['evaluados'],
                    'promedio'   => $prog['promedio'],
                ];
            }

            if ($row['evaluados'] > 0) {
                $row['promedio'] = round($row['suma'] / $row['evaluados'], 1);
            }
            unset($row['suma']);
            $rows[] = $row;
        }

        return ['subjects' => count($subjects), 'rows' => $rows];
    }

    /** Promedio de una sección (media de los promedios de estudiantes evaluados). */
    public static function promedioSeccion(array $resumen): ?float
    {
        $proms = array_values(array_filter(array_column($resumen['rows'], 'promedio')));
        if (count($proms) === 0) {
            return null;
        }
        return round(array_sum($proms) / count($proms), 1);
    }

    /**
     * Estatus motivacional completo (icono, colores, mensaje) según promedio.
     */
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