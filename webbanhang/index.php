<?php
// Front controller
session_start();

error_reporting(E_ALL);
ini_set('display_errors', '1');

// Tự động đăng nhập qua remember me cookie
require_once __DIR__ . '/app/config/database.php';
require_once __DIR__ . '/app/core/helpers.php';

// Xử lý Remember Me: nếu chưa đăng nhập nhưng có cookie
if (empty($_SESSION['user']) && !empty($_COOKIE['remember_token'])) {
    $token = $_COOKIE['remember_token'];
    $stmt = $pdo->prepare("SELECT * FROM users WHERE remember_token = ? AND is_active = 1 LIMIT 1");
    $stmt->execute([$token]);
    $rememberUser = $stmt->fetch();
    if ($rememberUser) {
        $_SESSION['user'] = [
            'id'     => $rememberUser['id'],
            'name'   => $rememberUser['name'],
            'email'  => $rememberUser['email'],
            'role'   => $rememberUser['role'],
            'avatar' => $rememberUser['avatar'],
        ];
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

$controller = $_GET['c'] ?? 'default';
$action     = $_GET['a'] ?? 'home';

$controllerClass = ucfirst(strtolower($controller)) . 'Controller';

loadController($controllerClass);

if (!class_exists($controllerClass)) {
    http_response_code(500);
    exit("Controller class not found: {$controllerClass}");
}

$obj = new $controllerClass($pdo);

if (!method_exists($obj, $action)) {
    http_response_code(404);
    exit("Action not found: {$controllerClass}::{$action}");
}

$obj->$action();
