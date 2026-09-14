# 🌿 Webquest - Plataforma Educativa para Ciencias Naturales

**Versión:** 1.0.0  
**Última actualización:** 14 de septiembre de 2026  
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

### 1. Entidades de Usuario y Seguridad

#### Tabla: `Usuarios`
| Campo             | Tipo          | Descripción                               |
|-------------------|---------------|-------------------------------------------|
| `id_usuario`      | INT (PK)      | Identificador único del usuario           |
| `nombre_usuario`  | VARCHAR       | Nombre único para inicio de sesión        |
| `password`        | VARCHAR       | Contraseña almacenada como hash           |
| `rol`             | ENUM          | `'docente'` o `'estudiante'`              |
| `nombre_completo` | VARCHAR       | Nombre completo del usuario               |
| `fecha_registro`  | DATETIME      | Fecha y hora del registro                 |

#### Tabla: `Preguntas_Seguridad`
| Campo           | Tipo          | Descripción                               |
|-----------------|---------------|-------------------------------------------|
| `id_pregunta`   | INT (PK)      | Identificador único                       |
| `id_usuario`    | INT (FK)      | Referencia a `Usuarios.id_usuario`        |
| `pregunta_1-5`  | VARCHAR       | Preguntas de seguridad (hasta 5)          |
| `respuesta_1-5` | VARCHAR       | Hash de las respuestas                    |

---

### 2. Entidades Académicas

#### Tabla: `Asignatura`
| Campo            | Tipo          | Descripción                               |
|------------------|---------------|-------------------------------------------|
| `id_asignatura`  | INT (PK)      | Identificador único                       |
| `titulo`         | VARCHAR       | Título de la asignatura                   |
| `descripcion`    | TEXT          | Descripción detallada                     |
| `id_docente`     | INT (FK)      | Referencia a `Usuarios.id_usuario`        |

#### Tabla: `Recursos`
| Campo            | Tipo          | Descripción                               |
|------------------|---------------|-------------------------------------------|
| `id_recurso`     | INT (PK)      | Identificador único                       |
| `id_asignatura`  | INT (FK)      | Referencia a `Asignaturas.id_asignatura`  |
| `tipo`           | ENUM          | `'enlace'`, `'video'`, `'documento'`      |
| `url_o_ruta`     | VARCHAR       | Enlace web o ruta de archivo              |

---

### 3. Entidades de Ejecución y Evaluación

#### Tabla: `Entregas_Taller`
| Campo              | Tipo          | Descripción                                   |
|--------------------|---------------|-----------------------------------------------|
| `id_entrega`       | INT (PK)      | Identificador único                           |
| `id_estudiante`    | INT (FK)      | Referencia a `Usuarios.id_usuario`            |
| `id_asignatura`    | INT (FK)      | Referencia a `Asignaturas.id_asignatura`      |
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

### 2. Preparación Docente
- Interfaz para estructurar unidades temáticas.
- Curaduría de recursos multimedia (enlaces, videos, documentos).

### 3. Área de Selección (Estudiante)
- Visualización de carga académica disponible.
- Autonomía de selección según ritmo de aprendizaje.

### 4. Taller (Ejecución Híbrida)
- Espacio de análisis y visualización de actividades.
- Entrega de evidencias mediante carga fotográfica de libretas físicas.

### 5. Revisión y Evaluación
- **Ponderación Cuantitativa:** Escala 0–20 puntos.
- **Ponderación Cualitativa:**
  - **A** (20–18 pts): Sobresaliente
  - **B** (17–15 pts): Distinguido
  - **C** (14–12 pts): Aprobado
  - **D** (11–0 pts): Por mejorar
- Feedback personalizado mediante observaciones pedagógicas.

### 6. Panel de Control Docente
- Visualización de cohorte en cuadrícula/lista.
- Analítica individual de actividades y promedios.

### 7. Mi Estado (Estatus Motivacional)
- Indicadores de progreso para el alumno.
- Títulos automáticos basados en desempeño:
  - "Sobresaliente"
  - "Buen estudiante"
  - "Vamos a mejorar juntos"

---

## 🔗 Relaciones Principales

| Relación                         | Tipo    |
|----------------------------------|---------|
| Docente → Asignatura             | 1 : N   |
| Asignatura → Recurso             | 1 : N   |
| Estudiante → Entrega             | 1 : N   |
| Entrega → Evaluación             | 1 : 1   |

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
| Implementación               | ✅ Completada  |
| Pruebas                      | 🔄 En curso     |
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