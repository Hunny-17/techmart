CREATE TABLE IF NOT EXISTS order_email_logs (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id      INT UNSIGNED NOT NULL,
    recipient     VARCHAR(150) NOT NULL,
    subject       VARCHAR(255) NOT NULL,
    status        VARCHAR(50) NOT NULL,
    send_status   ENUM('sent', 'failed') NOT NULL DEFAULT 'sent',
    mail_file     VARCHAR(500) DEFAULT NULL,
    error_message TEXT,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    INDEX idx_order_created (order_id, created_at),
    INDEX idx_send_status (send_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
