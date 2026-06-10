<?php require __DIR__ . '/../shares/header.php'; ?>

<section class="container pt-32">
  <div class="card center-card glow">
    <div class="success-badge green">
      <span class="material-symbols-outlined filled">check_circle</span>
    </div>
    <h2>Xóa sản phẩm thành công!</h2>
    <p class="muted">Sản phẩm đã được gỡ khỏi cửa hàng của bạn.</p>
    <div class="btnrow">
      <a class="btn btn-primary" href="<?= e(url('seller', 'products')) ?>">
        <span class="material-symbols-outlined">arrow_back</span> Quay lại danh sách
      </a>
      <a class="btn" href="<?= e(url('seller', 'add')) ?>">
        <span class="material-symbols-outlined">add_box</span> Đăng sản phẩm mới
      </a>
    </div>
  </div>
</section>

<?php require __DIR__ . '/../shares/footer.php'; ?>