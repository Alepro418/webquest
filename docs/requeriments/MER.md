# Esquema de Base de Datos para Plataforma Educativa - Webquest #

## 1. Entidades de Usuario y Seguridad ##
## Finalidad: Gestionar el acceso al sistema (sin correo electrónico) y el proceso de recuperación de contraseña mediante preguntas de seguridad. ##

## Tabla : Usuarios
_____________________________________________________________________________________________________
|     Campo 	    |         Tipo	            |                    Descripción                    |
|   id_usuario      |  INT, Clave Primaria (PK) |                 identificador único               |
|  Nombre_usuario   |         VARCHAR           |   Nombre único para el inicio de sesión (login)   |
|    password       |         VARCHAR           |           Contraseña almacenada como hash         |
|      rol          |          ENUM             |      Rol del usuario: 'docente' o 'estudiante'    |
| nombre_completo   |         VARCHAR           |            Nombre completo del usuario            |
| fecha_registro	|         DATETIME	        |             Fecha y hora del registro             |
-----------------------------------------------------------------------------------------------------

## Tabla: Preguntas_Seguridad
_____________________________________________________________________________________________________
|Campo	           |Tipo                        |                   	Descripción                 |
id_pregunta        |	INT	Clave Primaria (PK).|                                                   |
id_usuario         |	INT	Clave Foránea (FK)  |          que referencia a Usuario.id_usuario.     |
pregunta_1         | 	      VARCHAR	        |                Pregunta de seguridad 1            |
respuesta_1        |          VARCHAR	        |         Hash de la respuesta a la pregunta 1      |
pregunta_2         |     	  VARCHAR           |            	Pregunta de seguridad 2             |  
respuesta_2        |       	  VARCHAR           |   	   Hash de la respuesta a la pregunta 2     |
pregunta_3         |	      VARCHAR           |	            Pregunta de seguridad 3             |
respuesta_3	       |          VARCHAR           |          Hash de la respuesta a la pregunta 3     |
pregunta_4         |       	  VARCHAR           |            	Pregunta de seguridad 4             |
respuesta_4	       |          VARCHAR	        |          Hash de la respuesta a la pregunta 4     |
pregunta_5         |          VARCHAR           |	            Pregunta de seguridad 5             |
respuesta_5	       |          VARCHAR	        |          Hash de la respuesta a la pregunta 5     |
-----------------------------------------------------------------------------------------------------

## 2. Entidades Académicas (Ciencias Naturales) ##
## Finalidad: Gestionar la creación y estructuración de contenidos académicos por parte del docente. ##

## Tabla: Asignatura
___________________________________________________________________________________________________________________
|Campo	           |Tipo                        |                   	Descripción                               |
id_asignatura      |	INT	Clave Primaria (PK) |                                                                 |
titulo             |	      VARCHAR           |                  Título de la asignatura                        |
descripcion        | 	       TEXT    	        |                Descripción detallada del contenido              |
id_docente         |    INT	Clave Foránea (FK)	|               que referencia a Usuarios.id_usuario              |
-------------------------------------------------------------------------------------------------------------------

## Tabla: Recursos
___________________________________________________________________________________________________________________
|Campo	           |Tipo                        |                   	Descripción                               |
id_recurso         |	INT	Clave Primaria (PK) |                                                                 |
id_asignatura      |	INT	Clave Foránea (FK)  |           que referencia a Asignaturas.id_asignatura            |
tipo               | 	       ENUM    	        |          Tipo de recurso: 'enlace', 'video', 'documento'        |
url_o_ruta         |          VARCHAR       	|          Enlace web o ruta de almacenamiento del archivo        |
-------------------------------------------------------------------------------------------------------------------

## 3. Entidades de Ejecución y Evaluación (Taller) ##
## Finalidad: Gestionar el flujo de entrega de trabajos (fotografías de libretas) y el proceso de evaluación cualitativa y cuantitativa por parte del docente.

## Tabla: Entregas_Taller
_________________________________________________________________________________________________________________________
|Campo	           |Tipo                        |                   	Descripción                                     |
id_entrega         |	INT	Clave Primaria (PK) |                                                                       |
id_estudiante      |	INT	Clave Foránea (FK)  |             que referencia a Usuario.id_usuario                       |
id_asignatura      | 	INT	Clave Foránea (FK)  |          que referencia a Asignaturas.id_asignatura                   |
ruta_fotografia    |          VARCHAR	        |     Ubicación de almacenamiento de la imagen de la libreta            |
fecha_envio        |     	 TIMESTAMP          |            	  Marca de tiempo de la entrega                         | 
-------------------------------------------------------------------------------------------------------------------------

## Tabla: Evaluaciones
_______________________________________________________________________________________________________________________________________________________
|Campo	           |           Tipo              |                   	                      Descripción                                             |
id_evaluacion      |  INT Clave Primaria (PK)    |   	                                                                                              |
id_entrega         |  INT Clave Foránea (FK)     |	                             Que referencia a Entregas_Taller.id_entrega                          |
puntaje 	       |           INT               |                             Calificación numérica en una escala de 0 a 20                          |
escala_letra       |       	  CHAR(1)            |                                Calificación en letra: 'A', 'B', 'C', 'D'                           |
observaciones      |           TEXT     	     |                                   Comentarios cualitativos del docente                             |
estatus            |           ENUM              |	            Estado motivacional asignado ('Sobresaliente', 'Buen estudiante', 'Vamos a mejorar')  |
-------------------------------------------------------------------------------------------------------------------------------------------------------

## 4. Relaciones Principales del Esquema ##

Docente - Asignatura: Un docente (Usuarios.rol = 'docente') puede crear muchas asignaturas. Relación 1:N entre Usuarios y Asignaturas (a través de Asignaturas.id_docente).

Asignatura - Recurso: Una asignatura puede tener muchos recursos asociados (enlaces, videos, documentos). Relación 1:N entre Asignaturas y Recursos (a través de Recursos.id_asignatura).

Estudiante - Entrega: Un estudiante (Usuarios.rol = 'estudiante') puede realizar muchas entregas de taller para diferentes asignaturas. Relación 1:N entre Usuarios y Entregas_Taller (a través de Entregas_Taller.id_estudiante).

Entrega - Evaluación: Cada entrega de taller genera una y solo una evaluación por parte del docente. Relación 1:1 entre Entregas_Taller y Evaluaciones (a través de Evaluaciones.id_entrega).

Resumen de Integridad Referencial:

Preguntas_Seguridad.id_usuario → Usuarios.id_usuario

Asignaturas.id_docente → Usuarios.id_usuario

Recursos.id_asignatura → Asignaturas.id_asignatura

Entregas_Taller.id_estudiante → Usuarios.id_usuario

Entregas_Taller.id_asignatura → Asignaturas.id_asignatura

Evaluaciones.id_entrega → Entregas_Taller.id_entrega