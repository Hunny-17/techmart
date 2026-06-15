SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- =====================================================================
-- TechMart - Seed Data
-- Chạy SAU khi import schema.sql
--
-- Mật khẩu mặc định cho TẤT CẢ tài khoản demo: password123
-- (hash bcrypt của 'password123' - sinh bằng password_hash() PHP)
-- =====================================================================


-- ---------- Users ----------
-- Password tất cả: password123
INSERT INTO users (username, email, password_hash, full_name, phone, address, role, status) VALUES
('admin',     'admin@techmart.test',     '$2y$10$SAc6CYdeGze6iDM9B86l5e2KGBfEJ4RvM.NgAIKg60liJJAVld3oa', 'Quản Trị Viên',  '0900000001', 'HCM',  'admin',    'active'),
('staff01',   'staff01@techmart.test',   '$2y$10$HGIFtCgl6fLUCXaig90OouEsLjjjW7yn5QpC36xc5EvXvK7Zb3lmO', 'Nguyễn Văn A',   '0900000002', 'HCM',  'staff',    'active'),
('staff02',   'staff02@techmart.test',   '$2y$10$HGIFtCgl6fLUCXaig90OouEsLjjjW7yn5QpC36xc5EvXvK7Zb3lmO', 'Trần Thị B',     '0900000003', 'HCM',  'staff',    'active'),
('customer1', 'customer1@techmart.test', '$2y$10$HGIFtCgl6fLUCXaig90OouEsLjjjW7yn5QpC36xc5EvXvK7Zb3lmO', 'Lê Văn C',       '0901111111', '123 Nguyễn Trãi, Q1, HCM', 'customer', 'active'),
('customer2', 'customer2@techmart.test', '$2y$10$HGIFtCgl6fLUCXaig90OouEsLjjjW7yn5QpC36xc5EvXvK7Zb3lmO', 'Phạm Thị D',     '0902222222', '456 Lê Lợi, Q3, HCM',       'customer', 'active');

-- ---------- Categories ----------
INSERT INTO categories (name, slug) VALUES
('Laptop',     'laptop'),
('Điện thoại', 'dien-thoai'),
('Phụ kiện',   'phu-kien');

-- ---------- Products ----------
INSERT INTO products (category_id, name, slug, description, price, stock_quantity, image_url, status) VALUES
(1, 'MacBook Air M3 13"',            'macbook-air-m3-13',          'Apple silicon M3, 8GB RAM, 256GB SSD',     27990000, 15, 'https://placehold.co/400x300?text=MacBook+Air', 'active'),
(1, 'Dell XPS 13 Plus',              'dell-xps-13-plus',           'Intel Core i7, 16GB RAM, 512GB SSD',       32500000, 10, 'https://placehold.co/400x300?text=Dell+XPS',    'active'),
(1, 'ASUS ROG Zephyrus G14',         'asus-rog-zephyrus-g14',      'Ryzen 9, RTX 4060, 16GB RAM',              35900000,  8, 'https://placehold.co/400x300?text=ROG+G14',     'active'),
(1, 'Lenovo ThinkPad X1 Carbon',     'lenovo-thinkpad-x1-carbon',  'Doanh nhân cao cấp, i7 Gen 13',            42000000,  5, 'https://placehold.co/400x300?text=ThinkPad',    'active'),
(2, 'iPhone 15 Pro Max',             'iphone-15-pro-max',          'Titan, A17 Pro, camera 48MP',              33990000, 20, 'https://placehold.co/400x300?text=iPhone+15PM', 'active'),
(2, 'Samsung Galaxy S24 Ultra',      'samsung-galaxy-s24-ultra',   '12GB RAM, S Pen, Galaxy AI',               31990000, 12, 'https://placehold.co/400x300?text=S24+Ultra',   'active'),
(2, 'Xiaomi 14 Pro',                 'xiaomi-14-pro',              'Snapdragon 8 Gen 3, Leica camera',         24990000, 18, 'https://placehold.co/400x300?text=Xiaomi+14',   'active'),
(2, 'OPPO Find X7 Ultra',            'oppo-find-x7-ultra',         'Hasselblad camera, sạc nhanh 100W',        26990000,  9, 'https://placehold.co/400x300?text=Find+X7',     'active'),
(3, 'AirPods Pro 2',                 'airpods-pro-2',              'Chống ồn chủ động, USB-C',                  5990000, 30, 'https://placehold.co/400x300?text=AirPods',     'active'),
(3, 'Apple Watch Series 9',          'apple-watch-series-9',       'GPS, 45mm, S9 chip',                       10990000, 14, 'https://placehold.co/400x300?text=Watch+S9',    'active'),
(3, 'Logitech MX Master 3S',         'logitech-mx-master-3s',      'Chuột không dây cao cấp',                   2490000, 25, 'https://placehold.co/400x300?text=MX+Master',   'active'),
(3, 'Bàn phím Keychron K2 Pro',      'keychron-k2-pro',            'Cơ học, Bluetooth, hot-swap',               3290000, 16, 'https://placehold.co/400x300?text=Keychron',    'active');

-- ---------- Sample orders ----------
INSERT INTO orders (user_id, total_amount, status, shipping_address, payment_method, note) VALUES
(4, 27990000, 'delivered', '123 Nguyễn Trãi, Q1, HCM', 'cod', 'Giao giờ hành chính'),
(4, 33990000, 'shipping',  '123 Nguyễn Trãi, Q1, HCM', 'cod', NULL),
(5,  8480000, 'pending',   '456 Lê Lợi, Q3, HCM',      'cod', 'Gọi trước khi giao');

INSERT INTO order_details (order_id, product_id, quantity, unit_price) VALUES
(1, 1, 1, 27990000),
(2, 5, 1, 33990000),
(3, 9, 1,  5990000),
(3, 11,1,  2490000);

-- ---------- Sample reviews ----------
INSERT INTO reviews (product_id, user_id, order_id, rating, comment, status) VALUES
(1, 4, 1, 5, 'Máy chạy mượt, pin trâu, rất hài lòng!', 'visible');

SET FOREIGN_KEY_CHECKS = 1;
