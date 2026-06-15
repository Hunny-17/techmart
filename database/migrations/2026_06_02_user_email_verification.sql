ALTER TABLE users
    ADD COLUMN email_verified_at TIMESTAMP NULL DEFAULT NULL AFTER status,
    ADD COLUMN email_verification_token VARCHAR(64) DEFAULT NULL AFTER email_verified_at,
    ADD COLUMN email_verification_sent_at TIMESTAMP NULL DEFAULT NULL AFTER email_verification_token,
    ADD INDEX idx_email_verification_token (email_verification_token);

UPDATE users
SET email_verified_at = COALESCE(email_verified_at, CURRENT_TIMESTAMP)
WHERE email_verified_at IS NULL;
