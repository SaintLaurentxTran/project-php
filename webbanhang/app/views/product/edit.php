<?php require __DIR__ . '/../shares/header.php'; ?>

<section class="container pt-32">
  <div class="seller-head">
    <h1>Chỉnh Sửa Sản Phẩm</h1>
    <a class="btn" href="<?= e(url('seller', 'products')) ?>">Quay lại</a>
  </div>

  <form class="grid2" method="POST" action="<?= e(url('seller', 'update', ['id' => (int)$product['id']])) ?>" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?= (int)$product['id'] ?>">
    <input type="hidden" name="old_thumb_url" value="<?= htmlspecialchars($product['thumb_url']) ?>">

    <div class="card">
      <h3>Thông tin cơ bản</h3>

      <label class="label">Tên sản phẩm *</label>
      <input class="input" name="name" required value="<?= htmlspecialchars($product['name']) ?>"/>

      <div class="grid2 mt-8">
        <div>
          <label class="label">Danh mục *</label>
          <select class="input" name="category_id" required>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= (int)$cat['id'] ?>" <?= ((int)$cat['id']===(int)$product['category_id'])?'selected':'' ?>>
                <?= htmlspecialchars($cat['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="label">Thành phố</label>
          <input class="input" name="city" value="<?= htmlspecialchars($product['city']) ?>"/>
        </div>
      </div>

      <label class="label mt-8">Mô tả *</label>
      <textarea class="input" name="description" rows="6" required><?= htmlspecialchars($product['description']) ?></textarea>
    </div>

    <div class="card">
      <h3>Giá & Tồn kho</h3>
      <?php $basePrice = (int)($product['old_price'] ?: $product['price']); ?>
      <div class="grid2">
        <div>
          <label class="label">Giá gốc (₫) *</label>
          <input class="input" id="basePrice" name="base_price" type="number" min="0" required value="<?= $basePrice ?>"/>
        </div>
        <div>
          <label class="label">Giảm giá (%)</label>
          <input class="input" id="discountPercent" name="discount_percent" type="number" min="0" max="100" value="<?= (int)$product['discount_percent'] ?>"/>
        </div>
      </div>

      <p class="muted small mt-8">Giá sau giảm: <b id="discountedPricePreview"><?= number_format((int)$product['price'], 0, ',', '.') ?>₫</b></p>

      <div class="grid2 mt-8">
        <div>
          <label class="label">Tồn kho *</label>
          <input class="input" name="stock" type="number" required value="<?= (int)$product['stock'] ?>"/>
        </div>
      </div>
      <label class="label mt-8">
        <input type="checkbox" name="is_flash_sale" value="1" <?= ((int)$product['is_flash_sale']===1)?'checked':'' ?>>
        Flash Sale
      </label>

      <label class="label mt-8">Ảnh đại diện sản phẩm hiện tại</label>
      <?php if (!empty($product['thumb_url'])): ?>
        <div style="margin: 8px 0;">
          <img src="<?= htmlspecialchars($product['thumb_url']) ?>" alt="Current Thumb" style="max-width: 120px; border: 1px solid #ddd; border-radius: 4px; display: block;">
        </div>
      <?php endif; ?>

      <label class="label mt-8">Chọn ảnh mới (Có thể chọn nhiều ảnh cùng lúc để thay thế/bổ sung)</label>
      <input class="input" type="file" name="thumb_url[]" accept="image/*" multiple />

      <div class="row gap mt-16">
        <button class="btn btn-primary btn-lg" type="submit">Cập nhật</button>
        <a class="btn btn-lg" href="<?= e(url('seller', 'products')) ?>">Hủy</a>
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
    discountedPreview.textContent = discountedPrice.toLocaleString('vi-VN') + '₫';
  }

  basePriceInput.addEventListener('input', updateDiscountedPreview);
  discountInput.addEventListener('input', updateDiscountedPreview);
  updateDiscountedPreview();
</script>
<?php require __DIR__ . '/../shares/footer.php'; ?>
