<?php require __DIR__ . '/../shares/header.php'; ?>
<?php $cart = array_values($_SESSION['cart'] ?? []); ?>

<section class="container pt-32">
  <div class="cart-head">
    <h1>Giỏ Hàng Của Bạn</h1>
    <p class="muted">Quản lý các sản phẩm bạn đã thêm vào giỏ hàng.</p>
  </div>

  <?php if (count($cart) === 0): ?>
    <div class="card empty">
      <span class="material-symbols-outlined big">shopping_cart_off</span>
      <h3>Giỏ hàng của bạn còn trống</h3>
      <p class="muted">Hãy tìm thêm các sản phẩm tuyệt vời nhé!</p>
      <a class="btn btn-primary" href="<?= e(url()) ?>">Mua sắm ngay</a>
    </div>
  <?php else: ?>
    <div class="card cart-table">
      <div class="cart-row cart-headrow">
        <div>Sản phẩm</div>
        <div class="center">Đơn giá</div>
        <div class="center">Số lượng</div>
        <div class="center">Số tiền</div>
        <div class="right">Thao tác</div>
      </div>

      <?php $sum = 0; foreach ($cart as $it): $line = $it['price'] * $it['qty']; $sum += $line; ?>
        <div class="cart-row">
          <div class="cart-product">
            <img src="<?= htmlspecialchars($it['image']) ?>" alt="">
            <div>
              <div class="bold"><?= htmlspecialchars($it['name']) ?></div>
              <div class="muted small"><?= htmlspecialchars($it['city'] ?? '') ?></div>
            </div>
          </div>

          <div class="center"><?= number_format((int)$it['price'], 0, ',', '.') ?>₫</div>

          <div class="center">
            <form class="qtyform" method="POST" action="<?= e(url('cart', 'updateQty')) ?>">
              <input type="hidden" name="id" value="<?= (int)$it['id'] ?>">
              <div class="qty">
                <button class="qtybtn" type="button" data-qdelta="-1">-</button>
                <input name="qty" value="<?= (int)$it['qty'] ?>">
                <button class="qtybtn" type="button" data-qdelta="1">+</button>
              </div>
              <button class="btn small" type="submit">Cập nhật</button>
            </form>
          </div>

          <div class="center price"><?= number_format((int)$line, 0, ',', '.') ?>₫</div>

          <div class="right">
            <form method="POST" action="<?= e(url('cart', 'remove')) ?>">
              <input type="hidden" name="id" value="<?= (int)$it['id'] ?>">
              <button class="link danger" type="submit">
                <span class="material-symbols-outlined">delete</span> Xóa
              </button>
            </form>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="cart-footer">
      <form method="POST" action="<?= e(url('cart', 'clear')) ?>">
        <button class="btn" type="submit">Xóa tất cả</button>
      </form>

      <div class="totalbox">
        <div class="muted">Tổng thanh toán:</div>
        <div class="total"><?= number_format((int)$sum, 0, ',', '.') ?>₫</div>
        <a class="btn btn-primary btn-lg" href="<?= e(url('checkout', 'index')) ?>">Mua Hàng</a>
      </div>
    </div>
  <?php endif; ?>
</section>

<script>
  // qty +/- inside cart
  document.querySelectorAll('[data-qdelta]').forEach(btn => {
    btn.addEventListener('click', () => {
      const wrap = btn.closest('.qty');
      const input = wrap.querySelector('input');
      const delta = parseInt(btn.dataset.qdelta, 10);
      const cur = parseInt(input.value || '1', 10);
      input.value = Math.max(1, cur + delta);
    });
  });
</script>

<?php require __DIR__ . '/../shares/footer.php'; ?>