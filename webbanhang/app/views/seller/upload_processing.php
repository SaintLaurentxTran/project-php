<?php require __DIR__ . '/../shares/header.php'; ?>

<section class="container pt-32">
  <div class="card center-card">
    <div class="spinner">
      <span class="material-symbols-outlined">inventory_2</span>
    </div>
    <h3>Đang tải dữ liệu sản phẩm...</h3>
    <p class="muted">Hệ thống đang kiểm tra và xử lý. Vui lòng đợi.</p>

    <div class="progress">
      <div class="progress-inner" id="prog"></div>
    </div>
    <div class="row space-between wfull mt-8">
      <span class="muted small" id="step">Đang tối ưu hình ảnh...</span>
      <span class="bold primary" id="pct">45%</span>
    </div>
  </div>
</section>

<script>
  let p = 45;
  const prog = document.getElementById('prog');
  const pct  = document.getElementById('pct');

  const t = setInterval(() => {
    if (p < 95) {
      p += Math.floor(Math.random()*5)+1;
      if (p > 95) p = 95;
      prog.style.width = p + '%';
      pct.textContent = p + '%';
    } else {
      clearInterval(t);
      // finalize create (server-side)
      window.location.href = "<?= e(url('seller', 'finishCreate')) ?>";
    }
  }, 700);
</script>

<?php require __DIR__ . '/../shares/footer.php'; ?>
