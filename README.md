# TechMart Web

> Dá»± Ã¡n mÃ´n **Láº­p trÃ¬nh Web NÃ¢ng cao** â€” VÄƒn Hiáº¿n University 2026
> Stack: PHP 8 OOP + MySQL + MVC tá»± code + Bootstrap 5

## ðŸ“ Cáº¥u trÃºc thÆ° má»¥c

```
techmart-web/
â”œâ”€â”€ public/                    â† Document root (Apache trá» vÃ o Ä‘Ã¢y)
â”‚   â”œâ”€â”€ index.php              â† Front controller (entry point)
â”‚   â”œâ”€â”€ .htaccess              â† Rewrite rules
â”‚   â””â”€â”€ assets/                â† CSS, JS, images cÃ´ng khai
â”‚
â”œâ”€â”€ app/
â”‚   â”œâ”€â”€ Core/                  â† Framework core (KHÃ”NG sá»­a khi dev module)
â”‚   â”‚   â”œâ”€â”€ App.php            â† Bootstrap + autoload
â”‚   â”‚   â”œâ”€â”€ Router.php         â† URL routing
â”‚   â”‚   â”œâ”€â”€ Database.php       â† PDO singleton
â”‚   â”‚   â”œâ”€â”€ Controller.php     â† Base controller
â”‚   â”‚   â”œâ”€â”€ Model.php          â† Base model vá»›i CRUD generic
â”‚   â”‚   â”œâ”€â”€ Session.php        â† Session wrapper
â”‚   â”‚   â”œâ”€â”€ Auth.php           â† ÄÄƒng nháº­p, phÃ¢n quyá»n
â”‚   â”‚   â”œâ”€â”€ Csrf.php           â† CSRF token
â”‚   â”‚   â”œâ”€â”€ View.php           â† Render view + layout
â”‚   â”‚   â”œâ”€â”€ Validator.php      â† Validate input
â”‚   â”‚   â”œâ”€â”€ Flash.php          â† ThÃ´ng bÃ¡o flash
â”‚   â”‚   â””â”€â”€ helpers.php        â† Global functions (e, url, csrf_field...)
â”‚   â”‚
â”‚   â”œâ”€â”€ Controllers/           â† Controllers customer-facing
â”‚   â”‚   â”œâ”€â”€ HomeController.php
â”‚   â”‚   â”œâ”€â”€ AuthController.php
â”‚   â”‚   â”œâ”€â”€ CartController.php â† Giá» hÃ ng SESSION-based (yÃªu cáº§u GV)
â”‚   â”‚   â””â”€â”€ Admin/             â† Controllers admin
â”‚   â”‚       â”œâ”€â”€ DashboardController.php
â”‚   â”‚       â””â”€â”€ ProductController.php  â† PATTERN MáºªU
â”‚   â”‚
â”‚   â”œâ”€â”€ Models/                â† Active Record models
â”‚   â”‚   â”œâ”€â”€ User.php
â”‚   â”‚   â””â”€â”€ Product.php
â”‚   â”‚
â”‚   â””â”€â”€ Views/                 â† PHP templates
â”‚       â”œâ”€â”€ layouts/           â† Layout chung (customer.php, admin.php)
â”‚       â”œâ”€â”€ partials/          â† Component nhá» (flash.php)
â”‚       â”œâ”€â”€ home/, auth/, admin/, errors/
â”‚
â”œâ”€â”€ config/
â”‚   â”œâ”€â”€ config.php             â† Load .env vÃ  expose config
â”‚   â””â”€â”€ routes.php             â† Khai bÃ¡o route
â”‚
â”œâ”€â”€ database/
â”‚   â”œâ”€â”€ schema.sql             â† DDL táº¡o báº£ng (6 báº£ng)
â”‚   â””â”€â”€ seed.sql               â† Data máº«u (5 users, 12 products, 3 orders...)
â”‚
â”œâ”€â”€ storage/
â”‚   â”œâ”€â”€ uploads/               â† File upload (áº£nh sáº£n pháº©m)
â”‚   â””â”€â”€ logs/                  â† Log lá»—i
â”‚
â”œâ”€â”€ .env.example               â† Template biáº¿n mÃ´i trÆ°á»ng
â”œâ”€â”€ .gitignore
â”œâ”€â”€ .htaccess                  â† Redirect root â†’ public/
â””â”€â”€ README.md
```

