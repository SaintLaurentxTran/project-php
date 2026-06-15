<?php require __DIR__ . '/../shares/header.php'; ?>
<section class="container pt-32">
  <h1>Danh sach san pham</h1>
  <p class="muted">Du lieu duoc tai tu API san pham.</p>

  <div class="sortbar">
    <div class="sort-left">
      <input class="input" data-api-product-search placeholder="Tim san pham" value="<?= e($_GET['q'] ?? '') ?>">
      <button class="btn btn-primary" data-api-product-search-btn type="button">
        <span class="material-symbols-outlined">search</span>
        Tim
      </button>
    </div>
    <a class="btn" href="<?= e(url('seller', 'add')) ?>">
      <span class="material-symbols-outlined">add_box</span>
      Them san pham
    </a>
  </div>

  <div class="api-status" data-api-product-status>Dang tai san pham...</div>
  <div class="grid grid-5" data-api-product-list></div>
  <div class="pagination" data-api-product-pagination></div>
</section>
<?php require __DIR__ . '/../shares/footer.php'; ?>
