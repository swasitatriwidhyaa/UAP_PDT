CREATE TRIGGER trg_update_ketersediaan_kamar
AFTER UPDATE ON bookings
FOR EACH ROW
BEGIN
    IF NEW.status = 'confirmed' AND OLD.status != 'confirmed' THEN
        UPDATE indekos
        SET status_ketersediaan = 'terisi'
        WHERE id = NEW.room_id;
    END IF;

    IF NEW.status = 'rejected' AND OLD.status = 'confirmed' THEN
        UPDATE indekos
        SET status_ketersediaan = 'tersedia'
        WHERE id = NEW.room_id;
    END IF;
END;
