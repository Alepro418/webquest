-- ============================================================
-- WEBQUEST - CIENCIAS NATURALES
-- erasier.sql - Elimina TODAS las tablas del sistema.
--
-- Úsalo para reiniciar la base de datos antes de volver a cargar
-- schema.sql (o schema2.sql) y seed.php.
--
-- Uso:  mysql -u root -p webquest < src/db/erasier.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS webquest
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE webquest;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS Password_Resets;
DROP TABLE IF EXISTS Evaluaciones;
DROP TABLE IF EXISTS Entregas_Taller;
DROP TABLE IF EXISTS Recursos;
DROP TABLE IF EXISTS Talleres;
DROP TABLE IF EXISTS Asignaturas;
DROP TABLE IF EXISTS Proyectos_Aula;
DROP TABLE IF EXISTS Preguntas_Seguridad;
DROP TABLE IF EXISTS Usuarios;

SET FOREIGN_KEY_CHECKS = 1;
