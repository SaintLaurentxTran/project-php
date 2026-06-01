// ========== USER DROPDOWN ==========
(function () {
  const btn = document.getElementById('userMenuBtn');
  const dropdown = document.getElementById('userDropdown');
  if (!btn || !dropdown) return;

  btn.addEventListener('click', function (e) {
    e.stopPropagation();
    dropdown.classList.toggle('open');
  });

  document.addEventListener('click', function () {
    dropdown.classList.remove('open');
  });

  dropdown.addEventListener('click', function (e) {
    e.stopPropagation();
  });
})();

// ========== PASSWORD TOGGLE ==========
function togglePw(inputId) {
  const input = document.getElementById(inputId);
  const icon  = document.getElementById(inputId + '-icon');
  if (!input) return;
  if (input.type === 'password') {
    input.type = 'text';
    if (icon) icon.textContent = 'visibility_off';
  } else {
    input.type = 'password';
    if (icon) icon.textContent = 'visibility';
  }
}

// ========== AUTO-HIDE FLASH MESSAGES ==========
(function () {
  const flashes = document.querySelectorAll('.flash');
  flashes.forEach(function (f) {
    setTimeout(function () {
      f.style.transition = 'opacity .4s';
      f.style.opacity = '0';
      setTimeout(function () { f.remove(); }, 400);
    }, 4000);
  });
})();

// ========== AVATAR PREVIEW ==========
(function () {
  const fileInput = document.getElementById('avatarFile');
  if (!fileInput) return;
  fileInput.addEventListener('change', function () {
    const file = this.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = function (e) {
      const img = document.querySelector('.profile-avatar');
      if (img) img.src = e.target.result;
    };
    reader.readAsDataURL(file);
  });
})();
