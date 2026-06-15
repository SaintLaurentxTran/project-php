<?php
// Front controller
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

error_reporting(E_ALL);
ini_set('display_errors', '1');

// Khởi tạo mảng giỏ hàng mặc định
if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

require_once __DIR__ . '/app/config/database.php';
require_once __DIR__ . '/app/core/helpers.php';

// 🔥 NẠP THƯ VIỆN JWT VÀ COMPOSER
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

// 🔥 NẠP LỚP XỬ LÝ JWT RIÊNG BIỆT CỦA BẠN
if (file_exists(__DIR__ . '/app/core/JwtMiddleware.php')) {
    require_once __DIR__ . '/app/core/JwtMiddleware.php';
}

// Xử lý Remember Me
if (empty($_SESSION['user']) && !empty($_COOKIE['remember_token'])) {
    $token = $_COOKIE['remember_token'];
    $stmt = $pdo->prepare("SELECT * FROM users WHERE remember_token = ? AND is_active = 1 LIMIT 1");
    $stmt->execute([$token]);
    $rememberUser = $stmt->fetch();
    if ($rememberUser) {
        $_SESSION['user'] = [
            'id' => $rememberUser['id'],
            'name' => $rememberUser['name'],
            'email' => $rememberUser['email'],
            'role' => $rememberUser['role'],
            'avatar' => $rememberUser['avatar'],
        ];
        if (!empty($_SESSION['cart'])) {
            require_once __DIR__ . '/app/models/CartModel.php';
            $cartModel = new CartModel($pdo);
            foreach ($_SESSION['cart'] as $productId => $item) {
                $cartModel->addOrIncrement($rememberUser['id'], (int)$productId, (int)$item['qty']);
            }
            unset($_SESSION['cart']);
        }
    }
}

// Đồng bộ giỏ hàng từ DB
if (!empty($_SESSION['user']['id'])) {
    require_once __DIR__ . '/app/models/CartModel.php';
    $globalCartModel = new CartModel($pdo);
    $dbItems = $globalCartModel->getByUserId((int)$_SESSION['user']['id']);
    $_SESSION['cart'] = [];
    if (!empty($dbItems)) {
        foreach ($dbItems as $item) {
            $_SESSION['cart'][$item['id']] = [
                'id' => (int)$item['id'],
                'name' => $item['name'],
                'price' => (int)$item['price'],
                'image' => $item['image'],
                'qty' => (int)$item['qty'],
                'city' => $item['city'] ?? ''
            ];
        }
    }
}

function loadController($className) {
    $path = __DIR__ . "/app/controllers/{$className}.php";
    if (!file_exists($path)) {
        http_response_code(404);
        exit("Controller not found: {$className}.php");
    }
    require_once $path;
}

// Lấy thông tin route
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
$basePath = rtrim(str_replace('/index.php', '', $_SERVER['SCRIPT_NAME'] ?? '/index.php'), '/');
if ($basePath !== '' && str_starts_with($requestPath, $basePath)) {
    $requestPath = substr($requestPath, strlen($basePath));
}
$routePath = trim($requestPath, '/');
if ($routePath === 'index.php') $routePath = '';
$parts = array_values(array_filter(explode('/', $routePath), static fn($part) => $part !== ''));

