<?php require __DIR__ . '/../shares/header.php'; ?>

<div class="auth-page">
  <div class="auth-card">
    <div class="auth-logo">
      <a href="<?= e(url()) ?>">ShopeeFake</a>
    </div>
    <h1 class="auth-title">Đặt Lại Mật Khẩu</h1>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-error">
        <?php foreach ($errors as $e): ?><div><?= htmlspecialchars($e) ?></div><?php endforeach; ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="<?= e(url('auth', 'resetPassword', ['token' => $token])) ?>" novalidate>
      <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">

      <div class="form-group">
        <label for="password">Mật khẩu mới</label>
        <div class="input-with-toggle">
          <input type="password" id="password" name="password" class="form-control"
                 placeholder="Ít nhất 6 ký tự" required autofocus>
          <button type="button" class="toggle-pw" onclick="togglePw('password')">
            <span class="material-symbols-outlined" id="password-icon">visibility</span>
          </button>
        </div>
      </div>

      <div class="form-group">
        <label for="password_confirm">Xác nhận mật khẩu mới</label>
        <div class="input-with-toggle">
          <input type="password" id="password_confirm" name="password_confirm" class="form-control"
                 placeholder="Nhập lại mật khẩu mới" required>
          <button type="button" class="toggle-pw" onclick="togglePw('password_confirm')">
            <span class="material-symbols-outlined" id="password_confirm-icon">visibility</span>
          </button>
        </div>
      </div>

      <button type="submit" class="btn btn-primary btn-full">Đặt Lại Mật Khẩu</button>
    </form>
  </div>
</div>

<?php require __DIR__ . '/../shares/footer.php'; ?>
