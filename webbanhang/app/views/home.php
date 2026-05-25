<?php require __DIR__ . '/shares/header.php'; ?>

<section class="container">
  <!-- HERO SLIDER -->
  <div class="hero">
    <div class="hero-overlay"></div>

    <div class="hero-slider" data-autoplay="true" data-interval="3500">
      <div class="hero-slide is-active">
        <img src="https://images.unsplash.com/photo-1512436991641-6745cdb1723f?q=80&w=1600&auto=format&fit=crop" alt="slide 1">
      </div>
      <div class="hero-slide">
        <img src="https://images.unsplash.com/photo-1519741497674-611481863552?q=80&w=1600&auto=format&fit=crop" alt="slide 2">
      </div>
      <div class="hero-slide">
        <img src="https://images.unsplash.com/photo-1526170375885-4d8ecf77b99f?q=80&w=1600&auto=format&fit=crop" alt="slide 3">
      </div>

      <button class="hero-nav prev" type="button" aria-label="prev">
        <span class="material-symbols-outlined">chevron_left</span>
      </button>
      <button class="hero-nav next" type="button" aria-label="next">
        <span class="material-symbols-outlined">chevron_right</span>
      </button>

      <div class="hero-dots"></div>
    </div>

    <div class="hero-content">
      <span class="pill">SỰ KIỆN 11.11 SIÊU SALE</span>
      <h1>Lễ Hội Mua Sắm Lớn Nhất Năm</h1>
      <p>Giảm giá lên đến 90% các mặt hàng công nghệ và thời trang. Miễn phí vận chuyển toàn quốc.</p>
      <a class="btn btn-primary btn-lg" href="index.php?c=default&a=search">SĂN DEAL NGAY</a>
    </div>
  </div>
</section>

<section class="container">
  <div class="card">
    <div class="quick-grid">
      <?php foreach (array_slice($categories, 0, 8) as $cat): ?>
        <a class="quick-item hover-lift" href="index.php?c=default&a=search&category_id=<?= (int)$cat['id'] ?>">
          <div class="quick-icon">
            <span class="material-symbols-outlined"><?= htmlspecialchars($cat['icon'] ?: 'category') ?></span>
          </div>
          <div class="quick-text"><?= htmlspecialchars($cat['name']) ?></div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="container" id="flash">
  <div class="card">
    <div class="card-head">
      <div class="card-title">
        <h2 class="title-accent">Flash Sale</h2>
        <div class="countdown" data-countdown="daily"></div>
      </div>
      <a class="link" href="index.php?c=default&a=search">Xem tất cả <span class="material-symbols-outlined">chevron_right</span></a>
    </div>

    <div class="hscroll">
      <?php foreach ($flash as $p): ?>
        <a class="flash-card hover-lift" href="index.php?c=product&a=show&id=<?= (int)$p['id'] ?>">
          <div class="flash-img">
            <img src="<?= htmlspecialchars($p['thumb_url']) ?>" alt="<?= htmlspecialchars($p['name']) ?>">
            <?php if ((int)$p['discount_percent'] > 0): ?>
              <div class="discount-badge">-<?= (int)$p['discount_percent'] ?>%</div>
            <?php endif; ?>
          </div>
          <div class="price"><?= number_format((int)$p['price'], 0, ',', '.') ?>₫</div>
          <div class="bar">
            <div class="bar-fill" style="width: <?= min(95, max(15, (int)$p['discount_percent'] * 2)) ?>%"></div>
            <span class="bar-text">ĐANG BÁN CHẠY</span>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="container">
  <div class="sticky-title">
    <h2>Gợi Ý Hôm Nay</h2>
  </div>

  <div class="grid">
    <?php foreach ($latest as $p): ?>
      <a class="product-card hover-lift" href="index.php?c=product&a=show&id=<?= (int)$p['id'] ?>">
        <div class="pimg">
          <img src="<?= htmlspecialchars($p['thumb_url']) ?>" alt="<?= htmlspecialchars($p['name']) ?>">
          <?php if ((int)$p['is_flash_sale'] === 1): ?>
            <div class="tag">Flash</div>
          <?php endif; ?>
        </div>
        <div class="pbody">
          <div class="pname"><?= htmlspecialchars($p['name']) ?></div>
          <div class="prow">
            <div class="pprice"><?= number_format((int)$p['price'], 0, ',', '.') ?>₫</div>
            <div class="pcity"><?= htmlspecialchars($p['city']) ?></div>
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

  <div class="center mt-24">
    <a class="btn" href="index.php?c=default&a=search">Xem thêm</a>
  </div>
</section>

<?php require __DIR__ . '/shares/footer.php'; ?>