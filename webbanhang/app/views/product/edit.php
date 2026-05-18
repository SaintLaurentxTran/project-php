<div class="card">
  <div class="head">
    <h3>Sửa sản phẩm #<?= (int)$product['id'] ?></h3>
    <a class="btn" href="index.php?controller=product&action=index">← Quay lại</a>
  </div>

  <div class="body">
    <form method="post" enctype="multipart/form-data" action="index.php?controller=product&action=update">
      <input type="hidden" name="id" value="<?= (int)$product['id'] ?>" />

      <div class="grid" style="grid-template-columns: 1fr 1fr;">
        <div>
          <label>Danh mục</label>
          <select name="category_id" required class="input">
            <?php foreach ($categories as $c): ?>
              <option value="<?= (int)$c['id'] ?>"
                <?= ((int)$c['id'] === (int)$product['category_id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($c['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label>Giá</label>
          <input class="input" name="price" type="number" step="0.01"
                 value="<?= htmlspecialchars($product['price']) ?>" />
        </div>
      </div>

      <div style="margin-top:14px">
        <label>Tên sản phẩm</label>
        <input class="input" name="name" required value="<?= htmlspecialchars($product['name']) ?>" />
      </div>

      <div style="margin-top:14px">
        <label>Ảnh hiện tại</label>
        <?php if (!empty($product['image'])): ?>
          <div style="margin:8px 0">
            <img src="public/uploads/<?= htmlspecialchars($product['image']) ?>" style="max-width:220px; border-radius:14px; border:1px solid #e5e7eb" />
          </div>
          <div class="small">Upload ảnh mới để thay thế (ảnh cũ sẽ bị xóa).</div>
        <?php else: ?>
          <div class="small">Chưa có ảnh.</div>
        <?php endif; ?>
      </div>

      <div style="margin-top:14px">
        <label>Chọn ảnh mới (JPG/PNG/WEBP)</label>
        <input class="input" type="file" name="image" accept="image/png,image/jpeg,image/webp" />
      </div>

      <div style="margin-top:14px">
        <label>Mô tả</label>
        <textarea class="input" name="description" rows="4"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
      </div>

      <div style="margin-top:14px; display:flex; gap:10px; flex-wrap:wrap">
        <button class="btn primary" type="submit">Cập nhật</button>
        <a class="btn" href="index.php?controller=product&action=index">Hủy</a>
      </div>
    </form>
  </div>
</div>