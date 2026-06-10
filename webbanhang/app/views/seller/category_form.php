<?php require __DIR__ . '/../shares/header.php'; ?>

<section class="container pt-32">
  <div class="seller-head">
    <h1><?= e($formTitle) ?></h1>
    <a class="btn" href="<?= e(url('seller', 'categories')) ?>">Quay lai</a>
  </div>

  <form class="grid2" method="POST" action="<?= e($formAction) ?>">
    <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
    <input type="hidden" name="id" value="<?= (int)($category['id'] ?? 0) ?>">

    <div class="card">
      <h3>Thong tin danh muc</h3>

      <label class="label">Ten danh muc *</label>
      <input class="input" name="name" required maxlength="120"
             value="<?= e($category['name'] ?? '') ?>"
             placeholder="Vi du: Thoi Trang & Phu Kien">

      <label class="label mt-8">Material icon</label>
      <input class="input" name="icon" maxlength="64"
             value="<?= e($category['icon'] ?? 'category') ?>"
             placeholder="Vi du: checkroom, devices, chair">

      <p class="muted small mt-8">Ten icon su dung bo Material Symbols dang co tren website.</p>
    </div>

    <div class="card">
      <h3>Xem truoc</h3>
      <div class="quick-item" style="margin-top: 12px;">
        <div class="quick-icon">
          <span class="material-symbols-outlined" id="iconPreview"><?= e(($category['icon'] ?? '') ?: 'category') ?></span>
        </div>
        <div class="quick-text" id="namePreview"><?= e(($category['name'] ?? '') ?: 'Ten danh muc') ?></div>
      </div>

      <div class="row gap mt-16">
        <button class="btn btn-primary btn-lg" type="submit"><?= e($submitLabel) ?></button>
        <a class="btn btn-lg" href="<?= e(url('seller', 'categories')) ?>">Huy</a>
      </div>
    </div>
  </form>
</section>

<script>
  const nameInput = document.querySelector('input[name="name"]');
  const iconInput = document.querySelector('input[name="icon"]');
  const namePreview = document.getElementById('namePreview');
  const iconPreview = document.getElementById('iconPreview');

  nameInput.addEventListener('input', () => {
    namePreview.textContent = nameInput.value.trim() || 'Ten danh muc';
  });

  iconInput.addEventListener('input', () => {
    iconPreview.textContent = iconInput.value.trim() || 'category';
  });
</script>

<?php require __DIR__ . '/../shares/footer.php'; ?>
