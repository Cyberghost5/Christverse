<?php
include 'includes/db.php';

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
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        if (isset($pdo)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO newsletter_subscribers (email) VALUES (?)");
                $stmt->execute([$email]);
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
header("Location: index");
exit;
?>
