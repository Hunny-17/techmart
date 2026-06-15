CREATE TABLE IF NOT EXISTS vouchers (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code           VARCHAR(50) NOT NULL UNIQUE,
    discount_type  ENUM('percent','fixed') NOT NULL,
    discount_value DECIMAL(10,2) NOT NULL,
    min_order      DECIMAL(10,2) NOT NULL DEFAULT 0,
    max_uses       INT UNSIGNED DEFAULT NULL,
    used_count     INT UNSIGNED NOT NULL DEFAULT 0,
    expires_at     DATETIME DEFAULT NULL,
    is_active      TINYINT(1) NOT NULL DEFAULT 1,
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE orders
    ADD COLUMN IF NOT EXISTS voucher_id      INT UNSIGNED DEFAULT NULL        AFTER total_amount,
    ADD COLUMN IF NOT EXISTS discount_amount DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER voucher_id;
