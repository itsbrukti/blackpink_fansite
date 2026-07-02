<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;

$subscribe_message = '';
$message_type = '';

function ensureSubscriberColumns($conn) {
    $conn->query("CREATE TABLE IF NOT EXISTS subscribers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL UNIQUE,
        is_active TINYINT(1) NOT NULL DEFAULT 0,
        verification_token VARCHAR(64) DEFAULT NULL,
        verified_at TIMESTAMP NULL DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $columns = [];
    $result = $conn->query("SHOW COLUMNS FROM subscribers");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $columns[] = $row['Field'];
        }
    }

    if (!in_array('verification_token', $columns, true)) {
        $conn->query("ALTER TABLE subscribers ADD COLUMN verification_token VARCHAR(64) DEFAULT NULL");
    }

    if (!in_array('verified_at', $columns, true)) {
        $conn->query("ALTER TABLE subscribers ADD COLUMN verified_at TIMESTAMP NULL DEFAULT NULL");
    }
}

function sendConfirmationEmail($email, $token) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->Port = 587;
        $mail->SMTPAuth = true;
        $mail->Username = 'zemedbiruktawit@gmail.com';
        $mail->Password = 'xwvogybpdmmbebvm';
        $mail->SMTPSecure = 'tls';
        $mail->CharSet = 'UTF-8';

        $mail->setFrom('zemedbiruktawit@gmail.com', 'BLACKPINK Fansite');
        $mail->addAddress($email);
        $mail->Subject = 'Please confirm your subscription';

        $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost:8000');
        $confirmUrl = $baseUrl . '/subscribe.php?confirm=' . urlencode($token);

        $mail->isHTML(true);
        $mail->Body = "<p>Hi there,</p><p>Please confirm your subscription by clicking the link below:</p><p><a href=\"$confirmUrl\">Confirm subscription</a></p><p>If you did not subscribe, you can ignore this email.</p>";
        $mail->AltBody = "Please confirm your subscription by visiting: $confirmUrl";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

ensureSubscriberColumns($conn);

if (isset($_GET['confirm']) && is_string($_GET['confirm']) && $_GET['confirm'] !== '') {
    $token = trim($_GET['confirm']);
    $stmt = $conn->prepare("SELECT id FROM subscribers WHERE verification_token = ?");
    if ($stmt) {
        $stmt->bind_param('s', $token);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            $update = $conn->prepare("UPDATE subscribers SET is_active = 1, verification_token = NULL, verified_at = CURRENT_TIMESTAMP WHERE verification_token = ?");
            if ($update) {
                $update->bind_param('s', $token);
                $update->execute();
                $update->close();
            }
            $subscribe_message = "✅ Your email is now confirmed. Thanks for subscribing!";
            $message_type = 'success';
        } else {
            $subscribe_message = "⚠️ This confirmation link is invalid or has already been used.";
            $message_type = 'warning';
        }
        $stmt->close();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['subscribe_btn'])) {
    $email = trim((string)($_POST['email'] ?? ''));
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);

    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $check = $conn->prepare("SELECT id, is_active FROM subscribers WHERE email = ?");
        if ($check) {
            $check->bind_param('s', $email);
            $check->execute();
            $check->store_result();

            if ($check->num_rows > 0) {
                $existing = $check->get_result()->fetch_assoc();
                if (!empty($existing['is_active'])) {
                    $subscribe_message = "📧 This email is already subscribed!";
                    $message_type = 'warning';
                } else {
                    $token = bin2hex(random_bytes(16));
                    $update = $conn->prepare("UPDATE subscribers SET verification_token = ? WHERE email = ?");
                    if ($update) {
                        $update->bind_param('ss', $token, $email);
                        $update->execute();
                        $update->close();
                    }
                    if (sendConfirmationEmail($email, $token)) {
                        $subscribe_message = "📩 We sent a confirmation link to your email. Please check your inbox.";
                        $message_type = 'success';
                    } else {
                        $subscribe_message = "⚠️ We could not send the confirmation email. Please try again later.";
                        $message_type = 'warning';
                    }
                }
            } else {
                $token = bin2hex(random_bytes(16));
                $stmt = $conn->prepare("INSERT INTO subscribers (email, is_active, verification_token) VALUES (?, 0, ?)");
                if ($stmt) {
                    $stmt->bind_param('ss', $email, $token);
                    if ($stmt->execute()) {
                        if (sendConfirmationEmail($email, $token)) {
                            $subscribe_message = "📩 Please confirm your subscription. We sent a confirmation link to your inbox.";
                            $message_type = 'success';
                        } else {
                            $subscribe_message = "⚠️ Your email was saved, but the confirmation email could not be sent.";
                            $message_type = 'warning';
                        }
                    } else {
                        $subscribe_message = "❌ Something went wrong. Please try again.";
                        $message_type = 'error';
                    }
                    $stmt->close();
                }
            }
            $check->close();
        }
    } else {
        $subscribe_message = "⚠️ Please enter a valid email address.";
        $message_type = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Subscription Status</title>
  <style>
    body { font-family: Arial, sans-serif; background: #111; color: #fff; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
    .card { background: #1b1b1b; padding: 2rem 2.5rem; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.25); text-align: center; max-width: 480px; }
    .success { color: #ff8ccf; }
    .warning { color: #ffd166; }
    .error { color: #ff6b6b; }
    a { color: #ff8ccf; text-decoration: none; }
    a:hover { text-decoration: underline; }
  </style>
</head>
<body>
  <div class="card">
    <h2 class="<?php echo $message_type ?: 'success'; ?>">Subscription update</h2>
    <p class="<?php echo $message_type ?: 'success'; ?>"><?php echo $subscribe_message ?: 'No update was submitted.'; ?></p>
    <p><a href="index.html">Return to the site</a></p>
  </div>
</body>
</html>