// =========================================================================
// 🔥 ĐIỀU HƯỚNG API (RESTful) - ĐÃ TÍCH HỢP PHÂN QUYỀN
// =========================================================================
if (isset($parts[0]) && $parts[0] === 'api') {
    $resource = $parts[1] ?? null;
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    // -----------------------------------------------------------------
    // 1. QUẢN LÝ SẢN PHẨM (products)
    // -----------------------------------------------------------------
    if ($resource === 'products') {
        require_once __DIR__ . "/app/controllers/ApiProductController.php";
        $apiObj = new ApiProductController($pdo);
        if (isset($parts[2]) && is_numeric($parts[2])) $_GET['id'] = (int)$parts[2];

        switch ($method) {
            case 'GET': 
                // User & Admin đều được xem danh sách hoặc chi tiết sản phẩm
                isset($_GET['id']) ? $apiObj->show() : $apiObj->list(); 
                break;
            case 'POST': 
            case 'PUT': 
            case 'DELETE': 
                // 🔥 CHỈ ADMIN ĐƯỢC THÊM, SỬA, XÓA SẢN PHẨM
                JwtMiddleware::requireAdmin(); 
                if ($method === 'POST') $apiObj->store();
                if ($method === 'PUT') $apiObj->update();
                if ($method === 'DELETE') $apiObj->destroy();
                break;
            default: 
                header('HTTP/1.1 405 Method Not Allowed'); exit;
        }
    } 
    // -----------------------------------------------------------------
    // 2. QUẢN LÝ DANH MỤC (categories)
    // -----------------------------------------------------------------
    elseif ($resource === 'categories') {
        require_once __DIR__ . "/app/controllers/ApiCategoryController.php";
        $apiObj = new ApiCategoryController($pdo);
        if (isset($parts[2]) && is_numeric($parts[2])) $_GET['id'] = (int)$parts[2];

        switch ($method) {
            case 'GET': 
                // Ai cũng được xem danh mục
                isset($_GET['id']) ? $apiObj->show() : $apiObj->list(); 
                break;
            case 'POST': 
            case 'PUT': 
            case 'DELETE': 
                // 🔥 CHỈ ADMIN ĐƯỢC QUẢN LÝ DANH MỤC
                JwtMiddleware::requireAdmin(); 
                if ($method === 'POST') $apiObj->store();
                if ($method === 'PUT') $apiObj->update();
                if ($method === 'DELETE') $apiObj->destroy();
                break;
            default: 
                header('HTTP/1.1 405 Method Not Allowed'); exit;
        }
    } 
    // -----------------------------------------------------------------
    // 3. QUẢN LÝ GIỎ HÀNG (cart)
    // -----------------------------------------------------------------
    elseif ($resource === 'cart') {
        // 🔥 BẮT BUỘC ĐĂNG NHẬP (User hoặc Admin đều được dùng giỏ hàng)
        JwtMiddleware::getAuthenticatedUser();

        require_once __DIR__ . "/app/controllers/ApiCartController.php";
        $apiObj = new ApiCartController($pdo);
        if (isset($parts[2]) && is_numeric($parts[2])) $_GET['id'] = (int)$parts[2];

        switch ($method) {
            case 'GET': (isset($parts[2]) && $parts[2] === 'total') ? $apiObj->total() : $apiObj->index(); break;
            case 'POST': $apiObj->add(); break;
            case 'PUT': $apiObj->updateQty(); break;
            case 'DELETE': (isset($parts[2]) && $parts[2] === 'clear') ? $apiObj->clear() : $apiObj->remove(); break;
            default: header('HTTP/1.1 405 Method Not Allowed'); exit;
        }
    } 
    // -----------------------------------------------------------------
    // 4. QUẢN LÝ ĐƠN HÀNG (orders)
    // -----------------------------------------------------------------
    elseif ($resource === 'orders') {
        // 🔥 BẮT BUỘC ĐĂNG NHẬP
        $currentUser = JwtMiddleware::getAuthenticatedUser();

        require_once __DIR__ . "/app/controllers/ApiOrderController.php";
        $apiObj = new ApiOrderController($pdo);
        if (isset($parts[2]) && is_numeric($parts[2])) $_GET['id'] = (int)$parts[2];

        switch ($method) {
            case 'GET': 
                if (isset($_GET['id'])) {
                    $_GET['current_user'] = $currentUser; 
                    $apiObj->show(); 
                } else {
                    $_GET['current_user'] = $currentUser;
                    $apiObj->list(); 
                }
                break;
            case 'POST': 
                $_GET['current_user'] = $currentUser;
                (isset($parts[2]) && $parts[2] === 'cancel') ? $apiObj->cancel() : $apiObj->create(); 
                break;
            case 'PUT': 
                // 🔥 CHỈ ADMIN ĐƯỢC CẬP NHẬT TRẠNG THÁI ĐƠN HÀNG
                JwtMiddleware::requireAdmin(); 
                $apiObj->updateStatus(); 
                break;
            default: 
                header('HTTP/1.1 405 Method Not Allowed'); exit;
        }
    }
    // -----------------------------------------------------------------
    // 5. XỬ LÝ TÀI KHOẢN (auth)
    // -----------------------------------------------------------------
    elseif ($resource === 'auth') {
        $action = $parts[2] ?? null; 
        
        require_once __DIR__ . "/app/controllers/AuthController.php";
        $authObj = new AuthController($pdo);

        if ($action === 'register' && $method === 'POST') {
            $authObj->register();
        } elseif ($action === 'login' && $method === 'POST') {
            $authObj->login();
        } elseif ($action === 'me' && $method === 'GET') {
            $authObj->me();
        } elseif ($action === 'update-profile' && ($method === 'POST' || $method === 'PUT')) {
            $authObj->updateProfile();
        } elseif ($action === 'change-password' && $method === 'POST') {
            $authObj->apiChangePassword();
        } elseif ($action === 'forgot-password' && $method === 'POST') {
            $authObj->apiForgotPassword();
        } elseif ($action === 'reset-password' && $method === 'POST') {
            $authObj->apiResetPassword();
        } else {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Hành động auth không hợp lệ']);
        }
    } 
    else {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Endpoint không tồn tại']);
    }
    exit; // Kết thúc xử lý luồng API thành công!
}

// =========================================================================
// XỬ LÝ WEB CONTROLLER THÔNG THƯỜNG
// =========================================================================
$controller = $parts[0] ?? 'default';
$action = isset($parts[1]) ? route_action($parts[1]) : ($controller === 'default' ? 'home' : 'index');

$controllerClass = ucfirst(strtolower($controller)) . 'Controller';
loadController($controllerClass);
$obj = new $controllerClass($pdo);

if (!method_exists($obj, $action)) {
    http_response_code(404);
    exit("Action not found: {$controllerClass}::{$action}");
}
$obj->$action();