// HERO slider (smooth auto)
(function initHeroSlider() {
  const slider = document.querySelector('.hero-slider');
  if (!slider) return;

  const slides = Array.from(slider.querySelectorAll('.hero-slide'));
  const dotsWrap = slider.querySelector('.hero-dots');
  const prev = slider.querySelector('.hero-nav.prev');
  const next = slider.querySelector('.hero-nav.next');
  let idx = slides.findIndex(s => s.classList.contains('is-active'));
  if (idx < 0) idx = 0;

  // dots
  const dots = slides.map((_, i) => {
    const d = document.createElement('div');
    d.className = 'hero-dot' + (i === idx ? ' active' : '');
    d.addEventListener('click', () => go(i));
    dotsWrap.appendChild(d);
    return d;
  });

  function render() {
    slides.forEach((s, i) => s.classList.toggle('is-active', i === idx));
    dots.forEach((d, i) => d.classList.toggle('active', i === idx));
  }

  function go(i) {
    idx = (i + slides.length) % slides.length;
    render();
  }

  prev?.addEventListener('click', () => go(idx - 1));
  next?.addEventListener('click', () => go(idx + 1));

  // autoplay
  const autoplay = slider.dataset.autoplay === 'true';
  const interval = parseInt(slider.dataset.interval || '3500', 10);
  if (autoplay) {
    let t = setInterval(() => go(idx + 1), interval);
    slider.addEventListener('mouseenter', () => clearInterval(t));
    slider.addEventListener('mouseleave', () => t = setInterval(() => go(idx + 1), interval));
  }
})();

// Countdown (daily to 23:59:59)
(function initCountdown() {
  const el = document.querySelector('.countdown');
  if (!el) return;

  function pad(n) { return String(n).padStart(2, '0') }
  function tick() {
    const now = new Date();
    const h = 23 - now.getHours();
    const m = 59 - now.getMinutes();
    const s = 59 - now.getSeconds();
    el.innerHTML = `
      <span class="tbox">${pad(h)}</span><span class="tsep">:</span>
      <span class="tbox">${pad(m)}</span><span class="tsep">:</span>
      <span class="tbox">${pad(s)}</span>
    `;
  }
  tick();
  setInterval(tick, 1000);
})();

