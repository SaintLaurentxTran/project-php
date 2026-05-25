<?php
$cartCount = 0;
if (!empty($_SESSION['cart'])) {
  foreach ($_SESSION['cart'] as $it) $cartCount += (int)$it['qty'];
}
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
</head>
<body>
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
      <button class="icon-pill" type="button" title="Thông báo">
        <span class="material-symbols-outlined">notifications</span>
      </button>
      <button class="icon-pill" type="button" title="Ngôn ngữ">
        <span class="material-symbols-outlined">language</span>
      </button>

      <div class="divider"></div>

      <a class="link-btn" href="index.php?c=seller&a=products">Seller Center</a>
      <button class="link-btn" type="button">Đăng Nhập</button>
      <button class="btn btn-primary" type="button">Đăng Ký</button>
    </div>
  </div>

  <nav class="navlinks">
    <a href="index.php#flash">Flash Sale</a>
    <a href="index.php?c=default&a=search">Live</a>
    <a href="index.php?c=default&a=search">Vouchers</a>
    <a href="index.php?c=default&a=search">Global</a>
    <a href="index.php?c=default&a=search">Brand Outlet</a>
  </nav>
</header>

<main class="main">