<?php

function base_url(string $path = ''): string {
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
    $base = str_replace('/index.php', '', $scriptName);
    $base = rtrim($base, '/');
    return $base . ($path ? '/' . ltrim($path, '/') : '');
}

function redirect(string $to): void {
    header('Location: ' . $to);
    exit;
}

function e(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

function old(string $key, $default = '') {
    return $_POST[$key] ?? $default;
}

function money_vnd($amount): string {
    return number_format((float)$amount, 0, ',', '.') . '₫';
}

function csrf_token(): string {
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['_csrf'];
}

function csrf_check(): void {
    $token = $_POST['_csrf'] ?? '';
    if (!$token || !isset($_SESSION['_csrf']) || !hash_equals($_SESSION['_csrf'], $token)) {
        http_response_code(419);
        exit('CSRF token invalid.');
    }
}