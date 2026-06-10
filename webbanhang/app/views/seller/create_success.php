<?php require __DIR__ . '/../shares/header.php'; ?>

<section class="container pt-32">
  <div class="card center-card">
    <div class="success-badge green">
      <span class="material-symbols-outlined filled">check_circle</span>
    </div>
    <h2>Sản phẩm đã được đăng thành công!</h2>
    <p class="muted">Sản phẩm đang ở trạng thái hoạt động.</p>

    <div class="btnrow">
      <a class="btn" href="<?= e(url('default', 'search')) ?>">Xem sản phẩm trên shop</a>
      <a class="btn" href="<?= e(url('seller', 'products')) ?>">Quay lại danh sách</a>
      <a class="btn btn-primary" href="<?= e(url('seller', 'add')) ?>">Tiếp tục thêm sản phẩm</a>
    </div>
  </div>
</section>

<?php require __DIR__ . '/../shares/footer.php'; ?>