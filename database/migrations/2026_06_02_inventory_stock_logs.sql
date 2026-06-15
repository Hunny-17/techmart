CREATE TABLE IF NOT EXISTS inventory_stock_logs (
    id             BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id     INT UNSIGNED NOT NULL,
    variant_id     INT UNSIGNED DEFAULT NULL,
    admin_user_id  INT UNSIGNED NOT NULL,
    quantity       INT NOT NULL,
    stock_before   INT NOT NULL,
    stock_after    INT NOT NULL,
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (variant_id) REFERENCES product_variants(id) ON DELETE SET NULL,
    FOREIGN KEY (admin_user_id) REFERENCES users(id),
    INDEX idx_product_created (product_id, created_at),
    INDEX idx_variant_created (variant_id, created_at),
    INDEX idx_admin_created (admin_user_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
