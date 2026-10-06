-- =====================================================================
-- ReservaEspacios · Fase 2 · Caso A
-- Datos de prueba (seed.sql): 5 registros por tipo de espacio
-- y reservas relacionadas, algunas con fecha de hoy para el reporte.
-- Se ejecuta despues de database/schema.sql:
--   mysql -u root -p < database/seed.sql
-- ATENCION: borra y vuelve a insertar todos los datos de prueba.
-- =====================================================================
USE reserva_espacios;

SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE reservas;
TRUNCATE TABLE espacios;
SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------
-- Espacios: 5 salas, 5 escritorios y 5 canchas (sin imagen: la aplicacion
-- muestra la imagen por defecto hasta que se suba una foto real).
-- zona y deporte usan las claves admitidas por Escritorio::ZONAS y
-- Cancha::DEPORTES, porque la fabrica valida esos valores al leer.
-- Los 9 espacios originales conservan sus id 1..9 (a ellos apuntan las
-- reservas de prueba); los nuevos van en el segundo INSERT (id 10..15).
-- ---------------------------------------------------------------------
INSERT INTO espacios (tipo, nombre, capacidad, tarifa_base, imagen, ubicacion, zona, deporte) VALUES
    ('sala',      'Sala Aula Magna',        60, 120.00, NULL, 'Edificio principal, planta baja', NULL, NULL),
    ('sala',      'Sala de Reuniones Norte', 20,  65.00, NULL, 'Edificio B, salon 204',           NULL, NULL),
    ('sala',      'Sala de Capacitacion',    30,  80.00, NULL, 'Edificio C, salon 105',           NULL, NULL),
    ('escritorio','Escritorio Coworking 1',   1,  12.00, NULL, NULL, 'Coworking', NULL),
    ('escritorio','Escritorio Gabinete 3',    1,  20.00, NULL, NULL, 'Gabinete',  NULL),
    ('escritorio','Ejecutivo Ventana 2',      1,  28.00, NULL, NULL, 'Privado',   NULL),
    ('cancha',    'Cancha de futbol 11',     22,  90.00, NULL, NULL, NULL, 'Futbol'),
    ('cancha',    'Cancha de tenis',          4,  35.00, NULL, NULL, NULL, 'Tenis'),
    ('cancha',    'Cancha de basquet',       10,  55.00, NULL, NULL, NULL, 'Basquet');

-- Dos espacios mas por tipo (quedan sin reservas para probar el alta).
INSERT INTO espacios (tipo, nombre, capacidad, tarifa_base, imagen, ubicacion, zona, deporte) VALUES
    ('sala',      'Sala Videoconferencia',   12,  45.00, NULL, 'Edificio B, salon 210',  NULL,      NULL),
    ('sala',      'Sala de Eventos',         80, 150.00, NULL, 'Auditorio, planta alta', NULL,      NULL),
    ('escritorio','Escritorio Coworking 4',   1,  15.00, NULL, NULL, 'Coworking', NULL),
    ('escritorio','Escritorio Gabinete 5',    1,  22.00, NULL, NULL, 'Gabinete',  NULL),
    ('cancha',    'Cancha de voley',         12,  40.00, NULL, NULL, NULL, 'Voleibol'),
    ('cancha',    'Cancha de micro futbol',  16,  70.00, NULL, NULL, NULL, 'Futbol');

-- ---------------------------------------------------------------------
-- Reservas de prueba (espacio 1..9: los espacios 10..15 quedan libres para
-- probar el alta de reservas). Varias caen en la fecha actual para que el
-- panel y el reporte del dia muestren informacion real.
-- ---------------------------------------------------------------------
INSERT INTO reservas (espacio_id, fecha, hora_inicio, hora_fin, cliente) VALUES
    (1, CURDATE(), '09:00', '11:00', 'Facultad de Ingenieria'),
    (1, CURDATE(), '15:00', '17:00', 'Comite de Bienestar'),
    (4, CURDATE(), '08:00', '12:00', 'Ana Torres'),
    (7, CURDATE(), '17:00', '19:00', 'Club Deportivo UNI'),
    (8, CURDATE(), '10:00', '11:30', 'Carlos Mendoza'),
    (2, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '14:00', '16:00', 'Departamento de Sistemas'),
    (5, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '09:00', '13:00', 'Lucia Fernandez'),
    (9, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '16:00', '18:00', 'Seleccion Universitaria'),
    (3, DATE_SUB(CURDATE(), INTERVAL 1 DAY), '10:00', '12:00', 'Seminario de Titulacion'),
    (6, DATE_SUB(CURDATE(), INTERVAL 1 DAY), '14:00', '18:00', 'Diego Ramirez');
