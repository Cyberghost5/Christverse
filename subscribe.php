<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'includes/db.php';
include 'includes/captcha.php';

$referer = 'index';
if (!empty($_SERVER['HTTP_REFERER'])) {
    $parsed = parse_url($_SERVER['HTTP_REFERER']);
    if (isset($parsed['path'])) {
        $referer = basename($parsed['path']);
    }
}
if (empty($referer)) {
    $referer = 'index';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['email'])) {
    if (!check_captcha_answer('newsletter', $_POST['captcha'] ?? '')) {
        header("Location: " . $referer . "?subscribed=wrongcaptcha");
        exit;
    }
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        if (isset($pdo)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO newsletter_subscribers (email) VALUES (?)");
                $stmt->execute([$email]);
                // Build visual HTML wrapper for emails
                $html_message = "
                <!DOCTYPE html>
                <html>
                <head>
                    <meta charset='utf-8'>
                    <title>" . htmlspecialchars($subject) . "</title>
                    <style>
                        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f6f8; margin: 0; padding: 0; color: #2b303a; }
                        .wrapper { max-width: 600px; margin: 30px auto; background: #ffffff; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
                        .header { background: #CE9B2E; padding: 25px; text-align: center; }
                        .header img { max-height: 45px; }
                        .content { padding: 40px 30px; line-height: 1.6; font-size: 16px; }
                        .footer { background: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #8e9aa8; border-top: 1px solid #eeeeee; }
                        .footer a { color: #CE9B2E; text-decoration: none; }
                    </style>
                </head>
                <body>
                    <div class='wrapper'>
                        <div class='header'>
                            <!-- Brand Header -->
                            <div style='color: #ffffff; font-size: 24px; font-weight: bold; font-family: sans-serif; letter-spacing: 1px;'><img src='https://christverse.org/img/logos/Christverse%20Horizontal%20White.png' alt='Christverse Logo' style='height: 80px;'></div>
                        </div>
                        <div class='content'>
                            
                            <h3>Welcome to the Christverse!</h3>
                            <p>We are absolutely thrilled to welcome you to the Christverse global community. You are joining an army of narrative-changing believers committed to spreads the gospel of Christ and growing spiritually.</p>
                            <p>Here are a few next steps to get started:</p>
                            <ul>
                                <li><strong>Explore initiatives</strong>: Meet with our leadership teams on iCreate, TBT podcast, Colors, and Freeform.</li>
                                <li><strong>Engage in Fellowship</strong>: Attend our virtual and physical monthly meetups to build camaraderie.</li>
                                <li><strong>Declare the Word</strong>: Declare life daily through faith affirmations.</li>
                            </ul>
                            <p>If you registered to join a specific department, our talent managers will get in touch with you shortly.</p>
                            <p style='margin-top: 30px; text-align: center;'>
                                <a href='https://chat.whatsapp.com/EO3qU7PskxN7qrm503eKMW' style='background-color: #CE9B2E; color: white; padding: 12px 25px; text-decoration: none; border-radius: 30px; font-weight: bold; display: inline-block;'>Explore the Community</a>
                            </p>
                            <p>In Christ,</p>
                            <p><strong>Nathaniel Thomas Yosi</strong><br>Global Team Lead, Christverse</p>
                        
                        </div>
                        <div class='footer'>
                            <p>You received this email because you are registered with Christverse.</p>
                            <p>&copy; " . date('Y') . " Christverse Community, Abuja, Nigeria. All rights reserved.</p>
                        </div>
                    </div>
                </body>
                </html>
                ";

                // Send a customised email with html body to the user
                send_email($email, "Thank you for Subscribing!", $html_message);

                header("Location: " . $referer . "?subscribed=1");
                exit;
            } catch (\PDOException $e) {
                // Check code for duplicate entry (SQLSTATE 23000 / MySQL error 1062)
                if ($e->getCode() == 23000 || (isset($e->errorInfo[1]) && $e->errorInfo[1] == 1062)) {
                    header("Location: " . $referer . "?subscribed=duplicate");
                    exit;
                } else {
                    header("Location: " . $referer . "?subscribed=error");
                    exit;
                }
            }
        } else {
            header("Location: " . $referer . "?subscribed=nodb");
            exit;
        }
    }
}
header("Location: ./");
exit;
?>
