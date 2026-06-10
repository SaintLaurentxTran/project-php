<?php require __DIR__ . '/../shares/header.php'; ?>
<?php
  $cart = array_values($_SESSION['cart'] ?? []);
  $itemsTotal = 0;
  foreach ($cart as $it) $itemsTotal += $it['price'] * $it['qty'];
  $shippingFee = 32000;
  $voucher = 50000;
  $coins = 20000;
  $finalNoCoins = $itemsTotal + $shippingFee - $voucher;
?>
<section class="container pt-32">
  <div class="checkout-layout">
    <div class="left">
      <div class="card gradient-top">
        <div class="section-title">
          <span class="material-symbols-outlined">location_on</span>
          <h3>Địa chỉ nhận hàng</h3>
        </div>

        <div class="addr">
          <div>
            <div class="bold">Nguyễn Văn A (+84) 987 654 321</div>
            <div class="muted">Số 123 Đường Lê Lợi, Phường Bến Thành, Quận 1, TP. Hồ Chí Minh</div>
            <span class="pill pill-green">MẶC ĐỊNH</span>
          </div>
          <button class="link-btn" type="button">Thay đổi</button>
        </div>
      </div>

      <div class="card">
        <div class="table-head">
          <div>Sản phẩm</div><div class="center">Đơn giá</div><div class="center">Số lượng</div><div class="right">Thành tiền</div>
        </div>
        <?php foreach ($cart as $it): $line = $it['price'] * $it['qty']; ?>
          <div class="table-row">
            <div class="pcol">
              <img src="<?= htmlspecialchars($it['image']) ?>" alt="">
              <div>
                <div class="bold"><?= htmlspecialchars($it['name']) ?></div>
                <div class="muted small">Loại: mặc định</div>
              </div>
            </div>
            <div class="center"><?= number_format((int)$it['price'], 0, ',', '.') ?>₫</div>
            <div class="center"><?= (int)$it['qty'] ?></div>
            <div class="right bold"><?= number_format((int)$line, 0, ',', '.') ?>₫</div>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="card soft">
        <div class="shiprow">
          <div>
            <div class="section-title">
              <span class="material-symbols-outlined ok">local_shipping</span>
              <h3>Đơn vị vận chuyển</h3>
            </div>
            <div class="muted small">Nhận hàng dự kiến: 24 - 26</div>
          </div>
          <div class="bold primary"><?= number_format($shippingFee, 0, ',', '.') ?>₫</div>
        </div>
      </div>

      <form class="card" method="POST" action="<?= e(url('checkout', 'placeOrder')) ?>">
        <h3>Phương thức thanh toán</h3>

        <div class="paygrid">
          <label class="payopt active">
            <input type="radio" name="payment_method" value="shopeepay" checked>
            <span class="material-symbols-outlined">account_balance_wallet</span>
            <span>ShopeePay</span>
            <span class="material-symbols-outlined check filled">check_circle</span>
          </label>

          <label class="payopt">
            <input type="radio" name="payment_method" value="card">
            <span class="material-symbols-outlined">credit_card</span>
            <span>Thẻ tín dụng/ghi nợ</span>
          </label>

          <label class="payopt">
            <input type="radio" name="payment_method" value="cod">
            <span class="material-symbols-outlined">payments</span>
            <span>COD</span>
          </label>

          <label class="payopt">
            <input type="radio" name="payment_method" value="bank">
            <span class="material-symbols-outlined">account_balance</span>
            <span>Chuyển khoản</span>
          </label>
        </div>

        <div class="card-inner mt-16">
          <h4>Thông tin người nhận</h4>
          <div class="grid2">
            <input class="input" name="name" placeholder="Họ tên" value="Nguyễn Văn A">
            <input class="input" name="phone" placeholder="Số điện thoại" value="090 123 4567">
          </div>
          <input class="input mt-8" name="address" placeholder="Địa chỉ" value="123 Đường Lê Lợi, Quận 1, TP. Hồ Chí Minh">
        </div>

        <div class="coins mt-16">
          <div>
            <div class="bold">Shopee Xu</div>
            <div class="muted small">Dùng 20.000 xu</div>
          </div>
          <label class="switch">
            <input type="checkbox" name="use_coins" value="1" id="use_coins">
            <span class="slider"></span>
          </label>
        </div>

        <button class="hidden" type="submit" id="hiddenSubmit">submit</button>
      </form>
    </div>

    <aside class="right">
      <div class="card sticky">
        <h3>Chi tiết thanh toán</h3>

        <div class="sumrow"><span class="muted">Tổng tiền hàng</span><span><?= number_format($itemsTotal, 0, ',', '.') ?>₫</span></div>
        <div class="sumrow"><span class="muted">Phí vận chuyển</span><span><?= number_format($shippingFee, 0, ',', '.') ?>₫</span></div>
        <div class="sumrow"><span class="muted">Voucher</span><span>-<?= number_format($voucher, 0, ',', '.') ?>₫</span></div>
        <div class="sumrow"><span class="muted">Shopee Xu</span><span id="coins_line">-0₫</span></div>

        <div class="sumtotal">
          <div class="bold">Tổng thanh toán</div>
          <div class="total primary" id="final_total"><?= number_format($finalNoCoins, 0, ',', '.') ?>₫</div>
        </div>

        <div class="note">
          <span class="material-symbols-outlined">info</span>
          Nhấn “Đặt Hàng” đồng nghĩa với việc bạn đồng ý Điều khoản ShopeeFake.
        </div>

        <button class="btn btn-primary btn-lg wfull" type="button" id="placeOrderBtn">Đặt Hàng</button>
      </div>
    </aside>
  </div>
</section>

<script>
  // payment UI active state
  document.querySelectorAll('.payopt input').forEach(r => {
    r.addEventListener('change', () => {
      document.querySelectorAll('.payopt').forEach(l => l.classList.remove('active'));
      r.closest('.payopt').classList.add('active');
    });
  });

  // coins toggle recalculation
  const useCoins = document.getElementById('use_coins');
  const coinsLine = document.getElementById('coins_line');
  const finalTotal = document.getElementById('final_total');
  const finalNoCoins = <?= (int)$finalNoCoins ?>;
  const coins = <?= (int)$coins ?>;

  function formatVND(n){ return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".") + "₫"; }

  useCoins.addEventListener('change', () => {
    if (useCoins.checked) {
      coinsLine.textContent = "-" + formatVND(coins);
      finalTotal.textContent = formatVND(finalNoCoins - coins);
    } else {
      coinsLine.textContent = "-0₫";
      finalTotal.textContent = formatVND(finalNoCoins);
    }
  });

  // Place order
  document.getElementById('placeOrderBtn').addEventListener('click', () => {
    document.getElementById('hiddenSubmit').click();
  });
</script>

<?php require __DIR__ . '/../shares/footer.php'; ?>