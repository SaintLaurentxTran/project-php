<?php require __DIR__ . '/../shares/header.php'; ?>

<section class="container pt-32">
  <div class="card center-card">
    <div class="success-badge">
      <span class="material-symbols-outlined filled">check_circle</span>
    </div>
    <h1>Thanh toán thành công!</h1>
    <p class="muted">Cảm ơn bạn đã mua sắm tại ShopeeFake. Đơn hàng của bạn đang được xử lý.</p>

    <div class="btnrow">
      <a class="btn btn-primary btn-lg" href="<?= e(url()) ?>">Tiếp tục mua sắm</a>
      <a class="btn btn-lg" href="<?= e(url('seller', 'products')) ?>">Seller Center</a>
    </div>
  </div>

  <div class="grid2 mt-24">
    <div class="card">
      <h3><span class="material-symbols-outlined">receipt_long</span> Thông tin đơn hàng</h3>
      <div class="sumrow"><span class="muted">Mã đơn hàng</span><span class="bold"><?= htmlspecialchars($order['order_code']) ?></span></div>
      <div class="sumrow"><span class="muted">Tổng cộng</span><span class="bold primary"><?= number_format((int)$order['total_amount'], 0, ',', '.') ?>₫</span></div>
      <div class="sumrow"><span class="muted">Thanh toán</span><span class="bold"><?= htmlspecialchars($order['payment_method']) ?></span></div>
    </div>

    <div class="card">
      <h3><span class="material-symbols-outlined">local_shipping</span> Vận chuyển</h3>
      <div class="muted">
        <div class="bold"><?= htmlspecialchars($order['customer_name']) ?></div>
        <div><?= htmlspecialchars($order['customer_phone']) ?></div>
        <div><?= htmlspecialchars($order['customer_address']) ?></div>
      </div>
    </div>
  </div>

  <div class="card mt-24">
    <h3>Sản phẩm</h3>
    <?php foreach ($items as $it): ?>
      <div class="lineitem">
        <div class="bold"><?= htmlspecialchars($it['product_name']) ?></div>
        <div class="muted">x<?= (int)$it['qty'] ?> • <?= number_format((int)$it['price'], 0, ',', '.') ?>₫</div>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<?php require __DIR__ . '/../shares/footer.php'; ?>