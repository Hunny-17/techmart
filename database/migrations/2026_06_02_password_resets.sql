ALTER TABLE users
    ADD COLUMN password_reset_token VARCHAR(64) DEFAULT NULL AFTER email_verification_sent_at,
    ADD COLUMN password_reset_expires_at TIMESTAMP NULL DEFAULT NULL AFTER password_reset_token,
    ADD INDEX idx_password_reset_token (password_reset_token);
