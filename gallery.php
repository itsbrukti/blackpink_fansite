<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>BLACKPINK · gallery</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800;900&family=Poppins:wght@300;400;500;600;700&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <!-- external CSS -->
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/gallery.css">
</head>

<body>
  <!-- Navigation loaded from common file -->
  <div include-html="nav.html"></div>

  <!-- Gallery Hero Section -->
  <div class="gallery-hero">
    <div class="gallery-hero-overlay"></div>
    <div class="gallery-hero-content">
      <h1 class="gallery-hero-title">BLACKPINK <span>GALLERY</span></h1>
      <p class="gallery-hero-subtitle">moments with blackpink</p>
    </div>
    <!-- Scroll Indicator -->
    <div class="scroll-indicator">
      <span>explore</span>
      <i class="fas fa-chevron-down"></i>
    </div>
  </div>

  <main class="gallery-page">
    <div class="container">

      <!-- Filter Guide -->
      <div class="filter-guide">
        <h3>📸 discover moments</h3>
        <p>Use the filters below to explore BLACKPINK's photos by category</p>
        

      <!-- Filter tabs - all categories -->
      <div class="gallery-filters">
        <button class="filter-btn active" data-filter="all">ALL</button>
        <button class="filter-btn" data-filter="group">GROUP</button>
        <button class="filter-btn" data-filter="concert">CONCERT</button>
        <button class="filter-btn" data-filter="bts">BEHIND</button>
        <button class="filter-btn" data-filter="jennie">JENNIE</button>
        <button class="filter-btn" data-filter="rose">ROSÉ</button>
        <button class="filter-btn" data-filter="lisa">LISA</button>
        <button class="filter-btn" data-filter="jisoo">JISOO</button>
      </div>

      <!-- Sub-filters for pair categories -->
      <div class="sub-filters">
        <span class="sub-filter-label"><i class="fas fa-heart"></i> pairs</span>
        <button class="sub-filter-btn" data-filter="chelisa">Chelisa</button>
        <button class="sub-filter-btn" data-filter="jenlisa">Jenlisa</button>
        <button class="sub-filter-btn" data-filter="lisoo">Lisoo</button>
        <button class="sub-filter-btn" data-filter="jenchaeng">JenChaeng</button>
        <button class="sub-filter-btn" data-filter="chaesoo">ChaeSoo</button>
        <button class="sub-filter-btn" data-filter="jensoo">JenSoo</button>
      </div>

      <!-- Gallery Grid -->
      <div class="gallery-grid" id="galleryGrid">
        <?php 
        // Include database connection from config folder
        require_once __DIR__ . '/config/db.php';
        
        // Get all photos from gallery table
        $result = $conn->query("SELECT * FROM gallery ORDER BY id DESC");
        
        if ($result && $result->num_rows > 0):
          while($row = $result->fetch_assoc()):
            $category = strtolower($row['category']);
        ?>
        <div class="gallery-item" data-category="<?php echo $category; ?>" data-id="<?php echo $row['id']; ?>">
          <img src="<?php echo htmlspecialchars($row['image']); ?>" alt="<?php echo htmlspecialchars($row['category']); ?>" loading="lazy">
          <div class="item-overlay">
            <div class="item-badge">
              <span class="item-category"><?php echo htmlspecialchars($row['label']); ?></span>
            </div>
            <div class="item-actions">
              <button class="view-btn" onclick="openLightbox(<?php echo $row['id']; ?>)">
                <i class="fas fa-expand"></i>
              </button>
            </div>
          </div>
        </div>
        <?php 
          endwhile;
        else:
        ?>
        <div class="no-images">
          <i class="fas fa-images"></i>
          <h3>No Photos Yet</h3>
          <p>Check back soon for new photos!</p>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </main>

  <!-- Lightbox Modal -->
  <div id="lightbox" class="lightbox">
    <span class="close">&times;</span>
    <div class="lightbox-content-wrapper">
      <img class="lightbox-content" id="lightboxImg">
      <div class="lightbox-caption" id="caption"></div>
      
    </div>
    <button class="lightbox-prev"><i class="fas fa-chevron-left"></i></button>
    <button class="lightbox-next"><i class="fas fa-chevron-right"></i></button>
  </div>

  <!-- Footer loaded from common file -->
  <div include-html="footer.php"></div>

  <!-- back to top -->
  <button id="backToTop" class="back-to-top"><i class="fas fa-arrow-up"></i></button>

  <!-- Load scripts -->
  <script src="assets/js/theme.js"></script>
  <script src="assets/js/include.js"></script>
  <script src="assets/js/gallery.js"></script>
</body>

</html>