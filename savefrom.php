<?php
// savefrom.php - Video Downloader Backend
header('Content-Type: text/html; charset=utf-8');

// Error reporting for development (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configuration
define('MAX_URL_LENGTH', 2000);
define('VALID_DOMAINS', ['youtube.com', 'youtu.be', 'vimeo.com', 'dailymotion.com', 'facebook.com', 'instagram.com']);
define('TEMP_DIR', __DIR__ . '/temp/');
define('MAX_DOWNLOAD_SIZE', 500 * 1024 * 1024); // 500MB
define('DOWNLOAD_TIMEOUT', 300); // 5 minutes

// Create temp directory if it doesn't exist
if (!file_exists(TEMP_DIR)) {
    mkdir(TEMP_DIR, 0755, true);
}

// Security headers
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");

// Main processing
try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Validate CSRF token (you should implement proper CSRF protection)
    if (empty($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
        throw new Exception('Invalid CSRF token');
    }

    // Get and validate URL
    $url = trim($_POST['sf_url'] ?? '');
    if (empty($url)) {
        throw new Exception('Please enter a video URL');
    }

    if (strlen($url) > MAX_URL_LENGTH) {
        throw new Exception('URL is too long');
    }

    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        throw new Exception('Invalid URL format');
    }

    // Parse URL and validate domain
    $domain = parse_url($url, PHP_URL_HOST);
    $isValidDomain = false;
    
    foreach (VALID_DOMAINS as $validDomain) {
        if (strpos($domain, $validDomain) !== false) {
            $isValidDomain = true;
            break;
        }
    }

    if (!$isValidDomain) {
        throw new Exception('Unsupported video platform');
    }

    // Process the download
    $videoInfo = processDownload($url);
    
    // Output JSON response for AJAX or HTML for direct access
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'title' => $videoInfo['title'],
            'url' => $videoInfo['download_url'],
            'thumbnail' => $videoInfo['thumbnail'],
            'formats' => $videoInfo['formats']
        ]);
    } else {
        // Render download page
        renderDownloadPage($videoInfo);
    }

} catch (Exception $e) {
    handleError($e->getMessage());
}

/**
 * Process video download
 */
function processDownload($url) {
    // In a real implementation, you would:
    // 1. Fetch the video page
    // 2. Extract video information (title, formats, etc.)
    // 3. Generate download links
    
    // This is a simplified example - in reality you would use YouTube-DL, 
    // a service API, or custom parsing
    
    $domain = parse_url($url, PHP_URL_HOST);
    
    // Simulate fetching video info
    $videoInfo = [
        'title' => 'Example Video Title',
        'thumbnail' => 'https://img.youtube.com/vi/abc123/maxresdefault.jpg',
        'duration' => '10:30',
        'formats' => [
            ['format' => 'MP4 1080p', 'url' => '#', 'quality' => '1080p'],
            ['format' => 'MP4 720p', 'url' => '#', 'quality' => '720p'],
            ['format' => 'MP3 128kbps', 'url' => '#', 'quality' => 'audio'],
        ],
        'download_url' => '#'
    ];
    
    return $videoInfo;
}

/**
 * Render download page
 */
