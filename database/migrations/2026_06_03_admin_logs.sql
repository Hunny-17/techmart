CREATE TABLE IF NOT EXISTS admin_logs (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED    NOT NULL,
    action      VARCHAR(100)    NOT NULL,
    entity_type VARCHAR(50)     NOT NULL,
    entity_id   INT UNSIGNED    NULL,
    description TEXT            NULL,
    ip_address  VARCHAR(45)     NULL,
    user_agent  VARCHAR(255)    NULL,
    created_at  TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_user_created (user_id, created_at),
    INDEX idx_entity       (entity_type, entity_id),
    INDEX idx_created      (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
