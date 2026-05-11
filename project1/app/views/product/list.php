<?php
$pageTitle = 'Danh sách sản phẩm - SnackHub';
$activePage = 'list';
$showHero = true;
include 'app/views/layout/header.php';
$basePath = '/project1';
$images = [
    $basePath . '/public/images/snack-chips.svg',
    $basePath . '/public/images/snack-cookies.svg',
    $basePath . '/public/images/snack-drink.svg',
];
?>
<div class="container">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <h2 class="section-title mb-0">Danh sách sản phẩm</h2>
        <a href="<?php echo $basePath; ?>/Product/add" class="btn btn-primary">+ Thêm sản phẩm mới</a>
    </div>

    <?php if (empty($products)): ?>
        <div class="empty-state text-center">
            <p class="mb-2">Chưa có sản phẩm nào.</p>
            <a href="<?php echo $basePath; ?>/Product/add" class="btn btn-outline-primary">Bắt đầu thêm sản phẩm</a>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($products as $index => $product): ?>
                <div class="col-sm-6 col-lg-4">
                    <article class="product-card h-100">
                        <?php $productImages = $product->getImages(); ?>
                        <?php if (empty($productImages)): ?>
                            <img src="<?php echo htmlspecialchars($images[$index % count($images)], ENT_QUOTES, 'UTF-8'); ?>" alt="" class="product-image">
                        <?php elseif (count($productImages) === 1): ?>
                            <img src="<?php echo htmlspecialchars($basePath . '/' . $productImages[0], ENT_QUOTES, 'UTF-8'); ?>" alt="" class="product-image">
                        <?php else: ?>
                            <div class="product-slider" data-product-slider>
                                <?php foreach ($productImages as $imageIndex => $imagePath): ?>
                                    <img src="<?php echo htmlspecialchars($basePath . '/' . $imagePath, ENT_QUOTES, 'UTF-8'); ?>" alt="" class="product-image slide-image<?php echo $imageIndex === 0 ? ' is-active' : ''; ?>">
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        <h3 class="product-name"><?php echo htmlspecialchars($product->getName(), ENT_QUOTES, 'UTF-8'); ?></h3>
                        <p class="product-description"><?php echo htmlspecialchars($product->getDescription(), ENT_QUOTES, 'UTF-8'); ?></p>
                        <p class="product-price mb-3">Giá: <?php echo htmlspecialchars($product->getPrice(), ENT_QUOTES, 'UTF-8'); ?> đ</p>
                        <div class="d-flex gap-2 mt-auto">
                            <a class="btn btn-outline-primary btn-sm" href="<?php echo $basePath; ?>/Product/edit/<?php echo (int) $product->getID(); ?>">Sửa</a>
                            <a class="btn btn-outline-danger btn-sm" href="<?php echo $basePath; ?>/Product/delete/<?php echo (int) $product->getID(); ?>" onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này?');">Xóa</a>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<?php include 'app/views/layout/footer.php'; ?>
