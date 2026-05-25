<?php

require_once __DIR__ . '/Controller.php';

class Router
{
    public function dispatch(): void
    {
        $path = $_GET['url'] ?? '';
        $path = trim($path, '/');

        // default route
        if ($path === '') {
            $controllerName = 'DefaultController';
            $action = 'index';
            $params = [];
        } else {
            $parts = explode('/', $path);
            $controllerName = ucfirst($parts[0]) . 'Controller';
            $action = $parts[1] ?? 'index';
            $params = array_slice($parts, 2);
        }

        $controllerFile = __DIR__ . '/../controllers/' . $controllerName . '.php';
        if (!file_exists($controllerFile)) {
            http_response_code(404);
            echo "Controller not found: " . e($controllerName);
            return;
        }

        require_once $controllerFile;

        if (!class_exists($controllerName)) {
            http_response_code(500);
            echo "Controller class missing: " . e($controllerName);
            return;
        }

        $controller = new $controllerName();

        if (!method_exists($controller, $action)) {
            http_response_code(404);
            echo "Action not found: " . e($action);
            return;
        }

        call_user_func_array([$controller, $action], $params);
    }
}