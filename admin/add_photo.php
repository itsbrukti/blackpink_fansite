<?php
session_start();
include(__DIR__ . "/../config/db.php"); // or include("../config/db.php");

// Admin credentials (change these to your desired username and password)
define('ADMIN_USERNAME', 'blackpink_admin');
define('ADMIN_PASSWORD', 'blink1234');

// LOGIN LOGIC
if(isset($_POST['login'])){
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    if($username === ADMIN_USERNAME && $password === ADMIN_PASSWORD){
        $_SESSION['admin_logged_in'] = true;
        header("Location: add_photo.php");
        exit();
    } else {
        $login_error = "Invalid username or password!";
    }
}

// LOGOUT LOGIC
if(isset($_GET['logout'])){
    session_destroy();
    header("Location: add_photo.php");
    exit();
}

// CHECK IF LOGGED IN
$is_logged_in = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;

// DELETE PHOTO
if(isset($_GET['delete']) && $is_logged_in){
    $id = mysqli_real_escape_string($conn, $_GET['delete']);
    $sql = "DELETE FROM gallery WHERE id = $id";
    if($conn->query($sql)){
        $success_message = "Photo deleted successfully! ✅";
    } else {
        $error_message = "Error deleting photo: " . $conn->error;
    }
}

// ADD PHOTO - FIXED: Check for form submission using a hidden input
if(isset($_POST['submit_form']) && $_POST['submit_form'] === 'add_photo' && $is_logged_in){
    // Get and sanitize inputs
    $image = trim($_POST['image']);
    $category = trim($_POST['category']);
    $label = strtoupper($category);
    
    // Validate inputs
    if(empty($image) || empty($category)){
        $error_message = "Please fill in all fields!";
    } else {
        // Escape strings to prevent SQL injection
        $image = mysqli_real_escape_string($conn, $image);
        $category = mysqli_real_escape_string($conn, $category);
        $label = mysqli_real_escape_string($conn, $label);
        
        $sql = "INSERT INTO gallery (image, category, label) VALUES ('$image', '$category', '$label')";
        
        if($conn->query($sql)){
            $success_message = "Photo Added Successfully! ✅";
            // Clear POST data to prevent re-submission on refresh
            unset($_POST);
        } else {
            $error_message = "Database Error: " . $conn->error;
        }
    }
}

