<?php
include(__DIR__ . "/../config/db.php");

$sql = "SELECT * FROM gallery ORDER BY id DESC";
$result = $conn->query($sql);

while($row = $result->fetch_assoc()){
?>
    <div class="gallery-item" data-category="<?php echo $row['category']; ?>">
        <img src="<?php echo $row['image']; ?>" alt="<?php echo $row['label']; ?>">
        <div class="item-overlay">
            <span class="item-category"><?php echo $row['label']; ?></span>
            <div class="item-actions">
                <button class="view-btn">
                    <i class="fas fa-search-plus"></i>
                </button>
            </div>
        </div>
    </div>
<?php
}
?>