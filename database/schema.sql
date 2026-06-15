SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS admin_logs;
DROP TABLE IF EXISTS login_attempts;
DROP TABLE IF EXISTS wishlists;
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS order_email_logs;
DROP TABLE IF EXISTS order_details;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS vouchers;
DROP TABLE IF EXISTS product_images;
DROP TABLE IF EXISTS inventory_stock_logs;
DROP TABLE IF EXISTS product_variants;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username      VARCHAR(100) NOT NULL UNIQUE,
    email         VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name     VARCHAR(100) NOT NULL,
    phone         VARCHAR(20)  DEFAULT '',
    address       TEXT,
    role          ENUM('customer', 'staff', 'admin') NOT NULL DEFAULT 'customer',
    status        ENUM('active', 'locked')           NOT NULL DEFAULT 'active',
    email_verified_at TIMESTAMP NULL DEFAULT NULL,
    email_verification_token VARCHAR(64) DEFAULT NULL,
    email_verification_sent_at TIMESTAMP NULL DEFAULT NULL,
    password_reset_token VARCHAR(64) DEFAULT NULL,
    password_reset_expires_at TIMESTAMP NULL DEFAULT NULL,
    password_reset_sent_at TIMESTAMP NULL DEFAULT NULL,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_role_status (role, status),
    INDEX idx_email_verification_token (email_verification_token),
    INDEX idx_password_reset_token (password_reset_token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ---------------------------------------------------------------------
-- categories: danh má»¥c sáº£n pháº©m (cÃ³ há»— trá»£ nested)
-- ---------------------------------------------------------------------
CREATE TABLE categories (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100) NOT NULL,
    slug       VARCHAR(120) NOT NULL UNIQUE,
    parent_id  INT UNSIGNED DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- products: sáº£n pháº©m
-- ---------------------------------------------------------------------
CREATE TABLE products (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id     INT UNSIGNED NOT NULL,
    name            VARCHAR(200) NOT NULL,
    slug            VARCHAR(220) NOT NULL,
    description     TEXT,
    price           DECIMAL(12, 2) NOT NULL DEFAULT 0,
    stock_quantity  INT NOT NULL DEFAULT 0,
    image_url       VARCHAR(500) DEFAULT NULL,
    status          ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id),
    INDEX idx_status_created (status, created_at),
    FULLTEXT idx_search (name, description)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE product_images (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id INT UNSIGNED NOT NULL,
    image_url  VARCHAR(500) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_product_sort (product_id, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE product_variants (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id     INT UNSIGNED NOT NULL,
    name           VARCHAR(150) NOT NULL,
    price          DECIMAL(12, 2) DEFAULT NULL,
    stock_quantity INT NOT NULL DEFAULT 0,
    image_url      VARCHAR(500) DEFAULT NULL,
    status         ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_product_status (product_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE inventory_stock_logs (
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

-- ---------------------------------------------------------------------
-- vouchers: mã giảm giá
-- ---------------------------------------------------------------------
CREATE TABLE vouchers (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code           VARCHAR(50) NOT NULL UNIQUE,
    discount_type  ENUM('percent','fixed') NOT NULL,
    discount_value DECIMAL(12,2) NOT NULL,
    min_order      DECIMAL(12,2) NOT NULL DEFAULT 0,
    max_uses       INT UNSIGNED DEFAULT NULL,
    used_count     INT UNSIGNED NOT NULL DEFAULT 0,
    expires_at     DATETIME DEFAULT NULL,
    is_active      TINYINT(1) NOT NULL DEFAULT 1,
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- orders: đơn hàng
-- ---------------------------------------------------------------------
CREATE TABLE orders (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id          INT UNSIGNED NOT NULL,
    total_amount     DECIMAL(12, 2) NOT NULL DEFAULT 0,
    voucher_id       INT UNSIGNED DEFAULT NULL,
    discount_amount  DECIMAL(12, 2) NOT NULL DEFAULT 0,
    status           ENUM('pending','confirmed','shipping','delivered','cancelled') NOT NULL DEFAULT 'pending',
    shipping_address TEXT NOT NULL,
    payment_method   ENUM('cod', 'bank_transfer', 'e_wallet') NOT NULL DEFAULT 'cod',
    payment_reference_code VARCHAR(40) DEFAULT NULL,
    payment_status   ENUM('unpaid', 'awaiting_review', 'paid', 'refunded') NOT NULL DEFAULT 'unpaid',
    note             TEXT,
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (voucher_id) REFERENCES vouchers(id) ON DELETE SET NULL,
    INDEX idx_user_status (user_id, status),
    INDEX idx_payment_reference_code (payment_reference_code),
    INDEX idx_payment_status (payment_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- order_details: chi tiáº¿t Ä‘Æ¡n hÃ ng
-- ---------------------------------------------------------------------
CREATE TABLE order_details (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id   INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    variant_id INT UNSIGNED DEFAULT NULL,
    quantity   INT NOT NULL,
    unit_price DECIMAL(12, 2) NOT NULL,
    FOREIGN KEY (order_id)   REFERENCES orders(id)   ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (variant_id) REFERENCES product_variants(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE order_email_logs (
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

-- ---------------------------------------------------------------------
-- reviews: Ä‘Ã¡nh giÃ¡ sáº£n pháº©m
-- ---------------------------------------------------------------------
CREATE TABLE reviews (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id INT UNSIGNED NOT NULL,
    user_id    INT UNSIGNED NOT NULL,
    order_id   INT UNSIGNED NOT NULL,
    rating     TINYINT UNSIGNED NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment    TEXT,
    status     ENUM('visible', 'hidden') NOT NULL DEFAULT 'visible',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)    REFERENCES users(id),
    FOREIGN KEY (order_id)   REFERENCES orders(id),
    UNIQUE KEY unique_user_product_order (user_id, product_id, order_id),
    INDEX idx_product_status (product_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- wishlists: sản phẩm yêu thích của khách hàng
-- ---------------------------------------------------------------------
CREATE TABLE wishlists (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_user_product (user_id, product_id),
    KEY idx_user_id (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ---------------------------------------------------------------------
-- login_attempts: log đăng nhập để rate-limit brute force (IP + email)
-- ---------------------------------------------------------------------
CREATE TABLE login_attempts (
    id           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ip_address   VARCHAR(45)  NOT NULL,
    email        VARCHAR(150) NOT NULL,
    attempted_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    success      TINYINT(1)   NOT NULL DEFAULT 0,
    INDEX idx_ip_email_time (ip_address, email, attempted_at),
    INDEX idx_attempted_at  (attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- admin_logs: audit trail mọi action admin có side-effect
-- ---------------------------------------------------------------------
CREATE TABLE admin_logs (
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

SET FOREIGN_KEY_CHECKS = 1;