// FETCH PHOTOS
$result = $conn->query("SELECT * FROM gallery ORDER BY id DESC");
if(!$result){
    $error_message = "Error fetching photos: " . $conn->error;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BLACKPINK · Gallery Admin</title>
    
    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800;900&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 30px 20px;
        }

        .admin-container {
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Header Styles */
        .admin-header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 25px 35px;
            margin-bottom: 30px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .header-left i {
            font-size: 2.5rem;
            color: #ff69b4;
            background: linear-gradient(135deg, #ff69b4, #ff1493);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .header-left h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2.2rem;
            background: linear-gradient(135deg, #ff69b4, #ff1493);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .header-left p {
            color: #666;
            font-size: 0.95rem;
            margin-top: 5px;
        }

        .logout-btn {
            background: linear-gradient(135deg, #ff4757, #ee5a24);
            color: white;
            padding: 12px 30px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s;
            box-shadow: 0 10px 20px rgba(255, 71, 87, 0.3);
        }

        .logout-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(255, 71, 87, 0.4);
        }

        /* Login Form Styles */
        .login-container {
            max-width: 450px;
            margin: 50px auto;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 30px;
            padding: 40px;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.2);
            animation: fadeInUp 0.6s ease;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header i {
            font-size: 3.5rem;
            background: linear-gradient(135deg, #ff69b4, #ff1493);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 15px;
        }

        .login-header h2 {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            color: #333;
            margin-bottom: 10px;
        }

        .login-header p {
            color: #666;
            font-size: 0.95rem;
        }

        .login-error {
            background: linear-gradient(135deg, #ff4757, #ee5a24);
            color: white;
            padding: 15px 20px;
            border-radius: 15px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.95rem;
            animation: shake 0.5s ease;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
            font-size: 0.95rem;
        }

        .form-group label i {
            color: #ff69b4;
            margin-right: 8px;
        }

        .form-control {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #e0e0e0;
            border-radius: 15px;
            font-size: 1rem;
            transition: all 0.3s;
            font-family: 'Poppins', sans-serif;
        }

        .form-control:focus {
            outline: none;
            border-color: #ff69b4;
            box-shadow: 0 0 0 4px rgba(255, 105, 180, 0.1);
        }

        .login-btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #ff69b4, #ff1493);
            color: white;
            border: none;
            border-radius: 15px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.3s;
            box-shadow: 0 10px 20px rgba(255, 105, 180, 0.3);
        }

        .login-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(255, 105, 180, 0.4);
        }

        .login-footer {
            text-align: center;
            margin-top: 20px;
            color: #888;
            font-size: 0.9rem;
        }

        .login-footer i {
            color: #ff69b4;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        /* Admin Panel Styles */
        .add-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 30px;
            padding: 35px;
            margin-bottom: 30px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            animation: fadeInUp 0.6s ease;
        }

        .section-title {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
        }

        .section-title i {
            font-size: 2rem;
            color: #ff69b4;
        }

        .section-title h2 {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            color: #333;
        }

        .section-title p {
            color: #666;
            font-size: 0.95rem;
            margin-top: 5px;
        }

        .message {
            padding: 15px 25px;
            border-radius: 15px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideIn 0.5s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .message.success {
            background: linear-gradient(135deg, #00b894, #00cec9);
            color: white;
        }

        .message.error {
            background: linear-gradient(135deg, #ff4757, #ee5a24);
            color: white;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-select {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #e0e0e0;
            border-radius: 15px;
            font-size: 1rem;
            font-family: 'Poppins', sans-serif;
            background: white;
            cursor: pointer;
        }

        .form-select:focus {
            outline: none;
            border-color: #ff69b4;
        }

        .submit-btn {
            background: linear-gradient(135deg, #ff69b4, #ff1493);
            color: white;
            border: none;
            padding: 16px 30px;
            border-radius: 15px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.3s;
            width: fit-content;
            box-shadow: 0 10px 20px rgba(255, 105, 180, 0.3);
        }

        .submit-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(255, 105, 180, 0.4);
        }

        .submit-btn i {
            font-size: 1.1rem;
        }

        /* Image Preview */
        .image-preview {
            margin-top: 15px;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 15px;
            display: none;
        }

        .image-preview.show {
            display: block;
        }

        .image-preview img {
            max-width: 200px;
            max-height: 200px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .image-preview p {
            color: #666;
            font-size: 0.9rem;
            margin-top: 10px;
        }

        /* Table Styles */
        .table-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 30px;
            padding: 35px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 20px;
        }

        .table-header h3 {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            color: #333;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .table-header h3 i {
            color: #ff69b4;
        }

        .search-box {
            display: flex;
            align-items: center;
            gap: 10px;
            background: white;
            padding: 10px 20px;
            border-radius: 50px;
            border: 2px solid #e0e0e0;
        }

        .search-box i {
            color: #ff69b4;
        }

        .search-box input {
            border: none;
            outline: none;
            font-size: 0.95rem;
            width: 200px;
        }

        .table-responsive {
            overflow-x: auto;
            border-radius: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 20px;
            overflow: hidden;
        }

        th {
            background: linear-gradient(135deg, #ff69b4, #ff1493);
            color: white;
            font-weight: 600;
            padding: 18px 15px;
            font-size: 0.95rem;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
            color: #555;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover td {
            background: #fff5f9;
        }

        .photo-preview {
            width: 100px;
            height: 100px;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .photo-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .category-badge {
            background: linear-gradient(135deg, #ff69b4, #ff1493);
            color: white;
            padding: 6px 15px;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-block;
        }

        .delete-btn {
            background: linear-gradient(135deg, #ff4757, #ee5a24);
            color: white;
            padding: 10px 20px;
            border-radius: 50px;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
        }

        .delete-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(255, 71, 87, 0.3);
        }

        .empty-state {
            text-align: center;
            padding: 50px;
            color: #888;
        }

        .empty-state i {
            font-size: 4rem;
            color: #ff69b4;
            margin-bottom: 15px;
            opacity: 0.5;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .admin-header {
                flex-direction: column;
                text-align: center;
            }

            .header-left {
                flex-direction: column;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .submit-btn {
                width: 100%;
            }

            .table-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .search-box {
                width: 100%;
            }

            .search-box input {
                width: 100%;
            }
        }

        /* Loading Animation */
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php if(!$is_logged_in): ?>
            <!-- LOGIN FORM -->
            <div class="login-container">
                <div class="login-card">
                    <div class="login-header">
                        <i class="fas fa-lock"></i>
                        <h2>Admin Access</h2>
                        <p>Enter your credentials to manage gallery</p>
                    </div>

                    <?php if(isset($login_error)): ?>
                        <div class="login-error">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php echo $login_error; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="form-group">
                            <label><i class="fas fa-user"></i> Username</label>
                            <input type="text" name="username" class="form-control" placeholder="Enter username" required>
                        </div>

                        <div class="form-group">
                            <label><i class="fas fa-key"></i> Password</label>
                            <input type="password" name="password" class="form-control" placeholder="Enter password" required>
                        </div>

                        <button type="submit" name="login" class="login-btn">
                            <i class="fas fa-sign-in-alt"></i>
                            Login to Dashboard
                        </button>
                    </form>

                    <div class="login-footer">
                        <p><i class="fas fa-heart"></i> BLACKPINK Gallery Admin</p>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- ADMIN PANEL -->
            <div class="admin-header">
                <div class="header-left">
                    <i class="fas fa-crown"></i>
                    <div>
                        <h1>Gallery Management</h1>
                        <p><i class="fas fa-shield-alt"></i> Admin Dashboard · Manage your BLACKPINK gallery</p>
                    </div>
                </div>
                <a href="?logout=1" class="logout-btn" onclick="return confirm('Are you sure you want to logout?');">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>
            </div>

            <!-- DISPLAY MESSAGES -->
            <?php if(isset($success_message)): ?>
                <div class="message success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>

            <?php if(isset($error_message)): ?>
                <div class="message error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <!-- ADD PHOTO SECTION -->
            <div class="add-section">
                <div class="section-title">
                    <i class="fas fa-plus-circle"></i>
                    <div>
                        <h2>Add New Photo</h2>
                        <p>Upload new images to the gallery</p>
                    </div>
                </div>

                <form method="POST" id="addPhotoForm">
                    <!-- Hidden field to detect form submission -->
                    <input type="hidden" name="submit_form" value="add_photo">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-image"></i> Image URL</label>
                            <input type="url" name="image" class="form-control" placeholder="https://example.com/image.jpg" required id="imageUrl">
                            <div class="image-preview" id="imagePreview">
                                <img src="" alt="Preview" id="previewImg">
                                <p>Image preview will appear here</p>
                            </div>
                        </div>

                        <div class="form-group">
                            <label><i class="fas fa-tag"></i> Category</label>
                            <select name="category" class="form-select" required>
                                <option value="">Select Category</option>
                                <option value="group">👥 Group</option>
                                <option value="concert">🎤 Concert</option>
                                <option value="bts">🎬 Behind the Scenes</option>
                                <option value="jennie">💖 Jennie</option>
                                <option value="rose">🌹 Rosé</option>
                                <option value="lisa">⚡ Lisa</option>
                                <option value="jisoo">🌸 Jisoo</option>
                                <option value="chelisa">💕 Chelisa</option>
                                <option value="jenlisa">🔥 Jenlisa</option>
                                <option value="Lisoo">✨ Lisoo</option>
                                <option value="JenChaeng">🎀 JenChaeng</option>
                                <option value="ChaeSoo">💫 ChaeSoo</option>
                                <option value="JenSoo">💗 JenSoo</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="submit-btn" id="submitBtn">
                        <i class="fas fa-cloud-upload-alt"></i>
                        Add Photo to Gallery
                    </button>
                </form>
            </div>

            <!-- EXISTING PHOTOS TABLE -->
            <div class="table-section">
                <div class="table-header">
                    <h3><i class="fas fa-images"></i> Gallery Photos (<?php echo $result ? $result->num_rows : 0; ?>)</h3>
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchInput" placeholder="Search photos..." onkeyup="searchTable()">
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="galleryTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Image</th>
                                <th>Category</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if($result && $result->num_rows > 0):
                                while($row = $result->fetch_assoc()):
                            ?>
                            <tr>
                                <td><strong>#<?php echo $row['id']; ?></strong></td>
                                <td>
                                    <div class="photo-preview">
                                        <img src="<?php echo htmlspecialchars($row['image']); ?>" alt="Gallery Image" onerror="this.src='https://via.placeholder.com/100x100/ff69b4/ffffff?text=ERROR'">
                                    </div>
                                </td>
                                <td>
                                    <span class="category-badge">
                                        <?php echo ucfirst(htmlspecialchars($row['category'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="?delete=<?php echo $row['id']; ?>" 
                                       class="delete-btn"
                                       onclick="return confirmDelete('<?php echo addslashes($row['category']); ?> photo')">
                                        <i class="fas fa-trash-alt"></i>
                                        Delete
                                    </a>
                                </td>
                            </tr>
                            <?php 
                                endwhile;
                            else:
                            ?>
                            <tr>
                                <td colspan="4">
                                    <div class="empty-state">
                                        <i class="fas fa-images"></i>
                                        <h3>No Photos Yet</h3>
                                        <p>Add your first photo to the gallery using the form above</p>
                                    </div>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Confirm Delete Function
        function confirmDelete(category) {
            return confirm(`⚠️ Are you sure you want to delete this ${category}?\nThis action cannot be undone!`);
        }

        // Search Function
        function searchTable() {
            const input = document.getElementById('searchInput');
            const filter = input.value.toLowerCase();
            const table = document.getElementById('galleryTable');
            const rows = table.getElementsByTagName('tr');

            for (let i = 1; i < rows.length; i++) {
                const row = rows[i];
                const idCell = row.getElementsByTagName('td')[0];
                const categoryCell = row.getElementsByTagName('td')[2];
                
                if (idCell && categoryCell) {
                    const idText = idCell.textContent || idCell.innerText;
                    const categoryText = categoryCell.textContent || categoryCell.innerText;
                    
                    if (idText.toLowerCase().indexOf(filter) > -1 || 
                        categoryText.toLowerCase().indexOf(filter) > -1) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                }
            }
        }

        // Image Preview
        const imageUrlInput = document.getElementById('imageUrl');
        const imagePreview = document.getElementById('imagePreview');
        const previewImg = document.getElementById('previewImg');

        if(imageUrlInput) {
            imageUrlInput.addEventListener('input', function(e) {
                const url = e.target.value;
                if(url && (url.startsWith('http://') || url.startsWith('https://'))) {
                    previewImg.src = url;
                    imagePreview.classList.add('show');
                    
                    // Handle image load error
                    previewImg.onerror = function() {
                        previewImg.src = 'https://via.placeholder.com/200x200/ff69b4/ffffff?text=Invalid+URL';
                    };
                } else {
                    imagePreview.classList.remove('show');
                }
            });
        }

        // Form Submission Loading Effect
        document.getElementById('addPhotoForm')?.addEventListener('submit', function(e) {
            const btn = document.getElementById('submitBtn');
            btn.innerHTML = '<span class="loading"></span> Adding Photo...';
            btn.disabled = true;
        });

        // Auto-hide messages after 5 seconds
        setTimeout(() => {
            const messages = document.querySelectorAll('.message');
            messages.forEach(msg => {
                msg.style.transition = 'opacity 1s';
                msg.style.opacity = '0';
                setTimeout(() => msg.remove(), 1000);
            });
        }, 5000);

        // Add animation to table rows on load
        document.addEventListener('DOMContentLoaded', function() {
            const rows = document.querySelectorAll('#galleryTable tbody tr');
            rows.forEach((row, index) => {
                row.style.animation = `fadeInUp 0.3s ease ${index * 0.05}s both`;
            });
        });

        // Debug: Log form submission
        console.log('Page loaded. Admin logged in: <?php echo $is_logged_in ? "true" : "false"; ?>');
    </script>
</body>
</html>