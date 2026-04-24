SET NAMES utf8mb4;
SET foreign_key_checks = 0;

ALTER TABLE opening_hours
    ADD COLUMN IF NOT EXISTS label_from VARCHAR(120) NULL AFTER id,
    ADD COLUMN IF NOT EXISTS label_to VARCHAR(120) NULL AFTER label_from,
    ADD COLUMN IF NOT EXISTS position INT NOT NULL DEFAULT 100 AFTER note;

UPDATE opening_hours
SET
    label_from = COALESCE(label_from, DATE_FORMAT(date_from, '%d.%m.%Y')),
    label_to = COALESCE(label_to, DATE_FORMAT(date_to, '%d.%m.%Y'))
WHERE label_from IS NULL;

ALTER TABLE opening_hours
    MODIFY label_from VARCHAR(120) NOT NULL,
    MODIFY time_from VARCHAR(40) NOT NULL,
    MODIFY time_to VARCHAR(40) NOT NULL,
    DROP COLUMN IF EXISTS date_from,
    DROP COLUMN IF EXISTS date_to;

ALTER TABLE trainings
    DROP FOREIGN KEY trainings_ibfk_2;

ALTER TABLE trainings
    MODIFY trainer_user_id INT UNSIGNED NULL;

ALTER TABLE trainings
    ADD CONSTRAINT trainings_ibfk_2 FOREIGN KEY (trainer_user_id) REFERENCES users(id) ON DELETE SET NULL;

SET foreign_key_checks = 1;
