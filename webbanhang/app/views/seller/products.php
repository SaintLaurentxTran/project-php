<?php require __DIR__ . '/../shares/header.php'; ?>

<section class="container pt-32">
  <div class="seller-head">
    <h1>Seller Center • Product Management</h1>
    <div class="row gap">
      <a class="btn" href="<?= e(url('seller', 'categories')) ?>">
        <span class="material-symbols-outlined">category</span> Danh muc
      </a>
      <a class="btn btn-primary" href="<?= e(url('seller', 'add')) ?>">
        <span class="material-symbols-outlined">add_box</span> Thêm sản phẩm
      </a>
    </div>
  </div>

  <div class="card">
    <div class="table-head seller-table">
      <div>Sản phẩm</div><div>Kho</div><div>Giá</div><div>Trạng thái</div><div class="right">Thao tác</div>
    </div>

    <?php foreach ($result['items'] as $p): ?>
      <div class="table-row seller-table">
        <div class="pcol">
          <img src="<?= htmlspecialchars($p['thumb_url']) ?>" alt="">
          <div>
            <div class="bold"><?= htmlspecialchars($p['name']) ?></div>
            <div class="muted small"><?= htmlspecialchars($p['category_name']) ?></div>
          </div>
        </div>
        <div><?= (int)$p['stock'] ?></div>
        <div class="primary bold"><?= number_format((int)$p['price'], 0, ',', '.') ?>₫</div>
        <div><span class="pill pill-green">Active</span></div>
        <div class="right">
          <a class="link" href="<?= e(url('seller', 'edit', ['id' => (int)$p['id']])) ?>">Sửa</a>
          <span class="muted">•</span>
          <a class="link danger" href="<?= e(url('seller', 'deleteConfirm', ['id' => (int)$p['id']])) ?>">Xóa</a>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="pagination">
    <?php for ($i=1; $i <= $result['totalPages']; $i++): ?>
      <a class="pagebtn <?= ($i===$result['page'])?'active':'' ?>" href="<?= e(url('seller', 'products', ['page' => $i])) ?>"><?= $i ?></a>
    <?php endfor; ?>
  </div>
</section>

<?php require __DIR__ . '/../shares/footer.php'; ?>
