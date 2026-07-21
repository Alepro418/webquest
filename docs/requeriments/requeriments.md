==========================================================================
ESPECIFICACIÓN DE REQUERIMIENTOS DEL SISTEMA: WEBQUEST (CIENCIAS NATURALES)
==========================================================================

1. DESCRIPCIÓN GENERAL
----------------------
La Webquest es una plataforma pedagógica digital diseñada para la educación primaria. El sistema facilita la gestión académica permitiendo que el docente diseñe asignaciones e integre recursos multimedia para optimizar la comprensión cognitiva. El enfoque principal es el aprendizaje híbrido, donde el entorno digital sirve como guía y medio de entrega, pero la ejecución se mantiene en el plano físico (escritura manuscrita).

2. ARQUITECTURA Y METODOLOGÍA
-----------------------------
- Metodología de Desarrollo: Cascada (Waterfall).
- Arquitectura del Sistema: Monolítica.
- Patrón de Diseño: Modelo-Vista-Controlador (MVC).

3. MÓDULOS DEL SISTEMA
----------------------

3.1 Autenticación y Seguridad
- Gestión de acceso diferenciada para docentes y estudiantes.
- Restricción estricta de uso de correos electrónicos para menores de edad.
- Registro supervisado: Los padres o tutores deben avalar el alta del menor.
- Sistema de Recuperación: Uso de un formulario de cinco (5) preguntas de seguridad configuradas durante el registro para el restablecimiento de contraseñas.

3.2 Preparación Docente
- Gestión de Contenidos: Interfaz para estructurar unidades temáticas.
- Curaduría de Recursos: Capacidad para adjuntar hipervínculos, material audiovisual y documentos descargables.

3.3 Área de Selección (Estudiante)
- Visualización de carga académica disponible.
- Autonomía de selección según el ritmo de aprendizaje y nivel de comprensión del alumno.

3.4 Taller (Ejecución Híbrida)
- Espacio de análisis y visualización de la actividad.
- Entrega de evidencias: Carga de registro fotográfico de la labor realizada en la libreta física del estudiante.

3.5 Revisión y Evaluación
- Ponderación Cuantitativa: Escala de 0 a 20 puntos.
- Ponderación Cualitativa (Escala de Letras):
    * A (20 - 18 pts): Sobresaliente.
    * B (17 - 15 pts): Distinguido.
    * C (14 - 12 pts): Aprobado.
    * D (11 pts): Por mejorar.
- Feedback: Sistema de observaciones pedagógicas personalizadas por el docente.

3.6 Panel de Control Docente
- Monitoreo de Cohorte: Visualización de estudiantes en formato de cuadrícula o lista.
- Analítica Individual: Seguimiento de actividades completadas y promedios acumulados.

3.7 Mi Estado (Estatus Motivacional)
- Indicadores de progreso para el alumno.
- Títulos de desempeño automáticos ("Sobresaliente", "Buen estudiante", "Vamos a mejorar juntos") basados en la tasa de éxito para incentivar el compromiso escolar.

4. CASOS DE PARTICULARIDAD: RECUPERACIÓN DE ACCESO
-------------------------------------------------
Ante la ausencia de correos electrónicos, el sistema implementará un protocolo de desafío de seguridad. Al solicitar la recuperación, el controlador validará las respuestas del formulario contra el hash almacenado en el modelo de seguridad. La coincidencia exitosa permitirá la redefinición de la clave de acceso.
==========================================================================