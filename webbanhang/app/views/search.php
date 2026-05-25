<?php require __DIR__ . '/shares/header.php'; ?>

<section class="container pt-32">
  <div class="search-layout">
    <aside class="sidebar">
      <div class="side-head">
        <span class="material-symbols-outlined">filter_list</span>
        <h3>Bộ lọc tìm kiếm</h3>
      </div>

      <div class="side-card">
        <h4>Theo Danh Mục</h4>
        <div class="side-list">
          <a class="side-link <?= ((int)($_GET['category_id'] ?? 0) === 0) ? 'active':'' ?>" href="index.php?c=default&a=search&q=<?= urlencode($q) ?>">Tất cả</a>
          <?php foreach ($categories as $cat): ?>
            <a class="side-link <?= ((int)($_GET['category_id'] ?? 0) === (int)$cat['id']) ? 'active':'' ?>"
               href="index.php?c=default&a=search&category_id=<?= (int)$cat['id'] ?>&q=<?= urlencode($q) ?>">
              <?= htmlspecialchars($cat['name']) ?>
            </a>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="side-card">
        <h4>Khoảng giá</h4>
        <div class="row">
          <input class="input" type="number" placeholder="Từ" disabled>
          <input class="input" type="number" placeholder="Đến" disabled>
        </div>
        <button class="btn btn-primary wfull" type="button" disabled>ÁP DỤNG</button>
        <p class="muted small">(demo: lọc giá chưa bật)</p>
      </div>

      <a class="btn wfull" href="index.php?c=default&a=search">Xóa tất cả</a>
    </aside>

    <div class="content">
      <div class="sortbar">
        <div class="sort-left">
          <span class="muted">Sắp xếp theo</span>
          <button class="btn btn-primary" type="button">Phổ biến</button>
          <button class="btn" type="button">Mới nhất</button>
          <button class="btn" type="button">Bán chạy</button>
        </div>
        <div class="sort-right">
          <span class="muted"><b><?= (int)$result['page'] ?></b>/<?= (int)$result['totalPages'] ?></span>
        </div>
      </div>

      <div class="grid grid-5">
        <?php foreach ($result['items'] as $p): ?>
          <a class="product-card hover-lift" href="index.php?c=product&a=show&id=<?= (int)$p['id'] ?>">
            <div class="pimg">
              <img src="<?= htmlspecialchars($p['thumb_url']) ?>" alt="<?= htmlspecialchars($p['name']) ?>">
              <?php if ((int)$p['discount_percent'] > 0): ?>
                <div class="tag warn">-<?= (int)$p['discount_percent'] ?>%</div>
              <?php endif; ?>
            </div>
            <div class="pbody">
              <div class="pname"><?= htmlspecialchars($p['name']) ?></div>
              <div class="prow">
                <div class="pprice"><?= number_format((int)$p['price'], 0, ',', '.') ?>₫</div>
              </div>
              <div class="prate">
                <span class="material-symbols-outlined filled">star</span>
                <span class="material-symbols-outlined filled">star</span>
                <span class="material-symbols-outlined filled">star</span>
                <span class="material-symbols-outlined filled">star</span>
                <span class="material-symbols-outlined">star</span>
                <span class="sold">Đã bán <?= number_format((int)$p['sold_count'], 0, ',', '.') ?></span>
              </div>
            </div>
          </a>
        <?php endforeach; ?>
      </div>

      <div class="pagination">
        <?php
          $base = "index.php?c=default&a=search&q=" . urlencode($q) . "&category_id=" . (int)$category_id . "&page=";
        ?>
        <a class="pagebtn" href="<?= $base . max(1, $result['page']-1) ?>">&laquo;</a>
        <?php for ($i=1; $i <= $result['totalPages']; $i++): ?>
          <a class="pagebtn <?= ($i===$result['page'])?'active':'' ?>" href="<?= $base.$i ?>"><?= $i ?></a>
        <?php endfor; ?>
        <a class="pagebtn" href="<?= $base . min($result['totalPages'], $result['page']+1) ?>">&raquo;</a>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/shares/footer.php'; ?>