<?php

function base_url(string $path = ''): string {
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
    $base = str_replace('/index.php', '', $scriptName);
    $base = rtrim($base, '/');
    if ($path === '') {
        return $base !== '' ? $base : '/';
    }

    return ($base !== '' ? $base : '') . '/' . ltrim($path, '/');
}

function route_slug(string $value): string {
    $value = preg_replace('/([a-z])([A-Z])/', '$1-$2', $value);
    return strtolower(str_replace('_', '-', $value));
}

function route_action(string $slug): string {
    $slug = str_replace('_', '-', $slug);
    return preg_replace_callback('/-([a-z])/', fn($m) => strtoupper($m[1]), strtolower($slug));
}

function url(string $controller = 'default', string $action = 'home', array $params = [], string $fragment = ''): string {
    $controller = route_slug($controller);
    $action = route_slug($action);

    if ($controller === 'default' && $action === 'home') {
        $path = base_url();
    } else {
        $path = base_url($controller . '/' . $action);
    }

    $query = http_build_query(array_filter($params, static fn($value) => $value !== null && $value !== ''), '', '&', PHP_QUERY_RFC3986);
    return $path . ($query ? '?' . $query : '') . ($fragment ? '#' . ltrim($fragment, '#') : '');
}

function current_url(array $params = []): string {
    $controller = $_GET['c'] ?? 'default';
    $action = $_GET['a'] ?? 'home';
    $query = array_merge($_GET, $params);
    unset($query['c'], $query['a']);
    return url($controller, $action, $query);
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

// ============================
// AUTH HELPERS
// ============================

function isLoggedIn(): bool {
    return !empty($_SESSION['user']);
}

function isAdmin(): bool {
    return !empty($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin';
}

function currentUser(): ?array {
    return $_SESSION['user'] ?? null;
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        $_SESSION['flash_error'] = 'Vui lòng đăng nhập để tiếp tục.';
        redirect(url('auth', 'login'));
    }
}

function requireAdmin(): void {
    requireLogin();
    if (!isAdmin()) {
        http_response_code(403);
        exit('Bạn không có quyền truy cập trang này.');
    }
}

function avatar_url(?string $avatar): string {
    if ($avatar && file_exists(__DIR__ . '/../../' . $avatar)) {
        return $avatar;
    }
    return 'public/assets/default_avatar.png';
}

function flash(string $type = 'success'): ?string {
    $key = "flash_{$type}";
    if (!empty($_SESSION[$key])) {
        $msg = $_SESSION[$key];
        unset($_SESSION[$key]);
        return $msg;
    }
    return null;
}
