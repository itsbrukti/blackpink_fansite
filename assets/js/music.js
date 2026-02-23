// music.js - Complete Music Page Functionality - FIXED

document.addEventListener('DOMContentLoaded', function() {
    // ===== INITIALIZATION =====
    initTheme();
    setupEventListeners();
    
    // Hide all tracklists initially
    document.querySelectorAll('.tracklist').forEach(tracklist => {
        tracklist.style.display = 'none';
    });
});

// ===== THEME FUNCTIONS =====
function initTheme() {
    const savedTheme = localStorage.getItem('darkMode');
    if (savedTheme === null) {
        document.body.classList.add('dark');
        localStorage.setItem('darkMode', 'true');
    } else if (savedTheme === 'true') {
        document.body.classList.add('dark');
    } else {
        document.body.classList.remove('dark');
    }
    updateThemeIcon();
}

function updateThemeIcon() {
    const toggle = document.querySelector('.theme-toggle i');
    if (toggle) {
        if (document.body.classList.contains('dark')) {
            toggle.className = 'fas fa-sun';
        } else {
            toggle.className = 'fas fa-moon';
        }
    }
}

function toggleTheme() {
    document.body.classList.toggle('dark');
    const isDark = document.body.classList.contains('dark');
    localStorage.setItem('darkMode', isDark);
    updateThemeIcon();
}

// ===== SETUP ALL EVENT LISTENERS =====
function setupEventListeners() {
    // Theme toggle
    const themeToggle = document.querySelector('.theme-toggle');
    if (themeToggle) {
        themeToggle.addEventListener('click', toggleTheme);
    }
    
    // Main filter tabs
    setupMainFilterTabs();
    
    // Solo member tabs
    setupSoloTabs();
    
    // Show tracks buttons
    setupShowTracksButtons();
    
    // Back to top
    setupBackToTop();
}

// ===== MAIN FILTER TABS FUNCTIONALITY =====
function setupMainFilterTabs() {
    const filterTabs = document.querySelectorAll('.filter-tab');
    const filterSections = document.querySelectorAll('.filter-section');
    
    if (filterTabs.length === 0 || filterSections.length === 0) {
        console.log('Filter tabs or sections not found');
        return;
    }
    
    filterTabs.forEach(tab => {
        tab.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Remove active class from all tabs
            filterTabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            const filter = this.dataset.filter;
            
            // Hide all sections
            filterSections.forEach(section => {
                section.classList.remove('active');
                if (section.dataset.filter === filter) {
                    section.classList.add('active');
                }
            });
            
            // If solo section is active, ensure Jennie's section is visible
            if (filter === 'solo') {
                const jennieTab = document.querySelector('.solo-tab[data-member="jennie"]');
                if (jennieTab) {
                    // Simulate click on Jennie tab
                    setTimeout(() => {
                        jennieTab.click();
                    }, 100);
                }
            }
            
            // Scroll to the guide text of the selected section
            setTimeout(() => {
                const activeSection = document.querySelector('.filter-section.active');
                if (activeSection) {
                    // Find the guide text within the active section
                    const guideHeader = activeSection.querySelector('.section-header');
                    if (guideHeader) {
                        // Scroll to the guide header with offset for navbar
                        const headerOffset = 100;
                        const elementPosition = guideHeader.getBoundingClientRect().top;
                        const offsetPosition = elementPosition + window.pageYOffset - headerOffset;
                        
                        window.scrollTo({
                            top: offsetPosition,
                            behavior: 'smooth'
                        });
                    } else {
                        // If no guide header, scroll to the section itself
                        activeSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                }
            }, 200); // Small delay to ensure section is visible
        });
    });
}

// ===== SOLO MEMBER TABS FUNCTIONALITY =====
function setupSoloTabs() {
    const soloTabs = document.querySelectorAll('.solo-tab');
    const memberSections = document.querySelectorAll('.member-section');
    
    if (soloTabs.length === 0 || memberSections.length === 0) {
        console.log('Solo tabs or member sections not found');
        return;
    }
    
    soloTabs.forEach(tab => {
        tab.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Remove active class from all tabs
            soloTabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            const member = this.dataset.member;
            
            // Hide all member sections
            memberSections.forEach(section => {
                section.classList.remove('active');
                if (section.dataset.member === member) {
                    section.classList.add('active');
                }
            });
            
            // Scroll to the member section header
            setTimeout(() => {
                const activeMember = document.querySelector('.member-section.active');
                if (activeMember) {
                    const memberHeader = activeMember.querySelector('.member-header');
                    if (memberHeader) {
                        const headerOffset = 100;
                        const elementPosition = memberHeader.getBoundingClientRect().top;
                        const offsetPosition = elementPosition + window.pageYOffset - headerOffset;
                        
                        window.scrollTo({
                            top: offsetPosition,
                            behavior: 'smooth'
                        });
                    }
                }
            }, 100);
        });
    });
}

// ===== SHOW TRACKS BUTTONS FUNCTIONALITY - FIXED =====
function setupShowTracksButtons() {
    // Get all show tracks buttons
    const showTracksBtns = document.querySelectorAll('.show-tracks-btn');
    
    showTracksBtns.forEach(btn => {
        // Remove any existing event listeners
        btn.removeEventListener('click', toggleTracklist);
        // Add new event listener
        btn.addEventListener('click', toggleTracklist);
    });
}

