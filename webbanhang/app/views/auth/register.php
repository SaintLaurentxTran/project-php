<?php require __DIR__ . '/../shares/header.php'; ?>

<div class="auth-page">
  <div class="auth-card">
    <div class="auth-logo">
      <a href="<?= e(url()) ?>">ShopeeFake</a>
    </div>
    <h1 class="auth-title">Đăng Ký Tài Khoản</h1>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-error">
        <?php foreach ($errors as $e): ?>
          <div><?= htmlspecialchars($e) ?></div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="<?= e(url('auth', 'register')) ?>" novalidate>
      <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">

      <div class="form-group">
        <label for="name">Họ và Tên</label>
        <input type="text" id="name" name="name" class="form-control"
               value="<?= htmlspecialchars(old('name')) ?>"
               placeholder="Nhập họ và tên" required>
      </div>

      <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" class="form-control"
               value="<?= htmlspecialchars(old('email')) ?>"
               placeholder="Nhập địa chỉ email" required>
      </div>

      <div class="form-group">
        <label for="password">Mật khẩu</label>
        <div class="input-with-toggle">
          <input type="password" id="password" name="password" class="form-control"
                 placeholder="Ít nhất 6 ký tự" required>
          <button type="button" class="toggle-pw" onclick="togglePw('password')">
            <span class="material-symbols-outlined" id="password-icon">visibility</span>
          </button>
        </div>
      </div>

      <div class="form-group">
        <label for="password_confirm">Xác Nhận Mật Khẩu</label>
        <div class="input-with-toggle">
          <input type="password" id="password_confirm" name="password_confirm" class="form-control"
                 placeholder="Nhập lại mật khẩu" required>
          <button type="button" class="toggle-pw" onclick="togglePw('password_confirm')">
            <span class="material-symbols-outlined" id="password_confirm-icon">visibility</span>
          </button>
        </div>
      </div>

      <button type="submit" class="btn btn-primary btn-full">Đăng Ký</button>
    </form>

    <p class="auth-note">
      Sau khi dang ky, ban se nhan ma OTP qua email de kich hoat tai khoan.
    </p>

    <div class="auth-footer">
      Đã có tài khoản?
      <a href="<?= e(url('auth', 'login')) ?>">Đăng Nhập</a>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../shares/footer.php'; ?>
