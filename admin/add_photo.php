<!-- username:brukti
password:blink123 -->

<?php
// ===== SESSION START AND AUTH CHECK - MUST BE FIRST =====
session_start();
if (
    !isset($_SESSION['admin_logged_in']) ||
    $_SESSION['admin_logged_in'] !== true
) {
    $is_logged_in = false;
} else {
    $is_logged_in = true;
}
// Check if user is logged in - if not, show login form
$is_logged_in = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;

// If not logged in, we still need to process login attempts
// But don't process any other actions

require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../vendor/autoload.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ===== ADMIN LOGIN =====
if(isset($_POST['login'])){
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $stmt = $conn->prepare("SELECT id, username, password FROM admin_users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($row = $result->fetch_assoc()){
        if(password_verify($password, $row['password'])){
            $_SESSION['admin_id'] = $row['id'];
            $_SESSION['admin_username'] = $row['username'];
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['login_time'] = time();
            
            header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
            header("Cache-Control: post-check=0, pre-check=0", false);
            header("Pragma: no-cache");
            header("Location: add_photo.php");
            exit();
        } else {
            $login_error = "Invalid username or password!";
        }
    } else {
        $login_error = "Invalid username or password!";
    }
    $stmt->close();
}

// ===== LOGOUT =====
if(isset($_GET['logout'])){

    $_SESSION = [];

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();

        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    session_destroy();

    header("Location: add_photo.php");
    exit();
}

// ===== AFTER LOGIN/LOGOUT PROCESSING, RE-CHECK AUTH STATUS =====
$is_logged_in = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;

