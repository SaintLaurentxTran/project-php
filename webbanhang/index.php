<?php
// index.php (front controller)

session_start();

// index.php (front controller)
$controller = $_GET['controller'] ?? 'product';
$action     = $_GET['action'] ?? 'index';

$controller = $_GET['controller'] ?? 'product';
$action     = $_GET['action'] ?? 'index';

$controllerClass = ucfirst($controller) . 'Controller';
$controllerPath  = __DIR__ . '/app/controllers/' . $controllerClass . '.php';

if (!file_exists($controllerPath)) {
  http_response_code(404);
  exit("Controller not found");
}

require_once $controllerPath;

if (!class_exists($controllerClass)) {
  http_response_code(500);
  exit("Controller class missing");
}

$obj = new $controllerClass();

if (!method_exists($obj, $action)) {
  http_response_code(404);
  exit("Action not found");
}

$obj->$action();