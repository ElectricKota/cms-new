SET NAMES utf8mb4;
SET foreign_key_checks = 0;

CREATE TABLE IF NOT EXISTS media_assets (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    original_name VARCHAR(255) NOT NULL,
    stored_name VARCHAR(255) NOT NULL UNIQUE,
    mime_type VARCHAR(120) NOT NULL,
    size_bytes INT UNSIGNED NOT NULL,
    width INT UNSIGNED NOT NULL,
    height INT UNSIGNED NOT NULL,
    alt VARCHAR(255) NULL,
    checksum CHAR(64) NOT NULL,
    created_at DATETIME NOT NULL,
    INDEX idx_media_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS media_variants (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    media_asset_id INT UNSIGNED NOT NULL,
    format ENUM('avif', 'webp') NOT NULL,
    width INT UNSIGNED NOT NULL,
    height INT UNSIGNED NULL,
    path VARCHAR(255) NOT NULL UNIQUE,
    created_at DATETIME NOT NULL,
    FOREIGN KEY (media_asset_id) REFERENCES media_assets(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS project_settings (
    id TINYINT UNSIGNED PRIMARY KEY,
    company_name VARCHAR(160) NULL,
    person_name VARCHAR(160) NULL,
    description TEXT NULL,
    company_id VARCHAR(40) NULL,
    tracking_head MEDIUMTEXT NULL,
    tracking_body MEDIUMTEXT NULL,
    phone VARCHAR(60) NULL,
    email VARCHAR(160) NULL,
    street VARCHAR(160) NULL,
    city VARCHAR(120) NULL,
    zip VARCHAR(20) NULL,
    facebook_url VARCHAR(255) NULL,
    instagram_url VARCHAR(255) NULL,
    linkedin_url VARCHAR(255) NULL,
    og_image_id INT UNSIGNED NULL,
    intro_text MEDIUMTEXT NULL,
    gdpr_text MEDIUMTEXT NULL,
    contact_text MEDIUMTEXT NULL,
    terms_text MEDIUMTEXT NULL,
    product_detail_text MEDIUMTEXT NULL,
    updated_at DATETIME NULL,
    FOREIGN KEY (og_image_id) REFERENCES media_assets(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role ENUM('admin', 'manager', 'trainer', 'client') NOT NULL DEFAULT 'client',
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NULL,
    name VARCHAR(160) NOT NULL,
    phone VARCHAR(60) NULL,
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS galleries (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(160) NOT NULL,
    slug VARCHAR(180) NOT NULL UNIQUE,
    created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS gallery_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    gallery_id INT UNSIGNED NOT NULL,
    media_asset_id INT UNSIGNED NOT NULL,
    caption VARCHAR(255) NULL,
    position INT NOT NULL DEFAULT 100,
    created_at DATETIME NOT NULL,
    FOREIGN KEY (gallery_id) REFERENCES galleries(id) ON DELETE CASCADE,
    FOREIGN KEY (media_asset_id) REFERENCES media_assets(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS content_entries (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    type ENUM('news', 'article', 'page') NOT NULL,
    title VARCHAR(190) NOT NULL,
    slug VARCHAR(210) NOT NULL,
    excerpt TEXT NULL,
    body MEDIUMTEXT NULL,
    image_id INT UNSIGNED NULL,
    published TINYINT(1) NOT NULL DEFAULT 0,
    published_at DATETIME NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NULL,
    UNIQUE KEY uniq_content_type_slug (type, slug),
    FOREIGN KEY (image_id) REFERENCES media_assets(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS products (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(190) NOT NULL,
    slug VARCHAR(210) NOT NULL UNIQUE,
    description MEDIUMTEXT NULL,
    main_image_id INT UNSIGNED NULL,
    category_names VARCHAR(255) NULL,
    tag_names VARCHAR(255) NULL,
    published TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NULL,
    FOREIGN KEY (main_image_id) REFERENCES media_assets(id) ON DELETE SET NULL,
    FULLTEXT KEY ft_product_filter (title, category_names, tag_names)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS product_images (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id INT UNSIGNED NOT NULL,
    media_asset_id INT UNSIGNED NOT NULL,
    position INT NOT NULL DEFAULT 100,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (media_asset_id) REFERENCES media_assets(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS price_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(190) NOT NULL,
    description TEXT NULL,
    category VARCHAR(120) NULL,
    note VARCHAR(255) NULL,
    price VARCHAR(80) NOT NULL,
    position INT NOT NULL DEFAULT 100,
    created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS opening_hours (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    date_from DATE NOT NULL,
    date_to DATE NULL,
    time_from TIME NOT NULL,
    time_to TIME NOT NULL,
    note VARCHAR(255) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS rooms (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(160) NOT NULL,
    description TEXT NULL,
    capacity INT UNSIGNED NOT NULL,
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS trainings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    room_id INT UNSIGNED NOT NULL,
    trainer_user_id INT UNSIGNED NOT NULL,
    title VARCHAR(190) NOT NULL,
    description TEXT NULL,
    starts_at DATETIME NOT NULL,
    ends_at DATETIME NOT NULL,
    capacity INT UNSIGNED NULL,
    created_at DATETIME NOT NULL,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE RESTRICT,
    FOREIGN KEY (trainer_user_id) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_training_time (room_id, starts_at, ends_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS training_registrations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    training_id INT UNSIGNED NOT NULL,
    name VARCHAR(160) NOT NULL,
    email VARCHAR(190) NOT NULL,
    phone VARCHAR(60) NULL,
    cancel_token CHAR(36) NOT NULL UNIQUE,
    confirmed_at DATETIME NULL,
    cancelled_at DATETIME NULL,
    created_at DATETIME NOT NULL,
    FOREIGN KEY (training_id) REFERENCES trainings(id) ON DELETE CASCADE,
    INDEX idx_registration_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS menu_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    parent_id INT UNSIGNED NULL,
    title VARCHAR(120) NOT NULL,
    target_type ENUM('url', 'anchor', 'news_index', 'article_index', 'content_detail', 'product_detail') NOT NULL,
    target_value VARCHAR(255) NULL,
    position INT NOT NULL DEFAULT 100,
    visible TINYINT(1) NOT NULL DEFAULT 1,
    FOREIGN KEY (parent_id) REFERENCES menu_items(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO project_settings (id, company_name, person_name, description, intro_text, updated_at)
VALUES (1, 'Nový CMS web', 'Michal Kotek', 'Základ webu připravený v Nette.', 'Úvodní text upravíte v administraci.', NOW())
ON DUPLICATE KEY UPDATE id = id;

INSERT INTO users (role, email, password_hash, name, active, created_at)
VALUES ('admin', 'admin@example.test', NULL, 'Debug administrátor', 1, NOW())
ON DUPLICATE KEY UPDATE email = email;

SET foreign_key_checks = 1;
