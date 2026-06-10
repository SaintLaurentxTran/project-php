<?php require __DIR__ . '/../shares/header.php'; ?>

<section class="container pt-32">
  <div class="modal-backdrop">
    <div class="modal">
      <div class="modal-icon danger">
        <span class="material-symbols-outlined">warning</span>
      </div>
      <h3>Xác nhận xóa sản phẩm?</h3>
      <p class="muted">
        Hành động này không thể hoàn tác. Sản phẩm
        <b>"<?= htmlspecialchars($product['name']) ?>"</b> sẽ bị gỡ bỏ vĩnh viễn.
      </p>

      <div class="modal-actions">
        <a class="btn" href="<?= e(url('seller', 'products')) ?>">Hủy</a>
        <form method="POST" action="<?= e(url('seller', 'delete')) ?>">
          <input type="hidden" name="id" value="<?= (int)$product['id'] ?>">
          <button class="btn btn-danger" type="submit">Xác nhận xóa</button>
        </form>
      </div>

      <div class="progressline">
        <div class="progressbar"></div>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/../shares/footer.php'; ?>