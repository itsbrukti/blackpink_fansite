// assets/js/theme.js - Theme toggle functionality with DARK MODE AS DEFAULT

// Initialize theme based on localStorage or default to dark
function initTheme() {
  // Check if there's a saved preference
  const savedTheme = localStorage.getItem('darkMode');
  
  if (savedTheme === null) {
    // No saved preference - default to DARK MODE
    document.body.classList.add('dark');
    localStorage.setItem('darkMode', 'true');
  } else if (savedTheme === 'true') {
    // Saved preference is dark
    document.body.classList.add('dark');
  } else {
    // Saved preference is light
    document.body.classList.remove('dark');
  }
  
  updateThemeIcon();
}

// Update theme icon based on current theme
function updateThemeIcon() {
  const toggle = document.querySelector('.theme-toggle i');
  if (toggle) {
    if (document.body.classList.contains('dark')) {
      toggle.className = 'fas fa-sun';  // Sun icon for dark mode (to switch to light)
    } else {
      toggle.className = 'fas fa-moon'; // Moon icon for light mode (to switch to dark)
    }
  }
}

// Toggle theme function
function toggleTheme() {
  document.body.classList.toggle('dark');
  const isDark = document.body.classList.contains('dark');
  localStorage.setItem('darkMode', isDark);
  updateThemeIcon();
}

// Set up theme toggle event listener
function setupThemeToggle() {
  const toggle = document.querySelector('.theme-toggle');
  if (toggle) {
    // Remove any existing event listeners
    const newToggle = toggle.cloneNode(true);
    toggle.parentNode.replaceChild(newToggle, toggle);
    
    // Add new event listener
    newToggle.addEventListener('click', toggleTheme);
  }
}

// Run when page loads
document.addEventListener('DOMContentLoaded', function() {
  initTheme();
  // Wait a bit for nav to load, then setup toggle
  setTimeout(setupThemeToggle, 300);
});

// Also run when dynamic content is loaded
window.addEventListener('load', function() {
  setTimeout(setupThemeToggle, 300);
});