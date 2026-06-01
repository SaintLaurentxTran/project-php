<?php
require_once __DIR__ . '/../../core/helpers.php';
$cartCount = 0;
if (!empty($_SESSION['cart'])) {
  foreach ($_SESSION['cart'] as $it) $cartCount += (int)$it['qty'];
}
$auth = currentUser();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= htmlspecialchars($pageTitle ?? 'ShopeeFake') ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="public/assets/styles.css">
  <link rel="stylesheet" href="public/assets/auth.css">
</head>
<body>

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
      <a class="brand-title" href="index.php">ShopeeFake</a>
    </div>

    <form class="searchbar" action="index.php" method="GET">
      <input type="hidden" name="c" value="default">
      <input type="hidden" name="a" value="search">
      <input name="q" placeholder="Tìm sản phẩm, thương hiệu và tên shop" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>"/>
      <button type="submit" class="btn btn-primary icon-btn" aria-label="Search">
        <span class="material-symbols-outlined">search</span>
      </button>
    </form>

    <div class="actions">
      <a class="icon-pill" href="index.php?c=cart&a=index" title="Giỏ hàng">
        <span class="material-symbols-outlined">shopping_cart</span>
        <?php if ($cartCount > 0): ?>
          <span class="badge"><?= $cartCount ?></span>
        <?php endif; ?>
      </a>

      <div class="divider"></div>

      <?php if ($auth): ?>
        <!-- Đã đăng nhập: hiển thị thông tin người dùng -->
        <div class="user-menu-wrap">
          <button class="user-menu-btn" id="userMenuBtn" type="button">
            <img class="user-avatar-sm" src="<?= e(base_url(avatar_url($auth['avatar']))) ?>" alt="avatar">
            <span class="user-name-short"><?= e(mb_substr($auth['name'], 0, 15)) ?></span>
            <span class="material-symbols-outlined" style="font-size:18px">expand_more</span>
          </button>
          <div class="user-dropdown" id="userDropdown">
            <div class="user-dropdown-header">
              <div class="user-dropdown-name"><?= e($auth['name']) ?></div>
              <div class="user-dropdown-email"><?= e($auth['email']) ?></div>
              <?php if ($auth['role'] === 'admin'): ?>
                <span class="role-badge role-admin">Admin</span>
              <?php else: ?>
                <span class="role-badge role-user">Thành viên</span>
              <?php endif; ?>
            </div>
            <a class="dropdown-item" href="index.php?c=profile&a=index">
              <span class="material-symbols-outlined">person</span> Hồ sơ cá nhân
            </a>
            <?php if ($auth['role'] === 'admin'): ?>
            <a class="dropdown-item" href="index.php?c=admin&a=users">
              <span class="material-symbols-outlined">manage_accounts</span> Quản lý người dùng
            </a>
            <?php endif; ?>
            <?php if (isAdmin()): ?>
              <a class="dropdown-item" href="index.php?c=seller&a=products">
                <span class="material-symbols-outlined">storefront</span> Seller Center
              </a>
            <?php endif; ?>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item dropdown-logout" href="index.php?c=auth&a=logout">
              <span class="material-symbols-outlined">logout</span> Đăng Xuất
            </a>
          </div>
        </div>
      <?php else: ?>
        <!-- Chưa đăng nhập -->
        <a class="link-btn" href="index.php?c=auth&a=login">Đăng Nhập</a>
        <a class="btn btn-primary" href="index.php?c=auth&a=register">Đăng Ký</a>
      <?php endif; ?>
    </div>
  </div>

  <nav class="navlinks">
    <a href="index.php#flash">Flash Sale</a>
    <a href="index.php?c=default&a=search">Live</a>
    <a href="index.php?c=default&a=search">Vouchers</a>
    <a href="index.php?c=default&a=search">Global</a>
    <a href="index.php?c=default&a=search">Brand Outlet</a>
    <?php if (isAdmin()): ?>
      <a href="index.php?c=admin&a=users" style="color:#ffd700;font-weight:700;">⚙ Admin</a>
    <?php endif; ?>
  </nav>
</header>

<main class="main">
