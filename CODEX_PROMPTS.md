# Codex / Copilot Prompts

> File này chứa prompt mẫu để feed cho Codex (hoặc Claude Code / Cursor / Copilot) tự scaffold các module còn lại theo pattern đã có sẵn.

## 🎯 Context cho Codex (paste đầu mỗi session)

```
Tôi đang phát triển dự án TechMart - web TMĐT bằng PHP 8 OOP MVC tự code (KHÔNG dùng Laravel/Symfony/framework), MySQL, Bootstrap 5.

QUAN TRỌNG:
- Tuân thủ pattern có sẵn của `app/Controllers/Admin/ProductController.php` (CRUD admin chuẩn)
- Tuân thủ pattern có sẵn của `app/Models/Product.php` (Active Record)
- Mọi controller kế thừa `App\Core\Controller`
- Mọi model kế thừa `App\Core\Model`, set `$table` và `$fillable`
- POST request PHẢI gọi `Csrf::verify()` đầu method
- Validate input qua `$this->validate($_POST, [...])`
- Flash message qua `Flash::set('success'|'error', '...')`
- Redirect qua `$this->redirect('/path')`
- View dùng `$this->view('path/to/view', $data, 'admin')` với layout 'admin' cho trang admin
- Mọi output PHP trong view escape qua hàm `e($value)` để chống XSS
- Mọi form POST có `<?= csrf_field() ?>`
- KHÔNG dùng cookies cho cart, KHÔNG concat SQL string
- File PHP bắt đầu bằng `declare(strict_types=1);`
- Class set `final` nếu không có ý kế thừa
- Comment bằng tiếng Việt cho business logic
```

---

## Prompt 1 — Scaffold Quản lý Nhân viên

```
Dựa vào pattern app/Controllers/Admin/ProductController.php và app/Models/Product.php,
scaffold module "Quản lý nhân viên" với các yêu cầu:

1. Model: app/Models/Employee.php (thực ra là User với role='staff')
   - Kế thừa Model, override các method để filter role='staff'
   - Method: allStaff(), createStaff($data), findStaff($id)
   
2. Controller: app/Controllers/Admin/EmployeeController.php
   - 4 method theo note GV: index (xem), create (thêm form), store (xử lý thêm), destroy (xoá)
   - KHÔNG cần edit/update theo yêu cầu GV ("thêm xoá xoá" = thêm + xoá)
   - Field tạo NV: full_name, email, phone, password (auto-generate hash bcrypt), role='staff', status='active'

3. Views: app/Views/admin/employees/index.php, create.php
   - Bảng list NV
   - Form thêm NV mới
   - Nút xoá có confirm

4. Routes trong config/routes.php (admin middleware):
   - GET  /admin/employees           → index
   - GET  /admin/employees/create    → create
   - POST /admin/employees           → store
   - POST /admin/employees/{id}/delete → destroy

5. Thêm link sidebar trong app/Views/layouts/admin.php (đã có sẵn)
```

---

## Prompt 2 — Scaffold Quản lý Khách hàng

```
Scaffold module "Quản lý khách hàng" theo pattern Admin/ProductController:

1. Model: app/Models/Customer.php
   - Method: allCustomers(), lock($id), unlock($id) (đã có sẵn trong User.php — có thể reuse hoặc tạo wrapper)

2. Controller: app/Controllers/Admin/CustomerController.php
   - 3 method theo note GV: index (xem), lock (khoá), unlock (mở khoá)
   - Customer KHÔNG được phép xoá vì sẽ phá foreign key với orders → chỉ khoá

3. Views: app/Views/admin/customers/index.php
   - Bảng list customer với cột: ID, Họ tên, Email, SĐT, Số đơn hàng, Trạng thái, Action
   - Action: nút khoá/mở khoá tuỳ status

4. Routes:
   - GET  /admin/customers              → index
   - POST /admin/customers/{id}/lock    → lock
   - POST /admin/customers/{id}/unlock  → unlock
```

---

## Prompt 3 — Scaffold Quản lý Đánh giá

```
Scaffold module "Quản lý đánh giá":

1. Model: app/Models/Review.php
   - Kế thừa Model, $table = 'reviews'
   - $fillable = ['product_id', 'user_id', 'order_id', 'rating', 'comment', 'status']
   - Method: allWithDetails() — JOIN reviews + products + users để hiển thị tên SP và tên KH
   - Method: hide($id), show($id)

2. Controller: app/Controllers/Admin/ReviewController.php
   - 4 method: index, hide, show, destroy
   - Mặc định mới tạo là 'visible', admin có thể 'hide' để ẩn đánh giá vi phạm

3. Views: app/Views/admin/reviews/index.php
   - Bảng list review với: ID, Sản phẩm, Khách hàng, Sao (⭐), Comment, Status, Ngày, Action

4. Routes:
   - GET  /admin/reviews              → index
   - POST /admin/reviews/{id}/hide    → hide
   - POST /admin/reviews/{id}/show    → show (unhide)
   - POST /admin/reviews/{id}/delete  → destroy
```

---

## Prompt 4 — Scaffold Quản lý Đơn hàng (Admin)

