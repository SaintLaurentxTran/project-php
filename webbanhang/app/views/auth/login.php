<?php require __DIR__ . '/../shares/header.php'; ?>

<div class="auth-page">
  <div class="auth-card">
    <div class="auth-logo">
      <a href="<?= e(url()) ?>">ShopeeFake</a>
    </div>
    <h1 class="auth-title">Đăng Nhập</h1>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-error">
        <?php foreach ($errors as $e): ?>
          <div><?= htmlspecialchars($e) ?></div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="<?= e(url('auth', 'login')) ?>" novalidate>
      <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">

      <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" class="form-control"
               value="<?= htmlspecialchars(old('email')) ?>"
               placeholder="Nhập địa chỉ email" required autofocus>
      </div>

      <div class="form-group">
        <label for="password">Mật khẩu</label>
        <div class="input-with-toggle">
          <input type="password" id="password" name="password" class="form-control"
                 placeholder="Nhập mật khẩu" required>
          <button type="button" class="toggle-pw" onclick="togglePw('password')">
            <span class="material-symbols-outlined" id="password-icon">visibility</span>
          </button>
        </div>
      </div>

      <div class="form-group form-row-between">
        <label class="checkbox-label">
          <input type="checkbox" name="remember" value="1"> Ghi nhớ đăng nhập
        </label>
        <a href="<?= e(url('auth', 'forgotPassword')) ?>" class="link-small">Quên mật khẩu?</a>
      </div>

      <button type="submit" class="btn btn-primary btn-full">Đăng Nhập</button>
    </form>

    <div class="auth-footer">
      Chưa có tài khoản?
      <a href="<?= e(url('auth', 'register')) ?>">Đăng Ký Ngay</a>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../shares/footer.php'; ?>
