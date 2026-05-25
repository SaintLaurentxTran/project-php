<?php
// Front controller + tiny router
session_start();

error_reporting(E_ALL);
ini_set('display_errors', '1');

// Nạp file cấu hình cơ sở dữ liệu để lấy biến $pdo
require_once __DIR__ . '/app/config/database.php';

/**
 * Hàm nạp file Controller một cách an toàn
 */
function loadController($className) {
    // Sửa lỗi: Chỉ truyền tên Class vào đây, đuôi .php sẽ được tự động nối một lần duy nhất
    $path = __DIR__ . "/app/controllers/{$className}.php";
    
    if (!file_exists($path)) {
        http_response_code(404);
        exit("Controller not found: {$className}.php tại đường dẫn: {$path}");
    }
    require_once $path;
}

// 1. Lấy tham số từ URL (Mặc định là 'default' và 'home')
$controller = $_GET['c'] ?? 'default';
$action     = $_GET['a'] ?? 'home';

// 2. Chuẩn hóa tên Class (Ví dụ: default -> DefaultController)
$controllerClass = ucfirst(strtolower($controller)) . 'Controller';

// 3. Nạp file dựa trên tên Class (Hàm sẽ tự nối đuôi .php một cách chính xác)
loadController($controllerClass);

// 4. Kiểm tra Class có tồn tại trong file vừa nạp không
if (!class_exists($controllerClass)) {
    http_response_code(500);
    exit("Controller class not found: {$controllerClass}");
}

// 5. Khởi tạo Object và truyền kết nối $pdo vào __construct
$obj = new $controllerClass($pdo);

// 6. Kiểm tra xem Method (Action) có tồn tại trong Class không
if (!method_exists($obj, $action)) {
    http_response_code(404);
    exit("Action not found: {$controllerClass}::{$action}");
}

// 7. Chạy hàm tương ứng
$obj->$action();