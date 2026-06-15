<?php
require_once __DIR__ . '/../../core/helpers.php';

$cartCount = 0;
if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $it) {
        $cartCount += (int)$it['qty'];
    }
}

// 🔥 GIẢI PHÁP TRIỆT ĐỂ: Luôn ưu tiên đọc dữ liệu nóng nhất từ biến Session hệ thống
$auth = $_SESSION['user'] ?? null;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <base href="<?= e(base_url('/')) ?>">
  <title><?= htmlspecialchars($pageTitle ?? 'ShopeeFake') ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="<?= e(base_url('public/assets/styles.css')) ?>?v=<?= filemtime(__DIR__ . '/../../../public/assets/styles.css') ?>">
  <link rel="stylesheet" href="<?= e(base_url('public/assets/auth.css')) ?>?v=<?= filemtime(__DIR__ . '/../../../public/assets/auth.css') ?>">
</head>
<body data-base-url="<?= e(base_url('/')) ?>" data-login-url="<?= e(url('auth', 'login')) ?>">

<?php
// Hiển thị flash messages
$flashSuccess = flash('success');
$flashError   = flash('error');
if ($flashSuccess || $flashError): ?>
<div class="flash-container">
  <?php if ($flashSuccess): ?>
    <div class="flash flash-success">
      <span class="material-symbols-outlined">check_circle</span>
      <?= $flashSuccess ?>
      <button class="flash-close" onclick="this.parentElement.remove()">×</button>
    </div>
  <?php endif; ?>
  <?php if ($flashError): ?>
    <div class="flash flash-error">
      <span class="material-symbols-outlined">error</span>
      <?= $flashError ?>
      <button class="flash-close" onclick="this.parentElement.remove()">×</button>
    </div>
  <?php endif; ?>
</div>
<?php endif; ?>

<header class="topbar">
  <div class="topbar-inner">
    <div class="brand">
      <a class="brand-title" href="<?= e(url()) ?>">ShopeeFake</a>
    </div>

    <form class="searchbar" action="<?= e(url('default', 'search')) ?>" method="GET">
      <input name="q" placeholder="Tìm sản phẩm, thương hiệu và tên shop" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>"/>
      <button type="submit" class="btn btn-primary icon-btn" aria-label="Search">
        <span class="material-symbols-outlined">search</span>
      </button>
    </form>

    <div class="actions">
      <a class="icon-pill" href="<?= e(url('cart', 'index')) ?>" title="Giỏ hàng">
        <span class="material-symbols-outlined">shopping_cart</span>
        <?php if ($cartCount > 0): ?>
          <span class="badge"><?= $cartCount ?></span>
        <?php endif; ?>
      </a>

      <div class="divider"></div>

      <?php if ($auth): ?>
        <div class="user-menu-wrap">
          <button class="user-menu-btn" id="userMenuBtn" type="button">
            <img class="user-avatar-sm" 
                 src="<?= empty($auth['avatar']) ? '/public/assets/default_avatar.png' : '/public/uploads/avatars/' . e($auth['avatar']) . '?t=' . time() ?>" 
                 alt="avatar"
                 style="width: 35px; height: 35px; border-radius: 50%; object-fit: cover;">
            <span class="user-name-short"><?= e(mb_substr($auth['name'], 0, 15)) ?></span>
            <span class="material-symbols-outlined" style="font-size:18px">expand_more</span>
          </button>
          <div class="user-dropdown" id="userDropdown">
            <div class="user-dropdown-header">
              <div class="user-dropdown-name"><?= e($auth['name']) ?></div>
              <div class="user-dropdown-email"><?= e($auth['email']) ?></div>
              <?php if (isset($auth['role']) && $auth['role'] === 'admin'): ?>
                <span class="role-badge role-admin">Admin</span>
              <?php else: ?>
                <span class="role-badge role-user">Thành viên</span>
              <?php endif; ?>
            </div>
            <a class="dropdown-item" href="<?= e(url('profile', 'index')) ?>">
              <span class="material-symbols-outlined">person</span> Hồ sơ cá nhân
            </a>
            <?php if (isset($auth['role']) && $auth['role'] === 'admin'): ?>
            <a class="dropdown-item" href="<?= e(url('admin', 'users')) ?>">
              <span class="material-symbols-outlined">manage_accounts</span> Quản lý người dùng
            </a>
            <a class="dropdown-item" href="<?= e(url('admin', 'orders')) ?>">
              <span class="material-symbols-outlined">receipt_long</span> Quản lý đơn hàng
            </a>
            <?php endif; ?>
            <?php if (isAdmin()): ?>
              <a class="dropdown-item" href="<?= e(url('seller', 'products')) ?>">
                <span class="material-symbols-outlined">storefront</span> Seller Center
              </a>
              <a class="dropdown-item" href="<?= e(url('seller', 'categories')) ?>">
                <span class="material-symbols-outlined">category</span> Danh mục sản phẩm
              </a>
            <?php endif; ?>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item dropdown-logout" data-api-logout href="<?= e(url('auth', 'logout')) ?>">
              <span class="material-symbols-outlined">logout</span> Đăng Xuất
            </a>
          </div>
        </div>
      <?php else: ?>
        <a class="link-btn" href="<?= e(url('auth', 'login')) ?>">Đăng Nhập</a>
        <a class="btn btn-primary" href="<?= e(url('auth', 'register')) ?>">Đăng Ký</a>
      <?php endif; ?>
    </div>
  </div>

  <nav class="navlinks">
    <a href="<?= e(url('default', 'home', [], 'flash')) ?>">Flash Sale</a>
    <a href="<?= e(url('default', 'search')) ?>">Live</a>
    <a href="<?= e(url('default', 'search')) ?>">Vouchers</a>
    <a href="<?= e(url('default', 'search')) ?>">Global</a>
    <a href="<?= e(url('default', 'search')) ?>">Brand Outlet</a>
    <?php if (isAdmin()): ?>
      <a href="<?= e(url('admin', 'users')) ?>" style="color:#ffd700;font-weight:700;">⚙ Admin</a>
      <a href="<?= e(url('admin', 'orders')) ?>" style="color:#ffd700;font-weight:700;">Đơn hàng</a>
      <a href="<?= e(url('seller', 'categories')) ?>" style="color:#ffd700;font-weight:700;">Danh mục</a>
    <?php endif; ?>
  </nav>
</header>

<main class="main">
