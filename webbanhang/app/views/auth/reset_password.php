<?php require __DIR__ . '/../shares/header.php'; ?>

<div class="auth-page reset-flow-page">
  <div class="auth-card reset-flow-card">
    <div class="auth-logo">
      <a href="<?= e(url()) ?>">ShopeeFake</a>
    </div>

    <div class="reset-flow-icon">
      <span class="material-symbols-outlined">lock_reset</span>
    </div>

    <h1 class="auth-title">Đặt lại mật khẩu</h1>
    <p class="auth-subtitle">Tạo mật khẩu mới cho tài khoản của bạn.</p>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-error">
        <?php foreach ($errors as $e): ?><div><?= e($e) ?></div><?php endforeach; ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="<?= e(url('auth', 'resetPassword', ['token' => $token])) ?>" novalidate>
      <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">

      <div class="form-group">
        <label for="password">Mật khẩu mới</label>
        <div class="input-with-toggle">
          <input type="password" id="password" name="password" class="form-control"
                 placeholder="Ít nhất 6 ký tự" minlength="6" required autofocus>
          <button type="button" class="toggle-pw" onclick="togglePw('password')" aria-label="Hiện/ẩn mật khẩu">
            <span class="material-symbols-outlined" id="password-icon">visibility</span>
          </button>
        </div>
      </div>

      <div class="form-group">
        <label for="password_confirm">Xác nhận mật khẩu mới</label>
        <div class="input-with-toggle">
          <input type="password" id="password_confirm" name="password_confirm" class="form-control"
                 placeholder="Nhập lại mật khẩu mới" minlength="6" required>
          <button type="button" class="toggle-pw" onclick="togglePw('password_confirm')" aria-label="Hiện/ẩn xác nhận mật khẩu">
            <span class="material-symbols-outlined" id="password_confirm-icon">visibility</span>
          </button>
        </div>
      </div>

      <button type="submit" class="btn btn-primary btn-full">Cập nhật mật khẩu</button>
    </form>
  </div>
</div>

<?php require __DIR__ . '/../shares/footer.php'; ?>
