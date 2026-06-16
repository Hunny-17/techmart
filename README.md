# TechMart Web

TechMart Web là website thương mại điện tử bán thiết bị công nghệ, được xây dựng bằng PHP thuần theo mô hình MVC để phục vụ học tập và phát triển sản phẩm.

## Stack

- PHP 8 OOP, MVC tự xây dựng.
- MySQL/MariaDB trên XAMPP hoặc shared hosting.
- Bootstrap 5, Bootstrap Icons, Vanilla JavaScript.
- Giỏ hàng lưu bằng `$_SESSION`, không dùng cookie cho cart.
- Không dùng Laravel, React, Vue, Angular hoặc MongoDB.

## Tính Năng Chính

### Khách Hàng

- Đăng ký, xác thực email, đăng nhập, đăng xuất.
- Quên mật khẩu và đặt lại mật khẩu.
- Trang chủ có hero carousel, sản phẩm gợi ý và sản phẩm mới.
- Trang sản phẩm có tìm kiếm, gợi ý gần đúng, lọc danh mục, lọc giá, sắp xếp và phân trang.
- Chi tiết sản phẩm có gallery ảnh, tồn kho, mô tả, sản phẩm liên quan, đã xem gần đây và đánh giá.
- So sánh tối đa 3 sản phẩm bằng `localStorage`.
- Wishlist cho sản phẩm đang hoạt động.
- Giỏ hàng session, kiểm tra tồn kho trước khi thêm/cập nhật.
- Checkout COD, chuyển khoản ngân hàng và ví điện tử.
- Mã thanh toán dạng `TMYYYYMMDDXXXXXX`.
- QR VietQR tự render theo tổng tiền cần thanh toán và mã tham chiếu.
- Voucher có danh sách mã có thể dùng tại checkout, bấm để áp dụng nhanh.
- Theo dõi đơn hàng, xem QR/mã thanh toán, hủy đơn pending và đặt lại đơn.
- Đánh giá sản phẩm sau khi đã mua và đơn ở trạng thái delivered.

### Quản Trị

- Dashboard vận hành: doanh thu, đơn hàng, khách mới, tồn kho thấp, trạng thái thanh toán.
- Quản lý sản phẩm, ảnh phụ, variant, tồn kho và nhập kho.
- Không xóa sản phẩm còn tồn kho; sản phẩm đã phát sinh đơn hàng sẽ chuyển inactive thay vì xóa cứng.
- Quản lý danh mục, chặn xóa danh mục còn sản phẩm và chặn vòng lặp cha/con.
- Quản lý voucher, validate phần trăm <= 100, đơn tối thiểu không âm, giới hạn dùng hợp lệ.
- Voucher đã dùng hoặc đã phát sinh đơn hàng không bị xóa cứng, chỉ tắt active.
- Quản lý đơn hàng với luồng `pending -> confirmed -> shipping -> delivered`, có thể hủy trước delivered.
- Đơn chuyển khoản/ví phải có `payment_status = paid` trước khi admin duyệt từ pending sang confirmed.
- COD tự chuyển sang paid khi đơn được giao thành công.
- Hủy đơn hoàn lại tồn kho và giảm lượt dùng voucher nếu có.
- Quản lý khách hàng, nhân viên, đánh giá, tồn kho, nhật ký admin và xuất CSV.

## Cấu Trúc Thư Mục

```text
techmart-web/
|-- public/                  Document root
|   |-- index.php            Front controller
|   |-- .htaccess            Rewrite rules
|   `-- assets/
|       |-- css/
|       |-- js/
|       |-- img/
|       `-- uploads/         Ảnh sản phẩm public
|
|-- app/
|   |-- Core/                App, Router, Database, Model, View, Auth, Session...
|   |-- Controllers/         Controller phía khách hàng
|   |   `-- Admin/           Controller phía quản trị
|   |-- Models/              Model thao tác DB
|   |-- Services/            Mailer, notifier, logger, signer...
|   `-- Views/               PHP views và layouts
|
|-- config/
|   |-- config.php           Đọc .env và cấu hình ứng dụng
|   `-- routes.php           Khai báo route
|
|-- database/
|   |-- schema.sql           Cấu trúc database
|   |-- seed.sql             Dữ liệu mẫu
|   `-- migrations/          Migration bổ sung
|
|-- storage/
|   |-- logs/                Log local
|   |-- mail/                Email HTML khi MAIL_DRIVER=log
|   `-- uploads/             Upload cũ/nội bộ
|
|-- .env.example
|-- .gitignore
|-- .htaccess                Redirect root vào public/
|-- CODEX_PROMPTS.md
`-- README.md
```

## Cài Đặt Local Với XAMPP

### 1. Yêu cầu

- PHP 8.0 trở lên, khuyến nghị PHP 8.2.
- MySQL 8.0 hoặc MariaDB 10.4 trở lên.
- Apache bật `mod_rewrite`.
- Project đặt tại:

```text
C:\Xampp\htdocs\techmart-web
```

### 2. Clone hoặc lấy code

```bash
git clone https://github.com/Hunny-17/techmart.git techmart-web
cd techmart-web
```

Nếu đang dùng bản trong XAMPP:

```powershell
cd C:\Xampp\htdocs\techmart-web
```

### 3. Tạo file môi trường

Copy `.env.example` thành `.env`, sau đó chỉnh cấu hình DB:

