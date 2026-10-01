-- =====================================================================
-- ReservaEspacios · Fase 2 · Caso A
-- Recreacion completa de la base de datos (schema.sql)
-- Compatible con MySQL 8+/9+ y MariaDB 10.4+
--
-- Estrategia de la jerarquia de la Fase 1: TABLA UNICA con columna `tipo`
-- (Single Table Inheritance). Una sola tabla `espacios` guarda los campos
-- comunes, la columna `tipo` identifica la subclase y cada subtipo aporta
-- una columna propia que admite NULL cuando no aplica.
-- =====================================================================

DROP DATABASE IF EXISTS reserva_espacios;
CREATE DATABASE reserva_espacios
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
USE reserva_espacios;

-- ---------------------------------------------------------------------
-- Entidad principal: Espacio (jerarquia Sala | Escritorio | Cancha)
-- ---------------------------------------------------------------------
CREATE TABLE espacios (
    id           INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    tipo         VARCHAR(20)   NOT NULL,
    nombre       VARCHAR(80)   NOT NULL,
    capacidad    INT UNSIGNED  NOT NULL,
    tarifa_base  DECIMAL(10,2) NOT NULL,
    imagen       VARCHAR(120)  NULL DEFAULT NULL, -- solo nombre del archivo
    -- columnas especificas por subtipo: NULL cuando no aplican (STI)
    ubicacion    VARCHAR(80)   NULL DEFAULT NULL, -- Sala
    zona         VARCHAR(40)   NULL DEFAULT NULL, -- Escritorio
    deporte      VARCHAR(40)   NULL DEFAULT NULL, -- Cancha

    PRIMARY KEY (id),
    UNIQUE KEY uq_espacios_nombre (nombre),

    CONSTRAINT chk_espacios_tipo      CHECK (tipo IN ('sala', 'escritorio', 'cancha')),
    CONSTRAINT chk_espacios_capacidad CHECK (capacidad > 0),
    CONSTRAINT chk_espacios_tarifa    CHECK (tarifa_base > 0),
    -- cada subtipo debe traer su columna propia (ultima barrera en la BD)
    CONSTRAINT chk_espacios_sala      CHECK (tipo <> 'sala'      OR ubicacion IS NOT NULL),
    CONSTRAINT chk_espacios_escritorio CHECK (tipo <> 'escritorio' OR zona IS NOT NULL),
    CONSTRAINT chk_espacios_cancha    CHECK (tipo <> 'cancha'    OR deporte IS NOT NULL)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Entidad relacionada: Reserva (espacio, fecha, hora inicio/fin, cliente)
-- ---------------------------------------------------------------------
CREATE TABLE reservas (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    espacio_id   INT UNSIGNED NOT NULL,
    fecha        DATE         NOT NULL,
    hora_inicio  TIME         NOT NULL,
    hora_fin     TIME         NOT NULL,
    cliente      VARCHAR(80)  NOT NULL,
    creado_en    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    KEY idx_reservas_espacio_fecha (espacio_id, fecha),

    -- sin reservas huerfanas; no se borra un espacio con reservas asociadas
    CONSTRAINT fk_reservas_espacio FOREIGN KEY (espacio_id)
        REFERENCES espacios (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT chk_reservas_horas  CHECK (hora_fin > hora_inicio),
    CONSTRAINT chk_reservas_cliente CHECK (CHAR_LENGTH(cliente) > 0)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
