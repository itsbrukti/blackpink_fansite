// gallery.js - Gallery Functionality with Working Download

document.addEventListener('DOMContentLoaded', function() {
  // ===== VARIABLES =====
  const filterBtns = document.querySelectorAll('.filter-btn');
  const subFilterBtns = document.querySelectorAll('.sub-filter-btn');
  const galleryItems = document.querySelectorAll('.gallery-item');
  const lightbox = document.getElementById('lightbox');
  const lightboxImg = document.getElementById('lightboxImg');
  const caption = document.getElementById('caption');
  const closeBtn = document.querySelector('.close');
  const prevBtn = document.querySelector('.lightbox-prev');
  const nextBtn = document.querySelector('.lightbox-next');
  
  let currentIndex = 0;
  let currentItems = [];
  let currentFilter = 'all';

  // ===== FILTER FUNCTIONALITY =====
  filterBtns.forEach(btn => {
    btn.addEventListener('click', function() {
      filterBtns.forEach(b => b.classList.remove('active'));
      subFilterBtns.forEach(b => b.classList.remove('active'));
      this.classList.add('active');
      currentFilter = this.dataset.filter;
      filterItems(currentFilter);
    });
  });

  subFilterBtns.forEach(btn => {
    btn.addEventListener('click', function() {
      filterBtns.forEach(b => b.classList.remove('active'));
      subFilterBtns.forEach(b => b.classList.remove('active'));
      this.classList.add('active');
      currentFilter = this.dataset.filter;
      filterItems(currentFilter);
    });
  });

  function filterItems(filter) {
    galleryItems.forEach(item => {
      const category = item.dataset.category;
      const shouldShow = filter === 'all' || category === filter;
      item.style.display = shouldShow ? 'block' : 'none';
      if (shouldShow) {
        item.style.animation = 'none';
        item.offsetHeight;
        item.style.animation = 'fadeIn 0.4s ease';
      }
    });
    currentItems = Array.from(galleryItems).filter(i => i.style.display !== 'none');
  }

  // ===== LIGHTBOX FUNCTIONALITY =====
  galleryItems.forEach((item) => {
    item.addEventListener('click', function() {
      const img = this.querySelector('img');
      const category = this.querySelector('.item-category')?.textContent || 'BLACKPINK';
      
      lightboxImg.src = img.src;
      caption.textContent = category;
      
      currentItems = Array.from(galleryItems).filter(i => i.style.display !== 'none');
      currentIndex = currentItems.indexOf(this);
      
      lightbox.style.display = 'flex';
      document.body.style.overflow = 'hidden';
      document.body.classList.add('lightbox-open');
    });
  });

  function closeLightbox() {
    lightbox.style.display = 'none';
    document.body.style.overflow = 'auto';
    document.body.classList.remove('lightbox-open');
  }

  if (closeBtn) {
    closeBtn.addEventListener('click', closeLightbox);
  }

  lightbox.addEventListener('click', (e) => {
    if (e.target === lightbox) {
      closeLightbox();
    }
  });

  document.addEventListener('keydown', (e) => {
    if (lightbox.style.display === 'flex') {
      if (e.key === 'ArrowLeft') navigateLightbox(-1);
      else if (e.key === 'ArrowRight') navigateLightbox(1);
      else if (e.key === 'Escape') closeLightbox();
    }
  });

  if (prevBtn) {
    prevBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      navigateLightbox(-1);
    });
  }

  if (nextBtn) {
    nextBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      navigateLightbox(1);
    });
  }

  function navigateLightbox(direction) {
    if (currentItems.length === 0) return;
    currentIndex = (currentIndex + direction + currentItems.length) % currentItems.length;
    const currentItem = currentItems[currentIndex];
    const img = currentItem.querySelector('img');
    const category = currentItem.querySelector('.item-category')?.textContent || 'BLACKPINK';
    lightboxImg.src = img.src;
    caption.textContent = category;
  }

  // ===== DOWNLOAD FUNCTION - FIXED =====
  window.downloadImage = function() {
    const imageUrl = lightboxImg.src;
    
    if (!imageUrl || imageUrl === '') {
      showNotification('No image to download', 'error');
      return;
    }
    
    showNotification('Downloading image...', 'success');
    
    try {
      // Create a temporary anchor element
      const link = document.createElement('a');
      link.href = imageUrl;
      
      // Generate filename from URL or use default
      let filename = imageUrl.split('/').pop() || 'blackpink-image.jpg';
      // Remove query parameters if any
      filename = filename.split('?')[0];
      link.download = filename;
      
      // Append to body, click, and remove
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      
      showNotification('Download started! ✅', 'success');
    } catch (error) {
      // If download fails (cross-origin), open in new tab
      showNotification('Opening in new tab for download', 'info');
      window.open(imageUrl, '_blank');
    }
  };

  // ===== NOTIFICATION SYSTEM =====
  function showNotification(message, type = 'info') {
    const existing = document.querySelector('.gallery-notification');
    if (existing) existing.remove();
    
    const notification = document.createElement('div');
    notification.className = 'gallery-notification';
    const colors = {
      success: '#ff69b4',
      info: '#4a90d9',
      error: '#ff4444'
    };
    
    notification.style.cssText = `
      position: fixed;
      bottom: 120px;
      left: 50%;
      transform: translateX(-50%);
      background: ${colors[type] || colors.info};
      color: white;
      padding: 14px 28px;
      border-radius: 50px;
      font-size: 0.95rem;
      font-weight: 500;
      z-index: 10002;
      box-shadow: 0 10px 30px rgba(0,0,0,0.3);
      animation: slideUpFade 3s ease forwards;
      border: 2px solid rgba(255,255,255,0.2);
      backdrop-filter: blur(10px);
      font-family: 'Poppins', sans-serif;
      max-width: 90%;
      text-align: center;
    `;
    
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => {
      notification.remove();
    }, 3000);
  }

  // Add notification animation
  if (!document.querySelector('#notification-style')) {
    const style = document.createElement('style');
    style.id = 'notification-style';
    style.textContent = `
      @keyframes slideUpFade {
        0% { opacity: 0; transform: translateX(-50%) translateY(20px); }
        10% { opacity: 1; transform: translateX(-50%) translateY(0); }
        90% { opacity: 1; transform: translateX(-50%) translateY(0); }
        100% { opacity: 0; transform: translateX(-50%) translateY(-20px); }
      }
    `;
    document.head.appendChild(style);
  }

  // ===== BACK TO TOP =====
  const backBtn = document.getElementById('backToTop');
  window.addEventListener('scroll', () => {
    if (window.scrollY > 500) {
      backBtn.classList.add('show');
    } else {
      backBtn.classList.remove('show');
    }
  });
  
  if (backBtn) {
    backBtn.addEventListener('click', () => {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }

  // ===== OPEN LIGHTBOX FROM VIEW BUTTON =====
  window.openLightbox = function(id) {
    const item = document.querySelector(`.gallery-item[data-id="${id}"]`);
    if (item) {
      item.click();
    }
  };

  // ===== INITIAL FILTER =====
  setTimeout(() => {
    filterItems('all');
  }, 100);

  console.log('Gallery loaded successfully! ♥');
});