function renderDownloadPage($videoInfo) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Download Video - SaveFrom</title>
        <style>
            body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
            .video-info { display: flex; margin-bottom: 20px; }
            .thumbnail { width: 200px; margin-right: 20px; }
            .download-options { margin-top: 20px; }
            .format-option { padding: 10px; border: 1px solid #ddd; margin-bottom: 10px; }
            .download-btn { background: #4CAF50; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px; }
        </style>
    </head>
    <body>
        <h1>Download Video</h1>
        
        <div class="video-info">
            <img src="<?= htmlspecialchars($videoInfo['thumbnail']) ?>" alt="Thumbnail" class="thumbnail">
            <div>
                <h2><?= htmlspecialchars($videoInfo['title']) ?></h2>
                <p>Duration: <?= htmlspecialchars($videoInfo['duration']) ?></p>
            </div>
        </div>
        
        <div class="download-options">
            <h3>Available Formats:</h3>
            <?php foreach ($videoInfo['formats'] as $format): ?>
                <div class="format-option">
                    <?= htmlspecialchars($format['format']) ?>
                    <a href="<?= htmlspecialchars($format['url']) ?>" class="download-btn">Download</a>
                </div>
            <?php endforeach; ?>
        </div>
    </body>
    </html>
    <?php
}

/**
 * Handle errors
 */
function handleError($message) {
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => $message]);
    } else {
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Error - SaveFrom</title>
            <style>
                body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
                .error-box { 
                    background: #ffebee; 
                    border: 1px solid #ffcdd2; 
                    padding: 20px; 
                    display: inline-block;
                    border-radius: 4px;
                }
            </style>
        </head>
        <body>
            <div class="error-box">
                <h2>Error</h2>
                <p><?= htmlspecialchars($message) ?></p>
                <a href="/">Try again</a>
            </div>
        </body>
        </html>
        <?php
    }
    exit;
}

/**
 * Validate CSRF token (simplified example)
 */
function validateCsrfToken($token) {
    // In a real implementation, compare with session token
    return !empty($token); // This is just a placeholder
}
?><?php
// savefrom.php - Video Downloader Backend
header('Content-Type: text/html; charset=utf-8');

// Error reporting for development (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configuration
define('MAX_URL_LENGTH', 2000);
define('VALID_DOMAINS', ['youtube.com', 'youtu.be', 'vimeo.com', 'dailymotion.com', 'facebook.com', 'instagram.com']);
define('TEMP_DIR', __DIR__ . '/temp/');
define('MAX_DOWNLOAD_SIZE', 500 * 1024 * 1024); // 500MB
define('DOWNLOAD_TIMEOUT', 300); // 5 minutes

// Create temp directory if it doesn't exist
if (!file_exists(TEMP_DIR)) {
    mkdir(TEMP_DIR, 0755, true);
}

// Security headers
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");

// Main processing
try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Validate CSRF token (you should implement proper CSRF protection)
    if (empty($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
        throw new Exception('Invalid CSRF token');
    }

    // Get and validate URL
    $url = trim($_POST['sf_url'] ?? '');
    if (empty($url)) {
        throw new Exception('Please enter a video URL');
    }

    if (strlen($url) > MAX_URL_LENGTH) {
        throw new Exception('URL is too long');
    }

    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        throw new Exception('Invalid URL format');
    }

    // Parse URL and validate domain
    $domain = parse_url($url, PHP_URL_HOST);
    $isValidDomain = false;
    
    foreach (VALID_DOMAINS as $validDomain) {
        if (strpos($domain, $validDomain) !== false) {
            $isValidDomain = true;
            break;
        }
    }

    if (!$isValidDomain) {
        throw new Exception('Unsupported video platform');
    }

    // Process the download
    $videoInfo = processDownload($url);
    
    // Output JSON response for AJAX or HTML for direct access
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'title' => $videoInfo['title'],
            'url' => $videoInfo['download_url'],
            'thumbnail' => $videoInfo['thumbnail'],
            'formats' => $videoInfo['formats']
        ]);
    } else {
        // Render download page
        renderDownloadPage($videoInfo);
    }

} catch (Exception $e) {
    handleError($e->getMessage());
}

/**
 * Process video download
 */
