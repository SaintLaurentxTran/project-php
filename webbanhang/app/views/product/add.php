<?php require __DIR__ . '/../shares/header.php'; ?>

<section class="container pt-32">
  <div class="seller-head">
    <h1>Thêm Sản Phẩm Mới</h1>
    <a class="btn" href="index.php?c=seller&a=products">Quay lại</a>
  </div>

  <form class="grid2" method="POST" action="index.php?c=seller&a=store" enctype="multipart/form-data">
    <div class="card">
      <h3>Thông tin cơ bản</h3>

      <label class="label">Tên sản phẩm *</label>
      <input class="input" name="name" required placeholder="Ví dụ: Tai nghe Bluetooth..." />

      <div class="grid2 mt-8">
        <div>
          <label class="label">Danh mục *</label>
          <select class="input" name="category_id" required>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= (int)$cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="label">Thành phố</label>
          <input class="input" name="city" value="TP. Hồ Chí Minh"/>
        </div>
      </div>

      <label class="label mt-8">Mô tả *</label>
      <textarea class="input" name="description" rows="6" required>Demo mô tả sản phẩm...</textarea>
    </div>

    <div class="card">
      <h3>Giá & Tồn kho</h3>
      <div class="grid2">
        <div>
          <label class="label">Giá (₫) *</label>
          <input class="input" name="price" type="number" required value="199000"/>
        </div>
        <div>
          <label class="label">Giá cũ (₫)</label>
          <input class="input" name="old_price" type="number" value=""/>
        </div>
      </div>

      <div class="grid2 mt-8">
        <div>
          <label class="label">Giảm giá (%)</label>
          <input class="input" name="discount_percent" type="number" value="0"/>
        </div>
        <div>
          <label class="label">Tồn kho *</label>
          <input class="input" name="stock" type="number" required value="50"/>
        </div>
      </div>

      <label class="label mt-8">
        <input type="checkbox" name="is_flash_sale" value="1"> Flash Sale
      </label>

      <label class="label mt-8">Chọn các ảnh cho sản phẩm * (Có thể chọn nhiều ảnh cùng lúc)</label>
      <input class="input" type="file" name="thumb_url[]" accept="image/*" multiple required />
      
      <label class="label mt-8">Album ảnh phụ (Tùy chọn bổ sung)</label>
      <input class="input" type="file" name="gallery[]" accept="image/*" multiple />

      <div class="row gap mt-16">
        <button class="btn btn-primary btn-lg" type="submit">Đăng sản phẩm</button>
        <a class="btn btn-lg" href="index.php?c=seller&a=products">Hủy</a>
      </div>
    </div>
  </form>
</section>

<?php require __DIR__ . '/../shares/footer.php'; ?>