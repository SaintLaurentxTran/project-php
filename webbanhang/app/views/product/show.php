<?php include 'app/views/shared/header.php'; ?>
<h1><?php echo htmlspecialchars($product->name, ENT_QUOTES, 'UTF-8'); ?></h1>
<div class="card">
  <div class="card-body">
    <h5 class="card-title">Chi tiết sản phẩm</h5>
    <p class="card-text">
      <strong>Mô tả:</strong><br>
      <?php echo htmlspecialchars($product->description, ENT_QUOTES, 'UTF-8'); ?>
    </p>
    <p class="card-text">
      <strong>Giá:</strong> <?php echo htmlspecialchars($product->price, ENT_QUOTES, 'UTF-8'); ?> VNĐ
    </p>
    <p class="card-text">
      <strong>Danh mục:</strong> <?php echo htmlspecialchars($product->category_name, ENT_QUOTES, 'UTF-8'); ?>
    </p>
    <div class="mt-3">
      <a href="/webbanhang/Product/edit/<?php echo $product->id; ?>" class="btn btn-warning">Sửa</a>
      <a href="/webbanhang/Product/delete/<?php echo $product->id; ?>" class="btn btn-danger" onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này?');">Xóa</a>
      <a href="/webbanhang/Product/" class="btn btn-secondary">Quay lại danh sách</a>
    </div>
  </div>
</div>
<?php include 'app/views/shared/footer.php'; ?>