function processDownload($url) {
    // In a real implementation, you would:
    // 1. Fetch the video page
    // 2. Extract video information (title, formats, etc.)
    // 3. Generate download links
    
    // This is a simplified example - in reality you would use YouTube-DL, 
    // a service API, or custom parsing
    
    $domain = parse_url($url, PHP_URL_HOST);
    
    // Simulate fetching video info
    $videoInfo = [
        'title' => 'Example Video Title',
        'thumbnail' => 'https://img.youtube.com/vi/abc123/maxresdefault.jpg',
        'duration' => '10:30',
        'formats' => [
            ['format' => 'MP4 1080p', 'url' => '#', 'quality' => '1080p'],
            ['format' => 'MP4 720p', 'url' => '#', 'quality' => '720p'],
            ['format' => 'MP3 128kbps', 'url' => '#', 'quality' => 'audio'],
        ],
        'download_url' => '#'
    ];
    
    return $videoInfo;
}

/**
 * Render download page
 */
function renderDownloadPage($videoInfo) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Download Video - SaveFrom</title>
        <style>
            body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
            .video-info { display: flex; margin-bottom: 20px; }
            .thumbnail { width: 200px; margin-right: 20px; }
            .download-options { margin-top: 20px; }
            .format-option { padding: 10px; border: 1px solid #ddd; margin-bottom: 10px; }
            .download-btn { background: #4CAF50; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px; }
        </style>
    </head>
    <body>
        <h1>Download Video</h1>
        
        <div class="video-info">
            <img src="<?= htmlspecialchars($videoInfo['thumbnail']) ?>" alt="Thumbnail" class="thumbnail">
            <div>
                <h2><?= htmlspecialchars($videoInfo['title']) ?></h2>
                <p>Duration: <?= htmlspecialchars($videoInfo['duration']) ?></p>
            </div>
        </div>
        
        <div class="download-options">
            <h3>Available Formats:</h3>
            <?php foreach ($videoInfo['formats'] as $format): ?>
                <div class="format-option">
                    <?= htmlspecialchars($format['format']) ?>
                    <a href="<?= htmlspecialchars($format['url']) ?>" class="download-btn">Download</a>
                </div>
            <?php endforeach; ?>
        </div>
    </body>
    </html>
    <?php
}

/**
 * Handle errors
 */
function handleError($message) {
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => $message]);
    } else {
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Error - SaveFrom</title>
            <style>
                body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
                .error-box { 
                    background: #ffebee; 
                    border: 1px solid #ffcdd2; 
                    padding: 20px; 
                    display: inline-block;
                    border-radius: 4px;
                }
            </style>
        </head>
        <body>
            <div class="error-box">
                <h2>Error</h2>
                <p><?= htmlspecialchars($message) ?></p>
                <a href="/">Try again</a>
            </div>
        </body>
        </html>
        <?php
    }
    exit;
}

/**
 * Validate CSRF token (simplified example)
 */
function validateCsrfToken($token) {
    // In a real implementation, compare with session token
    return !empty($token); // This is just a placeholder
}
?><?php
// savefrom.php - Video Downloader Backend
header('Content-Type: text/html; charset=utf-8');

// Error reporting for development (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configuration
define('MAX_URL_LENGTH', 2000);
define('VALID_DOMAINS', ['youtube.com', 'youtu.be', 'vimeo.com', 'dailymotion.com', 'facebook.com', 'instagram.com']);
define('TEMP_DIR', __DIR__ . '/temp/');
define('MAX_DOWNLOAD_SIZE', 500 * 1024 * 1024); // 500MB
define('DOWNLOAD_TIMEOUT', 300); // 5 minutes

// Create temp directory if it doesn't exist
if (!file_exists(TEMP_DIR)) {
    mkdir(TEMP_DIR, 0755, true);
}

// Security headers
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");

