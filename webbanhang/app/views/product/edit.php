<?php include VIEWS_PATH . DIRECTORY_SEPARATOR . 'shared' . DIRECTORY_SEPARATOR . 'header.php'; ?>

<div class="page-header">
    <h1><i class="fas fa-edit"></i> Chỉnh Sửa Sản Phẩm</h1>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="/webbanhang/Product/update" class="form">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($product->id, ENT_QUOTES, 'UTF-8'); ?>">

                    <div class="form-group">
                        <label for="name" class="font-weight-bold">
                            <i class="fas fa-cube"></i> Tên Sản Phẩm
                        </label>
                        <input type="text" class="form-control" id="name" name="name" placeholder="Nhập tên sản phẩm" value="<?php echo htmlspecialchars($product->name, ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="description" class="font-weight-bold">
                            <i class="fas fa-align-left"></i> Mô Tả
                        </label>
                        <textarea class="form-control" id="description" name="description" rows="4" placeholder="Nhập mô tả sản phẩm" required><?php echo htmlspecialchars($product->description, ENT_QUOTES, 'UTF-8'); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="price" class="font-weight-bold">
                            <i class="fas fa-money-bill-wave"></i> Giá (VND)
                        </label>
                        <input type="number" class="form-control" id="price" name="price" placeholder="Nhập giá sản phẩm" step="1" min="0" value="<?php echo htmlspecialchars($product->price, ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="category_id" class="font-weight-bold">
                            <i class="fas fa-tag"></i> Danh Mục
                        </label>
                        <select class="form-control" id="category_id" name="category_id">
                            <option value="">-- Chọn Danh Mục --</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo htmlspecialchars($category->id, ENT_QUOTES, 'UTF-8'); ?>" <?php echo ($category->id == $product->category_id) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category->name, ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <hr>

                    <div class="form-group row justify-content-end">
                        <button type="submit" class="btn btn-warning btn-lg mr-2">
                            <i class="fas fa-save"></i> Cập Nhật
                        </button>
                        <a href="/webbanhang/Product/" class="btn btn-secondary btn-lg">
                            <i class="fas fa-times"></i> Hủy
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include VIEWS_PATH . DIRECTORY_SEPARATOR . 'shared' . DIRECTORY_SEPARATOR . 'footer.php'; ?>