```env
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost/techmart-web

DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=techmart
DB_USER=root
DB_PASS=
```

Không commit `.env` lên GitHub.

### 4. Tạo database

Tạo database:

```sql
CREATE DATABASE techmart CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Import lần lượt:

```sql
SOURCE C:/Xampp/htdocs/techmart-web/database/schema.sql;
SOURCE C:/Xampp/htdocs/techmart-web/database/seed.sql;
```

Hoặc import `schema.sql` và `seed.sql` bằng phpMyAdmin.

### 5. Chạy web

Bật Apache và MySQL trong XAMPP, sau đó mở:

```text
http://localhost/techmart-web/
```

## Tài Khoản Demo

Mật khẩu mặc định của tài khoản seed local là:

```text
password123
```

| Email | Vai trò |
| --- | --- |
| `admin@techmart.test` | Admin |
| `staff01@techmart.test` | Staff |
| `staff02@techmart.test` | Staff |
| `customer1@techmart.test` | Customer |
| `customer2@techmart.test` | Customer |

Ghi chú: trên domain demo, mật khẩu admin có thể khác seed local và được cấu hình riêng bởi chủ dự án.

## Cấu Hình Email

Local nên dùng chế độ log:

```env
MAIL_DRIVER=log
MAIL_FROM_EMAIL=no-reply@techmart.test
MAIL_FROM_NAME=TechMart
```

Email HTML sẽ được ghi trong:

```text
storage/mail
```

Các luồng email hiện có:

- Xác thực email sau đăng ký.
- Quên mật khẩu/đặt lại mật khẩu.
- Xác nhận đặt hàng.
- Thông báo thanh toán đã được admin xác nhận.
- Thông báo đổi trạng thái đơn hàng.

## Cấu Hình Thanh Toán Thủ Công

Checkout hỗ trợ:

- COD.
- Chuyển khoản ngân hàng.
- Ví điện tử.

Đơn chuyển khoản/ví có mã tham chiếu dạng `TMYYYYMMDDXXXXXX`.

Ví dụ cấu hình VietQR trong `.env`:

```env
PAYMENT_BANK_ID=MB
PAYMENT_BANK_ACCOUNT_NO=100612200517
PAYMENT_BANK_ACCOUNT_NAME=TRAN QUOC HUY
PAYMENT_BANK_NAME=MB Bank
```

QR được render theo tổng tiền checkout và mã thanh toán của từng đơn.

## Kiểm Thử Nhanh

Lint PHP:

```powershell
Get-ChildItem -Path app,config -Recurse -Filter *.php | ForEach-Object {
    C:\Xampp\php\php.exe -l $_.FullName
}
```

Các URL nên smoke test:

```text
http://localhost/techmart-web/
http://localhost/techmart-web/products
http://localhost/techmart-web/cart
http://localhost/techmart-web/checkout
http://localhost/techmart-web/login
http://localhost/techmart-web/my-orders
http://localhost/techmart-web/admin
http://localhost/techmart-web/admin/products
http://localhost/techmart-web/admin/orders
http://localhost/techmart-web/admin/customers
http://localhost/techmart-web/admin/inventory
http://localhost/techmart-web/admin/vouchers
```

Domain InfinityFree hiện dùng `https://techmart-huy.freedev.app/`. Khi test bằng script cần xử lý cookie `__test` của InfinityFree hoặc dùng trình duyệt thật.

## Bảo Mật Đã Triển Khai

| Hạng mục | Cách triển khai |
| --- | --- |
| SQL Injection | Query qua PDO prepared statements |
| XSS | Escape output bằng helper `e()` |
| CSRF | `Csrf::token()` và `Csrf::verify()` cho form POST |
| Password hash | `password_hash()` và `password_verify()` |
| Session security | HttpOnly, SameSite, regenerate session |
| Phân quyền | Middleware `auth` và `admin` trong Router |
| Upload an toàn | Kiểm tra MIME, đổi tên file random, giới hạn loại ảnh |
| HTTPS-ready | Tự nhận diện HTTPS để bật secure cookie khi deploy |
| Rate limit | Giới hạn login, quên mật khẩu và gửi lại email xác thực |
| Audit log | Ghi log thao tác admin quan trọng |

## Deploy InfinityFree Hoặc Shared Hosting

1. Tạo database trên hosting.
2. Import `database/schema.sql` và dữ liệu cần thiết.
3. Upload source vào `htdocs` theo đúng cấu trúc thư mục.
4. Root `htdocs` có `.htaccess` chuyển request vào `public/index.php`.
5. Tạo `.env` production trên hosting:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.example
DB_HOST=...
DB_NAME=...
DB_USER=...
DB_PASS=...
```

6. Bật SSL/HTTPS.
7. Kiểm tra quyền ghi cho `storage/logs`, `storage/mail`, `public/assets/uploads` nếu hosting cho phép upload.

## GitHub

Remote hiện tại:

```text
https://github.com/Hunny-17/techmart.git
```

Lệnh push thường dùng:

```powershell
git add .
git commit -m "chore: update project"
git push
```

## Ghi Chú

- Đây là dự án học tập, chưa phải hệ thống thương mại điện tử production.
- Không commit `.env`, log, file mail sinh tự động, zip deploy hoặc dữ liệu nhạy cảm.
- Thanh toán hiện là hướng dẫn thanh toán thủ công, chưa tích hợp cổng VNPay/Momo thật.

## License

Educational use only - Van Hien University 2026.