## ðŸš€ CÃ i Ä‘áº·t (Localhost vá»›i XAMPP)

### 1. YÃªu cáº§u
- PHP 8.0 trá»Ÿ lÃªn (recommend 8.2)
- MySQL 8.0 trá»Ÿ lÃªn (hoáº·c MariaDB 10.4+)
- Apache vá»›i `mod_rewrite` báº­t

### 2. Clone & setup
```bash
git clone <repo-url> techmart-web
cd techmart-web

# Copy env template
cp .env.example .env
# Sá»­a .env: DB_NAME, DB_USER, DB_PASS phÃ¹ há»£p vá»›i XAMPP
```

### 3. Database
Má»Ÿ phpMyAdmin (`http://localhost/phpmyadmin`) hoáº·c MySQL CLI:
```sql
SOURCE /Ä‘Æ°á»ng/dáº«n/Ä‘áº¿n/database/schema.sql;
SOURCE /Ä‘Æ°á»ng/dáº«n/Ä‘áº¿n/database/seed.sql;
```

### 4. Äáº·t project vÃ o htdocs
```
xampp/htdocs/techmart-web/
```

Má»Ÿ: `http://localhost/techmart-web/`

> `.htaccess` á»Ÿ root sáº½ tá»± redirect má»i request vÃ o `public/`.

### 5. TÃ i khoáº£n demo
| Email | Password | Role |
|-------|----------|------|
| `admin@techmart.test` | `password123` | admin |
| `staff01@techmart.test` | `password123` | staff |
| `customer1@techmart.test` | `password123` | customer |

## Email thong bao trang thai don hang

Mac dinh local dung che do log de demo chac chan:

```env
MAIL_DRIVER=log
MAIL_FROM_EMAIL=no-reply@techmart.test
MAIL_FROM_NAME=TechMart
```

Email se duoc ghi thanh file HTML trong:

```text
storage/mail
```

Neu muon gui email that qua SMTP provider nhu Gmail App Password, Brevo hoac Mailtrap, doi `.env`:

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

Gmail goi y:

```env
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls
MAIL_USERNAME=your-gmail@gmail.com
MAIL_PASSWORD=your-google-app-password
```

Sau khi admin doi trang thai don hang, he thong se gui email cho khach va luu lich su vao `order_email_logs`.


## ðŸ§© Pattern cho Codex / GitHub Copilot scaffold module má»›i

> **Má»¥c tiÃªu**: Khi cáº§n thÃªm 1 module CRUD admin má»›i (vd: Quáº£n lÃ½ nhÃ¢n viÃªn, Quáº£n lÃ½ khÃ¡ch hÃ ng, Quáº£n lÃ½ Ä‘Ã¡nh giÃ¡, Quáº£n lÃ½ Ä‘Æ¡n hÃ ng), Codex chá»‰ cáº§n copy pattern tá»« `Admin\ProductController` vÃ  adapt theo entity.

### Quy Æ°á»›c Ä‘áº·t tÃªn
- **Model**: `app/Models/<Entity>.php`, namespace `App\Models`, káº¿ thá»«a `App\Core\Model`
- **Controller customer**: `app/Controllers/<Entity>Controller.php`
- **Controller admin**: `app/Controllers/Admin/<Entity>Controller.php`
- **View**: `app/Views/admin/<entity-plural>/{index,create,edit}.php`
- **Route**: thÃªm vÃ o `config/routes.php`