```
Scaffold module "Quản lý đơn hàng" - phía admin:

1. Model: app/Models/Order.php
   - Kế thừa Model, $table = 'orders'
   - $fillable = ['user_id', 'total_amount', 'status', 'shipping_address', 'payment_method', 'note']
   - Method withDetails($id): JOIN orders + users + order_details + products
   - Method changeStatus($id, $status): UPDATE status
   - Statuses: pending → confirmed → shipping → delivered (hoặc cancelled bất cứ lúc nào)

2. Model: app/Models/OrderDetail.php
   - $fillable = ['order_id', 'product_id', 'quantity', 'unit_price']
   - Method byOrder($orderId): SELECT JOIN với products

3. Controller: app/Controllers/Admin/OrderController.php
   - Method index: list tất cả đơn, filter theo status (query param ?status=pending)
   - Method show($id): xem chi tiết 1 đơn (items, customer, address)
   - Method updateStatus($id): POST đổi status (validate transition hợp lệ)

4. Views:
   - app/Views/admin/orders/index.php — bảng list đơn
   - app/Views/admin/orders/show.php — chi tiết đơn + nút đổi status

5. Routes:
   - GET  /admin/orders                 → index
   - GET  /admin/orders/{id}            → show
   - POST /admin/orders/{id}/status     → updateStatus
```

---

## Prompt 5 — Scaffold Checkout (Customer)

```
Scaffold flow "Đặt hàng" cho customer:

1. Controller: app/Controllers/CheckoutController.php (yêu cầu auth middleware)
   - Method index (GET): hiển thị form checkout với items từ session cart + form địa chỉ giao hàng
   - Method placeOrder (POST):
     a. Csrf::verify()
     b. Validate địa chỉ, sđt, payment_method (chỉ 'cod' v1)
     c. Lấy cart từ $_SESSION
     d. BEGIN TRANSACTION:
        - INSERT vào orders (user_id, total_amount, status='pending', ...)
        - INSERT từng item vào order_details (unit_price = giá hiện tại từ products)
        - UPDATE products SET stock_quantity = stock_quantity - quantity
     e. COMMIT
     f. Xoá $_SESSION['cart']
     g. Flash::set('success', 'Đặt hàng thành công, mã đơn #...')
     h. Redirect /my-orders

2. View: app/Views/cart/checkout.php
   - Summary cart items + tổng tiền
   - Form: shipping_address (textarea), phone (input), payment_method (radio - chỉ COD v1), note (textarea)
   - Nút "Xác nhận đặt hàng"

3. Routes (đã có middleware 'auth'):
   - GET  /checkout       → index
   - POST /checkout       → placeOrder

4. Controller: app/Controllers/MyOrderController.php (auth)
   - Method index: list đơn của user hiện tại (Auth::id())
   - Method show($id): chi tiết đơn (verify user_id khớp Auth::id() để chống IDOR)

5. Routes:
   - GET /my-orders         → MyOrderController::index
   - GET /my-orders/{id}    → MyOrderController::show
```

---

## Prompt 6 — Scaffold Review từ phía khách hàng

```
Cho phép KH viết đánh giá sau khi nhận hàng:

1. Controller: app/Controllers/ReviewController.php (auth)
   - Method create (GET): /products/{id}/review — hiển thị form, kiểm tra:
     a. User đã có đơn 'delivered' chứa sản phẩm này
     b. User chưa review cho sản phẩm này từ đơn này
   - Method store (POST): /products/{id}/review
     a. Csrf::verify()
     b. Re-check 2 điều kiện trên (chống bypass)
     c. INSERT vào reviews
     d. Flash success, redirect về trang chi tiết SP

2. View: app/Views/reviews/create.php
   - Form rating (5 sao, radio hoặc star widget)
   - Textarea comment

3. Sửa app/Views/home/show.php:
   - Thêm section "Đánh giá sản phẩm" hiển thị reviews visible
   - Nếu user đã mua thì hiển thị nút "Viết đánh giá"
```

---

## Prompt 7 — Dashboard nâng cao với Chart.js

```
Nâng cấp app/Controllers/Admin/DashboardController.php với biểu đồ:

1. Bổ sung vào index():
   - $stats['orders'] = (new Order())->count()
   - $stats['revenue'] = tổng total_amount của đơn 'delivered'
   - $stats['pending_orders'] = count where status='pending'
   - $revenueLast7Days = array [['date' => '2026-06-15', 'amount' => 12500000], ...] - 7 ngày gần nhất
   - $topProducts = top 5 SP bán chạy nhất (JOIN order_details)

2. Sửa app/Views/admin/dashboard.php:
   - 4 card thống kê đầu trang
   - Thêm 2 canvas cho Chart.js:
     a. Line chart doanh thu 7 ngày
     b. Bar chart top 5 SP bán chạy
   - Import Chart.js qua CDN
   - Render data từ PHP qua JSON: <script>const data = <?= json_encode($revenueLast7Days) ?>;</script>
```

---

## 💡 Mẹo dùng Codex hiệu quả

1. **Mở 2 file pattern trong editor** trước khi prompt: `Admin/ProductController.php` và `Models/Product.php`. Codex sẽ "thấy" pattern và copy đúng style.

2. **Chia nhỏ prompt**: làm 1 module 1 lần, đừng generate 5 module cùng lúc — Codex dễ lệch pattern và bỏ sót.

3. **Verify ngay**: sau mỗi generate, chạy `php -l <file>` để check syntax, mở browser test thủ công, fix manually nếu cần.

4. **Commit ngay khi 1 module xong** trước khi generate module tiếp theo, để dễ rollback.

5. **Thứ tự đề xuất scaffold**:
   - Prompt 4 (Order admin) — vì checkout cần
   - Prompt 5 (Checkout) — để hoàn chỉnh flow KH
   - Prompt 6 (Review KH) — sau checkout
   - Prompt 1, 2, 3 (Employee, Customer, Review admin) — song song được
   - Prompt 7 (Dashboard charts) — cuối cùng để có data đẹp demo
