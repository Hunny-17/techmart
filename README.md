# TechMart Web

TechMart là website thương mại điện tử bán thiết bị công nghệ, được xây dựng cho môn Lập trình Web Nâng cao tại Văn Hiến University.

## Stack

- PHP 8 OOP, mô hình MVC tự xây dựng.
- MySQL hoặc MariaDB trên XAMPP.
- Bootstrap 5, Bootstrap Icons, Vanilla JavaScript.
- Giỏ hàng lưu bằng `$_SESSION`, không dùng cookie cho cart.
- Không dùng Laravel, Composer framework, React, Vue hoặc Angular.

## Tính năng chính

### Khách hàng

- Đăng ký, đăng nhập, đăng xuất.
- Xác thực email, quên mật khẩu và đặt lại mật khẩu.
- Trang chủ, danh sách sản phẩm, tìm kiếm, lọc danh mục, lọc giá, sắp xếp và phân trang.
- Trang chi tiết sản phẩm với gallery ảnh, mẫu/biến thể, tồn kho, mô tả, đánh giá và sản phẩm liên quan.
- So sánh tối đa 3 sản phẩm bằng `localStorage`.
- Danh sách yêu thích.
- Giỏ hàng lưu bằng session.
- Checkout với COD, chuyển khoản ngân hàng và ví điện tử.
- Gợi ý mã giảm giá có thể dùng ngay tại checkout.
- Mã thanh toán tham chiếu và QR VietQR tự render theo tổng tiền cần thanh toán.
- Theo dõi đơn hàng, xem chi tiết đơn, hủy đơn, đặt lại đơn.
- Đánh giá sản phẩm sau khi đơn đã giao.
- Trang tài khoản cá nhân, cập nhật thông tin và đổi mật khẩu.

### Quản trị

- Dashboard vận hành: doanh thu, đơn hàng, khách mới, tồn kho thấp, trạng thái thanh toán.
- Quản lý sản phẩm, ảnh phụ, mẫu/biến thể, tồn kho và nhập kho.
- Quản lý danh mục, có chặn vòng lặp danh mục cha/con.
- Quản lý đơn hàng, trạng thái đơn, trạng thái thanh toán, hóa đơn và email đã gửi.
- Ràng buộc thanh toán: đơn chuyển khoản/ví phải được xác nhận đã thanh toán trước khi duyệt.
- COD tự chuyển sang đã thanh toán khi đơn được giao thành công.
- Quản lý khách hàng, khóa/mở khóa, gửi lại email xác thực.
- Quản lý nhân viên.
- Quản lý đánh giá.
- Quản lý voucher, không xóa cứng voucher đã phát sinh đơn hàng.
- Tổng quan tồn kho.
- Nhật ký thao tác admin.
- Export dữ liệu đơn hàng/khách hàng ở các màn hỗ trợ.

## Cấu trúc thư mục

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
|   |-- Core/                MVC core: App, Router, Database, Model, View, Auth, Session...
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
|   |-- schema.sql           Cấu trúc database hiện tại
|   |-- seed.sql             Dữ liệu mẫu
|   `-- migrations/          Migration bổ sung trong quá trình phát triển
|
|-- storage/
|   |-- logs/                Log lỗi local
|   |-- mail/                Email HTML khi MAIL_DRIVER=log
|   `-- uploads/             Upload cũ/nội bộ
|
|-- .env.example             Mẫu cấu hình môi trường
|-- .gitignore
|-- .htaccess                Redirect root vào public/
|-- CODEX_PROMPTS.md
`-- README.md
```

## Cài đặt local với XAMPP

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

Nếu đang dùng bản trong XAMPP hiện tại:

```powershell
cd C:\Xampp\htdocs\techmart-web
```

### 3. Tạo file môi trường

Copy `.env.example` thành `.env`, sau đó chỉnh cấu hình DB cho đúng XAMPP:

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

Không commit file `.env` lên GitHub.

### 4. Tạo database

Mở phpMyAdmin hoặc MySQL CLI, tạo database:

