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
  <!-- external CSS - only gallery styles, no nav/footer -->
  <link rel="stylesheet" href="assets/css/gallery.css">
  <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
  <!-- Navigation loaded from common file -->
  <div include-html="nav.html"></div>
  <div class="gallery-header">
    <div class="gallery-header-content">
      <h1 class="page-title">GALLERY</h1>
      <p class="page-subtitle">moments with blackpink</p>
    </div>
  </div>

  <main class="gallery-page">
    <div class="container">


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
        <span class="sub-filter-label">pairs:</span>
        <button class="sub-filter-btn" data-filter="chelisa">Chelisa</button>
        <button class="sub-filter-btn" data-filter="jenlisa">Jenlisa</button>
        <button class="sub-filter-btn" data-filter="Lisoo">Lisoo</button>
        <button class="sub-filter-btn" data-filter="JenChaeng">JenChaeng</button>
        <button class="sub-filter-btn" data-filter="ChaeSoo">ChaeSoo</button>
        <button class="sub-filter-btn" data-filter="JenSoo">JenSoo</button>
      </div>

      <div class="gallery-grid" id="galleryGrid">
        <?php include("includes/load_gallery.php"); ?>
      </div>



      <!-- Lightbox Modal -->
      <div id="lightbox" class="lightbox">
        <span class="close">&times;</span>
        <img class="lightbox-content" id="lightboxImg">
        <div id="caption" class="lightbox-caption"></div>
        <button class="lightbox-prev"><i class="fas fa-chevron-left"></i></button>
        <button class="lightbox-next"><i class="fas fa-chevron-right"></i></button>
      </div>

      <!-- Footer loaded from common file -->
      <div include-html="footer.html"></div>

      <!-- back to top -->
      <button id="backToTop" class="back-to-top"><i class="fas fa-arrow-up"></i></button>

      <!-- Load scripts -->
      <script src="assets/js/theme.js"></script>
      <script src="assets/js/include.js"></script>
      <script src="assets/js/gallery.js"></script>
      <script src="assets/js/main.js"></script>
</body>

</html>