document.addEventListener('DOMContentLoaded', () => {

  const navbar = document.getElementById('navbar');
  if (navbar) {
    window.addEventListener('scroll', () => {
      navbar.classList.toggle('scrolled', window.scrollY > 50);
    });
    if (window.scrollY > 50) navbar.classList.add('scrolled');
  }

  window.toggleMenu = function () {
    const nav = document.getElementById('navLinks');
    const ham = document.getElementById('hamburger');
    if (nav) {
      nav.classList.toggle('open');
      ham.classList.toggle('open');
    }
  };

  const observerOptions = {
    threshold: 0.12,
    rootMargin: '0px 0px -50px 0px'
  };

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.style.opacity = '1';
        entry.target.style.transform = 'translateY(0)';
        observer.unobserve(entry.target);
      }
    });
  }, observerOptions);

  document.querySelectorAll('.card, .why-item, .stat-item, .section-header').forEach(el => {
    el.style.opacity = '0';
    el.style.transform = 'translateY(24px)';
    el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
    observer.observe(el);
  });

  const counters = document.querySelectorAll('.stat-number[data-target]');
  const counterObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (!entry.isIntersecting) return;
      const el = entry.target;
      const target = parseInt(el.dataset.target);
      const duration = 2000;
      const step = Math.ceil(target / (duration / 16));
      let current = 0;

      const timer = setInterval(() => {
        current = Math.min(current + step, target);
        el.textContent = current.toLocaleString('fr-FR') + (el.dataset.target.includes('+') ? '+' : '');
        if (current >= target) clearInterval(timer);
      }, 16);

      counterObserver.unobserve(el);
    });
  }, { threshold: 0.5 });

  counters.forEach(c => counterObserver.observe(c));

  const currentPath = window.location.pathname.split('/').pop() || 'index.php';
  document.querySelectorAll('.nav-links a').forEach(link => {
    const href = link.getAttribute('href');
    if (href && href.includes(currentPath.replace('.php', ''))) {
      link.style.color = 'var(--or-sable)';
    }
  });

  const cards = document.querySelectorAll('.villa-card, .activite-card');
  cards.forEach((card, index) => {
    card.style.transitionDelay = `${index * 0.08}s`;
  });

  document.querySelectorAll('[data-tooltip]').forEach(el => {
    const tip = document.createElement('div');
    tip.className = 'tooltip';
    tip.textContent = el.dataset.tooltip;
    tip.style.cssText = `
      position: absolute;
      background: var(--bleu-profond);
      color: #fff;
      padding: 6px 12px;
      border-radius: 6px;
      font-size: 0.78rem;
      pointer-events: none;
      opacity: 0;
      transition: opacity 0.2s;
      z-index: 100;
      white-space: nowrap;
    `;
    document.body.appendChild(tip);

    el.addEventListener('mouseenter', e => {
      const r = el.getBoundingClientRect();
      tip.style.top = (window.scrollY + r.top - 36) + 'px';
      tip.style.left = (r.left + r.width / 2 - tip.offsetWidth / 2) + 'px';
      tip.style.opacity = '1';
    });
    el.addEventListener('mouseleave', () => { tip.style.opacity = '0'; });
  });

});
