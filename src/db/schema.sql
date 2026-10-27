-- ===================================================================
-- WEBQUEST - CIENCIAS NATURALES
-- Esquema de base de datos (Modelo Entidad relación)
-- Versión 2.0 - Incluye Proyectos de Aula
-- ===================================================================

CREATE DATABASE IF NOT EXISTS webquest
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE webquest;

-- ------------------------------------------------------------------
-- Orden de borrado (de hojas a raices)
-- ------------------------------------------------------------------
DROP TABLE IF EXISTS Password_Resets;
DROP TABLE IF EXISTS Evaluaciones;
DROP TABLE IF EXISTS Entregas_Taller;
DROP TABLE IF EXISTS Recursos;
DROP TABLE IF EXISTS Talleres;
DROP TABLE IF EXISTS Asignaturas;
DROP TABLE IF EXISTS Proyectos_Aula;
DROP TABLE IF EXISTS Preguntas_Seguridad;
DROP TABLE IF EXISTS Usuarios;

-- ------------------------------------------------------------------
-- 1. Entidades de Usuario y Seguridad
-- ------------------------------------------------------------------

CREATE TABLE Usuarios (
    id_usuario          INT AUTO_INCREMENT PRIMARY KEY,
    nombre_usuario      VARCHAR(50) NOT NULL UNIQUE,
    password            VARCHAR(255) NOT NULL,
    rol                 ENUM('docente', 'estudiante') NOT NULL,
    nombre_completo     VARCHAR(120) NOT NULL,
    email               VARCHAR(120) NULL,
    grado               TINYINT      NULL,
    seccion             CHAR(1)      NULL,
    intentos_fallidos   TINYINT      NOT NULL,
    bloqueado_hasta     DATETIME     NULL,
    fecha_registro      DATETIME     NOT NULL
) ENGINE=InnoDB;

