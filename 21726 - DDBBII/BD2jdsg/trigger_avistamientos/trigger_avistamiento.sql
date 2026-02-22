DELIMITER $$

CREATE TRIGGER trg_cambio_colonia_gato
AFTER INSERT ON avistamiento
FOR EACH ROW
BEGIN
    -- Solo actuar si NO existe un avistamiento posterior
    IF NOT EXISTS (
        SELECT 1
        FROM avistamiento a
        WHERE a.id_gato = NEW.id_gato
          AND a.fecha > NEW.fecha
    ) THEN

        -- 1. Cerrar historial activo previo del gato (si existe)
        UPDATE historial_colonia
        SET fecha_salida = NEW.fecha
        WHERE id_gato = NEW.id_gato
          AND fecha_salida IS NULL;

        -- 2. Crear nuevo historial solo si no existe ya uno activo
        IF NOT EXISTS (
            SELECT 1
            FROM historial_colonia
            WHERE id_gato = NEW.id_gato
              AND id_colonia = NEW.id_colonia
              AND fecha_ingreso = NEW.fecha
              AND fecha_salida IS NULL
        ) THEN
            INSERT INTO historial_colonia (id_gato, id_colonia, fecha_ingreso, fecha_salida)
            VALUES (NEW.id_gato, NEW.id_colonia, NEW.fecha, NULL);
        END IF;
    END IF;
END$$

DELIMITER ;