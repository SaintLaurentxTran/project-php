<?php include VIEWS_PATH . DIRECTORY_SEPARATOR . 'shared' . DIRECTORY_SEPARATOR . 'header.php'; ?>

<div class="page-header">
    <h1><i class="fas fa-list"></i> Danh Sách Sản Phẩm</h1>
</div>

<div class="row mb-4">
    <div class="col-md-6">
        <a href="/webbanhang/Product/add" class="btn btn-success btn-lg">
            <i class="fas fa-plus-circle"></i> Thêm Sản Phẩm Mới
        </a>
    </div>
    <div class="col-md-6 text-right">
        <span class="badge badge-primary badge-lg p-3">
            <i class="fas fa-box"></i> Tổng: <?php echo count($products); ?> sản phẩm
        </span>
    </div>
</div>

<?php if (!empty($products)): ?>
    <div class="table-responsive">
        <table class="table table-hover table-bordered">
            <thead class="thead-dark">
                <tr>
                    <th><i class="fas fa-hashtag"></i> ID</th>
                    <th><i class="fas fa-cube"></i> Tên Sản Phẩm</th>
                    <th><i class="fas fa-align-left"></i> Mô Tả</th>
                    <th><i class="fas fa-tag"></i> Danh Mục</th>
                    <th><i class="fas fa-money-bill"></i> Giá</th>
                    <th style="width: 200px;"><i class="fas fa-cogs"></i> Hành Động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($product->id, ENT_QUOTES, 'UTF-8'); ?></strong></td>
                        <td>
                            <a href="/webbanhang/Product/show/<?php echo $product->id; ?>" class="text-primary">
                                <?php echo htmlspecialchars($product->name, ENT_QUOTES, 'UTF-8'); ?>
                            </a>
                        </td>
                        <td>
                            <small><?php echo htmlspecialchars(substr($product->description, 0, 50), ENT_QUOTES, 'UTF-8'); ?>...</small>
                        </td>
                        <td>
                            <span class="badge badge-info">
                                <?php echo htmlspecialchars($product->category_name ?? 'Chưa phân loại', ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                        </td>
                        <td class="text-success">
                            <strong>₫<?php echo number_format($product->price, 0, ',', '.'); ?></strong>
                        </td>
                        <td>
                            <a href="/webbanhang/Product/show/<?php echo $product->id; ?>" class="btn btn-sm btn-info" title="Xem Chi Tiết">
                                <i class="fas fa-eye"></i> Xem
                            </a>
                            <a href="/webbanhang/Product/edit/<?php echo $product->id; ?>" class="btn btn-sm btn-warning" title="Chỉnh Sửa">
                                <i class="fas fa-edit"></i> Sửa
                            </a>
                            <a href="/webbanhang/Product/delete/<?php echo $product->id; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này?');" title="Xóa">
                                <i class="fas fa-trash"></i> Xóa
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="alert alert-info alert-lg" role="alert">
        <h4 class="alert-heading"><i class="fas fa-info-circle"></i> Thông Báo</h4>
        <p>Chưa có sản phẩm nào trong hệ thống.</p>
        <hr>
        <a href="/webbanhang/Product/add" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tạo Sản Phẩm Mới
        </a>
    </div>
<?php endif; ?>

<?php include VIEWS_PATH . DIRECTORY_SEPARATOR . 'shared' . DIRECTORY_SEPARATOR . 'footer.php'; ?>
