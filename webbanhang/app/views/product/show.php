<?php include VIEWS_PATH . DIRECTORY_SEPARATOR . 'shared' . DIRECTORY_SEPARATOR . 'header.php'; ?>

<div class="page-header">
    <h1><i class="fas fa-eye"></i> Chi Tiết Sản Phẩm</h1>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <h2 class="card-title mb-4">
                    <?php echo htmlspecialchars($product->name, ENT_QUOTES, 'UTF-8'); ?>
                </h2>
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold text-muted">
                                <i class="fas fa-hashtag"></i> Mã Sản Phẩm
                            </label>
                            <p class="lead">#<?php echo htmlspecialchars($product->id, ENT_QUOTES, 'UTF-8'); ?></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold text-muted">
                                <i class="fas fa-tag"></i> Danh Mục
                            </label>
                            <p class="lead">
                                <span class="badge badge-info">
                                    <?php echo htmlspecialchars($product->category_name ?? 'Chưa phân loại', ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="form-group mb-4">
                    <label class="font-weight-bold text-muted">
                        <i class="fas fa-align-left"></i> Mô Tả
                    </label>
                    <p class="lead">
                        <?php echo htmlspecialchars($product->description, ENT_QUOTES, 'UTF-8'); ?>
                    </p>
                </div>

                <div class="form-group mb-4">
                    <label class="font-weight-bold text-muted">
                        <i class="fas fa-money-bill-wave"></i> Giá Bán
                    </label>
                    <p class="lead text-success">
                        <strong>₫<?php echo number_format($product->price, 0, ',', '.'); ?></strong>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card bg-light">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="fas fa-cogs"></i> Hành Động
                </h5>
                <hr>
                <a href="/webbanhang/Product/edit/<?php echo $product->id; ?>" class="btn btn-warning btn-block mb-2">
                    <i class="fas fa-edit"></i> Chỉnh Sửa
                </a>
                <a href="/webbanhang/Product/delete/<?php echo $product->id; ?>" class="btn btn-danger btn-block mb-3" onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này?');">
                    <i class="fas fa-trash"></i> Xóa
                </a>
                <a href="/webbanhang/Product/" class="btn btn-secondary btn-block">
                    <i class="fas fa-arrow-left"></i> Quay Lại
                </a>
            </div>
        </div>
    </div>
</div>

<?php include VIEWS_PATH . DIRECTORY_SEPARATOR . 'shared' . DIRECTORY_SEPARATOR . 'footer.php'; ?>
