ALTER TABLE users
    ADD COLUMN password_reset_sent_at TIMESTAMP NULL DEFAULT NULL AFTER password_reset_expires_at;