// ===== ONLY PROCESS DASHBOARD ACTIONS IF LOGGED IN =====
if($is_logged_in){
    // ===== UPDATE ADMIN CREDENTIALS =====
    if(isset($_POST['update_admin'])){
        $new_username = trim($_POST['new_username']);
        $new_password = trim($_POST['new_password']);
        $confirm_password = trim($_POST['confirm_password']);
        
        if(!empty($new_username) && !empty($new_password) && $new_password === $confirm_password){
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE admin_users SET username = ?, password = ? WHERE id = ?");
            $stmt->bind_param("ssi", $new_username, $hashed_password, $_SESSION['admin_id']);
            
            if($stmt->execute()){
                $_SESSION['admin_username'] = $new_username;
                $admin_message = "✅ Admin credentials updated successfully!";
                $admin_message_type = "success";
            } else {
                $admin_message = "❌ Error updating credentials.";
                $admin_message_type = "error";
            }
            $stmt->close();
        } else {
            $admin_message = "⚠️ Please fill all fields and ensure passwords match.";
            $admin_message_type = "warning";
        }
    }

    // ===== SEND EMAIL TO ALL SUBSCRIBERS =====
    if(isset($_POST['send_email'])){
        $subject = trim($_POST['email_subject']);
        $message = trim($_POST['email_message']);
        $from_email = trim($_POST['from_email']);
        
        if(!empty($subject) && !empty($message) && !empty($from_email)){
            $subscribers = $conn->query("SELECT email FROM subscribers WHERE is_active = 1");
            $sent_count = 0;
            $failed_count = 0;
            
            while($sub = $subscribers->fetch_assoc()){
                $mail = new PHPMailer(true);

                try {
                    $mail->isSMTP();

                    $mail_host = getenv('MAIL_HOST') ?: 'smtp.gmail.com';
                    $mail_port = (int) (getenv('MAIL_PORT') ?: 587);
                    $mail_username = getenv('MAIL_USERNAME') ?: 'zemedbiruktawit@gmail.com';
                    $mail_password = getenv('MAIL_PASSWORD') ?: 'xwvogybpdmmbebvm';
                    $mail_encryption = getenv('MAIL_ENCRYPTION') ?: 'tls';
                    $mail_from = getenv('MAIL_FROM') ?: 'zemedbiruktawit@gmail.com';
                    $mail_from_name = getenv('MAIL_FROM_NAME') ?: 'BLACKPINK Fansite';

                    $mail->Host = $mail_host;
                    $mail->Port = $mail_port;
                    $mail->SMTPAuth = !empty($mail_username) && !empty($mail_password);
                    $mail->Username = $mail_username;
                    $mail->Password = $mail_password;
                    $mail->SMTPSecure = $mail_encryption;
                    $mail->CharSet = 'UTF-8';

                    if ($mail_host === 'smtp.gmail.com') {
                        $mail->SMTPSecure = 'tls';
                        $mail->Port = 587;
                    }

                    $mail->setFrom($mail_from, $mail_from_name);
                    $mail->addAddress($sub['email']);
                    $mail->Subject = $subject;
                    $mail->isHTML(true);

                    $html_message = "
                    <html>
                    <head>
                        <style>
                            body { font-family: Arial, sans-serif; background: #f5f0ed; padding: 20px; }
                            .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
                            .header { text-align: center; padding-bottom: 20px; border-bottom: 2px solid #ff69b4; }
                            .header h1 { color: #ff69b4; font-family: 'Playfair Display', serif; }
                            .content { padding: 20px 0; }
                            .footer { text-align: center; padding-top: 20px; border-top: 1px solid #eee; color: #888; font-size: 0.9rem; }
                        </style>
                    </head>
                    <body>
                        <div class='container'>
                            <div class='header'>
                                <h1>🖤💗 BLACKPINK UPDATE</h1>
                            </div>
                            <div class='content'>
                                <p>Dear BLINK,</p>
                                <p>" . nl2br(htmlspecialchars($message)) . "</p>
                            </div>
                            <div class='footer'>
                                <p>This email was sent to all BLINK subscribers.</p>
                                <p>© BLACKPINK Fansite · All rights reserved</p>
                            </div>
                        </div>
                    </body>
                    </html>
                    ";

                    $mail->Body = $html_message;
                    $mail->AltBody = strip_tags($message);
                    $mail->send();
                    $sent_count++;
                } catch (Exception $e) {
                    $failed_count++;
                    file_put_contents(
                        __DIR__ . '/email_failures.log',
                        date('Y-m-d H:i:s') . " | {$sub['email']} | {$subject} | {$mail->ErrorInfo}\n",
                        FILE_APPEND
                    );
                }
            }
            
            if($sent_count === 0 && $failed_count > 0){
                $email_result = "⚠️ Email sending could not be completed. Check your SMTP settings or Gmail app password.";
                $email_result_type = "warning";
            } else {
                $email_result = "📧 Email sent to $sent_count subscribers";
                if($failed_count > 0){
                    $email_result .= " (Failed: $failed_count)";
                }
                $email_result_type = "success";
            }
        } else {
            $email_result = "⚠️ Please fill in all email fields.";
            $email_result_type = "warning";
        }
    }

    // ===== DELETE PHOTO =====
    if(isset($_GET['delete'])){
        $id = mysqli_real_escape_string($conn, $_GET['delete']);
        $sql = "DELETE FROM gallery WHERE id = $id";
        if($conn->query($sql)){
            $success_message = "Photo deleted successfully! ✅";
        } else {
            $error_message = "Error deleting photo: " . $conn->error;
        }
    }

    // ===== ADD PHOTO =====
    if(isset($_POST['submit_form']) && $_POST['submit_form'] === 'add_photo'){
        $image = trim($_POST['image']);
        $category = trim($_POST['category']);
        $label = strtoupper($category);
        
        if(empty($image) || empty($category)){
            $error_message = "Please fill in all fields!";
        } else {
            $image = mysqli_real_escape_string($conn, $image);
            $category = mysqli_real_escape_string($conn, $category);
            $label = mysqli_real_escape_string($conn, $label);
            
            $sql = "INSERT INTO gallery (image, category, label) VALUES ('$image', '$category', '$label')";
            
            if($conn->query($sql)){
                $success_message = "Photo Added Successfully! ✅";
                unset($_POST);
            } else {
                $error_message = "Database Error: " . $conn->error;
            }
        }
    }

    // ===== FETCH PHOTOS =====
    $result = $conn->query("SELECT * FROM gallery ORDER BY id DESC");
    if(!$result){
        $error_message = "Error fetching photos: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BLACKPINK · Admin Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800;900&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Prevent caching of pages */
        html, body {
            margin: 0;
            padding: 0;
            height: 100%;
        }
        
        /* ===== NO-CACHE META FOR BACK BUTTON ===== */
        .no-cache {
            display: none;
        }

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

        /* ===== ADMIN HEADER ===== */
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

        /* ===== LOGIN FORM ===== */
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
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
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

        /* ===== ADMIN PANEL ===== */
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
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.3s;
            box-shadow: 0 10px 20px rgba(255, 105, 180, 0.3);
        }

        .submit-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(255, 105, 180, 0.4);
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
            from { opacity: 0; transform: translateX(-20px); }
            to { opacity: 1; transform: translateX(0); }
        }

        .message.success {
            background: linear-gradient(135deg, #00b894, #00cec9);
            color: white;
        }

        .message.error {
            background: linear-gradient(135deg, #ff4757, #ee5a24);
            color: white;
        }

        .message.warning {
            background: linear-gradient(135deg, #fdcb6e, #f39c12);
            color: white;
        }

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

        /* ===== TABLE ===== */
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
            text-align: left;
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

        .empty-state h3 {
            font-size: 1.5rem;
            margin-bottom: 10px;
        }

        /* ===== SUBSCRIBERS ===== */
        .subscribers-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: linear-gradient(135deg, rgba(255,105,180,0.1), rgba(255,20,147,0.05));
            padding: 20px;
            border-radius: 20px;
            border: 1px solid rgba(255,105,180,0.2);
            text-align: center;
        }

        .stat-card .number {
            font-size: 2.5rem;
            font-weight: 800;
            color: #ff69b4;
        }

        .stat-card .label {
            color: #888;
            font-size: 0.9rem;
        }

        .subscriber-list {
            max-height: 400px;
            overflow-y: auto;
        }

        .subscriber-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 15px;
            border-bottom: 1px solid #f0f0f0;
            align-items: center;
        }

        .subscriber-item:hover {
            background: #f9f9f9;
        }

        .subscriber-email {
            font-weight: 500;
            color: #333;
        }

        .subscriber-date {
            color: #888;
            font-size: 0.9rem;
        }

        .badge-active {
            background: #00b894;
            color: white;
            padding: 2px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
        }

        .badge-inactive {
            background: #ff4757;
            color: white;
            padding: 2px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
        }

        .email-section {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            border-radius: 30px;
            padding: 35px;
            margin-bottom: 30px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            border: 1px solid rgba(255,255,255,0.2);
        }

        .admin-section {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            border-radius: 30px;
            padding: 35px;
            margin-bottom: 30px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            border: 1px solid rgba(255,255,255,0.2);
        }

        .textarea-full {
            width: 100%;
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 15px;
            font-family: 'Poppins', sans-serif;
            font-size: 1rem;
            resize: vertical;
            min-height: 150px;
        }

        .textarea-full:focus {
            outline: none;
            border-color: #ff69b4;
            box-shadow: 0 0 0 4px rgba(255,105,180,0.1);
        }

        .admin-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .admin-tab {
            padding: 12px 25px;
            background: rgba(255,255,255,0.1);
            border: 2px solid rgba(255,105,180,0.3);
            border-radius: 50px;
            color: #ff69b4;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 600;
        }

        .admin-tab:hover, .admin-tab.active {
            background: #ff69b4;
            color: white;
            border-color: #ff69b4;
        }

        .tab-content {
            display: none;
            animation: fadeIn 0.5s ease;
        }

        .tab-content.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .subscribe-message {
            padding: 12px 20px;
            border-radius: 15px;
            margin-bottom: 15px;
            font-weight: 500;
            text-align: center;
        }

        .subscribe-message.success {
            background: linear-gradient(135deg, #00b894, #00cec9);
            color: white;
        }

        .subscribe-message.warning {
            background: linear-gradient(135deg, #fdcb6e, #f39c12);
            color: white;
        }

        .subscribe-message.error {
            background: linear-gradient(135deg, #ff4757, #ee5a24);
            color: white;
        }

        /* ===== LOADING ===== */
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

        /* ===== RESPONSIVE ===== */
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
                justify-content: center;
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

            .admin-tabs {
                justify-content: center;
            }

            .admin-tab {
                padding: 10px 18px;
                font-size: 0.9rem;
            }

            .login-card {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Hidden meta tags to prevent caching -->
    <div class="no-cache">
        <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
        <meta http-equiv="Pragma" content="no-cache">
        <meta http-equiv="Expires" content="0">
    </div>
    
    <?php if(!$is_logged_in): ?>
        <!-- LOGIN FORM -->
        <div class="login-container">
            <div class="login-card">
                <div class="login-header">
                    <i class="fas fa-lock"></i>
                    <h2>Admin Access</h2>
                    <p>Enter your credentials to manage dashboard</p>
                </div>
                <?php if(isset($login_error)): ?>
                    <div class="login-error"><i class="fas fa-exclamation-circle"></i> <?php echo $login_error; ?></div>
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
                        <i class="fas fa-sign-in-alt"></i> Login to Dashboard
                    </button>
                </form>
                <div class="login-footer"><p><i class="fas fa-heart"></i> BLACKPINK Admin Dashboard</p></div>
            </div>
        </div>
    <?php else: ?>
        <!-- ADMIN HEADER -->
        <div class="admin-header">
            <div class="header-left">
                <i class="fas fa-crown"></i>
                <div>
                    <h1>Admin Dashboard</h1>
                    <p><i class="fas fa-shield-alt"></i> Welcome, <?php echo $_SESSION['admin_username']; ?></p>
                </div>
            </div>
            <a href="?logout=1" class="logout-btn" onclick="return confirm('Are you sure you want to logout?');">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>

        <!-- Admin Tabs -->
        <div class="admin-tabs">
            <button class="admin-tab active" data-tab="gallery">📸 Gallery</button>
            <button class="admin-tab" data-tab="subscribers">📧 Subscribers</button>
            <button class="admin-tab" data-tab="email">📨 Send Email</button>
            <button class="admin-tab" data-tab="settings">⚙️ Settings</button>
        </div>

        <!-- GALLERY TAB -->
        <div id="tab-gallery" class="tab-content active">
            <?php if(isset($success_message)): ?>
                <div class="message success"><i class="fas fa-check-circle"></i> <?php echo $success_message; ?></div>
            <?php endif; ?>
            <?php if(isset($error_message)): ?>
                <div class="message error"><i class="fas fa-exclamation-triangle"></i> <?php echo $error_message; ?></div>
            <?php endif; ?>

            <div class="add-section">
                <div class="section-title">
                    <i class="fas fa-plus-circle"></i>
                    <div><h2>Add New Photo</h2><p>Upload new images to the gallery</p></div>
                </div>
                <form method="POST" id="addPhotoForm">
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
                    <button type="submit" class="submit-btn"><i class="fas fa-cloud-upload-alt"></i> Add Photo to Gallery</button>
                </form>
            </div>

            <div class="table-section">
                <div class="table-header">
                    <h3><i class="fas fa-images"></i> Gallery Photos (<?php echo isset($result) ? $result->num_rows : 0; ?>)</h3>
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchInput" placeholder="Search photos..." onkeyup="searchTable()">
                    </div>
                </div>
                <div class="table-responsive">
                    <table id="galleryTable">
                        <thead><tr><th>ID</th><th>Image</th><th>Category</th><th>Actions</th></tr></thead>
                        <tbody>
                            <?php if(isset($result) && $result && $result->num_rows > 0): while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><strong>#<?php echo $row['id']; ?></strong></td>
                                <td>
                                    <div class="photo-preview">
                                        <img src="<?php echo htmlspecialchars($row['image']); ?>" alt="Gallery Image" onerror="this.src='https://via.placeholder.com/100x100/ff69b4/ffffff?text=ERROR'">
                                    </div>
                                </td>
                                <td><span class="category-badge"><?php echo ucfirst(htmlspecialchars($row['category'])); ?></span></td>
                                <td>
                                    <a href="?delete=<?php echo $row['id']; ?>" class="delete-btn" onclick="return confirmDelete('<?php echo addslashes($row['category']); ?> photo')">
                                        <i class="fas fa-trash-alt"></i> Delete
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; else: ?>
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
        </div>

        <!-- SUBSCRIBERS TAB -->
        <div id="tab-subscribers" class="tab-content">
            <div class="email-section">
                <div class="section-title">
                    <i class="fas fa-users"></i>
                    <div><h2>Subscribers</h2><p>Manage your email subscribers</p></div>
                </div>
                
                <?php
                $count_result = $conn->query("SELECT COUNT(*) as total FROM subscribers WHERE is_active = 1");
                $active_count = $count_result->fetch_assoc()['total'];
                $total_result = $conn->query("SELECT COUNT(*) as total FROM subscribers");
                $total_count = $total_result->fetch_assoc()['total'];
                ?>
                
                <div class="subscribers-stats">
                    <div class="stat-card">
                        <div class="number"><?php echo number_format($active_count); ?></div>
                        <div class="label">Active Subscribers</div>
                    </div>
                    <div class="stat-card">
                        <div class="number"><?php echo number_format($total_count); ?></div>
                        <div class="label">Total Subscribers</div>
                    </div>
                </div>
                
                <div class="subscriber-list">
                    <?php
                    $subscribers = $conn->query("SELECT * FROM subscribers ORDER BY subscribed_at DESC");
                    if($subscribers && $subscribers->num_rows > 0):
                        while($sub = $subscribers->fetch_assoc()):
                    ?>
                    <div class="subscriber-item">
                        <span class="subscriber-email"><?php echo htmlspecialchars($sub['email']); ?></span>
                        <span>
                            <span class="subscriber-date"><?php echo date('M d, Y', strtotime($sub['subscribed_at'])); ?></span>
                            <span class="badge-<?php echo $sub['is_active'] ? 'active' : 'inactive'; ?>">
                                <?php echo $sub['is_active'] ? 'Active' : 'Inactive'; ?>
                            </span>
                        </span>
                    </div>
                    <?php endwhile; else: ?>
                    <div class="empty-state">
                        <i class="fas fa-envelope"></i>
                        <h3>No Subscribers Yet</h3>
                        <p>Your subscribers will appear here once they sign up</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- SEND EMAIL TAB -->
        <div id="tab-email" class="tab-content">
            <div class="email-section">
                <div class="section-title">
                    <i class="fas fa-paper-plane"></i>
                    <div><h2>Send Email to Subscribers</h2><p>Send updates to all your subscribers</p></div>
                </div>
                
                <?php if(isset($email_result)): ?>
                    <div class="message <?php echo $email_result_type; ?>">
                        <i class="fas fa-info-circle"></i> <?php echo $email_result; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> From Email</label>
                        <input type="email" name="from_email" class="form-control" placeholder="your-email@example.com" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-tag"></i> Subject</label>
                        <input type="text" name="email_subject" class="form-control" placeholder="Enter email subject" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-envelope"></i> Message</label>
                        <textarea name="email_message" class="textarea-full" placeholder="Write your message here..." required></textarea>
                    </div>
                    <button type="submit" name="send_email" class="submit-btn" onclick="return confirm('Send this email to all <?php echo number_format($active_count); ?> subscribers?')">
                        <i class="fas fa-paper-plane"></i> Send to All Subscribers
                    </button>
                    <p style="color:#888; margin-top:10px; font-size:0.9rem;">
                        <i class="fas fa-info-circle"></i> This will send to <?php echo number_format($active_count); ?> active subscribers
                    </p>
                </form>
            </div>
        </div>

        <!-- SETTINGS TAB -->
        <div id="tab-settings" class="tab-content">
            <div class="admin-section">
                <div class="section-title">
                    <i class="fas fa-cog"></i>
                    <div><h2>Admin Settings</h2><p>Update your admin credentials</p></div>
                </div>
                
                <?php if(isset($admin_message)): ?>
                    <div class="message <?php echo $admin_message_type; ?>">
                        <i class="fas fa-info-circle"></i> <?php echo $admin_message; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> New Username</label>
                        <input type="text" name="new_username" class="form-control" placeholder="Enter new username" required value="<?php echo $_SESSION['admin_username']; ?>">
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-key"></i> New Password</label>
                        <input type="password" name="new_password" class="form-control" placeholder="Enter new password" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-check"></i> Confirm Password</label>
                        <input type="password" name="confirm_password" class="form-control" placeholder="Confirm new password" required>
                    </div>
                    <button type="submit" name="update_admin" class="submit-btn">
                        <i class="fas fa-save"></i> Update Credentials
                    </button>
                </form>
            </div>
        </div>
        
    <?php endif; ?>
</div>

<script>
    // JavaScript to handle back button - force reload and check session
    window.addEventListener('pageshow', function(event) {
        // If page is loaded from cache (back button), force reload
        if (event.persisted) {
            window.location.reload();
        }
    });

    // Prevent forward button from showing cached page
    window.addEventListener('beforeunload', function() {
        // This helps prevent caching
    });

    // Tab switching
    document.querySelectorAll('.admin-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            document.querySelectorAll('.admin-tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            const tabId = this.dataset.tab;
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
                if(content.id === 'tab-' + tabId) {
                    content.classList.add('active');
                }
            });
        });
    });

    function confirmDelete(category) {
        return confirm(`⚠️ Are you sure you want to delete this ${category}?\nThis action cannot be undone!`);
    }

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

    const imageUrlInput = document.getElementById('imageUrl');
    const imagePreview = document.getElementById('imagePreview');
    const previewImg = document.getElementById('previewImg');

    if(imageUrlInput) {
        imageUrlInput.addEventListener('input', function(e) {
            const url = e.target.value;
            if(url && (url.startsWith('http://') || url.startsWith('https://'))) {
                previewImg.src = url;
                imagePreview.classList.add('show');
                previewImg.onerror = function() {
                    previewImg.src = 'https://via.placeholder.com/200x200/ff69b4/ffffff?text=Invalid+URL';
                };
            } else {
                imagePreview.classList.remove('show');
            }
        });
    }

    document.getElementById('addPhotoForm')?.addEventListener('submit', function(e) {
        const btn = document.getElementById('submitBtn');
        btn.innerHTML = '<span class="loading"></span> Adding Photo...';
        btn.disabled = true;
    });

    setTimeout(() => {
        document.querySelectorAll('.message').forEach(msg => {
            msg.style.transition = 'opacity 1s';
            msg.style.opacity = '0';
            setTimeout(() => msg.remove(), 1000);
        });
    }, 5000);
</script>
</body>
</html>