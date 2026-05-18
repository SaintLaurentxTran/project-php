<div class="card">
  <div class="head">
    <h3>Sản phẩm mới nhất</h3>
    <span class="small">Hiển thị tối đa 10 sản phẩm</span>
  </div>

  <div class="body">
    <div class="grid">
      <?php foreach ($products as $p): ?>
        <?php
          // Nếu có ảnh trong DB => dùng ảnh upload
          // Nếu không có => dùng placeholder
          $img = !empty($p['image'])
            ? "public/uploads/" . $p['image']
            : "https://via.placeholder.com/640x480?text=No+Image";
        ?>
        <div class="pcard">
          <img class="pimg" src="<?= htmlspecialchars($img) ?>" alt="image" />

          <div class="pbody">
            <p class="ptitle"><?= htmlspecialchars($p['name']) ?></p>

            <div class="pmeta">
              <span>Danh mục: <?= htmlspecialchars($p['category_name']) ?></span>
              <span>•</span>
              <span>ID: <?= (int)$p['id'] ?></span>
            </div>

            <p class="pprice"><?= number_format((float)$p['price'], 0, ',', '.') ?> đ</p>

            <div class="pactions">
              <a class="btn" href="index.php?controller=product&action=show&id=<?= (int)$p['id'] ?>">Xem</a>
              <a class="btn" href="index.php?controller=product&action=edit&id=<?= (int)$p['id'] ?>">Sửa</a>
              <a class="btn danger"
                 href="index.php?controller=product&action=delete&id=<?= (int)$p['id'] ?>"
                 onclick="return confirm('Xóa sản phẩm này?');">Xóa</a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>

      <?php if (empty($products)): ?>
        <div class="small">Chưa có sản phẩm nào.</div>
      <?php endif; ?>
    </div>
  </div>
</div>