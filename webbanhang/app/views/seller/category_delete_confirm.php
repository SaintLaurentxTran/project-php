<?php require __DIR__ . '/../shares/header.php'; ?>

<section class="container pt-32">
  <div class="modal-backdrop">
    <div class="modal">
      <div class="modal-icon danger">
        <span class="material-symbols-outlined">warning</span>
      </div>
      <h3>Xac nhan xoa danh muc?</h3>
      <p class="muted">
        Danh muc <b>"<?= e($category['name']) ?>"</b>
        <?php if ($productCount > 0): ?>
          dang co <b><?= (int)$productCount ?></b> san pham. Hay chuyen/xoa san pham trong danh muc nay truoc.
        <?php else: ?>
          se bi xoa khoi he thong.
        <?php endif; ?>
      </p>

      <div class="modal-actions">
        <a class="btn" href="<?= e(url('seller', 'categories')) ?>">Huy</a>
        <?php if ($productCount === 0): ?>
          <form method="POST" action="<?= e(url('seller', 'categoryDelete')) ?>">
            <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
            <input type="hidden" name="id" value="<?= (int)$category['id'] ?>">
            <button class="btn btn-danger" type="submit">Xac nhan xoa</button>
          </form>
        <?php endif; ?>
      </div>

      <div class="progressline">
        <div class="progressbar"></div>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/../shares/footer.php'; ?>
