<?php
$host = '127.0.0.1';
$db   = 'christverse';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$db`");
    
    // Create registrations table for onboarding
    $pdo->exec("CREATE TABLE IF NOT EXISTS `registrations` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(255) NOT NULL,
        `email` VARCHAR(255) NOT NULL,
        `department` VARCHAR(100) NOT NULL,
        `message` TEXT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");

    // Create contacts table for queries
    $pdo->exec("CREATE TABLE IF NOT EXISTS `contacts` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(255) NOT NULL,
        `email` VARCHAR(255) NOT NULL,
        `subject` VARCHAR(255) NOT NULL,
        `message` TEXT NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");

    // Create newsletter_subscribers table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `newsletter_subscribers` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `email` VARCHAR(255) NOT NULL UNIQUE,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");

    // Create events table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `events` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `title` VARCHAR(255) NOT NULL,
        `description` TEXT NOT NULL,
        `category` VARCHAR(100) NOT NULL,
        `event_date` DATETIME NOT NULL,
        `location` VARCHAR(255) NOT NULL,
        `image_path` VARCHAR(255) NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");

    // Create event_registrations table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `event_registrations` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `event_id` INT NOT NULL,
        `name` VARCHAR(255) NOT NULL,
        `email` VARCHAR(255) NOT NULL,
        `phone` VARCHAR(50) NULL,
        `message` TEXT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`event_id`) REFERENCES `events`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB");

    // Seed events if table is empty
    $count = $pdo->query("SELECT COUNT(*) FROM `events`")->fetchColumn();
    if ($count == 0) {
        $stmt = $pdo->prepare("INSERT INTO `events` (`title`, `description`, `category`, `event_date`, `location`, `image_path`) VALUES (?, ?, ?, ?, ?, ?)");
        
        $stmt->execute([
            'Colors Outreach Hangout',
            'Our official hangout and outdoor fellowship session where we connect, share stories, play team games, and share the gospel of Christ.',
            'Outreach',
            '2026-07-11 16:00:00',
            'Abuja Central Park & Online',
            'img/courses-1.jpg'
        ]);

        $stmt->execute([
            'Camp Christos Mentorship Masterclass',
            'A structured session for youth development, personal growth, and kingdom principles, aimed at raising narrative-changing rulers.',
            'Mentorship',
            '2026-07-25 10:00:00',
            'Zoom & YouTube Live',
            'img/courses-2.jpg'
        ]);

        $stmt->execute([
            'Freeform Virtual Meetup',
            'Our monthly virtual gathering for community bonding, fellowship, team camaraderie, and sharing faith declarations.',
            'Community',
            '2026-08-08 18:00:00',
            'Google Meet / Discord',
            'img/courses-3.jpg'
        ]);
    }

} catch (\PDOException $e) {
    // Save error to variable, we can check it in pages if connection failed
    $db_error = $e->getMessage();
}
?>