### Pattern 1 â€” Táº¡o Model má»›i
```php
<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Order extends Model
{
    protected string $table = 'orders';
    protected array $fillable = [
        'user_id', 'total_amount', 'status',
        'shipping_address', 'payment_method', 'note',
    ];

    // ThÃªm method riÃªng náº¿u cáº§n JOIN hoáº·c query phá»©c táº¡p
    public function withCustomer(int $id): ?array
    {
        $stmt = $this->db()->prepare("
            SELECT o.*, u.full_name AS customer_name, u.email AS customer_email
            FROM orders o
            JOIN users u ON o.user_id = u.id
            WHERE o.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }
}
```

### Pattern 2 â€” Táº¡o Controller admin CRUD
Copy y nguyÃªn `app/Controllers/Admin/ProductController.php`, Ä‘á»•i:
- `Product` â†’ `<Entity>`
- `products` â†’ `<entities>` (sá»‘ nhiá»u, lowercase)
- Adjust `validate()` rules theo schema entity
- Adjust view names

6 method báº¯t buá»™c: `index`, `create`, `store`, `edit`, `update`, `destroy`.

### Pattern 3 â€” ThÃªm route
Trong `config/routes.php`:
```php
use App\Controllers\Admin\OrderController as AdminOrderController;

$router->get('/admin/orders',               [AdminOrderController::class, 'index'],   'admin');
$router->get('/admin/orders/create',        [AdminOrderController::class, 'create'],  'admin');
$router->post('/admin/orders',              [AdminOrderController::class, 'store'],   'admin');
$router->get('/admin/orders/{id}/edit',     [AdminOrderController::class, 'edit'],    'admin');
$router->post('/admin/orders/{id}',         [AdminOrderController::class, 'update'],  'admin');
$router->post('/admin/orders/{id}/delete',  [AdminOrderController::class, 'destroy'], 'admin');
```

### Pattern 4 â€” View
Copy 3 file view tá»« `app/Views/admin/products/` (`index.php`, `create.php`, `edit.php`), Ä‘á»•i:
- ÄÆ°á»ng dáº«n URL trong form action
- Field names khá»›p vá»›i schema entity
- Cá»™t trong table

## ðŸ”’ Báº£o máº­t â€” ÄÃ£ implement sáºµn

| # | Äiá»ƒm | Vá»‹ trÃ­ code |
|---|------|-------------|
| 1 | **SQL Injection** | Má»i query qua `PDO::prepare()` â€” xem `Model::find()`, `Model::create()` |
| 2 | **XSS** | Helper `e()` escape má»i output, xem `helpers.php` |
| 3 | **CSRF** | `Csrf::token()` + `Csrf::verify()` â€” xem má»i controller POST |
| 4 | **Password hash** | `password_hash(PASSWORD_BCRYPT)` â€” xem `User::register()` |
| 5 | **Session security** | HttpOnly, Secure, SameSite, regenerate â€” xem `Session::start()` |
| 6 | **PhÃ¢n quyá»n route** | Middleware `'admin'` â€” xem `Router::runMiddleware()` |
| 7 | **Upload an toÃ n** | Check MIME tháº­t, Ä‘á»•i tÃªn random â€” xem `Admin\ProductController::handleUpload()` |
| 8 | **HTTPS-ready** | Tá»± detect HTTPS Ä‘á»ƒ set secure cookie â€” xem `Session::start()` |

Khi viáº¿t bÃ¡o cÃ¡o ChÆ°Æ¡ng 4.3 (Báº£o máº­t), copy 8 Ä‘iá»ƒm trÃªn + screenshot/snippet code.

