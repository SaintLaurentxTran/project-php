<?php
// Define base path for the application
define('BASE_PATH', __DIR__);
define('APP_PATH', BASE_PATH . DIRECTORY_SEPARATOR . 'app');
define('CONTROLLERS_PATH', APP_PATH . DIRECTORY_SEPARATOR . 'controllers');
define('MODELS_PATH', APP_PATH . DIRECTORY_SEPARATOR . 'models');
define('VIEWS_PATH', APP_PATH . DIRECTORY_SEPARATOR . 'views');

// Get the URL from query parameter
$url = $_GET['url'] ?? '';
$url = rtrim($url, '/');
$url = filter_var($url, FILTER_SANITIZE_URL);
$url_parts = explode('/', $url);

// Determine controller name (convert first URL segment to PascalCase + 'Controller')
$controllerName = (isset($url_parts[0]) && $url_parts[0] != '') 
    ? ucfirst(strtolower($url_parts[0])) . 'Controller' 
    : 'DefaultController';

// Determine action name (second URL segment or default to 'index')
$action = (isset($url_parts[1]) && $url_parts[1] != '') 
    ? $url_parts[1] 
    : 'index';

// Build controller file path with absolute path
$controllerPath = CONTROLLERS_PATH . DIRECTORY_SEPARATOR . $controllerName . '.php';

// Check if controller file exists
if (!file_exists($controllerPath)) {
    http_response_code(404);
    die('Controller not found: ' . htmlspecialchars($controllerName));
}

// Include controller file
require_once $controllerPath;

// Check if controller class exists
if (!class_exists($controllerName)) {
    http_response_code(500);
    die('Controller class not defined: ' . htmlspecialchars($controllerName));
}

// Create controller instance
$controller = new $controllerName();

// Check if action method exists
if (!method_exists($controller, $action)) {
    http_response_code(404);
    die('Action not found: ' . htmlspecialchars($action));
}

// Call the action with remaining URL segments as parameters
$params = array_slice($url_parts, 2);
call_user_func_array([$controller, $action], $params);