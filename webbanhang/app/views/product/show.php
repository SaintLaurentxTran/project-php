<div class="card">
  <div class="head">
    <h3>Chi tiết sản phẩm</h3>
    <a class="btn" href="index.php?controller=product&action=index">← Quay lại</a>
  </div>

  <div class="body">
    <?php if (!empty($product['image'])): ?>
      <img src="public/uploads/<?= htmlspecialchars($product['image']) ?>"
           style="width:100%; max-width:520px; border-radius:16px; border:1px solid #e5e7eb; display:block; margin-bottom:14px" />
    <?php endif; ?>

    <p><b>ID:</b> <?= (int)$product['id'] ?></p>
    <p><b>Tên:</b> <?= htmlspecialchars($product['name']) ?></p>
    <p><b>Danh mục:</b> <?= htmlspecialchars($product['category_name']) ?></p>
    <p><b>Giá:</b> <?= number_format((float)$product['price'], 0, ',', '.') ?> đ</p>
    <p><b>Mô tả:</b><br/><?= nl2br(htmlspecialchars($product['description'] ?? '')) ?></p>

    <div style="display:flex; gap:10px; flex-wrap:wrap; margin-top:12px">
      <a class="btn" href="index.php?controller=product&action=edit&id=<?= (int)$product['id'] ?>">Sửa</a>
      <a class="btn danger" href="index.php?controller=product&action=delete&id=<?= (int)$product['id'] ?>"
         onclick="return confirm('Xóa sản phẩm này?');">Xóa</a>
    </div>
  </div>
</div>