-- CompilerHub Database Schema
-- MySQL 8.0+

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'STRICT_TRANS_TABLES,NO_ENGINE_SUBSTITUTION';

-- -------------------------------------------------------
-- users
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
    `id`            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `username`      VARCHAR(50)     NOT NULL,
    `email`         VARCHAR(255)    NOT NULL,
    `password_hash` VARCHAR(255)    NOT NULL,
    `role`          ENUM('user','admin') NOT NULL DEFAULT 'user',
    `is_banned`     TINYINT(1)      NOT NULL DEFAULT 0,
    `created_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `last_login`    DATETIME                 DEFAULT NULL,
    `updated_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_users_email`    (`email`),
    UNIQUE KEY `uq_users_username` (`username`),
    KEY `idx_users_role`     (`role`),
    KEY `idx_users_is_banned`(`is_banned`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- compilation_sessions
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `compilation_sessions` (
    `id`                 INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`            INT UNSIGNED          DEFAULT NULL,
    `language`           VARCHAR(20)  NOT NULL,
    `code_input`         MEDIUMTEXT   NOT NULL,
    `compilation_output` MEDIUMTEXT            DEFAULT NULL,
    `status`             ENUM('pending','success','error') NOT NULL DEFAULT 'pending',
    `execution_time`     DECIMAL(10,4)         DEFAULT NULL COMMENT 'seconds',
    `created_at`         DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_cs_user_id`   (`user_id`),
    KEY `idx_cs_language`  (`language`),
    KEY `idx_cs_status`    (`status`),
    KEY `idx_cs_created_at`(`created_at`),
    CONSTRAINT `fk_cs_user_id` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- ast_results
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `ast_results` (
    `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `session_id`   INT UNSIGNED NOT NULL,
    `ast_json`     LONGTEXT     NOT NULL,
    `tokens_count` INT UNSIGNED NOT NULL DEFAULT 0,
    `created_at`   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_ar_session_id`(`session_id`),
    CONSTRAINT `fk_ar_session_id` FOREIGN KEY (`session_id`)
        REFERENCES `compilation_sessions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- user_preferences
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `user_preferences` (
    `id`                 INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`            INT UNSIGNED NOT NULL,
    `theme`              VARCHAR(30)  NOT NULL DEFAULT 'dark',
    `visualization_mode` VARCHAR(30)  NOT NULL DEFAULT '3d',
    `auto_compile`       TINYINT(1)   NOT NULL DEFAULT 0,
    `updated_at`         DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_up_user_id` (`user_id`),
    CONSTRAINT `fk_up_user_id` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- saved_visualizations
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `saved_visualizations` (
    `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`       INT UNSIGNED NOT NULL,
    `session_id`    INT UNSIGNED NOT NULL,
    `name`          VARCHAR(255) NOT NULL,
    `description`   TEXT                  DEFAULT NULL,
    `export_format` VARCHAR(20)  NOT NULL DEFAULT 'json',
    `file_path`     VARCHAR(500)          DEFAULT NULL,
    `created_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_sv_user_id`   (`user_id`),
    KEY `idx_sv_session_id`(`session_id`),
    CONSTRAINT `fk_sv_user_id` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_sv_session_id` FOREIGN KEY (`session_id`)
        REFERENCES `compilation_sessions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- analytics
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `analytics` (
    `id`               INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `user_id`          INT UNSIGNED           DEFAULT NULL,
    `language_used`    VARCHAR(20)   NOT NULL,
    `session_duration` INT UNSIGNED  NOT NULL DEFAULT 0 COMMENT 'seconds',
    `features_used`    JSON                   DEFAULT NULL,
    `ip_address`       VARCHAR(45)            DEFAULT NULL,
    `created_at`       DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_an_user_id`     (`user_id`),
    KEY `idx_an_language`    (`language_used`),
    KEY `idx_an_created_at`  (`created_at`),
    CONSTRAINT `fk_an_user_id` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- admin_logs
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `admin_logs` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `admin_id`    INT UNSIGNED NOT NULL,
    `action`      VARCHAR(100) NOT NULL,
    `target_type` VARCHAR(50)           DEFAULT NULL,
    `target_id`   INT UNSIGNED          DEFAULT NULL,
    `details`     TEXT                  DEFAULT NULL,
    `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_al_admin_id`  (`admin_id`),
    KEY `idx_al_created_at`(`created_at`),
    CONSTRAINT `fk_al_admin_id` FOREIGN KEY (`admin_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- refresh_tokens
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `refresh_tokens` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`    INT UNSIGNED NOT NULL,
    `token_hash` VARCHAR(255) NOT NULL,
    `expires_at` DATETIME     NOT NULL,
    `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_rt_token_hash`(`token_hash`),
    KEY `idx_rt_user_id`   (`user_id`),
    KEY `idx_rt_expires_at`(`expires_at`),
    CONSTRAINT `fk_rt_user_id` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
