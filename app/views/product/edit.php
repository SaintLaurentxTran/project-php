<?php
$pageTitle = 'Sửa sản phẩm - SnackHub';
$activePage = 'list';
$showHero = false;
include 'app/views/layout/header.php';
$basePath = '/project1';
?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="form-card">
                <h2 class="section-title mb-4">Sửa sản phẩm</h2>
                <form method="POST" action="<?php echo $basePath; ?>/Product/edit/<?php echo (int) $product->getID(); ?>" class="row g-3" id="product-form">
                    <div class="col-12">
                        <label for="name" class="form-label">Tên sản phẩm</label>
                        <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($product->getName(), ENT_QUOTES, 'UTF-8'); ?>" required minlength="10" maxlength="100">
                    </div>
                    <div class="col-12">
                        <label for="description" class="form-label">Mô tả</label>
                        <textarea id="description" name="description" class="form-control" rows="4" required><?php echo htmlspecialchars($product->getDescription(), ENT_QUOTES, 'UTF-8'); ?></textarea>
                    </div>
                    <div class="col-12">
                        <label for="price" class="form-label">Giá</label>
                        <input type="number" id="price" name="price" step="0.01" min="0.01" class="form-control" value="<?php echo htmlspecialchars($product->getPrice(), ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>
                    <div class="col-12 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                        <a href="<?php echo $basePath; ?>/Product/list" class="btn btn-outline-secondary">Quay lại</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include 'app/views/layout/footer.php'; ?>
