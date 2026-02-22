
USE BD2XAMPPions;

-- 1. TABLAS DE BACKUP

-- Se duplican las siguientes tablas utilizando SELECT * FROM ... WHERE FALSE
-- para duplicar los atributos que tiene cada tabla, pero sin copiar los datos.
-- Es como "copiar la plantilla"
CREATE TABLE IF NOT EXISTS backup_gato AS 
SELECT * FROM gato WHERE FALSE;

CREATE TABLE IF NOT EXISTS backup_colonia AS 
SELECT * FROM colonia WHERE FALSE;

CREATE TABLE IF NOT EXISTS backup_historial_colonia AS 
SELECT * FROM historial_colonia WHERE FALSE;

CREATE TABLE IF NOT EXISTS backup_ayuntamiento AS 
SELECT * FROM ayuntamiento WHERE FALSE;

CREATE TABLE IF NOT EXISTS backup_avistamiento AS 
SELECT * FROM avistamiento WHERE FALSE;


-- 2. STORED PROCEDURE

DELIMITER //

CREATE PROCEDURE backup_poblacion_colonias()
BEGIN
    -- Limpiar backups anteriores
    DELETE FROM backup_gato;
    DELETE FROM backup_colonia;
    DELETE FROM backup_historial_colonia;
    DELETE FROM backup_ayuntamiento;
    DELETE FROM backup_avistamiento;


    -- Copiar datos actuales principales
    INSERT INTO backup_gato
    SELECT * FROM gato;

    INSERT INTO backup_colonia
    SELECT * FROM colonia;

    INSERT INTO backup_historial_colonia
    SELECT * FROM historial_colonia;

    INSERT INTO backup_ayuntamiento
    SELECT * FROM ayuntamiento;

    INSERT INTO backup_avistamiento
    SELECT * FROM avistamiento;

END//

DELIMITER ;

-- 3. ACTIVAR EL EVENT SCHEDULER

SET GLOBAL event_scheduler = ON;

-- 4. EVENT DIARIO A LAS 00:00

DELIMITER //

CREATE EVENT IF NOT EXISTS ev_backup_diario_colonias
ON SCHEDULE EVERY 1 DAY
STARTS TIMESTAMP(CURRENT_DATE, '00:00:00')
DO
BEGIN
    CALL backup_poblacion_colonias();
END//

DELIMITER ;