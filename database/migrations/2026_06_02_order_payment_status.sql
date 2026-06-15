ALTER TABLE orders
    ADD COLUMN payment_status ENUM('unpaid', 'awaiting_review', 'paid', 'refunded') NOT NULL DEFAULT 'unpaid' AFTER payment_reference_code,
    ADD INDEX idx_payment_status (payment_status);
