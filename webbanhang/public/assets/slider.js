(function () {
    function setupHeroSlider() {
      const root = document.querySelector('[data-slider="hero"]');
      const dotsRoot = document.querySelector('[data-dots="hero"]');
      if (!root || !dotsRoot) return;
  
      const slides = Array.from(root.querySelectorAll('.sf-hero-slide'));
      if (slides.length <= 1) return;
  
      let index = 0;
      let timer = null;
  
      dotsRoot.innerHTML = slides.map((_, i) => `<div class="sf-dot ${i===0?'is-active':''}" data-i="${i}"></div>`).join('');
      const dots = Array.from(dotsRoot.querySelectorAll('.sf-dot'));
  
      function go(i) {
        slides[index].classList.remove('is-active');
        dots[index].classList.remove('is-active');
        index = i;
        slides[index].classList.add('is-active');
        dots[index].classList.add('is-active');
      }
  
      function next() { go((index + 1) % slides.length); }
  
      dots.forEach(d => d.addEventListener('click', () => {
        go(parseInt(d.dataset.i, 10));
        restart();
      }));
  
      function restart(){
        if(timer) clearInterval(timer);
        timer = setInterval(next, 4500);
      }
  
      restart();
    }
  
    function setupHorizontalAutoScroll(selector, speed = 0.5) {
      const el = document.querySelector(selector);
      if (!el) return;
  
      let raf = null;
      let dir = 1;
  
      function step() {
        el.scrollLeft += speed * dir;
        // bounce at ends
        if (el.scrollLeft + el.clientWidth >= el.scrollWidth - 2) dir = -1;
        if (el.scrollLeft <= 0) dir = 1;
        raf = requestAnimationFrame(step);
      }
  
      // pause on hover
      el.addEventListener('mouseenter', () => { if (raf) cancelAnimationFrame(raf); raf = null; });
      el.addEventListener('mouseleave', () => { if (!raf) raf = requestAnimationFrame(step); });
  
      // drag to scroll
      let isDown=false, startX=0, startLeft=0;
      el.addEventListener('mousedown', (e)=>{
        isDown=true; startX=e.pageX; startLeft=el.scrollLeft;
        el.style.scrollBehavior = 'auto';
      });
      window.addEventListener('mouseup', ()=>{ isDown=false; el.style.scrollBehavior='smooth'; });
      window.addEventListener('mousemove', (e)=>{
        if(!isDown) return;
        const dx = e.pageX - startX;
        el.scrollLeft = startLeft - dx;
      });
  
      el.style.scrollBehavior = 'smooth';
      raf = requestAnimationFrame(step);
    }
  
    document.addEventListener('DOMContentLoaded', () => {
      setupHeroSlider();
      setupHorizontalAutoScroll('[data-slider="flash"]', 0.55); // Flash sale auto slide
    });
  })();