CREATE TABLE Preguntas_Seguridad (
    id_pregunta     INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario      INT NOT NULL UNIQUE,
    pregunta_1      VARCHAR(120) NOT NULL,
    respuesta_1     VARCHAR(120) NOT NULL,
    pregunta_2      VARCHAR(120) NOT NULL,
    respuesta_2     VARCHAR(120) NOT NULL,
    pregunta_3      VARCHAR(120) NOT NULL,
    respuesta_3     VARCHAR(120) NOT NULL,
    pregunta_4      VARCHAR(120) NOT NULL,
    respuesta_4     VARCHAR(120) NOT NULL,
    pregunta_5      VARCHAR(120) NOT NULL, 
    respuesta_5     VARCHAR(120) NOT NULL 
    CONSTRAINT fk_preguntas_usuario
        FOREIGN KEY (id_usuario) REFERENCES Usuarios(id_usuario) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------------
-- 2. Proyectos de Aula (NUEVO NIVEL)
-- ------------------------------------------------------------------

-- Tabla: Proyectos_Aula
-- Un PA Correspondiente a un grado, sección y lapso específico de un docente.
-- Aquí viven los datos generales que antes no tenían lugar.
CREATE TABLE Proyectos_Aula (
    id_proyecto     INT AUTO_INCREMENT PRIMARY KEY,
    titulo          VARCHAR(150) NOT NULL,
    descripcion     TEXT,
    momento           ENUM('1', '2', '3') NOT NULL, -- 1er, 2do, 3er momento
    anio_escolar    VARCHAR(9) NOT NULL,          -- Ej: "2025 - 2026"
    grado           TINYINT NOT NULL,             -- 1..6 (primaria)
    seccion         CHAR(1) NOT NULL,             -- 'A', 'B', 'C'...
    id_docente      INT NOT NULL,
    fecha_inicio    DATE NULL,
    fecha_fin       DATE NULL,
    estado          ENUM('borrador', 'publicada', 'cerrada') NOT NULL DEFAULT 'borrador',
    fecha_creacion  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_proyecto_docente
        FOREIGN KEY (id_docente) REFERENCES Usuarios(id_usuario) ON DELETE CASCADE,
    CONSTRAINT fk_proyecto_periodo
        UNIQUE (id_docente, grado, seccion, lapso, anio_escolar)
) ENGINE=InnoDB;

-- ------------------------------------------------------------------
-- 3. Entidades Académicas
-- ------------------------------------------------------------------

-- Tabla: Asignaturas
-- Ahora dependen de un Proyecto_Aula en lugar de depender directamente del docente
-- Grado, sección y lapso se heredan del PA (no se repiten aquí).
CREATE TABLE Asignaturas (
    id_asignatura   INT AUTO_INCREMENT PRIMARY KEY,
    id_proyecto     INT NOT NULL,
    titulo          VARCHAR(120) NOT NULL,
    descripcion     TEXT,
    estado          ENUM('borrador', 'publicada') NOT NULL DEFAULT 'borrador',
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_asignatura_proyecto
        FOREIGN KEY (id_proyecto) REFERENCES Proyecto_Aula(id_proyecto) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Tabla: Talleres (Unidades temáticas dentro de una asignatura)
CREATE Talleres (
    id_taller       INT AUTO_INCREMENT PRIMARY KEY,
    id_asignatura   INT NOT NULL,
    titulo          VARCHAR(150) NOT NULL,
    descripcion     TEXT,
    unidad          VARCHAR(50) NULL,
    fehac_limite    DATE NULL,
    CONSTRAINT fk_taller_asignatura
        FOREIGN KEY (id_asignatura) REFERENCES Asignaturas(id_asignatura) ON DELETE CASCADEd
) ENGINE=InnoDB;

-- Tabla: Recursos
CREATE TABLE Recursos (
    id_recurso      INT AUTO_INCREMENT PRIMARY KEY;
    id_asignatura   INT NOT NULL,
    titulo          VARCHAR(150) NOT NULL,
    tipo            ENUM('enlace', 'video', 'documento') NOT NULL,
    url_o_ruta      VARCHAR(255) NOT NULL,
    CONSTRAINT fk_recurso_asignatura
        FOREIGN KEY (id_asignatura) REFERENCES Asignaturas(id_asignatura) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------------
-- 4. Entidades de Ejecución y Evaluación
-- ------------------------------------------------------------------

CREATE TABLE Entregas_Taller (
    id_entrega      INT AUTO_INCREMENT PRIMARY KEY,
    id_estudiante   INT NOT NULL,
    id_taller       INT NOT NULL,
    ruta_fotografia VARCHAR(255) NOT NULL,
    comentario      TEXT NULL,
    fecha_envio     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_estudiante_taller UNIQUE (id_estudiante, id_taller),
    CONSTRAINT fk_entrega_estudiante
        FOREIGN KEY (id_estudiante) REFERENCES Usuarios(id_usuario) ON DELETE CASCADE,
    CONSTRAINT fk_entrega_taller
        FOREIGN KEY (id_taller) REFERENCES Talleres(id_taller) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE Evaluaciones (
    id_evaluacion       INT AUTO_INCREMENT PRIMARY KEY,
    id_entrega          INT NOT NULL UNIQUE,
    puntaje             DECIMAL(4, 1) NOT NULL,
    escala_letra        CHAR(1) NOT NULL,
    observaciones       TEXT,
    estatus             VARCHAR(50),
    fecha_evaluacion    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_evaluacion_entrega
        FOREIGN KEY (id_entrega) REFERENCES Entregas_Taller(id_entrega) ON DELETE CASCADE,
) ENGINE=InnoDB;

-- ------------------------------------------------------------------
-- 5. Recuperación de acceso (tokens)
-- ------------------------------------------------------------------

CREATE TABLE Password_Resets (
    id_reset        INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario      INT NOT NULL,
    token           VARCHAR(64) NOT NULL,
    expiracion      DATETIME NOT NULL,
    usado           TINYINT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_reset_usuario
        FOREIGN KEY (id_usuario) REFERENCES Usuarios(id_usuario) ON DELETE CASCADE
) ENGINE=InnoDB