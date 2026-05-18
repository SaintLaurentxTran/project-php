<?php include VIEWS_PATH . DIRECTORY_SEPARATOR . 'shared' . DIRECTORY_SEPARATOR . 'header.php'; ?>

<div class="jumbotron jumbotron-fluid bg-primary text-white mb-5">
    <div class="container">
        <h1 class="display-4">Chào mừng đến với Hệ Thống Quản Lý Sản Phẩm</h1>
        <p class="lead">Quản lý sản phẩm và danh mục một cách hiệu quả</p>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <h2 class="mb-4">
            <i class="fas fa-box"></i> Sản Phẩm Nổi Bật
        </h2>
        <?php if (!empty($products)): ?>
            <div class="row">
                <?php 
                $featured_products = array_slice($products, 0, 3);
                foreach ($featured_products as $product): 
                ?>
                    <div class="col-md-4 mb-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($product->name, ENT_QUOTES, 'UTF-8'); ?></h5>
                                <p class="card-text text-muted"><?php echo htmlspecialchars(substr($product->description, 0, 80), ENT_QUOTES, 'UTF-8'); ?>...</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="h5 mb-0 text-success">₫<?php echo number_format($product->price, 0, ',', '.'); ?></span>
                                    <a href="/webbanhang/Product/show/<?php echo $product->id; ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i> Xem
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="mt-3">
                <a href="/webbanhang/Product" class="btn btn-primary btn-lg">
                    <i class="fas fa-list"></i> Xem Tất Cả Sản Phẩm (<?php echo count($products); ?>)
                </a>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Chưa có sản phẩm nào. 
                <a href="/webbanhang/Product/add">Tạo sản phẩm mới</a>
            </div>
        <?php endif; ?>
    </div>

    <div class="col-md-4">
        <div class="card bg-light">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="fas fa-tachometer-alt"></i> Thống Kê
                </h5>
                <hr>
                <div class="mb-3">
                    <p class="mb-2">
                        <strong>Tổng Sản Phẩm:</strong>
                        <span class="badge badge-primary"><?php echo count($products); ?></span>
                    </p>
                    <p class="mb-2">
                        <strong>Danh Mục:</strong>
                        <span class="badge badge-success"><?php echo count($categories); ?></span>
                    </p>
                </div>
            </div>
        </div>

        <div class="card mt-4 bg-light">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="fas fa-bolt"></i> Tùy Chọn Nhanh
                </h5>
                <hr>
                <a href="/webbanhang/Product/add" class="btn btn-success btn-block mb-2">
                    <i class="fas fa-plus"></i> Thêm Sản Phẩm
                </a>
                <a href="/webbanhang/Product" class="btn btn-info btn-block">
                    <i class="fas fa-list"></i> Danh Sách Sản Phẩm
                </a>
            </div>
        </div>
    </div>
</div>

<?php include VIEWS_PATH . DIRECTORY_SEPARATOR . 'shared' . DIRECTORY_SEPARATOR . 'footer.php'; ?>
