<?php include VIEWS_PATH . DIRECTORY_SEPARATOR . 'shared' . DIRECTORY_SEPARATOR . 'header.php'; ?>

<div class="page-header">
    <h1><i class="fas fa-th-list"></i> Danh Sách Danh Mục</h1>
</div>

<div class="row mb-4">
    <div class="col-md-12">
        <span class="badge badge-primary badge-lg p-3">
            <i class="fas fa-folder"></i> Tổng: <?php echo count($categories); ?> danh mục
        </span>
    </div>
</div>

<?php if (!empty($categories)): ?>
    <div class="row">
        <?php foreach ($categories as $category): ?>
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-folder"></i> <?php echo htmlspecialchars($category->name, ENT_QUOTES, 'UTF-8'); ?>
                        </h5>
                        <p class="card-text text-muted">
                            ID: <strong><?php echo htmlspecialchars($category->id, ENT_QUOTES, 'UTF-8'); ?></strong>
                        </p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="alert alert-info" role="alert">
        <i class="fas fa-info-circle"></i> Chưa có danh mục nào trong hệ thống.
    </div>
<?php endif; ?>

<div class="mt-4">
    <a href="/webbanhang/" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Quay Lại
    </a>
</div>

<?php include VIEWS_PATH . DIRECTORY_SEPARATOR . 'shared' . DIRECTORY_SEPARATOR . 'footer.php'; ?>
