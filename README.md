# 🌿 Webquest - Plataforma Educativa para Ciencias Naturales

**Versión:** 2.2.0  
**Última actualización:** 28 de octubre de 2026  
**Licencia:** MIT

---

## 📘 Descripción General

**Webquest** es una plataforma pedagógica digital diseñada específicamente para la educación primaria en el área de **Ciencias Naturales**. Su propósito es facilitar la gestión académica, permitiendo a los docentes diseñar asignaciones e integrar recursos multimedia que optimicen la comprensión cognitiva de los estudiantes.

---

## 🧠 Filosofía Educativa

El sistema se fundamenta en el **aprendizaje híbrido**, donde el entorno digital actúa como guía y medio de entrega de contenidos, mientras que la ejecución práctica se mantiene en el plano físico (escritura manuscrita en libretas). Este enfoque promueve la **autonomía del estudiante** y preserva la conexión con metodologías tradicionales de aprendizaje.

---

## 🏗️ Arquitectura del Sistema

| Componente            | Descripción                          |
|-----------------------|--------------------------------------|
| **Metodología**       | Cascada (Waterfall)                  |
| **Arquitectura**      | Monolítica                           |
| **Patrón de diseño**  | Modelo-Vista-Controlador (MVC)       |

---

## 📁 Estructura del Proyecto

/Webquest
├── /docs
│ ├── /codes
│ │ ├── /activity # Código fuente de diagramas de actividad
│ │ ├── /class # Código fuente de diagramas de clase
│ │ ├── /sequence # Código fuente de diagramas de secuencia
│ │ └── /use # Código fuente de diagramas de casos de uso
│ ├── /diagrams
│ │ ├── /activity # Diagramas de actividad (generados)
│ │ ├── /class # Diagramas de clase (generados)
│ │ ├── /sequence # Diagramas de secuencia (generados)
│ │ └── /use # Diagramas de casos de uso (generados)
│ ├── /requirements # Documentación de requerimientos
│ └── structure.txt # Estructura del proyecto
├── /public
│ ├── /css # Hojas de estilo
│ ├── /js # Scripts del lado del cliente
│ ├── /student # Assets específicos del área estudiantil
│ └── /teaching # Assets específicos del área docente
├── /src
│ ├── /core # Lógica de negocio y controladores
│ └── /db # Conexión y consultas a base de datos
├── /template # Plantillas de vistas (HTML/PHP)
├── .gitignore # Archivos ignorados por Git
├── config.json # Configuración del sistema
└── LICENSE.md # Licencia del proyecto


---

## 🗃️ Modelo Entidad-Relación

**Jerarquía académica (versión 2.2):**

```
Momento pedagógico (1er, 2do, 3er momento)
        └── Proyecto de Aula (título, fechas, grado, sección, año escolar)
                └── Asignatura (unidad temática del PA)
                        └── Taller (actividad concreta)
                                └── Entrega → Evaluación (nota del estudiante)
```

Cada nivel depende del anterior: el grado, la sección y el momento pedagógico se
heredan del Proyecto de Aula, de modo que toda nota queda trazada a un momento,
a un PA y a un periodo de fechas.

### 1. Entidades de Usuario y Seguridad

#### Tabla: `Usuarios`
| Campo             | Tipo          | Descripción                               |
|-------------------|---------------|-------------------------------------------|
| `id_usuario`      | INT (PK)      | Identificador único del usuario           |
| `nombre_usuario`  | VARCHAR       | Nombre único para inicio de sesión        |
| `password`        | VARCHAR       | Contraseña almacenada como hash           |
| `rol`             | ENUM          | `'docente'` o `'estudiante'`              |
| `nombre_completo` | VARCHAR       | Nombre completo del usuario               |
| `grado`           | TINYINT       | Cohorte del estudiante (NULL en docentes) |
| `seccion`         | CHAR(1)       | Sección de la cohorte                     |
| `fecha_registro`  | DATETIME      | Fecha y hora del registro                 |

#### Tabla: `Preguntas_Seguridad`
| Campo           | Tipo          | Descripción                               |
|-----------------|---------------|-------------------------------------------|
| `id_pregunta`   | INT (PK)      | Identificador único                       |
| `id_usuario`    | INT (FK)      | Referencia a `Usuarios.id_usuario`        |
| `pregunta_1-5`  | VARCHAR       | Preguntas de seguridad (hasta 5)          |
| `respuesta_1-5` | VARCHAR       | Hash de las respuestas                    |

---

### 2. Proyectos de Aula (nuevo nivel)

