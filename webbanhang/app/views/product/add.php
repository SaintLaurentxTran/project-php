<div class="card">
  <div class="head">
    <h3>Thêm sản phẩm</h3>
    <a class="btn" href="index.php?controller=product&action=index">← Quay lại</a>
  </div>

  <div class="body">
    <form method="post" enctype="multipart/form-data" action="index.php?controller=product&action=store">
      <div class="grid" style="grid-template-columns: 1fr 1fr;">
        <div>
          <label>Danh mục</label>
          <select name="category_id" required class="input">
            <option value="">-- Chọn --</option>
            <?php foreach ($categories as $c): ?>
              <option value="<?= (int)$c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label>Giá</label>
          <input class="input" name="price" type="number" step="0.01" value="0" />
        </div>
      </div>

      <div style="margin-top:14px">
        <label>Tên sản phẩm</label>
        <input class="input" name="name" required />
      </div>

      <div style="margin-top:14px">
        <label>Hình ảnh (JPG/PNG/WEBP)</label>
        <input class="input" type="file" name="image" accept="image/png,image/jpeg,image/webp" />
        <div class="small" style="margin-top:6px">Có thể bỏ trống nếu chưa có ảnh.</div>
      </div>

      <div style="margin-top:14px">
        <label>Mô tả</label>
        <textarea class="input" name="description" rows="4"></textarea>
      </div>

      <div style="margin-top:14px; display:flex; gap:10px; flex-wrap:wrap">
        <button class="btn primary" type="submit">Lưu</button>
        <a class="btn" href="index.php?controller=product&action=index">Hủy</a>
      </div>
    </form>
  </div>
</div>