```sql
CREATE DATABASE techmart CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Import lần lượt:

```sql
SOURCE C:/Xampp/htdocs/techmart-web/database/schema.sql;
SOURCE C:/Xampp/htdocs/techmart-web/database/seed.sql;
```

Hoặc import hai file trên bằng phpMyAdmin.

### 5. Chạy web

Bật Apache và MySQL trong XAMPP, sau đó mở:

```text
http://localhost/techmart-web/
```

## Tài khoản demo

Mật khẩu mặc định của các tài khoản seed là:

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

Ghi chú: trên domain demo hiện tại admin có thể đã được đổi mật khẩu sang `password124`.

## Cấu hình email

Local nên dùng chế độ log để demo ổn định:

```env
MAIL_DRIVER=log
MAIL_FROM_EMAIL=no-reply@techmart.test
MAIL_FROM_NAME=TechMart
```

Email sẽ được ghi thành file HTML trong:

```text
storage/mail
```

Nếu muốn gửi email thật qua SMTP provider như Gmail App Password, Brevo hoặc Mailtrap, chỉnh `.env`:

```env
MAIL_DRIVER=smtp
MAIL_FROM_EMAIL=your-sender@example.com
MAIL_FROM_NAME=TechMart
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls
MAIL_USERNAME=your-smtp-username
MAIL_PASSWORD=your-smtp-password
MAIL_TIMEOUT=15
```

Các luồng email hiện có:

- Xác thực email sau đăng ký.
- Quên mật khẩu/đặt lại mật khẩu.
- Xác nhận đặt hàng.
- Thông báo thanh toán đã được admin xác nhận.
- Thông báo đổi trạng thái đơn hàng.

## Cấu hình thanh toán thủ công

Checkout hỗ trợ:

- COD.
- Chuyển khoản ngân hàng.
- Ví điện tử.

Đơn chuyển khoản/ví điện tử có mã tham chiếu dạng `TMYYYYMMDDXXXXXX`.

Nếu muốn hiển thị QR VietQR, cấu hình trong `.env`:

```env
PAYMENT_BANK_ID=MB
PAYMENT_BANK_ACCOUNT_NO=100612200517
PAYMENT_BANK_ACCOUNT_NAME=TRAN QUOC HUY
PAYMENT_BANK_NAME=MB Bank
```

QR được render theo tổng tiền checkout và mã thanh toán của từng đơn.

## Kiểm thử nhanh

Lint toàn bộ PHP:

```powershell
Get-ChildItem -Path app,config -Recurse -Filter *.php | ForEach-Object {
    C:\Xampp\php\php.exe -l $_.FullName
}
```

Các URL nên smoke test:

```text
http://localhost/techmart-web/
http://localhost/techmart-web/products
http://localhost/techmart-web/products/13
http://localhost/techmart-web/cart
http://localhost/techmart-web/checkout
http://localhost/techmart-web/login
http://localhost/techmart-web/register
http://localhost/techmart-web/profile
http://localhost/techmart-web/my-wishlist
http://localhost/techmart-web/my-orders
http://localhost/techmart-web/admin
http://localhost/techmart-web/admin/products
http://localhost/techmart-web/admin/orders
http://localhost/techmart-web/admin/customers
http://localhost/techmart-web/admin/inventory
http://localhost/techmart-web/admin/vouchers
http://localhost/techmart-web/admin/logs
```

## Bảo mật đã triển khai

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

## Quy ước code

- Mỗi file PHP bắt đầu bằng `declare(strict_types=1);`.
- Namespace theo cấu trúc `App\Core`, `App\Controllers`, `App\Controllers\Admin`, `App\Models`, `App\Services`.
- Controller kế thừa `App\Core\Controller` khi dùng helper render/json/redirect nội bộ.
- Model kế thừa `App\Core\Model`, khai báo `$table` và `$fillable`.
- Mỗi form POST cần có `<?= csrf_field() ?>`.
- Mỗi output từ dữ liệu động cần dùng `e($value)`.
- View dùng PHP thuần, không dùng Blade/Twig.
- UI dùng Bootstrap 5 và CSS trong `public/assets/css/app.css`.

## Deploy InfinityFree hoặc shared hosting

1. Tạo database trên hosting.
2. Import `database/schema.sql` và `database/seed.sql`.
3. Upload source lên `htdocs`.
4. Nếu hosting đặt document root ở `htdocs`, có thể upload cả project vào `htdocs`; file `.htaccess` root sẽ chuyển request vào `public/`.
5. Tạo `.env` production:

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

Lệnh push:

```powershell
git add .
git commit -m "chore: update project"
git push
```

## Ghi chú

- Đây là dự án học tập, không phải hệ thống thương mại điện tử production.
- Không commit `.env`, log, file mail sinh tự động hoặc dữ liệu nhạy cảm.
- Thanh toán hiện là hướng dẫn thanh toán thủ công, chưa tích hợp cổng VNPay/Momo thật.

## License

Educational use only - Văn Hiến University 2026.