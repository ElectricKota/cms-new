SET NAMES utf8mb4;

ALTER TABLE trainings
    ADD COLUMN IF NOT EXISTS image_id INT UNSIGNED NULL AFTER description;

SET @constraint_exists = (
    SELECT COUNT(*)
    FROM information_schema.TABLE_CONSTRAINTS
    WHERE CONSTRAINT_SCHEMA = DATABASE()
        AND TABLE_NAME = 'trainings'
        AND CONSTRAINT_NAME = 'trainings_ibfk_3'
);

SET @statement = IF(
    @constraint_exists = 0,
    'ALTER TABLE trainings ADD CONSTRAINT trainings_ibfk_3 FOREIGN KEY (image_id) REFERENCES media_assets(id) ON DELETE SET NULL',
    'SELECT 1'
);

PREPARE stmt FROM @statement;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
