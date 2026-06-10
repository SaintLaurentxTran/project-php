<?php require __DIR__ . '/../shares/header.php'; ?>

<div class="auth-page reset-flow-page">
  <div class="auth-card reset-flow-card">
    <div class="auth-logo">
      <a href="<?= e(url()) ?>">ShopeeFake</a>
    </div>

    <div class="reset-flow-icon">
      <span class="material-symbols-outlined">mark_email_unread</span>
    </div>

    <h1 class="auth-title">Quên mật khẩu</h1>
    <p class="auth-subtitle">
      Nhập email đã đăng ký, hệ thống sẽ gửi Verification Link để bạn đặt lại mật khẩu.
    </p>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-error">
        <?php foreach ($errors as $e): ?><div><?= e($e) ?></div><?php endforeach; ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="<?= e(url('auth', 'forgotPassword')) ?>" novalidate>
      <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">

      <div class="form-group">
        <label for="email">Email đăng ký</label>
        <input type="email" id="email" name="email" class="form-control"
               value="<?= e(old('email')) ?>"
               placeholder="tenban@email.com" required autofocus>
      </div>

      <button type="submit" class="btn btn-primary btn-full">Gửi link đặt lại mật khẩu</button>
    </form>

    <div class="auth-footer">
      <a href="<?= e(url('auth', 'login')) ?>">Quay lại đăng nhập</a>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../shares/footer.php'; ?>
