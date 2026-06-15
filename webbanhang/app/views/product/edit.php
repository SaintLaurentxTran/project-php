<?php require __DIR__ . '/../shares/header.php'; ?>

<section class="container pt-32">
  <div class="seller-head">
    <h1>Chinh sua san pham</h1>
    <a class="btn" href="<?= e(url('seller', 'products')) ?>">Quay lai</a>
  </div>

  <form class="grid2" data-api-product-form data-api-method="PUT" data-api-product-id="<?= (int)$product['id'] ?>" data-api-success-url="<?= e(url('seller', 'products')) ?>">
    <div class="card">
      <h3>Thong tin co ban</h3>
      <div class="api-status" data-api-form-message>Dang tai du lieu cu tu API...</div>

      <label class="label">Ten san pham *</label>
      <input class="input" name="name" required value="<?= htmlspecialchars($product['name']) ?>"/>

      <div class="grid2 mt-8">
        <div>
          <label class="label">Danh muc *</label>
          <select class="input" name="category_id" required>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= (int)$cat['id'] ?>" <?= ((int)$cat['id']===(int)$product['category_id'])?'selected':'' ?>>
                <?= htmlspecialchars($cat['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="label">Thanh pho</label>
          <input class="input" name="city" value="<?= htmlspecialchars($product['city']) ?>"/>
        </div>
      </div>

      <label class="label mt-8">Mo ta *</label>
      <textarea class="input" name="description" rows="6" required><?= htmlspecialchars($product['description']) ?></textarea>
    </div>

    <div class="card">
      <h3>Gia, ton kho va anh</h3>
      <?php $basePrice = (int)($product['old_price'] ?: $product['price']); ?>
      <div class="grid2">
        <div>
          <label class="label">Gia goc (VND) *</label>
          <input class="input" id="basePrice" name="base_price" type="number" min="1" required value="<?= $basePrice ?>"/>
        </div>
        <div>
          <label class="label">Giam gia (%)</label>
          <input class="input" id="discountPercent" name="discount_percent" type="number" min="0" max="100" value="<?= (int)$product['discount_percent'] ?>"/>
        </div>
      </div>

      <p class="muted small mt-8">Gia sau giam: <b id="discountedPricePreview"><?= number_format((int)$product['price'], 0, ',', '.') ?> VND</b></p>

      <label class="label mt-8">Ton kho *</label>
      <input class="input" name="stock" type="number" min="0" required value="<?= (int)$product['stock'] ?>"/>

      <label class="label mt-8">Duong dan anh *</label>
      <input class="input" name="thumb_url" required value="<?= htmlspecialchars($product['thumb_url']) ?>" />
      <div class="mt-8" data-api-thumb-preview>
        <?php if (!empty($product['thumb_url'])): ?>
          <img src="<?= htmlspecialchars($product['thumb_url']) ?>" alt="Current Thumb" style="max-width: 120px; border: 1px solid #ddd; border-radius: 4px;">
        <?php endif; ?>
      </div>

      <label class="label mt-8">
        <input type="checkbox" name="is_flash_sale" value="1" <?= ((int)$product['is_flash_sale']===1)?'checked':'' ?>>
        Flash Sale
      </label>

      <div class="row gap mt-16">
        <button class="btn btn-primary btn-lg" type="submit">Cap nhat qua API</button>
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
