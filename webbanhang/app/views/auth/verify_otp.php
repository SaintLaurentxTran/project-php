<?php require __DIR__ . '/../shares/header.php'; ?>

<div class="otp-page">
  <div class="otp-shell">
    <div class="otp-card">
      <a class="otp-brand" href="<?= e(url()) ?>">ShopeeFake</a>
      <div class="otp-icon">
        <span class="material-symbols-outlined">mark_email_unread</span>
      </div>
      <h1>Xác thực OTP</h1>
      <p class="otp-subtitle">
        Nhập mã gồm 6 chữ số đã được gửi đến email đăng ký để kích hoạt tài khoản.
      </p>
      <div class="otp-email"><?= e($email) ?></div>

      <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
          <?php foreach ($errors as $err): ?>
            <div><?= e($err) ?></div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="<?= e(url('auth', 'verifyOtp', ['email' => $email])) ?>" id="otpForm" novalidate>
        <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
        <input type="hidden" name="email" value="<?= e($email) ?>">

        <div class="otp-inputs" aria-label="OTP code">
          <?php for ($i = 0; $i < 6; $i++): ?>
            <input class="otp-box" name="otp[]" type="text" inputmode="numeric" pattern="[0-9]*"
                   maxlength="1" autocomplete="one-time-code" aria-label="OTP digit <?= $i + 1 ?>">
          <?php endfor; ?>
        </div>

        <div class="otp-meta">
          <span>Còn lại <strong id="otpTimer"><?= sprintf('%02d:%02d', intdiv($remainingSeconds, 60), $remainingSeconds % 60) ?></strong></span>
          <span>Không nhận được mã?</span>
        </div>

        <button class="otp-primary" type="submit">Xác thực</button>
        <a class="otp-cancel" href="<?= e(url('auth', 'login')) ?>">Hủy</a>
      </form>

      <form method="POST" action="<?= e(url('auth', 'resendOtp')) ?>" class="otp-resend-form">
        <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
        <input type="hidden" name="email" value="<?= e($email) ?>">
        <button class="otp-resend" id="resendBtn" type="submit" <?= $remainingSeconds > 0 ? 'disabled' : '' ?>>Gửi lại mã OTP</button>
      </form>

      <p class="otp-note">Trong môi trường local, nếu mail chưa được cấu hình SMTP, mã OTP vẫn được ghi vào email_log.txt.</p>
    </div>
  </div>
</div>

<script>
  const boxes = Array.from(document.querySelectorAll('.otp-box'));
  const timer = document.getElementById('otpTimer');
  const resendBtn = document.getElementById('resendBtn');
  let remaining = <?= (int)$remainingSeconds ?>;

  boxes[0]?.focus();

  boxes.forEach((box, index) => {
    box.addEventListener('input', () => {
      box.value = box.value.replace(/\D/g, '').slice(0, 1);
      if (box.value && boxes[index + 1]) boxes[index + 1].focus();
    });

    box.addEventListener('keydown', (event) => {
      if (event.key === 'Backspace' && !box.value && boxes[index - 1]) {
        boxes[index - 1].focus();
      }
    });

    box.addEventListener('paste', (event) => {
      event.preventDefault();
      const digits = event.clipboardData.getData('text').replace(/\D/g, '').slice(0, boxes.length).split('');
      digits.forEach((digit, digitIndex) => {
        boxes[digitIndex].value = digit;
      });
      boxes[Math.min(digits.length, boxes.length) - 1]?.focus();
    });
  });

  function renderTimer() {
    const minutes = String(Math.floor(remaining / 60)).padStart(2, '0');
    const seconds = String(remaining % 60).padStart(2, '0');
    timer.textContent = `${minutes}:${seconds}`;
    if (remaining <= 0) {
      resendBtn.disabled = false;
      return;
    }
    remaining -= 1;
    setTimeout(renderTimer, 1000);
  }

  renderTimer();
</script>

<?php require __DIR__ . '/../shares/footer.php'; ?>
