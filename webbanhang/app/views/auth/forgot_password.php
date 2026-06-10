<?php require __DIR__ . '/../shares/header.php'; ?>

<div class="auth-page">
  <div class="auth-card">
    <div class="auth-logo">
      <a href="<?= e(url()) ?>">ShopeeFake</a>
    </div>
    <h1 class="auth-title">Quên Mật Khẩu</h1>
    <p class="auth-subtitle">Nhập email của bạn, chúng tôi sẽ gửi link đặt lại mật khẩu.</p>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-error">
        <?php foreach ($errors as $e): ?><div><?= htmlspecialchars($e) ?></div><?php endforeach; ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="<?= e(url('auth', 'forgotPassword')) ?>" novalidate>
      <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">

      <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" class="form-control"
               value="<?= htmlspecialchars(old('email')) ?>"
               placeholder="Nhập địa chỉ email đã đăng ký" required autofocus>
      </div>

      <button type="submit" class="btn btn-primary btn-full">Gửi Link Đặt Lại</button>
    </form>

    <div class="auth-footer">
      <a href="<?= e(url('auth', 'login')) ?>">← Quay lại Đăng Nhập</a>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../shares/footer.php'; ?>