#### Tabla: `Proyectos_Aula`
| Campo            | Tipo          | Descripción                                        |
|------------------|---------------|----------------------------------------------------|
| `id_proyecto`    | INT (PK)      | Identificador único del Proyecto de Aula           |
| `titulo`         | VARCHAR(150)  | Nombre del PA (ej. "Ecosistemas Terrestres")        |
| `descripcion`    | TEXT          | Descripción pedagógica del PA                      |
| `momento`        | ENUM('1','2','3') | Momento pedagógico (1er, 2do o 3er momento)     |
| `anio_escolar`   | VARCHAR(9)    | Año escolar en formato `2025-2026`                  |
| `grado`          | TINYINT       | Grado del grupo (1 a 6)                            |
| `seccion`        | CHAR(1)       | Sección del grupo (A, B, C...)                     |
| `id_docente`     | INT (FK)      | Referencia a `Usuarios.id_usuario`                 |
| `fecha_inicio`   | DATE          | Inicio del momento                                 |
| `fecha_fin`      | DATE          | Fin del momento                                    |
| `estado`         | ENUM          | `'borrador'`, `'publicada'` o `'cerrada'`          |

Restricción `UNIQUE (id_docente, grado, seccion, momento, anio_escolar)`: un
momento no puede tener más de un PA para la misma cohorte.

---

### 3. Entidades Académicas

#### Tabla: `Asignatura`
| Campo            | Tipo          | Descripción                               |
|------------------|---------------|-------------------------------------------|
| `id_asignatura`  | INT (PK)      | Identificador único                       |
| `id_proyecto`    | INT (FK)      | Referencia a `Proyectos_Aula.id_proyecto`  |
| `titulo`         | VARCHAR       | Título de la asignatura                   |
| `descripcion`    | TEXT          | Descripción detallada                     |
| `estado`         | ENUM          | `'borrador'` o `'publicada'`               |

El grado, la sección y el momento **ya no se guardan** en la asignatura: se
heredan del Proyecto de Aula.

#### Tabla: `Talleres`
| Campo            | Tipo          | Descripción                               |
|------------------|---------------|-------------------------------------------|
| `id_taller`      | INT (PK)      | Identificador único                       |
| `id_asignatura`  | INT (FK)      | Referencia a `Asignaturas.id_asignatura`  |
| `titulo`         | VARCHAR       | Título del taller                         |
| `descripcion`    | TEXT          | Consigna del taller                       |
| `unidad`         | VARCHAR       | Unidad temática                           |
| `fecha_limite`   | DATE          | Fecha límite de entrega                   |

#### Tabla: `Recursos`
| Campo            | Tipo          | Descripción                               |
|------------------|---------------|-------------------------------------------|
| `id_recurso`     | INT (PK)      | Identificador único                       |
| `id_asignatura`  | INT (FK)      | Referencia a `Asignaturas.id_asignatura`  |
| `tipo`           | ENUM          | `'enlace'`, `'video'`, `'documento'`      |
| `url_o_ruta`     | VARCHAR       | Enlace web o ruta de archivo              |

---

### 4. Entidades de Ejecución y Evaluación

#### Tabla: `Entregas_Taller`
| Campo              | Tipo          | Descripción                                   |
|--------------------|---------------|-----------------------------------------------|
| `id_entrega`       | INT (PK)      | Identificador único                           |
| `id_estudiante`    | INT (FK)      | Referencia a `Usuarios.id_usuario`            |
| `id_taller`        | INT (FK)      | Referencia a `Talleres.id_taller`             |
| `ruta_fotografia`  | VARCHAR       | Ubicación de la imagen de la libreta          |
| `fecha_envio`      | TIMESTAMP     | Marca de tiempo de la entrega                 |

#### Tabla: `Evaluaciones`
| Campo             | Tipo          | Descripción                                   |
|-------------------|---------------|-----------------------------------------------|
| `id_evaluacion`   | INT (PK)      | Identificador único                           |
| `id_entrega`      | INT (FK)      | Referencia a `Entregas_Taller.id_entrega`     |
| `puntaje`         | INT           | Calificación numérica (0-20)                  |
| `escala_letra`    | CHAR(1)       | `'A'`, `'B'`, `'C'`, `'D'`                    |
| `observaciones`   | TEXT          | Comentarios cualitativos                      |
| `estatus`         | ENUM          | `'Sobresaliente'`, `'Buen estudiante'`, `'Vamos a mejorar'` |

---

## 🔧 Módulos del Sistema

### 1. Autenticación y Seguridad
- Gestión de acceso diferenciada para docentes y estudiantes.
- Registro supervisado con aval de padres/tutores (sin uso de correos electrónicos).
- Sistema de recuperación mediante 5 preguntas de seguridad.

### 2. Configuración del Proyecto de Aula
- Asignación de un Proyecto de Aula a cada momento pedagógico.
- Fechas de inicio y fin del momento, grado, sección y año escolar.
- Estados del PA: borrador, publicada y cerrada.
- Bloqueo de la creación de contenido sin un PA publicado.

### 3. Preparación Docente
- Interfaz para estructurar unidades temáticas dentro del PA activo.
- Curaduría de recursos multimedia (enlaces, videos, documentos).

