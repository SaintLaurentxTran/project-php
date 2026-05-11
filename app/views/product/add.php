<?php
$pageTitle = 'Thêm sản phẩm - SnackHub';
$activePage = 'list';
$showHero = false;
include 'app/views/layout/header.php';
$basePath = '/project1';
?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="form-card">
                <h2 class="section-title mb-4">Thêm sản phẩm mới</h2>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger" role="alert">
                        <ul class="mb-0 ps-3">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?php echo $basePath; ?>/Product/add" class="row g-3" id="product-form">
                    <div class="col-12">
                        <label for="name" class="form-label">Tên sản phẩm</label>
                        <input type="text" id="name" name="name" class="form-control" required minlength="10" maxlength="100">
                    </div>
                    <div class="col-12">
                        <label for="description" class="form-label">Mô tả</label>
                        <textarea id="description" name="description" class="form-control" rows="4" required></textarea>
                    </div>
                    <div class="col-12">
                        <label for="price" class="form-label">Giá</label>
                        <input type="number" id="price" name="price" step="0.01" min="0.01" class="form-control" required>
                    </div>
                    <div class="col-12 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Thêm sản phẩm</button>
                        <a href="<?php echo $basePath; ?>/Product/list" class="btn btn-outline-secondary">Quay lại</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include 'app/views/layout/footer.php'; ?>
