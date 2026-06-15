ALTER TABLE orders
    MODIFY payment_method ENUM('cod', 'bank_transfer', 'e_wallet') NOT NULL DEFAULT 'cod',
    ADD COLUMN payment_reference_code VARCHAR(40) DEFAULT NULL AFTER payment_method,
    ADD INDEX idx_payment_reference_code (payment_reference_code);
