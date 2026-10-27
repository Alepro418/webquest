-- ============================================================
-- WEBQUEST - CIENCIAS NATURALES
-- Esquema de base de datos (Modelo Entidad-Relación)
-- Versión 2.1 - FKs separadas para compatibilidad con phpMyAdmin
-- ============================================================

CREATE DATABASE IF NOT EXISTS webquest
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE webquest;

-- ----------------------------------------
-- Orden de borrado (de hojas a raíces)
-- ----------------------------------------
DROP TABLE IF EXISTS Password_Resets;
DROP TABLE IF EXISTS Evaluaciones;
DROP TABLE IF EXISTS Entregas_Taller;
DROP TABLE IF EXISTS Recursos;
DROP TABLE IF EXISTS Talleres;
DROP TABLE IF EXISTS Asignaturas;
DROP TABLE IF EXISTS Proyectos_Aula;
DROP TABLE IF EXISTS Preguntas_Seguridad;
DROP TABLE IF EXISTS Usuarios;

-- ============================================================
-- 1. Entidades de Usuario y Seguridad
-- ============================================================

CREATE TABLE Usuarios (
    id_usuario        INT AUTO_INCREMENT PRIMARY KEY,
    nombre_usuario    VARCHAR(50)  NOT NULL UNIQUE,
    password          VARCHAR(255) NOT NULL,
    rol               ENUM('docente','estudiante') NOT NULL,
    nombre_completo   VARCHAR(120) NOT NULL,
    email             VARCHAR(120) NULL,
    grado             TINYINT      NULL,
    seccion           CHAR(1)      NULL,
    intentos_fallidos TINYINT      NOT NULL DEFAULT 0,
    bloqueado_hasta   DATETIME     NULL,
    fecha_registro    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE Preguntas_Seguridad (
    id_pregunta   INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario    INT NOT NULL,
    pregunta_1    VARCHAR(120) NOT NULL,
    respuesta_1   VARCHAR(255) NOT NULL,
    pregunta_2    VARCHAR(120) NOT NULL,
    respuesta_2   VARCHAR(255) NOT NULL,
    pregunta_3    VARCHAR(120) NOT NULL,
    respuesta_3   VARCHAR(255) NOT NULL,
    pregunta_4    VARCHAR(120) NOT NULL,
    respuesta_4   VARCHAR(255) NOT NULL,
    pregunta_5    VARCHAR(120) NOT NULL,
    respuesta_5   VARCHAR(255) NOT NULL,
    UNIQUE KEY uq_preguntas_usuario (id_usuario)
) ENGINE=InnoDB;

ALTER TABLE Preguntas_Seguridad
    ADD CONSTRAINT fk_preguntas_usuario
    FOREIGN KEY (id_usuario) REFERENCES Usuarios(id_usuario)
    ON DELETE CASCADE;

-- ============================================================
-- 2. Proyectos de Aula
-- ============================================================

CREATE TABLE Proyectos_Aula (
    id_proyecto     INT AUTO_INCREMENT PRIMARY KEY,
    titulo          VARCHAR(150) NOT NULL,
    descripcion     TEXT,
    momento         ENUM('1','2','3') NOT NULL,
    anio_escolar    VARCHAR(9)   NOT NULL,
    grado           TINYINT      NOT NULL,
    seccion         CHAR(1)      NOT NULL,
    id_docente      INT          NOT NULL,
    fecha_inicio    DATE         NULL,
    fecha_fin       DATE         NULL,
    estado          ENUM('borrador','publicada','cerrada') NOT NULL DEFAULT 'borrador',
    fecha_creacion  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_proyecto_periodo (id_docente, grado, seccion, momento, anio_escolar)
) ENGINE=InnoDB;

ALTER TABLE Proyectos_Aula
    ADD CONSTRAINT fk_proyecto_docente
    FOREIGN KEY (id_docente) REFERENCES Usuarios(id_usuario)
    ON DELETE CASCADE;

-- ============================================================
-- 3. Entidades Académicas
-- ============================================================

CREATE TABLE Asignaturas (
    id_asignatura  INT AUTO_INCREMENT PRIMARY KEY,
    id_proyecto    INT          NOT NULL,
    titulo         VARCHAR(120) NOT NULL,
    descripcion    TEXT,
    estado         ENUM('borrador','publicada') NOT NULL DEFAULT 'borrador',
    fecha_creacion DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

ALTER TABLE Asignaturas
    ADD CONSTRAINT fk_asignatura_proyecto
    FOREIGN KEY (id_proyecto) REFERENCES Proyectos_Aula(id_proyecto)
    ON DELETE CASCADE;

CREATE TABLE Talleres (
    id_taller     INT AUTO_INCREMENT PRIMARY KEY,
    id_asignatura INT NOT NULL,
    titulo        VARCHAR(150) NOT NULL,
    descripcion   TEXT,
    unidad        VARCHAR(50)  NULL,
    fecha_limite  DATE NULL
) ENGINE=InnoDB;

ALTER TABLE Talleres
    ADD CONSTRAINT fk_taller_asignatura
    FOREIGN KEY (id_asignatura) REFERENCES Asignaturas(id_asignatura)
    ON DELETE CASCADE;

CREATE TABLE Recursos (
    id_recurso     INT AUTO_INCREMENT PRIMARY KEY,
    id_asignatura  INT NOT NULL,
    titulo         VARCHAR(150) NOT NULL,
    tipo           ENUM('enlace','video','documento') NOT NULL,
    url_o_ruta     VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

ALTER TABLE Recursos
    ADD CONSTRAINT fk_recurso_asignatura
    FOREIGN KEY (id_asignatura) REFERENCES Asignaturas(id_asignatura)
    ON DELETE CASCADE;

-- ============================================================
-- 4. Entidades de Ejecución y Evaluación
-- ============================================================

CREATE TABLE Entregas_Taller (
    id_entrega       INT AUTO_INCREMENT PRIMARY KEY,
    id_estudiante    INT NOT NULL,
    id_taller        INT NOT NULL,
    ruta_fotografia  VARCHAR(255) NOT NULL,
    comentario       TEXT NULL,
    fecha_envio      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_estudiante_taller (id_estudiante, id_taller)
) ENGINE=InnoDB;

ALTER TABLE Entregas_Taller
    ADD CONSTRAINT fk_entrega_estudiante
    FOREIGN KEY (id_estudiante) REFERENCES Usuarios(id_usuario)
    ON DELETE CASCADE;

ALTER TABLE Entregas_Taller
    ADD CONSTRAINT fk_entrega_taller
    FOREIGN KEY (id_taller) REFERENCES Talleres(id_taller)
    ON DELETE CASCADE;

CREATE TABLE Evaluaciones (
    id_evaluacion    INT AUTO_INCREMENT PRIMARY KEY,
    id_entrega       INT NOT NULL,
    puntaje          DECIMAL(4,1) NOT NULL,
    escala_letra     CHAR(1) NOT NULL,
    observaciones    TEXT,
    estatus          VARCHAR(50),
    fecha_evaluacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_evaluacion_entrega (id_entrega)
) ENGINE=InnoDB;

ALTER TABLE Evaluaciones
    ADD CONSTRAINT fk_evaluacion_entrega
    FOREIGN KEY (id_entrega) REFERENCES Entregas_Taller(id_entrega)
    ON DELETE CASCADE;

-- ============================================================
-- 5. Recuperación de acceso (tokens)
-- ============================================================

CREATE TABLE Password_Resets (
    id_reset       INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario     INT NOT NULL,
    token          VARCHAR(64) NOT NULL,
    expiracion     DATETIME NOT NULL,
    usado          TINYINT NOT NULL DEFAULT 0,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

ALTER TABLE Password_Resets
    ADD CONSTRAINT fk_reset_usuario
    FOREIGN KEY (id_usuario) REFERENCES Usuarios(id_usuario)
    ON DELETE CASCADE;