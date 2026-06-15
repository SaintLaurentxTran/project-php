<?php require __DIR__ . '/../shares/header.php'; ?>

<section class="container pt-32">
  <div class="seller-head">
    <h1>Them san pham moi</h1>
    <a class="btn" href="<?= e(url('seller', 'products')) ?>">Quay lai</a>
  </div>

  <form class="grid2" data-api-product-form data-api-method="POST" data-api-success-url="<?= e(url('seller', 'products')) ?>">
    <div class="card">
      <h3>Thong tin co ban</h3>
      <div class="api-status" data-api-form-message hidden></div>

      <label class="label">Ten san pham *</label>
      <input class="input" name="name" required placeholder="Vi du: Tai nghe Bluetooth" />

      <div class="grid2 mt-8">
        <div>
          <label class="label">Danh muc *</label>
          <select class="input" name="category_id" required>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= (int)$cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="label">Thanh pho</label>
          <input class="input" name="city" value="TP. Ho Chi Minh"/>
        </div>
      </div>

      <label class="label mt-8">Mo ta *</label>
      <textarea class="input" name="description" rows="6" required>Demo mo ta san pham...</textarea>
    </div>

    <div class="card">
      <h3>Gia, ton kho va anh</h3>
      <div class="grid2">
        <div>
          <label class="label">Gia goc (VND) *</label>
          <input class="input" id="basePrice" name="base_price" type="number" min="1" required value="199000"/>
        </div>
        <div>
          <label class="label">Giam gia (%)</label>
          <input class="input" id="discountPercent" name="discount_percent" type="number" min="0" max="100" value="0"/>
        </div>
      </div>

      <p class="muted small mt-8">Gia sau giam: <b id="discountedPricePreview">199.000 VND</b></p>

      <label class="label mt-8">Ton kho *</label>
      <input class="input" name="stock" type="number" min="0" required value="50"/>

      <label class="label mt-8">Duong dan anh *</label>
      <input class="input" name="thumb_url" required placeholder="uploads/ten-anh.jpg hoac https://..." />
      <p class="muted small mt-8">API san pham nhan duong dan anh, khong nhan file upload trong JSON.</p>

      <label class="label mt-8">
        <input type="checkbox" name="is_flash_sale" value="1"> Flash Sale
      </label>

      <div class="row gap mt-16">
        <button class="btn btn-primary btn-lg" type="submit">Dang san pham qua API</button>
        <a class="btn btn-lg" href="<?= e(url('seller', 'products')) ?>">Huy</a>
      </div>
    </div>
  </form>
</section>

<script>
  const basePriceInput = document.getElementById('basePrice');
  const discountInput = document.getElementById('discountPercent');
  const discountedPreview = document.getElementById('discountedPricePreview');

  function updateDiscountedPreview() {
    const basePrice = Math.max(0, Number(basePriceInput.value || 0));
    const discount = Math.min(100, Math.max(0, Number(discountInput.value || 0)));
    const discountedPrice = Math.round(basePrice * (100 - discount) / 100);
    discountedPreview.textContent = discountedPrice.toLocaleString('vi-VN') + ' VND';
  }

  basePriceInput.addEventListener('input', updateDiscountedPreview);
  discountInput.addEventListener('input', updateDiscountedPreview);
  updateDiscountedPreview();
</script>
<?php require __DIR__ . '/../shares/footer.php'; ?>