// Main processing
try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Validate CSRF token (you should implement proper CSRF protection)
    if (empty($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
        throw new Exception('Invalid CSRF token');
    }

    // Get and validate URL
    $url = trim($_POST['sf_url'] ?? '');
    if (empty($url)) {
        throw new Exception('Please enter a video URL');
    }

    if (strlen($url) > MAX_URL_LENGTH) {
        throw new Exception('URL is too long');
    }

    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        throw new Exception('Invalid URL format');
    }

    // Parse URL and validate domain
    $domain = parse_url($url, PHP_URL_HOST);
    $isValidDomain = false;
    
    foreach (VALID_DOMAINS as $validDomain) {
        if (strpos($domain, $validDomain) !== false) {
            $isValidDomain = true;
            break;
        }
    }

    if (!$isValidDomain) {
        throw new Exception('Unsupported video platform');
    }

    // Process the download
    $videoInfo = processDownload($url);
    
    // Output JSON response for AJAX or HTML for direct access
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'title' => $videoInfo['title'],
            'url' => $videoInfo['download_url'],
            'thumbnail' => $videoInfo['thumbnail'],
            'formats' => $videoInfo['formats']
        ]);
    } else {
        // Render download page
        renderDownloadPage($videoInfo);
    }

} catch (Exception $e) {
    handleError($e->getMessage());
}

/**
 * Process video download
 */
function processDownload($url) {
    // In a real implementation, you would:
    // 1. Fetch the video page
    // 2. Extract video information (title, formats, etc.)
    // 3. Generate download links
    
    // This is a simplified example - in reality you would use YouTube-DL, 
    // a service API, or custom parsing
    
    $domain = parse_url($url, PHP_URL_HOST);
    
    // Simulate fetching video info
    $videoInfo = [
        'title' => 'Example Video Title',
        'thumbnail' => 'https://img.youtube.com/vi/abc123/maxresdefault.jpg',
        'duration' => '10:30',
        'formats' => [
            ['format' => 'MP4 1080p', 'url' => '#', 'quality' => '1080p'],
            ['format' => 'MP4 720p', 'url' => '#', 'quality' => '720p'],
            ['format' => 'MP3 128kbps', 'url' => '#', 'quality' => 'audio'],
        ],
        'download_url' => '#'
    ];
    
    return $videoInfo;
}

/**
 * Render download page
 */
function renderDownloadPage($videoInfo) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Download Video - SaveFrom</title>
        <style>
            body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
            .video-info { display: flex; margin-bottom: 20px; }
            .thumbnail { width: 200px; margin-right: 20px; }
            .download-options { margin-top: 20px; }
            .format-option { padding: 10px; border: 1px solid #ddd; margin-bottom: 10px; }
            .download-btn { background: #4CAF50; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px; }
        </style>
    </head>
    <body>
        <h1>Download Video</h1>
        
        <div class="video-info">
            <img src="<?= htmlspecialchars($videoInfo['thumbnail']) ?>" alt="Thumbnail" class="thumbnail">
            <div>
                <h2><?= htmlspecialchars($videoInfo['title']) ?></h2>
                <p>Duration: <?= htmlspecialchars($videoInfo['duration']) ?></p>
            </div>
        </div>
        
        <div class="download-options">
            <h3>Available Formats:</h3>
            <?php foreach ($videoInfo['formats'] as $format): ?>
                <div class="format-option">
                    <?= htmlspecialchars($format['format']) ?>
                    <a href="<?= htmlspecialchars($format['url']) ?>" class="download-btn">Download</a>
                </div>
            <?php endforeach; ?>
        </div>
    </body>
    </html>
    <?php
}

/**
 * Handle errors
 */
function handleError($message) {
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => $message]);
    } else {
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Error - SaveFrom</title>
            <style>
                body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
                .error-box { 
                    background: #ffebee; 
                    border: 1px solid #ffcdd2; 
                    padding: 20px; 
                    display: inline-block;
                    border-radius: 4px;
                }
            </style>
        </head>
        <body>
            <div class="error-box">
                <h2>Error</h2>
                <p><?= htmlspecialchars($message) ?></p>
                <a href="/">Try again</a>
            </div>
        </body>
        </html>
        <?php
    }
    exit;
}

/**
 * Validate CSRF token (simplified example)
 */
function validateCsrfToken($token) {
    // In a real implementation, compare with session token
    return !empty($token); // This is just a placeholder
}
?>