### 4. Área de Selección (Estudiante)
- Visualización de la carga académica disponible por momento.
- Autonomía de selección según ritmo de aprendizaje.

### 5. Taller (Ejecución Híbrida)
- Espacio de análisis y visualización de actividades.
- Entrega de evidencias mediante carga fotográfica de libretas físicas.
- Entregas limitadas al periodo activo del Proyecto de Aula.

### 6. Revisión y Evaluación
- **Ponderación Cuantitativa:** Escala 0–20 puntos.
- **Ponderación Cualitativa:**
  - **A** (20–18 pts): Sobresaliente
  - **B** (17–15 pts): Distinguido
  - **C** (14–12 pts): Aprobado
  - **D** (11–0 pts): Por mejorar
- Feedback personalizado mediante observaciones pedagógicas.

### 7. Panel de Control Docente
- Visualización de cohorte en cuadrícula/lista.
- Analítica individual de actividades y promedios.

### 8. Mi Estado (Estatus Motivacional)
- Indicadores de progreso para el alumno.
- Títulos automáticos basados en desempeño:
  - "Sobresaliente"
  - "Buen estudiante"
  - "Vamos a mejorar juntos"

---

## 🔗 Relaciones Principales

| Relación                          | Tipo    |
|-----------------------------------|---------|
| Docente → Proyecto de Aula        | 1 : N   |
| Proyecto de Aula → Asignatura     | 1 : N   |
| Asignatura → Taller               | 1 : N   |
| Asignatura → Recurso              | 1 : N   |
| Taller → Entrega                  | 1 : N   |
| Estudiante → Entrega              | 1 : N   |
| Entrega → Evaluación              | 1 : 1   |

---

## 🗂️ Configuración de Proyectos de Aula

El docente configura cada **momento pedagógico** desde la sección
*Proyecto de Aula* del panel (`public/teaching/pa.php`):

1. Asignar un Proyecto de Aula a cada momento (1er, 2do o 3er momento).
2. Definir las fechas de inicio y fin del momento (dentro del año escolar).
3. Establecer el grado y la sección del grupo.
4. Cambiar el estado del PA entre `borrador`, `publicada` y `cerrada`.
5. Activar el PA del momento en el que se está trabajando.

**Reglas aplicadas por el sistema:**

- Sin un PA en estado `publicada`, el docente no puede crear ni editar
  asignaturas o talleres (se muestra la advertencia y se ofrece el enlace a
  Configuración).
- Un mismo momento no puede tener dos PA activos para la misma cohorte
  (restricción `UNIQUE`).
- `fecha_fin` debe ser posterior a `fecha_inicio` y ambas deben caber dentro
  del año escolar configurado.
- Un PA `cerrada` no admite nuevas entregas de los estudiantes; sus notas
  siguen consultándose en los reportes.

Los reportes de notas (grupal e individual) se generan **por momento** e
incluyen el PA, el periodo de fechas, el docente y el grado y sección.

---

## 🚀 Puesta en Marcha

```bash
# 1. Crear la base de datos y las tablas
mysql -u root -p < src/db/schema.sql      # o src/db/schema2.sql (phpMyAdmin)

# 2. Cargar los datos de demostración
php src/db/seed.php

# 3. Abrir la aplicación
http://localhost/webquest/public/
```

Credenciales de demostración: docente `Manuel` / `Docente2026!`;
estudiantes `Barbara`, `Daniel`, `Pedro`, `Sandra`, `Miguel` / `Estudiante2026!`.

---

## ⚙️ Características Técnicas Destacadas

- **Seguridad:** Contraseñas y respuestas almacenadas como hash.
- **Sin correo electrónico:** Sistema adaptado para menores de edad.
- **Recuperación de acceso:** Basada exclusivamente en preguntas de seguridad.
- **Escalabilidad:** Estructura modular preparada para crecimiento.

---

## 📌 Estado del Proyecto

| Fase                         | Estado         |
|------------------------------|----------------|
| Análisis y Requerimientos    | ✅ Completada  |
| Diseño del Sistema           | ✅ Completada  |
| Implementación               | 🔄 Iterando    |
| Pruebas                      | 🔄 En curso    |
| Despliegue                   | ⏳ Próxima     |

--- 

## 👥 Roles de Usuario

| Rol           | Responsabilidades                                 |
|---------------|----------------------------------------------------|
| **Docente**   | Creación de contenido, gestión de asignaturas, evaluación |
| **Estudiante**| Visualización de tareas, entrega de trabajos, seguimiento |
| **Padre/Tutor**| Aval de registro (sin acceso directo al sistema) |

---

## 📄 Licencia

Este proyecto está bajo la licencia **MIT**.  
Para más detalles, consulta el archivo [`LICENSE.md`](LICENSE.md).

---

> 🌱 *"Fomentando el aprendizaje híbrido en Ciencias Naturales"*