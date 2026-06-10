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

$controller = $_GET['c'] ?? null;
$action     = $_GET['a'] ?? null;

if ($controller !== null && $action !== null && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') {
    $query = $_GET;
    unset($query['c'], $query['a']);
    header('Location: ' . url($controller, $action, $query), true, 301);
    exit;
}

if ($controller === null || $action === null) {
    $requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
    $basePath = rtrim(str_replace('/index.php', '', $_SERVER['SCRIPT_NAME'] ?? '/index.php'), '/');

    if ($basePath !== '' && str_starts_with($requestPath, $basePath)) {
        $requestPath = substr($requestPath, strlen($basePath));
    }

    $routePath = trim($requestPath, '/');
    if ($routePath === 'index.php') {
        $routePath = '';
    }

    $parts = array_values(array_filter(explode('/', $routePath), static fn($part) => $part !== ''));
    $controller = $parts[0] ?? 'default';
    $action = isset($parts[1]) ? route_action($parts[1]) : ($controller === 'default' ? 'home' : 'index');

    $_GET['c'] = $controller;
    $_GET['a'] = $action;
}

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
