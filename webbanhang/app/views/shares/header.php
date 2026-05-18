<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>WEBBANHANG</title>
  <link rel="stylesheet" href="public/assets/style.css">
</head>
<body>
  <div class="container">
    <div class="nav">
      <div class="brand">
        <b>WEBBANHANG</b>
        <span>PHP MVC • Laragon</span>
      </div>
      <div class="actions">
        <a class="btn" href="index.php?controller=product&action=index">Sản phẩm</a>
        <a class="btn primary" href="index.php?controller=product&action=add">+ Thêm sản phẩm</a>
      </div>
    </div>

    <?php if (!empty($_SESSION['flash'])): ?>
      <div class="flash"><?= htmlspecialchars($_SESSION['flash']) ?></div>
      <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>