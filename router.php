<?php
// router.php - Local PHP server router to mimic Apache RewriteRules
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($uri === '/' || $uri === '') {
    $uri = '/index.php';
}

$file = $_SERVER['DOCUMENT_ROOT'] . $uri;

if (is_dir($file)) {
    $indexFile = rtrim($file, '/') . '/index.php';
    if (file_exists($indexFile)) {
        $_SERVER['SCRIPT_NAME'] = rtrim($uri, '/') . '/index.php';
        $_SERVER['PHP_SELF'] = rtrim($uri, '/') . '/index.php';
        include $indexFile;
        exit;
    }
}

if (file_exists($file) && !is_dir($file)) {
    return false;
}

$phpFile = $_SERVER['DOCUMENT_ROOT'] . $uri . '.php';
if (file_exists($phpFile)) {
    $_SERVER['SCRIPT_NAME'] = $uri . '.php';
    $_SERVER['PHP_SELF'] = $uri . '.php';
    include $phpFile;
    exit;
}

if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/404.php')) {
    http_response_code(404);
    include $_SERVER['DOCUMENT_ROOT'] . '/404.php';
    exit;
}

return false;
?>