// Toggle tracklist function
function toggleTracklist(e) {
    e.preventDefault();
    e.stopPropagation();
    
    const btn = e.currentTarget;
    
    // Find the tracklist - it could be in the same album card or a sibling
    let tracklist = null;
    
    // Check if we're in a solo album card or regular album card
    const albumCard = btn.closest('.solo-album-card') || btn.closest('.album-card');
    
    if (albumCard) {
        tracklist = albumCard.querySelector('.tracklist');
    } else {
        // If no album card, look for next sibling or parent
        tracklist = btn.nextElementSibling;
        if (!tracklist || !tracklist.classList.contains('tracklist')) {
            tracklist = btn.parentElement?.querySelector('.tracklist');
        }
    }
    
    if (tracklist) {
        const isHidden = tracklist.style.display === 'none' || !tracklist.style.display;
        
        if (isHidden) {
            tracklist.style.display = 'block';
            btn.innerHTML = '<i class="fas fa-chevron-up"></i> HIDE TRACKS';
            
            // Animate
            tracklist.style.animation = 'slideDown 0.4s ease';
        } else {
            tracklist.style.display = 'none';
            btn.innerHTML = '<i class="fas fa-list"></i> SHOW ALL TRACKS';
        }
    } else {
        console.log('Tracklist not found for button:', btn);
    }
}

// ===== PLAY ON YOUTUBE FUNCTION =====
function playOnYouTube(query) {
    // Clean up the query for better search results
    let searchQuery = query.trim();
    
    // Add artist name if not present
    if (!searchQuery.toLowerCase().includes('blackpink') && 
        !searchQuery.toLowerCase().includes('jennie') &&
        !searchQuery.toLowerCase().includes('rose') &&
        !searchQuery.toLowerCase().includes('rosé') &&
        !searchQuery.toLowerCase().includes('lisa') &&
        !searchQuery.toLowerCase().includes('jisoo') &&
        !searchQuery.toLowerCase().includes('zico') &&
        !searchQuery.toLowerCase().includes('bruno mars') &&
        !searchQuery.toLowerCase().includes('the weeknd')) {
        
        // Try to determine which artist
        if (searchQuery.includes('SOLO') || searchQuery.includes('Mantra') || searchQuery.includes('You & Me')) {
            searchQuery = 'Jennie ' + searchQuery;
        } else if (searchQuery.includes('APT') || searchQuery.includes('On The Ground') || searchQuery.includes('Gone')) {
            searchQuery = 'Rosé ' + searchQuery;
        } else if (searchQuery.includes('LALISA') || searchQuery.includes('Money') || searchQuery.includes('Rockstar')) {
            searchQuery = 'Lisa ' + searchQuery;
        } else if (searchQuery.includes('Flower') || searchQuery.includes('Earthquake') || searchQuery.includes('All Eyes On Me')) {
            searchQuery = 'Jisoo ' + searchQuery;
        } else {
            searchQuery = 'BLACKPINK ' + searchQuery;
        }
    }
    
    // Encode for URL
    const encodedQuery = encodeURIComponent(searchQuery);
    
    // Open YouTube search in new tab
    window.open(`https://www.youtube.com/results?search_query=${encodedQuery}`, '_blank');
    
    // Show a small notification
    showNotification(`Searching YouTube for: ${searchQuery}`);
}

// ===== SHOW NOTIFICATION =====
function showNotification(message) {
    // Remove existing notification if any
    const existingNotif = document.querySelector('.music-notification');
    if (existingNotif) {
        existingNotif.remove();
    }
    
    // Create notification
    const notification = document.createElement('div');
    notification.className = 'music-notification';
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    // Remove after animation
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 3000);
}

// ===== SONG INFO MODAL =====
function showSongInfo(title, album, length, writers, fact) {
    const modal = document.getElementById('songModal');
    if (!modal) return;
    
    document.getElementById('modalTitle').textContent = title;
    document.getElementById('modalAlbum').textContent = album;
    document.getElementById('modalLength').textContent = length;
    document.getElementById('modalWriters').textContent = writers;
    document.getElementById('modalFact').textContent = fact;
    
    // Update links to YouTube
    const searchQuery = encodeURIComponent(title + ' ' + album);
    document.getElementById('youtubeLink').href = `https://www.youtube.com/results?search_query=${searchQuery}`;
    document.getElementById('spotifyLink').href = `https://open.spotify.com/search/${searchQuery}`;
    document.getElementById('appleLink').href = `https://music.apple.com/search?term=${searchQuery}`;
    
    modal.style.display = 'flex';
}

// ===== CLOSE MODAL =====
function closeModal() {
    const modal = document.getElementById('songModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

// ===== BACK TO TOP FUNCTIONALITY =====
function setupBackToTop() {
    const backBtn = document.getElementById('backToTop');
    if (!backBtn) return;
    
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

// ===== CLICK OUTSIDE MODAL =====
window.addEventListener('click', (e) => {
    const modal = document.getElementById('songModal');
    if (e.target === modal) {
        closeModal();
    }
});

// ===== EXPOSE FUNCTIONS GLOBALLY =====
window.showSongInfo = showSongInfo;
window.closeModal = closeModal;
window.playOnYouTube = playOnYouTube;