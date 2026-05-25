<?php require __DIR__ . '/../shares/header.php'; ?>

<section class="container pt-32">
  <div class="seller-head">
    <h1>Chỉnh Sửa Sản Phẩm</h1>
    <a class="btn" href="index.php?c=seller&a=products">Quay lại</a>
  </div>

  <form class="grid2" method="POST" action="index.php?c=seller&a=update&id=<?= (int)$product['id'] ?>" enctype="multipart/form-data">
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
      <div class="grid2">
        <div>
          <label class="label">Giá (₫) *</label>
          <input class="input" name="price" type="number" required value="<?= (int)$product['price'] ?>"/>
        </div>
        <div>
          <label class="label">Giá cũ (₫)</label>
          <input class="input" name="old_price" type="number" value="<?= htmlspecialchars($product['old_price'] ?? '') ?>"/>
        </div>
      </div>

      <div class="grid2 mt-8">
        <div>
          <label class="label">Giảm giá (%)</label>
          <input class="input" name="discount_percent" type="number" value="<?= (int)$product['discount_percent'] ?>"/>
        </div>
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
        <a class="btn btn-lg" href="index.php?c=seller&a=products">Hủy</a>
      </div>
    </div>
  </form>
</section>

<?php require __DIR__ . '/../shares/footer.php'; ?>