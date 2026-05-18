# Quản lý Sản phẩm - Hệ thống quản lý sản phẩm

Một ứng dụng web đơn giản để quản lý sản phẩm và danh mục, xây dựng bằng PHP thuần với cấu trúc MVC.

## Tính năng

- ✅ **Danh sách sản phẩm** - Xem tất cả sản phẩm
- ✅ **Thêm sản phẩm** - Tạo sản phẩm mới
- ✅ **Xem chi tiết** - Xem thông tin chi tiết một sản phẩm
- ✅ **Sửa sản phẩm** - Cập nhật thông tin sản phẩm
- ✅ **Xóa sản phẩm** - Xóa sản phẩm khỏi hệ thống
- ✅ **Danh mục** - Phân loại sản phẩm theo danh mục

## Yêu cầu hệ thống

- PHP 7.0+
- MySQL 5.7+
- Laragon (hoặc bất kỳ máy chủ web nào hỗ trợ PHP và MySQL)

## Cài đặt

### 1. Tải xuống/Sao chép dự án

```bash
git clone https://github.com/SaintLaurentxTran/project-php.git
```

### 2. Thiết lập cơ sở dữ liệu

#### Trên Laragon:
1. Mở Laragon
2. Click vào **Database** → **MySQL** để khởi động MySQL
3. Mở **HeidiSQL** (hoặc công cụ quản lý MySQL khác)
4. Tạo kết nối đến MySQL
5. Mở file `webbanhang/database_setup.sql` và thực thi để tạo cơ sở dữ liệu và bảng
   - Hoặc sao chép nội dung và dán vào HeidiSQL rồi thực thi

#### Thay thế, sử dụng dòng lệnh:
```bash
mysql -u root -p < webbanhang/database_setup.sql
```

### 3. Cấu hình Laragon

1. Mở Laragon
2. Click chuột phải trên Laragon → **Menu** → **Preferences**
3. Đặt **Root** thành thư mục chứa repository (ví dụ: `C:\Users\YourName\project-php`)
4. Khởi động lại Apache

### 4. Truy cập ứng dụng

Mở trình duyệt web và truy cập:
```
http://localhost/webbanhang/
```

hoặc

```
http://localhost/webbanhang/Product/
```

## Cấu trúc dự án

```
webbanhang/
├── index.php                 # Front controller
├── .htaccess                 # URL rewriting rules
├── database_setup.sql        # Database schema
├── app/
│   ├── config/
│   │   └── database.php      # Database configuration
│   ├── controllers/
│   │   ├── ProductController.php
│   │   ├── CategoryController.php
│   │   └── DefaultController.php
│   ├── models/
│   │   ├── ProductModel.php
│   │   └── CategoryModel.php
│   └── views/
│       ├── shared/
│       │   ├── header.php
│       │   └── footer.php
│       └── product/
│           ├── list.php      # Danh sách sản phẩm
│           ├── show.php      # Chi tiết sản phẩm
│           ├── add.php       # Form thêm sản phẩm
│           └── edit.php      # Form sửa sản phẩm
```

## Các tuyến đường (Routes)

| Đường dẫn | Phương thức | Mô tả |
|-----------|-----------|------|
| `/webbanhang/Product/` | GET | Danh sách sản phẩm |
| `/webbanhang/Product/add` | GET | Form thêm sản phẩm |
| `/webbanhang/Product/save` | POST | Lưu sản phẩm mới |
| `/webbanhang/Product/show/:id` | GET | Chi tiết sản phẩm |
| `/webbanhang/Product/edit/:id` | GET | Form sửa sản phẩm |
| `/webbanhang/Product/update` | POST | Lưu sản phẩm đã sửa |
| `/webbanhang/Product/delete/:id` | GET | Xóa sản phẩm |

## Thay đổi cấu hình cơ sở dữ liệu

Nếu bạn muốn thay đổi tên cơ sở dữ liệu, tên người dùng hoặc mật khẩu, hãy chỉnh sửa file `app/config/database.php`:

```php
private $host = "localhost";
private $db_name = "my_store";      // Thay đổi tên DB ở đây
private $username = "root";          // Thay đổi username
private $password = "";              // Thay đổi password
```

## Troubleshooting

### Lỗi: "Connection error"
- Kiểm tra xem MySQL có đang chạy không
- Kiểm tra thông tin kết nối trong `app/config/database.php`
- Kiểm tra xem cơ sở dữ liệu `my_store` có tồn tại không

### Lỗi: "Controller not found"
- Kiểm tra URL, phải có định dạng `/webbanhang/ControllerName/action/params`
- Kiểm tra xem file controller có tồn tại không

### Lỗi: ".htaccess không hoạt động"
- Kiểm tra xem `mod_rewrite` có được bật trong Apache không
- Trên Laragon, bình thường nó đã được bật sẵn
- Thử đặt lại Apache: Laragon → Stop → Start

### Lỗi: "404 Not Found"
- Kiểm tra URL có chính xác không
- Kiểm tra xem file `.htaccess` có tồn tại trong thư mục `webbanhang` không
- Thử xóa cache trình duyệt (Ctrl+Shift+Delete)

## Phát triển thêm

Để thêm tính năng mới:

1. **Model** - Tạo các phương thức truy vấn cơ sở dữ liệu trong `app/models/`
2. **Controller** - Xử lý logic ứng dụng trong `app/controllers/`
3. **View** - Tạo giao diện người dùng trong `app/views/`

## Bảo mật

- Tất cả dữ liệu đầu vào đều được làm sạch bằng `htmlspecialchars()` và `strip_tags()`
- Sử dụng Prepared Statements để phòng chống SQL Injection
- Mật khẩu trong cấu hình không nên để trống trong môi trường production

## Giấy phép

MIT License - tự do sử dụng cho các dự án cá nhân và thương mại

## Tác giả

Saint Laurent X Tran

---

**Ghi chú:** Đây là một ứng dụng học tập đơn giản. Để sử dụng trong môi trường production, hãy thêm các tính năng như xác thực, phân quyền, logging, v.v.
