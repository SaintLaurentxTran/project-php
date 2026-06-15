<?php require __DIR__ . '/../shares/header.php'; ?>

<section class="container pt-32">
  <div class="seller-head">
    <h1>Seller Center - Product Management</h1>
    <div class="row gap">
      <a class="btn" href="<?= e(url('seller', 'categories')) ?>">
        <span class="material-symbols-outlined">category</span> Danh muc
      </a>
      <a class="btn btn-primary" href="<?= e(url('seller', 'add')) ?>">
        <span class="material-symbols-outlined">add_box</span> Them san pham
      </a>
    </div>
  </div>

  <div class="api-status" data-api-seller-status>Dang tai san pham tu API...</div>

  <div class="card" data-api-seller-products>
    <div class="table-head seller-table">
      <div>San pham</div><div>Kho</div><div>Gia</div><div>Trang thai</div><div class="right">Thao tac</div>
    </div>
    <div data-api-seller-product-rows></div>
  </div>

  <div class="pagination" data-api-seller-pagination></div>
</section>

<?php require __DIR__ . '/../shares/footer.php'; ?>
