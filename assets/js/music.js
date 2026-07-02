// music.js - Complete Music Page Functionality

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
    const themeToggle = document.querySelector('.theme-toggle');
    if (themeToggle) {
        themeToggle.addEventListener('click', toggleTheme);
    }
    
    setupMainFilterTabs();
    setupSoloTabs();
    setupShowTracksButtons();
    setupBackToTop();
}

// ===== MAIN FILTER TABS =====
function setupMainFilterTabs() {
    const filterTabs = document.querySelectorAll('.filter-tab');
    const filterSections = document.querySelectorAll('.filter-section');
    
    if (filterTabs.length === 0 || filterSections.length === 0) return;
    
    filterTabs.forEach(tab => {
        tab.addEventListener('click', function(e) {
            e.preventDefault();
            
            filterTabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            const filter = this.dataset.filter;
            
            filterSections.forEach(section => {
                section.classList.remove('active');
                if (section.dataset.filter === filter) {
                    section.classList.add('active');
                }
            });
            
            if (filter === 'solo') {
                const jennieTab = document.querySelector('.solo-tab[data-member="jennie"]');
                if (jennieTab) {
                    setTimeout(() => {
                        jennieTab.click();
                    }, 100);
                }
            }
            
            setTimeout(() => {
                const activeSection = document.querySelector('.filter-section.active');
                if (activeSection) {
                    const guideHeader = activeSection.querySelector('.section-header');
                    if (guideHeader) {
                        const headerOffset = 100;
                        const elementPosition = guideHeader.getBoundingClientRect().top;
                        const offsetPosition = elementPosition + window.pageYOffset - headerOffset;
                        window.scrollTo({ top: offsetPosition, behavior: 'smooth' });
                    }
                }
            }, 200);
        });
    });
}

// ===== SOLO MEMBER TABS =====
function setupSoloTabs() {
    const soloTabs = document.querySelectorAll('.solo-tab');
    const memberSections = document.querySelectorAll('.member-section');
    
    if (soloTabs.length === 0 || memberSections.length === 0) return;
    
    soloTabs.forEach(tab => {
        tab.addEventListener('click', function(e) {
            e.preventDefault();
            
            soloTabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            const member = this.dataset.member;
            
            memberSections.forEach(section => {
                section.classList.remove('active');
                if (section.dataset.member === member) {
                    section.classList.add('active');
                }
            });
            
            setTimeout(() => {
                const activeMember = document.querySelector('.member-section.active');
                if (activeMember) {
                    const memberHeader = activeMember.querySelector('.member-header');
                    if (memberHeader) {
                        const headerOffset = 100;
                        const elementPosition = memberHeader.getBoundingClientRect().top;
                        const offsetPosition = elementPosition + window.pageYOffset - headerOffset;
                        window.scrollTo({ top: offsetPosition, behavior: 'smooth' });
                    }
                }
            }, 100);
        });
    });
}

// ===== SHOW TRACKS BUTTONS =====
function setupShowTracksButtons() {
    const showTracksBtns = document.querySelectorAll('.show-tracks-btn');
    
    showTracksBtns.forEach(btn => {
        btn.removeEventListener('click', toggleTracklist);
        btn.addEventListener('click', toggleTracklist);
    });
}

function toggleTracklist(e) {
    e.preventDefault();
    e.stopPropagation();
    
    const btn = e.currentTarget;
    let tracklist = null;
    const albumCard = btn.closest('.solo-album-card') || btn.closest('.album-card');
    
    if (albumCard) {
        tracklist = albumCard.querySelector('.tracklist');
    } else {
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
            tracklist.style.animation = 'slideDown 0.4s ease';
        } else {
            tracklist.style.display = 'none';
            btn.innerHTML = '<i class="fas fa-list"></i> SHOW ALL TRACKS';
        }
    }
}

// ===== PLAY ON YOUTUBE =====
function playOnYouTube(query) {
    let searchQuery = query.trim();
    
    // Add artist name if not present
    const artists = ['blackpink', 'jennie', 'rose', 'rosé', 'lisa', 'jisoo', 'zico', 'bruno mars', 'the weeknd', 'dominic fike', 'doechii', 'rosalía', 'taeyang', 'dj snake'];
    const hasArtist = artists.some(artist => searchQuery.toLowerCase().includes(artist));
    
    if (!hasArtist) {
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
    
    const encodedQuery = encodeURIComponent(searchQuery);
    window.open(`https://www.youtube.com/results?search_query=${encodedQuery}`, '_blank');
    showNotification(`Searching YouTube for: ${searchQuery}`);
}

// ===== SHOW NOTIFICATION =====
function showNotification(message) {
    const existingNotif = document.querySelector('.music-notification');
    if (existingNotif) existingNotif.remove();
    
    const notification = document.createElement('div');
    notification.className = 'music-notification';
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        if (notification.parentNode) notification.remove();
    }, 3000);
}

// ===== SONG INFO MODAL - ONLY YOUTUBE =====
function showSongInfo(title, album, length, writers, fact) {
    const modal = document.getElementById('songModal');
    if (!modal) return;
    
    document.getElementById('modalTitle').textContent = title;
    document.getElementById('modalAlbum').textContent = album;
    document.getElementById('modalLength').textContent = length;
    document.getElementById('modalWriters').textContent = writers;
    document.getElementById('modalFact').textContent = fact;
    
    const searchQuery = encodeURIComponent(title + ' ' + album);
    document.getElementById('youtubeLink').href = `https://www.youtube.com/results?search_query=${searchQuery}`;
    
    modal.style.display = 'flex';
}

// ===== CLOSE MODAL =====
function closeModal() {
    const modal = document.getElementById('songModal');
    if (modal) modal.style.display = 'none';
}

// ===== BACK TO TOP =====
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
    if (e.target === modal) closeModal();
});

// ===== EXPOSE FUNCTIONS GLOBALLY =====
window.showSongInfo = showSongInfo;
window.closeModal = closeModal;
window.playOnYouTube = playOnYouTube;