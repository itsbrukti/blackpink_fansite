// main.js - Complete JavaScript
document.addEventListener('DOMContentLoaded', function() {
  
  // ---------- DARK MODE TOGGLE ----------
  const toggle = document.querySelector('.theme-toggle');
  if (toggle) {
    toggle.addEventListener('click', () => {
      document.body.classList.toggle('dark');
      const icon = toggle.querySelector('i');
      if (document.body.classList.contains('dark')) {
        icon.className = 'fas fa-sun';
        localStorage.setItem('darkMode', 'true');
      } else {
        icon.className = 'fas fa-moon';
        localStorage.setItem('darkMode', 'false');
      }
    });
    
    // check for saved preference
    if (localStorage.getItem('darkMode') === 'true') {
      document.body.classList.add('dark');
      toggle.querySelector('i').className = 'fas fa-sun';
    }
  }

  // ---------- BACK TO TOP ----------
  const backBtn = document.getElementById('backToTop');
  if (backBtn) {
    window.addEventListener('scroll', () => {
      if (window.scrollY > 500) {
        backBtn.classList.add('show');
      } else {
        backBtn.classList.remove('show');
      }
    });
    
    backBtn.addEventListener('click', () => {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }

  // ---------- SMOOTH SCROLL FOR ANCHOR LINKS ----------
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
      e.preventDefault();
      const target = document.querySelector(this.getAttribute('href'));
      if (target) {
        target.scrollIntoView({ behavior: 'smooth' });
      }
    });
  });

  // ---------- HIGHLIGHT ACTIVE NAVIGATION ----------
  const currentPage = window.location.pathname.split('/').pop() || 'index.html';
  const navLinks = document.querySelectorAll('.nav-links a');
  navLinks.forEach(link => {
    if (link.getAttribute('href') === currentPage) {
      link.classList.add('active');
    }
  });

  // ---------- MEMBER PAGE HASH SCROLL ----------
  if (window.location.hash) {
    const element = document.querySelector(window.location.hash);
    if (element) {
      setTimeout(() => {
        element.scrollIntoView({ behavior: 'smooth' });
      }, 300);
    }
  }

  // ---------- PREVIEW CARD HOVER EFFECT ----------
  const previewCards = document.querySelectorAll('.preview-card');
  previewCards.forEach(card => {
    card.addEventListener('mouseenter', () => {
      card.style.transform = 'translateY(-15px) scale(1.02)';
    });
    card.addEventListener('mouseleave', () => {
      card.style.transform = 'translateY(0) scale(1)';
    });
  });

  console.log('BLACKPINK fan site loaded successfully! ♥');
});