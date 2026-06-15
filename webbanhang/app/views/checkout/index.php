<?php require __DIR__ . '/../shares/header.php'; ?>

<section class="container pt-32" data-api-checkout-page>
  <div class="checkout-layout">
    <div class="left">
      <div class="card gradient-top">
        <div class="section-title">
          <span class="material-symbols-outlined">location_on</span>
          <h3>Dia chi nhan hang</h3>
        </div>

        <div class="addr">
          <div>
            <div class="bold" data-api-checkout-name-preview>Nguoi nhan</div>
            <div class="muted" data-api-checkout-address-preview>Nhap thong tin giao hang ben duoi</div>
            <span class="pill pill-green">API ORDER</span>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="table-head">
          <div>San pham</div><div class="center">Don gia</div><div class="center">So luong</div><div class="right">Thanh tien</div>
        </div>
        <div data-api-checkout-items></div>
      </div>

      <div class="card soft">
        <div class="shiprow">
          <div>
            <div class="section-title">
              <span class="material-symbols-outlined ok">local_shipping</span>
              <h3>Don vi van chuyen</h3>
            </div>
            <div class="muted small">Phi van chuyen co dinh cho demo API</div>
          </div>
          <div class="bold primary" data-api-shipping-fee>32.000 VND</div>
        </div>
      </div>

      <form class="card" data-api-order-form>
        <h3>Phuong thuc thanh toan</h3>
        <div class="api-status" data-api-order-message hidden></div>

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
            <span>The tin dung/ghi no</span>
          </label>
          <label class="payopt">
            <input type="radio" name="payment_method" value="cod">
            <span class="material-symbols-outlined">payments</span>
            <span>COD</span>
          </label>
          <label class="payopt">
            <input type="radio" name="payment_method" value="bank">
            <span class="material-symbols-outlined">account_balance</span>
            <span>Chuyen khoan</span>
          </label>
        </div>

        <div class="card-inner mt-16">
          <h4>Thong tin nguoi nhan</h4>
          <div class="grid2">
            <input class="input" name="customer_name" placeholder="Ho ten" required>
            <input class="input" name="customer_phone" placeholder="So dien thoai" required>
          </div>
          <input class="input mt-8" name="customer_address" placeholder="Dia chi" required>
        </div>

        <div class="coins mt-16">
          <div>
            <div class="bold">Shopee Xu</div>
            <div class="muted small">Dung 20.000 xu</div>
          </div>
          <label class="switch">
            <input type="checkbox" name="use_coins" value="1" id="use_coins">
            <span class="slider"></span>
          </label>
        </div>
      </form>
    </div>

    <aside class="right">
      <div class="card sticky">
        <h3>Chi tiet thanh toan</h3>

        <div class="sumrow"><span class="muted">Tong tien hang</span><span data-api-items-total>0 VND</span></div>
        <div class="sumrow"><span class="muted">Phi van chuyen</span><span>32.000 VND</span></div>
        <div class="sumrow"><span class="muted">Voucher</span><span>-50.000 VND</span></div>
        <div class="sumrow"><span class="muted">Shopee Xu</span><span data-api-coins-line>-0 VND</span></div>

        <div class="sumtotal">
          <div class="bold">Tong thanh toan</div>
          <div class="total primary" data-api-final-total>0 VND</div>
        </div>

        <div class="note">
          <span class="material-symbols-outlined">info</span>
          Don hang se duoc gui bang API dat hang.
        </div>

        <button class="btn btn-primary btn-lg wfull" type="button" data-api-place-order>Dat hang</button>
      </div>
    </aside>
  </div>
</section>

<?php require __DIR__ . '/../shares/footer.php'; ?>
