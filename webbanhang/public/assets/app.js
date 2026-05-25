// HERO slider (smooth auto)
(function initHeroSlider(){
  const slider = document.querySelector('.hero-slider');
  if(!slider) return;

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

  function render(){
    slides.forEach((s,i)=>s.classList.toggle('is-active', i===idx));
    dots.forEach((d,i)=>d.classList.toggle('active', i===idx));
  }

  function go(i){
    idx = (i + slides.length) % slides.length;
    render();
  }

  prev?.addEventListener('click', ()=>go(idx-1));
  next?.addEventListener('click', ()=>go(idx+1));

  // autoplay
  const autoplay = slider.dataset.autoplay === 'true';
  const interval = parseInt(slider.dataset.interval || '3500', 10);
  if (autoplay){
    let t = setInterval(()=>go(idx+1), interval);
    slider.addEventListener('mouseenter', ()=>clearInterval(t));
    slider.addEventListener('mouseleave', ()=>t = setInterval(()=>go(idx+1), interval));
  }
})();

// Countdown (daily to 23:59:59)
(function initCountdown(){
  const el = document.querySelector('.countdown');
  if(!el) return;

  function pad(n){return String(n).padStart(2,'0')}
  function tick(){
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