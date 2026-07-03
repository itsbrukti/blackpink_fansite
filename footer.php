<?php
// Start session at the VERY TOP before any HTML output
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check for messages passed via session
$subscribe_message = '';
$message_type = '';
if (isset($_SESSION['subscribe_message'])) {
    $subscribe_message = $_SESSION['subscribe_message'];
    $message_type = $_SESSION['message_type'];
    unset($_SESSION['subscribe_message']);
    unset($_SESSION['message_type']);
}
?>
<!-- footer.php - Common Footer with Subscribe Form -->
<style>
/* FORCE FOOTER PINK BACKGROUND - OVERRIDE ANYTHING */
.footer {
  background: rgba(235, 195, 210, 0.95) !important;
  color: #4a4a4a !important;
}

body.dark .footer {
  background: rgba(18, 18, 18, 0.95) !important;
  color: #f0f0f0 !important;
}

.footer-wave svg path {
  fill: rgba(235, 195, 210, 0.95) !important;
}

body.dark .footer-wave svg path {
  fill: rgba(18, 18, 18, 0.95) !important;
}

.footer-subscribe {
  background: rgba(225, 175, 195, 0.5) !important;
}

body.dark .footer-subscribe {
  background: rgba(18, 18, 18, 0.5) !important;
}

/* ===== FOOTER TEXT COLORS - BRIGHT MODE ===== */
.footer .subscribe-title {
  color: #c45d7a !important; /* Dark pink for visibility */
}

body.dark .footer .subscribe-title {
  color: #ffb6c1 !important;
}

.footer .subscribe-description {
  color: #4a4a4a !important;
}

body.dark .footer .subscribe-description {
  color: #ccc !important;
}

.footer .form-note {
  color: #6b6b6b !important;
}

body.dark .footer .form-note {
  color: #999 !important;
}

.footer .footer-logo {
  color: #c45d7a !important;
}

body.dark .footer .footer-logo {
  color: #ffb6c1 !important;
}

.footer .footer-logo span {
  color: #c45d7a !important;
}

body.dark .footer .footer-logo span {
  color: #ff69b4 !important;
}

.footer .footer-tagline {
  color: #4a4a4a !important;
}

body.dark .footer .footer-tagline {
  color: #ccc !important;
}

.footer .footer-links-column h4 {
  color: #c45d7a !important;
}

body.dark .footer .footer-links-column h4 {
  color: #ffb6c1 !important;
}

.footer .footer-links-column a {
  color: #4a4a4a !important;
}

body.dark .footer .footer-links-column a {
  color: #ccc !important;
}

.footer .footer-links-column a i {
  color: #c45d7a !important;
}

body.dark .footer .footer-links-column a i {
  color: #ffb6c1 !important;
}

.footer .footer-links-column a:hover {
  color: #c45d7a !important;
}

body.dark .footer .footer-links-column a:hover {
  color: #ffb6c1 !important;
}

.footer .footer-copyright p {
  color: #4a4a4a !important;
}

body.dark .footer .footer-copyright p {
  color: #aaa !important;
}

.footer .footer-copyright .credit {
  color: #6b6b6b !important;
}

body.dark .footer .footer-copyright .credit {
  color: #888 !important;
}

.footer .footer-copyright .credit i {
  color: #c45d7a !important;
}

body.dark .footer .footer-copyright .credit i {
  color: #ff69b4 !important;
}

.footer .stat-item {
  color: #c45d7a !important;
  background: rgba(196, 93, 122, 0.1) !important;
  border-color: rgba(196, 93, 122, 0.2) !important;
}

body.dark .footer .stat-item {
  color: #ffb6c1 !important;
  background: rgba(255, 105, 180, 0.08) !important;
  border-color: rgba(255, 105, 180, 0.2) !important;
}

.footer .stat-item i {
  color: #c45d7a !important;
}

body.dark .footer .stat-item i {
  color: #ffb6c1 !important;
}

/* ===== SUBSCRIBE INPUT ===== */
.footer .subscribe-input {
  color: #4a4a4a !important;
  background: rgba(255, 255, 255, 0.9) !important;
  border-color: rgba(196, 93, 122, 0.3) !important;
}

body.dark .footer .subscribe-input {
  color: #f0f0f0 !important;
  background: rgba(255, 255, 255, 0.05) !important;
  border-color: rgba(255, 105, 180, 0.3) !important;
}

.footer .subscribe-input::placeholder {
  color: #999 !important;
}

body.dark .footer .subscribe-input::placeholder {
  color: #666 !important;
}