// API-driven UI
(function initApiUi() {
  const body = document.body;
  if (!body) return;

  const baseUrl = (body.dataset.baseUrl || '/').replace(/\/?$/, '/');
  const loginUrl = body.dataset.loginUrl || `${baseUrl}auth/login`;
  const tokenKey = 'sf_access_token';

  function apiUrl(path) {
    return baseUrl + path.replace(/^\/+/, '');
  }

  function token() {
    return localStorage.getItem(tokenKey) || sessionStorage.getItem(tokenKey) || '';
  }

  function saveToken(value, remember) {
    localStorage.removeItem(tokenKey);
    sessionStorage.removeItem(tokenKey);
    (remember ? localStorage : sessionStorage).setItem(tokenKey, value);
  }

  function clearToken() {
    localStorage.removeItem(tokenKey);
    sessionStorage.removeItem(tokenKey);
  }

  function redirectLogin() {
    clearToken();
    const current = window.location.pathname + window.location.search;
    window.location.href = `${loginUrl}?expired=1&redirect=${encodeURIComponent(current)}`;
  }

  async function apiFetch(path, options = {}) {
    const headers = new Headers(options.headers || {});
    const authToken = token();
    if (authToken) headers.set('Authorization', `Bearer ${authToken}`);
    if (options.body && !(options.body instanceof FormData) && !headers.has('Content-Type')) {
      headers.set('Content-Type', 'application/json');
    }

    const response = await fetch(apiUrl(path), {
      credentials: 'same-origin',
      ...options,
      headers
    });

    let payload = null;
    try {
      payload = await response.json();
    } catch (error) {
      payload = { success: false, message: 'Phan hoi API khong phai JSON hop le.' };
    }

    if (response.status === 401) {
      redirectLogin();
      throw new Error('Token het han hoac khong hop le.');
    }

    if (!response.ok || payload.success === false) {
      const message = payload.message || (Array.isArray(payload.errors) ? payload.errors.join(', ') : 'API request failed.');
      throw new Error(message);
    }

    return payload;
  }

  function money(value) {
    return `${Number(value || 0).toLocaleString('vi-VN')} VND`;
  }

  function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, (char) => ({
      '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
    }[char]));
  }

  function imageSrc(value) {
    const src = String(value || '').trim();
    if (!src) return apiUrl('public/assets/default_avatar.png');
    if (/^https?:\/\//i.test(src) || src.startsWith('/')) return src;
    return apiUrl(src);
  }

  function setStatus(el, message, type = '') {
    if (!el) return;
    el.hidden = false;
    el.textContent = message;
    el.classList.toggle('api-status-error', type === 'error');
    el.classList.toggle('api-status-success', type === 'success');
  }

  function productCard(product) {
    const href = apiUrl(`product/show?id=${encodeURIComponent(product.id)}`);
    return `
      <a class="product-card hover-lift" href="${href}">
        <div class="pimg">
          <img src="${escapeHtml(imageSrc(product.thumb_url))}" alt="${escapeHtml(product.name)}">
          ${Number(product.discount_percent || 0) > 0 ? `<div class="tag warn">-${Number(product.discount_percent)}%</div>` : ''}
        </div>
        <div class="pbody">
          <div class="pname">${escapeHtml(product.name)}</div>
          <div class="prow">
            <div class="pprice">${money(product.price)}</div>
            <div class="pcity">${escapeHtml(product.city || '')}</div>
          </div>
          <div class="prate">
            <span class="material-symbols-outlined filled">star</span>
            <span class="material-symbols-outlined filled">star</span>
            <span class="material-symbols-outlined filled">star</span>
            <span class="material-symbols-outlined filled">star</span>
            <span class="material-symbols-outlined">star</span>
            <span class="sold">Da ban ${Number(product.sold_count || 0).toLocaleString('vi-VN')}</span>
          </div>
        </div>
      </a>`;
  }

  async function updateCartBadge() {
    const badge = document.querySelector('.icon-pill .badge');
    if (!token()) return;
    try {
      const payload = await apiFetch('api/cart/total');
      const count = Number(payload.data?.total_items || 0);
      if (badge) {
        badge.textContent = count;
        badge.hidden = count <= 0;
      }
    } catch (error) {
      // Auth errors are handled globally; badge refresh should stay quiet.
    }
  }

  function initLogin() {
    const form = document.querySelector('[data-api-login-form]');
    if (!form) return;

    form.addEventListener('submit', async (event) => {
      event.preventDefault();
      const message = form.querySelector('[data-api-message]');
      const submit = form.querySelector('[type="submit"]');
      submit.disabled = true;
      setStatus(message, 'Dang dang nhap...');

      try {
        const data = Object.fromEntries(new FormData(form).entries());
        const payload = await apiFetch('api/auth/login', {
          method: 'POST',
          body: JSON.stringify({ email: data.email, password: data.password })
        });
        saveToken(payload.access_token, Boolean(data.remember));
        setStatus(message, 'Dang nhap thanh cong. Dang chuyen trang...', 'success');
        const params = new URLSearchParams(window.location.search);
        window.location.href = params.get('redirect') || baseUrl;
      } catch (error) {
        setStatus(message, error.message, 'error');
      } finally {
        submit.disabled = false;
      }
    });
  }

  function initLogout() {
    document.querySelectorAll('[data-api-logout]').forEach((link) => {
      link.addEventListener('click', () => clearToken());
    });
  }

  async function loadProductList(kind = 'public', page = 1) {
    const isSeller = kind === 'seller';
    const list = document.querySelector(isSeller ? '[data-api-seller-product-rows]' : '[data-api-product-list]');
    const status = document.querySelector(isSeller ? '[data-api-seller-status]' : '[data-api-product-status]');
    const pagination = document.querySelector(isSeller ? '[data-api-seller-pagination]' : '[data-api-product-pagination]');
    if (!list) return;

    setStatus(status, 'Dang tai san pham tu API...');
    const searchInput = document.querySelector('[data-api-product-search]');
    const query = searchInput?.value?.trim() || list.dataset.apiSearch || '';
    const categoryId = Number(list.dataset.apiCategoryId || 0);
    const qs = new URLSearchParams({ page: String(page), limit: '20' });
    if (query) qs.set('search', query);
    if (categoryId > 0) qs.set('category_id', String(categoryId));

    try {
      const payload = await apiFetch(`api/products?${qs.toString()}`);
      const data = payload.data || {};
      const items = data.items || [];
      if (isSeller) {
        list.innerHTML = items.map((product) => `
          <div class="table-row seller-table">
            <div class="pcol">
              <img src="${escapeHtml(imageSrc(product.thumb_url))}" alt="">
              <div>
                <div class="bold">${escapeHtml(product.name)}</div>
                <div class="muted small">${escapeHtml(product.category_name || '')}</div>
              </div>
            </div>
            <div>${Number(product.stock || 0)}</div>
            <div class="primary bold">${money(product.price)}</div>
            <div><span class="pill pill-green">Active</span></div>
            <div class="right">
              <a class="link" href="${apiUrl(`seller/edit?id=${encodeURIComponent(product.id)}`)}">Sua</a>
              <span class="muted"> / </span>
              <button class="link danger api-link-button" type="button" data-api-delete-product="${Number(product.id)}">Xoa</button>
            </div>
          </div>`).join('');
      } else {
        list.innerHTML = items.map(productCard).join('');
      }

      if (pagination) {
        const totalPages = Number(data.totalPages || 1);
        pagination.innerHTML = Array.from({ length: totalPages }, (_, index) => {
          const pageNo = index + 1;
          return `<button class="pagebtn ${pageNo === Number(data.page || page) ? 'active' : ''}" type="button" data-api-page="${pageNo}">${pageNo}</button>`;
        }).join('');
      }
      setStatus(status, items.length ? `Da tai ${items.length} san pham.` : 'Khong co san pham nao.', items.length ? 'success' : '');
    } catch (error) {
      setStatus(status, error.message, 'error');
    }
  }

  function initProductLists() {
    if (document.querySelector('[data-api-product-list]')) loadProductList('public');
    if (document.querySelector('[data-api-seller-product-rows]')) loadProductList('seller');

    document.addEventListener('click', (event) => {
      const pageBtn = event.target.closest('[data-api-page]');
      if (pageBtn) {
        const kind = document.querySelector('[data-api-seller-product-rows]') ? 'seller' : 'public';
        loadProductList(kind, Number(pageBtn.dataset.apiPage || 1));
      }
      const searchBtn = event.target.closest('[data-api-product-search-btn]');
      if (searchBtn) loadProductList('public');
    });
  }

  function productFormData(form) {
    const data = Object.fromEntries(new FormData(form).entries());
    const basePrice = Number(data.base_price || data.price || 0);
    const discount = Number(data.discount_percent || 0);
    const discounted = Math.round(basePrice * (100 - Math.min(100, Math.max(0, discount))) / 100);
    return {
      name: data.name,
      category_id: Number(data.category_id),
      base_price: basePrice,
      price: discounted,
      discount_percent: discount,
      stock: Number(data.stock || 0),
      city: data.city || '',
      description: data.description || '',
      is_flash_sale: data.is_flash_sale ? 1 : 0,
      thumb_url: data.thumb_url || ''
    };
  }

  async function initProductForm() {
    const form = document.querySelector('[data-api-product-form]');
    if (!form) return;
    const message = form.querySelector('[data-api-form-message]');
    const method = form.dataset.apiMethod || 'POST';
    const productId = form.dataset.apiProductId;

    if (method === 'PUT' && productId) {
      try {
        const payload = await apiFetch(`api/products/${encodeURIComponent(productId)}`);
        const product = payload.data || {};
        Object.entries(product).forEach(([key, value]) => {
          const field = form.elements[key];
          if (!field || field.type === 'checkbox') return;
          field.value = value ?? '';
        });
        if (form.elements.base_price) {
          form.elements.base_price.value = product.old_price || product.price || '';
        }
        if (form.elements.is_flash_sale) {
          form.elements.is_flash_sale.checked = Number(product.is_flash_sale || 0) === 1;
        }
        setStatus(message, 'Da tai du lieu cu tu API.', 'success');
      } catch (error) {
        setStatus(message, error.message, 'error');
      }
    }

    form.addEventListener('submit', async (event) => {
      event.preventDefault();
      const submit = form.querySelector('[type="submit"]');
      submit.disabled = true;
      setStatus(message, 'Dang gui du lieu qua API...');
      try {
        const path = method === 'PUT' ? `api/products/${encodeURIComponent(productId)}` : 'api/products';
        await apiFetch(path, { method, body: JSON.stringify(productFormData(form)) });
        setStatus(message, 'Luu san pham thanh cong.', 'success');
        window.location.href = form.dataset.apiSuccessUrl || apiUrl('seller/products');
      } catch (error) {
        setStatus(message, error.message, 'error');
      } finally {
        submit.disabled = false;
      }
    });
  }

  function initProductDelete() {
    document.addEventListener('click', async (event) => {
      const button = event.target.closest('[data-api-delete-product]');
      if (!button) return;
      if (!window.confirm('Xoa san pham nay qua API?')) return;
      const status = document.querySelector('[data-api-seller-status]');
      button.disabled = true;
      try {
        await apiFetch(`api/products/${encodeURIComponent(button.dataset.apiDeleteProduct)}`, { method: 'DELETE' });
        setStatus(status, 'Da xoa san pham qua API.', 'success');
        loadProductList('seller');
      } catch (error) {
        setStatus(status, error.message, 'error');
        button.disabled = false;
      }
    });
  }

  async function initProductDetail() {
    const section = document.querySelector('[data-api-product-detail]');
    if (!section) return;

    const productId = section.dataset.apiProductDetail;
    try {
      const payload = await apiFetch(`api/products/${encodeURIComponent(productId)}`);
      const product = payload.data || {};
      const title = section.querySelector('[data-api-detail-title]');
      const breadcrumb = section.querySelector('[data-api-detail-breadcrumb]');
      const image = section.querySelector('[data-api-detail-image]');
      const price = section.querySelector('.pricebox .now');
      const city = section.querySelector('.ship-content b');
      const stock = section.querySelector('[data-api-detail-stock]');
      const description = section.querySelector('.desc .muted');

      if (title) {
        const flash = Number(product.is_flash_sale || 0) === 1 ? '<span class="pill pill-green">FLASH</span> ' : '';
        title.innerHTML = `${flash}${escapeHtml(product.name || '')}`;
      }
      if (breadcrumb) breadcrumb.textContent = product.name || '';
      if (image && product.thumb_url) {
        image.src = imageSrc(product.thumb_url);
        image.alt = product.name || '';
      }
      if (price) price.textContent = money(product.price);
      if (city) city.textContent = product.city || '';
      if (stock) stock.textContent = `${Number(product.stock || 0)} san pham co san`;
      if (description) description.textContent = product.description || '';
    } catch (error) {
      console.warn('Khong the tai chi tiet san pham tu API:', error.message);
    }
  }

  function initAddCart() {
    const form = document.querySelector('[data-api-add-cart]');
    if (!form) return;
    form.addEventListener('submit', async (event) => {
      event.preventDefault();
      const data = Object.fromEntries(new FormData(form).entries());
      try {
        await apiFetch('api/cart', {
          method: 'POST',
          body: JSON.stringify({ product_id: Number(data.id), qty: Number(data.qty || 1) })
        });
        await updateCartBadge();
        window.location.href = apiUrl('cart/index');
      } catch (error) {
        window.alert(error.message);
      }
    });
  }

  function cartRows(items) {
    if (!items.length) {
      return `
        <div class="card empty">
          <span class="material-symbols-outlined big">shopping_cart_off</span>
          <h3>Gio hang cua ban con trong</h3>
          <p class="muted">Hay tim them san pham phu hop.</p>
          <a class="btn btn-primary" href="${baseUrl}">Mua sam ngay</a>
        </div>`;
    }

    const rows = items.map((item) => {
      const line = Number(item.price || 0) * Number(item.qty || 0);
      return `
        <div class="cart-row">
          <div class="cart-product">
            <img src="${escapeHtml(imageSrc(item.image || item.thumb_url))}" alt="">
            <div>
              <div class="bold">${escapeHtml(item.name)}</div>
              <div class="muted small">${escapeHtml(item.city || '')}</div>
            </div>
          </div>
          <div class="center">${money(item.price)}</div>
          <div class="center">
            <div class="qty">
              <button class="qtybtn" type="button" data-api-cart-delta="-1" data-id="${Number(item.id)}">-</button>
              <input value="${Number(item.qty || 1)}" data-api-cart-qty="${Number(item.id)}">
              <button class="qtybtn" type="button" data-api-cart-delta="1" data-id="${Number(item.id)}">+</button>
            </div>
          </div>
          <div class="center price">${money(line)}</div>
          <div class="right">
            <button class="link danger api-link-button" type="button" data-api-cart-remove="${Number(item.id)}">
              <span class="material-symbols-outlined">delete</span> Xoa
            </button>
          </div>
        </div>`;
    }).join('');

    return `
      <div class="card cart-table">
        <div class="cart-row cart-headrow">
          <div>San pham</div><div class="center">Don gia</div><div class="center">So luong</div><div class="center">So tien</div><div class="right">Thao tac</div>
        </div>
        ${rows}
      </div>
      <div class="cart-footer">
        <button class="btn" type="button" data-api-cart-clear>Xoa tat ca</button>
        <div class="totalbox">
          <div class="muted">Tong thanh toan:</div>
          <div class="total">${money(items.reduce((sum, item) => sum + Number(item.price || 0) * Number(item.qty || 0), 0))}</div>
          <a class="btn btn-primary btn-lg" href="${apiUrl('checkout/index')}">Mua hang</a>
        </div>
      </div>`;
  }

  async function loadCart() {
    const content = document.querySelector('[data-api-cart-content]');
    const status = document.querySelector('[data-api-cart-status]');
    if (!content) return;
    setStatus(status, 'Dang tai gio hang tu API...');
    try {
      const payload = await apiFetch('api/cart');
      const items = payload.data?.items || [];
      content.innerHTML = cartRows(items);
      setStatus(status, 'Da tai gio hang.', 'success');
      await updateCartBadge();
    } catch (error) {
      setStatus(status, error.message, 'error');
    }
  }

  function initCart() {
    if (document.querySelector('[data-api-cart-page]')) loadCart();

    document.addEventListener('click', async (event) => {
      const deltaBtn = event.target.closest('[data-api-cart-delta]');
      const removeBtn = event.target.closest('[data-api-cart-remove]');
      const clearBtn = event.target.closest('[data-api-cart-clear]');
      if (!deltaBtn && !removeBtn && !clearBtn) return;

      try {
        if (deltaBtn) {
          const id = Number(deltaBtn.dataset.id);
          const input = document.querySelector(`[data-api-cart-qty="${id}"]`);
          const qty = Math.max(1, Number(input.value || 1) + Number(deltaBtn.dataset.apiCartDelta || 0));
          await apiFetch('api/cart', { method: 'PUT', body: JSON.stringify({ product_id: id, qty }) });
        } else if (removeBtn) {
          await apiFetch(`api/cart/${encodeURIComponent(removeBtn.dataset.apiCartRemove)}`, { method: 'DELETE' });
        } else if (clearBtn) {
          await apiFetch('api/cart/clear', { method: 'DELETE' });
        }
        await loadCart();
      } catch (error) {
        const status = document.querySelector('[data-api-cart-status]');
        setStatus(status, error.message, 'error');
      }
    });
  }

  const checkoutState = { itemsTotal: 0, shippingFee: 32000, voucher: 50000, coins: 20000 };

  function recalcCheckout() {
    const useCoins = document.getElementById('use_coins')?.checked;
    const coinsUsed = useCoins ? checkoutState.coins : 0;
    const finalTotal = Math.max(0, checkoutState.itemsTotal + checkoutState.shippingFee - checkoutState.voucher - coinsUsed);
    const itemsTotal = document.querySelector('[data-api-items-total]');
    const coinsLine = document.querySelector('[data-api-coins-line]');
    const final = document.querySelector('[data-api-final-total]');
    if (itemsTotal) itemsTotal.textContent = money(checkoutState.itemsTotal);
    if (coinsLine) coinsLine.textContent = `-${money(coinsUsed)}`;
    if (final) final.textContent = money(finalTotal);
  }

  async function loadCheckout() {
    const itemsWrap = document.querySelector('[data-api-checkout-items]');
    if (!itemsWrap) return;
    try {
      const payload = await apiFetch('api/cart');
      const items = payload.data?.items || [];
      if (!items.length) {
        window.location.href = apiUrl('cart/index');
        return;
      }
      checkoutState.itemsTotal = items.reduce((sum, item) => sum + Number(item.price || 0) * Number(item.qty || 0), 0);
      itemsWrap.innerHTML = items.map((item) => `
        <div class="table-row">
          <div class="pcol">
            <img src="${escapeHtml(imageSrc(item.image || item.thumb_url))}" alt="">
            <div>
              <div class="bold">${escapeHtml(item.name)}</div>
              <div class="muted small">Loai: mac dinh</div>
            </div>
          </div>
          <div class="center">${money(item.price)}</div>
          <div class="center">${Number(item.qty || 0)}</div>
          <div class="right bold">${money(Number(item.price || 0) * Number(item.qty || 0))}</div>
        </div>`).join('');
      recalcCheckout();
    } catch (error) {
      const message = document.querySelector('[data-api-order-message]');
      setStatus(message, error.message, 'error');
    }
  }

  function initCheckout() {
    if (!document.querySelector('[data-api-checkout-page]')) return;
    loadCheckout();

    document.querySelectorAll('.payopt input').forEach((radio) => {
      radio.addEventListener('change', () => {
        document.querySelectorAll('.payopt').forEach((label) => label.classList.remove('active'));
        radio.closest('.payopt').classList.add('active');
      });
    });

    document.getElementById('use_coins')?.addEventListener('change', recalcCheckout);

    const form = document.querySelector('[data-api-order-form]');
    form?.addEventListener('input', () => {
      document.querySelector('[data-api-checkout-name-preview]').textContent = form.elements.customer_name.value || 'Nguoi nhan';
      document.querySelector('[data-api-checkout-address-preview]').textContent = form.elements.customer_address.value || 'Nhap thong tin giao hang ben duoi';
    });

    document.querySelector('[data-api-place-order]')?.addEventListener('click', async () => {
      const message = document.querySelector('[data-api-order-message]');
      if (!form.reportValidity()) return;
      const data = Object.fromEntries(new FormData(form).entries());
      setStatus(message, 'Dang gui don hang qua API...');
      try {
        const payload = await apiFetch('api/orders', {
          method: 'POST',
          body: JSON.stringify({
            customer_name: data.customer_name,
            customer_phone: data.customer_phone,
            customer_address: data.customer_address,
            payment_method: data.payment_method,
            shipping_fee: checkoutState.shippingFee,
            voucher_amount: checkoutState.voucher,
            coins_used: data.use_coins ? checkoutState.coins : 0
          })
        });
        const id = payload.data?.order_id || '';
        window.location.href = apiUrl(`checkout/success?id=${encodeURIComponent(id)}`);
      } catch (error) {
        setStatus(message, error.message, 'error');
      }
    });
  }

  initLogin();
  initLogout();
  initProductLists();
  initProductForm();
  initProductDelete();
  initProductDetail();
  initAddCart();
  initCart();
  initCheckout();
})();
