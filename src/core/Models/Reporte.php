<?php
declare(strict_types=1);

/**
 * Reporte - Agregaciones y estadísticas para paneles y reportes.
 *
 * Todos los cálculos parten de un Proyecto de Aula (momento pedagógico), de
 * modo que cada nota queda trazada a su PA, su periodo de fechas y su grado y
 * sección.
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
        $promedios = array_filter(array_column($students, 'promedio'), static fn($p) => $p !== null);
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
             JOIN Proyectos_Aula p ON p.id_proyecto = a.id_proyecto
             WHERE p.id_docente = :d AND DATE(et.fecha_envio) = CURDATE()',
            [':d' => $docenteId]
        );

        return [
            'total_estudiantes' => $totales,
            'entregas_hoy'      => $entregasHoy,
            'promedio_grupal'   => $promedioGrupal,
            'por_mejorar'       => $porMejorar,
            'total_evaluadas'   => count($promedios),
        ];
    }

    /** Estudiantes que pertenecen a las cohortes de los PA del docente. */
    public static function studentsByDocenteCohorte(int $docenteId): array
    {
        $rows = Database::rows(
            'SELECT DISTINCT u.id_usuario, u.nombre_completo, u.grado, u.seccion
             FROM Proyectos_Aula p
             JOIN Usuarios u ON u.rol = \'estudiante\' AND u.grado = p.grado AND u.seccion = p.seccion
             WHERE p.id_docente = :d
             ORDER BY u.grado, u.seccion, u.nombre_completo',
            [':d' => $docenteId]
        );

        foreach ($rows as &$row) {
            $avg = Evaluacion::promedioDeEstudiante((int)$row['id_usuario'], $docenteId);
            $row['promedio'] = $avg;
            $row['estatus']  = $avg !== null ? Evaluacion::estatus($avg) : null;
            $row['entregas'] = (int) Database::scalar(
                'SELECT COUNT(*)
                 FROM Entregas_Taller et
                 JOIN Talleres t ON t.id_taller = et.id_taller
                 JOIN Asignaturas a ON a.id_asignatura = t.id_asignatura
                 JOIN Proyectos_Aula p ON p.id_proyecto = a.id_proyecto
                 WHERE p.id_docente = :d AND et.id_estudiante = :s',
                [':d' => $docenteId, ':s' => (int)$row['id_usuario']]
            );
        }
        unset($row);
        return $rows;
    }

    /**
     * Progreso de un estudiante en una asignatura:
     * talleres, entregados, evaluados y promedio de esa asignatura.
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
            'promedio'    => $evaluados > 0 ? round($acumulado / $evaluados, 1) : null,
        ];
    }

    /** Proyectos de Aula del docente con sus métricas, para los selectores. */
    public static function proyectosDeDocente(int $docenteId): array
    {
        return ProyectoAula::byDocenteWithStats($docenteId);
    }

    /**
     * Resumen general de un Proyecto de Aula: una fila por estudiante con los
     * totales de sus asignaturas en ese momento. Si $estudianteId se indica,
     * solo se devuelve ese estudiante con su desglose por asignatura.
     */
    public static function proyectoResumen(int $proyectoId, ?int $estudianteId = null): ?array
    {
        $proyecto = ProyectoAula::find($proyectoId);
        if ($proyecto === null) {
            return null;
        }

        $subjects = Asignatura::byProyecto($proyectoId);
        $students = Usuario::studentsByGrade((int)$proyecto['grado'], (string)$proyecto['seccion']);
        if ($estudianteId !== null) {
            $students = array_values(array_filter($students, static fn($s) => (int)$s['id_usuario'] === $estudianteId));
        }
        usort($students, static fn($a, $b) => strcasecmp($a['nombre_completo'], $b['nombre_completo']));

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
                    'id_asignatura' => (int)$asig['id_asignatura'],
                    'titulo'        => $asig['titulo'],
                    'talleres'      => $prog['total'],
                    'entregados'    => $prog['entregados'],
                    'evaluados'     => $prog['evaluados'],
                    'promedio'      => $prog['promedio'],
                ];
            }

            $row['promedio_exacto'] = null;
            if ($row['evaluados'] > 0) {
                $exacto = $row['suma'] / $row['evaluados'];
                $row['promedio_exacto'] = $exacto;
                $row['promedio'] = round($exacto, 2);
            }
            $row['porcentaje'] = self::porcentaje($row['entregados'], $row['talleres']);
            unset($row['suma']);
            $rows[] = $row;
        }

        return [
            'proyecto'   => $proyecto,
            'asignaturas' => $subjects,
            'subjects'   => count($subjects),
            'rows'       => $rows,
            'total_talleres' => array_sum(array_column($rows, 'talleres')),
        ];
    }

    /** Totales de la sección para el pie del reporte grupal. */
    public static function totalesGrupales(array $resumen): array
    {
        $rows = $resumen['rows'];
        $talleres  = (int)array_sum(array_column($rows, 'talleres'));
        $entregados = (int)array_sum(array_column($rows, 'entregados'));
        $evaluados = (int)array_sum(array_column($rows, 'evaluados'));

        return [
            'asignaturas' => (int)array_sum(array_column($rows, 'asignaturas')),
            'talleres'    => $talleres,
            'entregados'  => $entregados,
            'evaluados'   => $evaluados,
            'porcentaje'  => self::porcentaje($entregados, $talleres),
            'promedio'    => self::promedioSeccion($resumen),
        ];
    }

    /** Promedio de la sección (media de los promedios de estudiantes evaluados). */
    public static function promedioSeccion(array $resumen): ?float
    {
        $proms = [];
        foreach ($resumen['rows'] as $row) {
            if (($row['promedio_exacto'] ?? null) !== null) {
                $proms[] = (float)$row['promedio_exacto'];
            } elseif (($row['promedio'] ?? null) !== null) {
                $proms[] = (float)$row['promedio'];
            }
        }
        if (count($proms) === 0) {
            return null;
        }
        return round(array_sum($proms) / count($proms), 2);
    }

    /**
     * Reporte individual de un estudiante dentro de un momento: sus
     * asignaturas, sus talleres con estado y nota, el promedio del momento y
     * el estatus motivacional.
     */
    public static function reporteIndividual(int $proyectoId, int $estudianteId): ?array
    {
        $resumen = self::proyectoResumen($proyectoId, $estudianteId);
        if ($resumen === null) {
            return null;
        }

        $alumno = $resumen['rows'][0] ?? null;
        $talleres = [];

        foreach ($resumen['asignaturas'] as $asig) {
            $prog = self::progresoEstudianteEnAsignatura($estudianteId, $asig);
            foreach ($prog['talleres'] as $t) {
                $evaluacion = $t['evaluacion'];
                $entrega = $t['entrega'];
                if ($evaluacion !== null) {
                    $estado = 'Evaluado';
                } elseif ($entrega !== null) {
                    $estado = 'Entregado';
                } else {
                    $estado = 'Sin entrega';
                }
                $talleres[] = [
                    'asignatura'  => $asig['titulo'],
                    'taller'      => $t['titulo'],
                    'unidad'      => $t['unidad'],
                    'fecha_limite' => $t['fecha_limite'],
                    'estado'      => $estado,
                    'puntaje'     => $evaluacion !== null ? (float)$evaluacion['puntaje'] : null,
                    'escala'      => $evaluacion['escala_letra'] ?? null,
                    'estatus'     => $evaluacion['estatus'] ?? null,
                    'observaciones' => $evaluacion['observaciones'] ?? null,
                ];
            }
        }

        return [
            'proyecto'    => $resumen['proyecto'],
            'alumno'      => $alumno,
            'asignaturas' => $resumen['asignaturas'],
            'talleres'    => $talleres,
            'promedio'    => $alumno['promedio'] ?? null,
            'estatus'     => ($alumno !== null && $alumno['promedio'] !== null)
                ? self::estatusConfig((float)$alumno['promedio'])
                : null,
        ];
    }

    /** Porcentaje entero de entregas realizadas. */
    public static function porcentaje(int $parte, int $total): int
    {
        if ($total <= 0) {
            return 0;
        }
        return (int)round($parte * 100 / $total);
    }

    /**
     * Estatus motivacional completo (icono, colores, mensaje) según promedio.
     */
    public static function estatusConfig(?float $promedio): array
    {
        $list = Config::get('academic.evaluation.status_motivacional', []);
        if ($promedio !== null) {
            foreach ($list as $nombre => $cfg) {
                if ($promedio >= (float)$cfg['threshold']) {
                    return ['nombre' => $nombre] + (array)$cfg;
                }
            }
        }
        $vacio = $list['Vamos a mejorar'] ?? ['icon' => '🌱', 'color' => '#9a3412', 'bg_color' => '#ffedd5', 'message' => ''];
        return ['nombre' => 'Vamos a mejorar'] + (array)$vacio;
    }
}