## âœ… ÄÃ£ cÃ³
- [x] Framework MVC core hoÃ n chá»‰nh
- [x] Auth (Ä‘Äƒng kÃ½, Ä‘Äƒng nháº­p, Ä‘Äƒng xuáº¥t, phÃ¢n quyá»n)
- [x] Giá» hÃ ng session-based (yÃªu cáº§u cá»‘t lÃµi cá»§a GV)
- [x] Module Quáº£n lÃ½ sáº£n pháº©m â€” CRUD Ä‘áº§y Ä‘á»§ (PATTERN MáºªU)
- [x] Module Dashboard admin
- [x] Trang chá»§ + danh sÃ¡ch + chi tiáº¿t SP (customer)
- [x] Layout customer + admin
- [x] Flash messages, validate, CSRF, upload an toÃ n
- [x] Schema 6 báº£ng + seed data Ä‘áº§y Ä‘á»§

## ðŸ“‹ Codex scaffold tiáº¿p cÃ¡c module sau

Theo pattern `Admin\ProductController`:

- [ ] **Admin\EmployeeController** + view `admin/employees/` â€” quáº£n lÃ½ nhÃ¢n viÃªn (thÃªm/xoÃ¡ theo note GV)
- [ ] **Admin\CustomerController** + view `admin/customers/` â€” xem khÃ¡ch hÃ ng + khoÃ¡ tÃ i khoáº£n
- [ ] **Admin\ReviewController** + view `admin/reviews/` â€” duyá»‡t/áº©n Ä‘Ã¡nh giÃ¡
- [ ] **Admin\OrderController** + view `admin/orders/` â€” xem Ä‘Æ¡n + Ä‘á»•i tráº¡ng thÃ¡i
- [ ] **CheckoutController** + view `cart/checkout.php` â€” Ä‘áº·t hÃ ng tá»« giá»
- [ ] **OrderController** (customer) â€” lá»‹ch sá»­ Ä‘Æ¡n hÃ ng KH
- [ ] **ReviewController** (customer) â€” KH viáº¿t Ä‘Ã¡nh giÃ¡ sau khi nháº­n hÃ ng
- [ ] View `cart/index.php` â€” hiá»ƒn thá»‹ giá» hÃ ng
- [ ] View `home/products.php`, `home/show.php` â€” list + detail SP

## ðŸŒ Deploy

### Option A â€” InfinityFree (miá»…n phÃ­ 100%)
1. ÄÄƒng kÃ½ tÃ i khoáº£n táº¡i [infinityfree.com](https://infinityfree.com)
2. Táº¡o subdomain hoáº·c add domain `.io.vn` (Ä‘Äƒng kÃ½ miá»…n phÃ­ táº¡i [iov.vn](https://iov.vn))
3. VÃ o cPanel â†’ MySQL Databases â†’ táº¡o DB má»›i
4. VÃ o phpMyAdmin â†’ import `schema.sql` rá»“i `seed.sql`
5. FTP toÃ n bá»™ folder `techmart-web/` lÃªn `htdocs/`
6. Sá»­a `.env`:
   - `APP_ENV=production`
   - `APP_DEBUG=false`
   - `APP_URL=https://yourdomain.io.vn`
   - `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` theo cPanel
7. SSL: vÃ o cPanel â†’ Free SSL Certificate â†’ enable

### Option B â€” Shared hosting cÃ³ phÃ­ (AZDIGI / Hostinger)
TÆ°Æ¡ng tá»±, nhÆ°ng performance tá»‘t hÆ¡n. Khoáº£ng 30-50k/thÃ¡ng cho student plan.

## ðŸ› ï¸ Coding conventions

- `declare(strict_types=1);` Ä‘áº§u má»i file PHP
- Class `final` khi khÃ´ng cÃ³ Ã½ Ä‘á»‹nh káº¿ thá»«a
- Typed properties, constructor promotion (PHP 8+)
- Namespace `App\Core\*`, `App\Controllers\*`, `App\Models\*`
- TÃªn view kebab-case: `products/index.php`
- Comment tiáº¿ng Viá»‡t cho business logic, tiáº¿ng Anh cho technical

## ðŸ“ License
Educational use only â€” VÄƒn Hiáº¿n University 2026
