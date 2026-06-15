<?php require __DIR__ . '/../shares/header.php'; ?>

<section class="container pt-32" data-api-product-detail="<?= (int)$product['id'] ?>">
  <nav class="breadcrumb">
    <a href="<?= e(url()) ?>">Trang chủ</a>
    <span class="material-symbols-outlined">chevron_right</span>
    <a href="<?= e(url('default', 'search', ['category_id' => (int)$product['category_id']])) ?>"><?= htmlspecialchars($product['category_name']) ?></a>
    <span class="material-symbols-outlined">chevron_right</span>
    <span class="truncate" data-api-detail-breadcrumb><?= htmlspecialchars($product['name']) ?></span>
  </nav>

  <div class="card product-hero">
    <div class="gallery">
      <div class="mainimg">
        <img id="mainProductImage" data-api-detail-image src="<?= htmlspecialchars($product['thumb_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
      </div>
      <div class="thumbs">
        <button class="thumb active" type="button" data-src="<?= htmlspecialchars($product['thumb_url']) ?>">
          <img src="<?= htmlspecialchars($product['thumb_url']) ?>" alt="thumb">
        </button>
        <?php foreach ($gallery as $img): ?>
          <button class="thumb" type="button" data-src="<?= htmlspecialchars($img['image_url']) ?>">
            <img src="<?= htmlspecialchars($img['image_url']) ?>" alt="thumb">
          </button>
        <?php endforeach; ?>
      </div>
      <div class="gallery-actions">
        <button class="link-btn" type="button"><span class="material-symbols-outlined">share</span> Chia sẻ</button>
        <div class="divider"></div>
        <button class="link-btn" type="button"><span class="material-symbols-outlined filled">favorite</span> Đã thích</button>
      </div>
    </div>

    <div class="info">
      <h1 class="pdetail-title" data-api-detail-title>
        <?php if ((int)$product['is_flash_sale'] === 1): ?>
          <span class="pill pill-green">FLASH</span>
        <?php endif; ?>
        <?= htmlspecialchars($product['name']) ?>
      </h1>

      <div class="meta">
        <div class="stars">
          <b>4.9</b>
          <span class="material-symbols-outlined filled">star</span>
          <span class="material-symbols-outlined filled">star</span>
          <span class="material-symbols-outlined filled">star</span>
          <span class="material-symbols-outlined filled">star</span>
          <span class="material-symbols-outlined filled">star</span>
        </div>
        <div class="sep"></div>
        <div><b><?= number_format((int)$product['sold_count'], 0, ',', '.') ?></b> <span class="muted">Đã bán</span></div>
      </div>

      <div class="pricebox">
        <?php if (!empty($product['old_price'])): ?>
          <span class="old"><?= number_format((int)$product['old_price'], 0, ',', '.') ?>₫</span>
        <?php endif; ?>
        <span class="now"><?= number_format((int)$product['price'], 0, ',', '.') ?>₫</span>
        <?php if ((int)$product['discount_percent'] > 0): ?>
          <span class="pill pill-warn"><?= (int)$product['discount_percent'] ?>% GIẢM</span>
        <?php endif; ?>
        <div class="assure">
          <span class="material-symbols-outlined">verified</span> ShopeeFake Đảm Bảo | Nhận hàng hoặc hoàn tiền
        </div>
      </div>

      <div class="ship">
        <div class="ship-label">Vận chuyển</div>
        <div class="ship-content">
          <div><span class="material-symbols-outlined ok">local_shipping</span> <b>Miễn phí vận chuyển</b></div>
          <div class="muted small">Vận chuyển từ <b><?= htmlspecialchars($product['city']) ?></b></div>
          <div class="muted small">Phí vận chuyển: <b>0₫</b></div>
        </div>
      </div>

      <form class="buybox" method="POST" action="<?= e(url('cart', 'add')) ?>" data-api-add-cart>
        <input type="hidden" name="id" value="<?= (int)$product['id'] ?>">
        <div class="qty">
          <button class="qtybtn" type="button" data-qty="-1">-</button>
          <input id="qtyInput" name="qty" value="1" />
          <button class="qtybtn" type="button" data-qty="1">+</button>
          <span class="muted small"><?= (int)$product['stock'] ?> sản phẩm có sẵn</span>
        </div>
        <div class="buyactions">
          <button class="btn" type="submit">
            <span class="material-symbols-outlined">add_shopping_cart</span>
            Thêm Vào Giỏ Hàng
          </button>
          <a class="btn btn-primary" href="<?= e(url('cart', 'index')) ?>">Mua Ngay</a>
        </div>
      </form>

      <div class="desc card-inner">
        <h3>Chi tiết sản phẩm</h3>
        <p class="muted"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
      </div>
    </div>
  </div>
</section>

<script>
  const mainImg = document.getElementById('mainProductImage');
  const thumbs = document.querySelectorAll('.thumb');
  let currentIndex = 0;
  let autoSlideInterval;

  // Hàm chuyển ảnh chung áp dụng hiệu ứng mờ dần (Fade transition)
  function changeToImage(index) {
    thumbs.forEach(b => b.classList.remove('active'));
    thumbs[index].classList.add('active');
    
    mainImg.style.opacity = 0;
    setTimeout(() => {
      mainImg.src = thumbs[index].dataset.src;
      mainImg.style.opacity = 1;
    }, 160);
  }

  // 1. Lắng nghe sự kiện click thủ công vào các nút ảnh nhỏ
  thumbs.forEach((btn, idx) => {
    btn.addEventListener('click', () => {
      currentIndex = idx;
      changeToImage(currentIndex);
      resetAutoPlay(); // Reset thời gian đếm lật ảnh tự động nếu người dùng tương tác bằng tay
    });
  });

  // 2. CƠ CHẾ THÊM: Tự động chạy Slider lật ảnh sau mỗi 3 giây
  function startAutoPlay() {
    if (thumbs.length > 1) { // Chỉ kích hoạt khi sản phẩm có từ 2 bức ảnh trở lên
      autoSlideInterval = setInterval(() => {
        currentIndex = (currentIndex + 1) % thumbs.length;
        changeToImage(currentIndex);
      }, 3000); // Tốc độ lật ảnh (3000ms = 3 giây)
    }
  }

  function resetAutoPlay() {
    clearInterval(autoSlideInterval);
    startAutoPlay();
  }

  // Kích hoạt chạy Slider tự động khi nạp trang thành công
  startAutoPlay();

  // 3. Xử lý tăng giảm số lượng (Giữ nguyên logic cũ của bạn)
  const qtyInput = document.getElementById('qtyInput');
  document.querySelectorAll('[data-qty]').forEach(b => {
    b.addEventListener('click', () => {
      const delta = parseInt(b.dataset.qty, 10);
      const cur = parseInt(qtyInput.value || '1', 10);
      qtyInput.value = Math.max(1, cur + delta);
    });
  });
</script>

<?php require __DIR__ . '/../shares/footer.php'; ?>
