// gallery.js - Gallery functionality

document.addEventListener('DOMContentLoaded', function() {
  // ===== FILTER FUNCTIONALITY =====
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
  let currentFilteredItems = [];

  // Main filter buttons
  filterBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      // Remove active class from all filter buttons
      filterBtns.forEach(b => b.classList.remove('active'));
      subFilterBtns.forEach(b => b.classList.remove('active'));
      
      // Add active class to clicked button
      btn.classList.add('active');
      
      const filter = btn.dataset.filter;
      filterItems(filter);
    });
  });

  // Sub-filter buttons (pairs)
  subFilterBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      // Remove active class from all buttons
      filterBtns.forEach(b => b.classList.remove('active'));
      subFilterBtns.forEach(b => b.classList.remove('active'));
      
      // Add active class to clicked button
      btn.classList.add('active');
      
      const filter = btn.dataset.filter;
      filterItems(filter);
    });
  });

  // Filter items function
  function filterItems(filter) {
    let visibleCount = 0;
    
    galleryItems.forEach(item => {
      const category = item.dataset.category;
      
      if (filter === 'all' || category === filter) {
        item.style.display = 'block';
        visibleCount++;
        
        // Add animation
        item.style.animation = 'none';
        item.offsetHeight; // Trigger reflow
        item.style.animation = 'fadeIn 0.5s ease';
      } else {
        item.style.display = 'none';
      }
    });
    
    // Update current filtered items for lightbox navigation
    currentFilteredItems = Array.from(galleryItems).filter(
      item => item.style.display !== 'none'
    );
    
    console.log(`Showing ${visibleCount} items`);
  }

  // ===== LIGHTBOX FUNCTIONALITY =====
  galleryItems.forEach((item, index) => {
    item.addEventListener('click', () => {
      const img = item.querySelector('img');
      const category = item.querySelector('.item-category')?.textContent || 'BLACKPINK';
      
      lightboxImg.src = img.src;
      caption.textContent = category;
      
      // Find index in filtered items
      const filteredItems = Array.from(galleryItems).filter(
        i => i.style.display !== 'none'
      );
      currentIndex = filteredItems.indexOf(item);
      
      lightbox.style.display = 'flex';
      document.body.style.overflow = 'hidden';
    });
  });

  // Close lightbox
  if (closeBtn) {
    closeBtn.addEventListener('click', () => {
      lightbox.style.display = 'none';
      document.body.style.overflow = 'auto';
    });
  }

  // Click outside to close
  lightbox.addEventListener('click', (e) => {
    if (e.target === lightbox) {
      lightbox.style.display = 'none';
      document.body.style.overflow = 'auto';
    }
  });

  // Previous button
  if (prevBtn) {
    prevBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      navigateLightbox(-1);
    });
  }

  // Next button
  if (nextBtn) {
    nextBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      navigateLightbox(1);
    });
  }

  // Keyboard navigation
  document.addEventListener('keydown', (e) => {
    if (lightbox.style.display === 'flex') {
      if (e.key === 'ArrowLeft') {
        navigateLightbox(-1);
      } else if (e.key === 'ArrowRight') {
        navigateLightbox(1);
      } else if (e.key === 'Escape') {
        lightbox.style.display = 'none';
        document.body.style.overflow = 'auto';
      }
    }
  });

  // Navigate lightbox
  function navigateLightbox(direction) {
    // Get currently visible items
    const visibleItems = Array.from(galleryItems).filter(
      item => item.style.display !== 'none'
    );
    
    if (visibleItems.length === 0) return;
    
    currentIndex = (currentIndex + direction + visibleItems.length) % visibleItems.length;
    const currentItem = visibleItems[currentIndex];
    const img = currentItem.querySelector('img');
    const category = currentItem.querySelector('.item-category')?.textContent || 'BLACKPINK';
    
    lightboxImg.src = img.src;
    caption.textContent = category;
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

  // Add fade animation
  const style = document.createElement('style');
  style.textContent = `
    @keyframes fadeIn {
      from { opacity: 0; transform: scale(0.9); }
      to { opacity: 1; transform: scale(1); }
    }
  `;
  document.head.appendChild(style);
});

