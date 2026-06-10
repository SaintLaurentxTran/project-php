<?php require __DIR__ . '/../shares/header.php'; ?>

<section class="container pt-32">
  <div class="seller-head">
    <h1>Seller Center &bull; Category Management</h1>
    <div class="row gap">
      <a class="btn" href="<?= e(url('seller', 'products')) ?>">
        <span class="material-symbols-outlined">inventory_2</span> San pham
      </a>
      <a class="btn btn-primary" href="<?= e(url('seller', 'categoryAdd')) ?>">
        <span class="material-symbols-outlined">add_box</span> Them danh muc
      </a>
    </div>
  </div>

  <form method="GET" action="<?= e(url('seller', 'categories')) ?>" class="card mb-16">
    <div class="row gap">
      <input class="input" name="q" value="<?= e($_GET['q'] ?? '') ?>" placeholder="Tim theo ten danh muc hoac icon">
      <button class="btn btn-primary" type="submit">
        <span class="material-symbols-outlined">search</span> Tim kiem
      </button>
      <?php if (!empty($_GET['q'])): ?>
        <a class="btn" href="<?= e(url('seller', 'categories')) ?>">Xoa loc</a>
      <?php endif; ?>
    </div>
  </form>

  <div class="card">
    <div class="table-head seller-table">
      <div>Danh muc</div><div>Icon</div><div>San pham</div><div>Trang thai</div><div class="right">Thao tac</div>
    </div>

    <?php foreach ($result['items'] as $cat): ?>
      <div class="table-row seller-table">
        <div class="pcol">
          <div class="quick-icon">
            <span class="material-symbols-outlined"><?= e($cat['icon'] ?: 'category') ?></span>
          </div>
          <div>
            <div class="bold"><?= e($cat['name']) ?></div>
            <div class="muted small">ID #<?= (int)$cat['id'] ?></div>
          </div>
        </div>
        <div><?= e($cat['icon'] ?: 'category') ?></div>
        <div><?= (int)$cat['product_count'] ?></div>
        <div><span class="pill pill-green">Active</span></div>
        <div class="right">
          <a class="link" href="<?= e(url('seller', 'categoryEdit', ['id' => (int)$cat['id']])) ?>">Sua</a>
          <span class="muted">&bull;</span>
          <a class="link danger" href="<?= e(url('seller', 'categoryDeleteConfirm', ['id' => (int)$cat['id']])) ?>">Xoa</a>
        </div>
      </div>
    <?php endforeach; ?>

    <?php if (empty($result['items'])): ?>
      <div class="table-row seller-table">
        <div class="muted">Khong tim thay danh muc nao.</div><div></div><div></div><div></div><div></div>
      </div>
    <?php endif; ?>
  </div>

  <div class="pagination">
    <?php for ($i = 1; $i <= $result['totalPages']; $i++): ?>
      <a class="pagebtn <?= ($i === $result['page']) ? 'active' : '' ?>"
         href="<?= e(url('seller', 'categories', ['page' => $i, 'q' => $_GET['q'] ?? ''])) ?>"><?= $i ?></a>
    <?php endfor; ?>
  </div>
</section>

<?php require __DIR__ . '/../shares/footer.php'; ?>
