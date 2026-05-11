<?php
$pageTitle = $pageTitle ?? 'Snack Shop';
$activePage = $activePage ?? '';
$showHero = $showHero ?? true;
$basePath = '/project1';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="<?php echo $basePath; ?>/public/css/style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark snack-navbar sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?php echo $basePath; ?>/Default/index">SnackHub</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#snackNav" aria-controls="snackNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="snackNav">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                <li class="nav-item">
                    <a class="nav-link <?php echo $activePage === 'home' ? 'active' : ''; ?>" href="<?php echo $basePath; ?>/Default/index">Trang chủ</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $activePage === 'list' ? 'active' : ''; ?>" href="<?php echo $basePath; ?>/Product/list">Sản phẩm</a>
                </li>
                <li class="nav-item ms-lg-2">
                    <a class="btn btn-sm btn-warning fw-semibold" href="<?php echo $basePath; ?>/Product/add">+ Thêm sản phẩm</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
<?php if ($showHero): ?>
<section class="hero-banner py-5">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                <span class="badge rounded-pill text-bg-light mb-3">Snack Shop Online</span>
                <h1 class="hero-title mb-3">Đồ ăn vặt ngon, nhanh, tiện ngay tại SnackHub</h1>
                <p class="hero-subtitle mb-0">Khám phá các món snack được yêu thích với giao diện hiện đại, dễ quản lý và dễ mua sắm.</p>
            </div>
            <div class="col-lg-5 text-lg-end">
                <img src="<?php echo $basePath; ?>/public/images/snack-hero.svg" class="img-fluid hero-image" alt="Đồ ăn vặt SnackHub">
            </div>
        </div>
    </div>
</section>
<?php endif; ?>
<main class="py-4">
