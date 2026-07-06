-- Run this on your cloud database to add the sessions table for Vercel deployment
CREATE TABLE IF NOT EXISTS `sessions` (
    `session_id` VARCHAR(128) NOT NULL,
    `session_data` LONGTEXT NOT NULL,
    `expires_at` DATETIME NOT NULL,
    PRIMARY KEY (`session_id`),
    INDEX `idx_expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