/* ===== SUBSCRIBE BUTTON ===== */
.footer .subscribe-button {
  background: linear-gradient(135deg, #c45d7a, #b35d7a) !important;
  color: white !important;
}

.footer .subscribe-button:hover {
  background: linear-gradient(135deg, #a04d6a, #8a3d5a) !important;
  box-shadow: 0 10px 30px rgba(196, 93, 122, 0.4) !important;
}

body.dark .footer .subscribe-button {
  background: linear-gradient(135deg, #ff69b4, #ff1493) !important;
}

body.dark .footer .subscribe-button:hover {
  background: linear-gradient(135deg, #ff1493, #e0128a) !important;
}

/* ===== SUBSCRIBE MESSAGE ===== */
.footer .subscribe-message {
  font-weight: 500 !important;
}

.footer .subscribe-message.success {
  background: rgba(0, 184, 148, 0.15) !important;
  color: #00a884 !important;
  border: 1px solid #00b894 !important;
}

.footer .subscribe-message.error {
  background: rgba(255, 71, 87, 0.15) !important;
  color: #d63031 !important;
  border: 1px solid #ff4757 !important;
}

.footer .subscribe-message.warning {
  background: rgba(253, 203, 110, 0.15) !important;
  color: #d48a20 !important;
  border: 1px solid #fdcb6e !important;
}

body.dark .footer .subscribe-message.success {
  background: rgba(0, 184, 148, 0.25) !important;
  color: #55efc4 !important;
}

body.dark .footer .subscribe-message.error {
  background: rgba(255, 71, 87, 0.25) !important;
  color: #ff6b81 !important;
}

body.dark .footer .subscribe-message.warning {
  background: rgba(253, 203, 110, 0.25) !important;
  color: #fdcb6e !important;
}

/* ===== SUBSCRIBE MESSAGE ANIMATION ===== */
.subscribe-message {
  padding: 12px 20px;
  border-radius: 15px;
  text-align: center;
  width: 100%;
  margin-bottom: 15px;
  animation: slideDown 0.3s ease;
  transition: opacity 0.5s ease;
}

@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>

<footer class="footer">
  <div class="footer-wave">
    <svg viewBox="0 0 1200 120" preserveAspectRatio="none">
      <path d="M0,0V46.29c47.79,22.2,103.59,32.17,158,28,70.36-5.37,136.33-33.31,206.8-37.5C438.64,32.43,512.34,53.67,583,72.05c69.27,18,138.3,24.88,209.4,13.08,36.15-6,69.85-17.84,104.45-29.34C989.49,25,1113-14.29,1200,52.47V0Z" opacity=".25"></path>
      <path d="M0,0V15.81C13,21.25,27.93,25.42,44.24,28.45c69.76,12.2,136.58,5,205.66-5.32C334.56,14.84,383.74,38,462.2,55.58c74.36,16.45,151.18,18.59,226.14,5.54,40.94-7.12,77.57-21.72,113.12-36.69C891.06,8.61,1047.82-21.13,1200,38.82V0Z" opacity=".5"></path>
      <path d="M0,0V5.63C69.88,17,140.25,25.7,211.41,27.32c79.57,1.84,154.72-9.42,232-20.53,41.35-6,82.2-13.72,124.44-16.13C633.69-5.6,712.81,5,787.31,17.34c54.68,9,110.41,14.37,166,7.06,34.51-4.54,67-14.89,99.48-23.81C1111.76-14.5,1194.9-20.8,1200,4.28V0Z"></path>
    </svg>
  </div>

  <div class="footer-content">
    <div class="footer-container">
      
      <!-- Subscribe Section -->
      <div class="footer-subscribe">
        <div class="subscribe-content">
          <div class="subscribe-text">
            <h3 class="subscribe-title">never miss an update</h3>
            <p class="subscribe-description">Subscribe to get the latest BLACKPINK news, releases, and exclusive content directly in your inbox.</p>
          </div>

          <?php if (!empty($subscribe_message)): ?>
          <div class="subscribe-message <?php echo $message_type; ?>">
              <?php echo htmlspecialchars($subscribe_message); ?>
          </div>
          <?php endif; ?>

          <form class="subscribe-form" method="POST" action="subscribe.php">
            <div class="form-group">
              <input type="email" name="email" class="subscribe-input" placeholder="Your email address" required>
              <button type="submit" name="subscribe_btn" class="subscribe-button">
                <span>subscribe</span>
                <i class="fas fa-paper-plane"></i>
              </button>
            </div>
            <p class="form-note">We respect your privacy · unsubscribe anytime</p>
          </form>
        </div>
      </div>

      <!-- Footer Main Content -->
      <div class="footer-main">
        <!-- Brand Section -->
        <div class="footer-brand">
          <div class="footer-logo-wrapper">
            <div class="footer-logo">BLACKPINK<span>·blink</span></div>
            <div class="footer-heart">🖤💗</div>
          </div>
          <p class="footer-tagline">dedicated to the biggest blinks around the world</p>
        </div>

        <!-- Links Section -->
        <div class="footer-links-group">
          <div class="footer-links-column">
            <h4>explore</h4>
            <a href="index.html"><i class="fas fa-chevron-right"></i> home</a>
            <a href="members.html"><i class="fas fa-chevron-right"></i> members</a>
            <a href="music.html"><i class="fas fa-chevron-right"></i> music</a>
            <a href="gallery.php"><i class="fas fa-chevron-right"></i> gallery</a>
            <a href="about.html"><i class="fas fa-chevron-right"></i> about</a>
          </div>
          <div class="footer-links-column">
            <h4>members</h4>
            <a href="members.html#jisoo"><i class="fas fa-chevron-right"></i> jisoo</a>
            <a href="members.html#jennie"><i class="fas fa-chevron-right"></i> jennie</a>
            <a href="members.html#rose"><i class="fas fa-chevron-right"></i> rosé</a>
            <a href="members.html#lisa"><i class="fas fa-chevron-right"></i> lisa</a>
          </div>
        </div>
      </div>

      <!-- Footer Bottom -->
      <div class="footer-bottom">
        <div class="footer-copyright">
          <p>fan-based website · all information for blinks ♥</p>
          <p class="credit">created with <i class="fas fa-heart" style="color: #ff69b4;"></i> · no copyright infringement intended</p>
        </div>
        <div class="footer-stats">
          <span class="footer-stat-item"><i class="fas fa-envelope"></i> 1000+ subscribers</span>
          <span class="footer-stat-item"><i class="fas fa-users"></i> 1M+ monthly visitors</span>
        </div>
      </div>
    </div>
  </div>
</footer>

<script>
// Auto-hide message after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const message = document.querySelector('.subscribe-message');
    if (message) {
        setTimeout(function() {
            message.style.opacity = '0';
            setTimeout(function() {
                message.style.display = 'none';
            }, 500);
        }, 5000);
    }
});
</script>