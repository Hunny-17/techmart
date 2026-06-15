CREATE TABLE IF NOT EXISTS login_attempts (
    id           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ip_address   VARCHAR(45)  NOT NULL,
    email        VARCHAR(150) NOT NULL,
    attempted_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    success      TINYINT(1)   NOT NULL DEFAULT 0,
    INDEX idx_ip_email_time (ip_address, email, attempted_at),
    INDEX idx_attempted_at  (attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
