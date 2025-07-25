<?php
// Enable error reporting (for development only)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize form input
    $videoUrl = trim($_POST['sf_url'] ?? '');
    $lang = $_POST['lang'] ?? 'en';
    $new = $_POST['new'] ?? '0';

    // Validate input
    if (empty($videoUrl)) {
        echo "<div style='color: red;'>Error: No video URL was provided.</div>";
        exit;
    }

    // Very basic URL validation
    if (!filter_var($videoUrl, FILTER_VALIDATE_URL)) {
        echo "<div style='color: red;'>Error: Invalid URL format.</div>";
        exit;
    }

    // Simulate fake video details
    $videoTitle = "Sample Video Title";
    $videoThumbnail = "https://via.placeholder.com/480x270.png?text=Video+Thumbnail";

    // Display simulated download options
    echo "<div style='font-family: Arial, sans-serif; max-width: 600px;'>";
    echo "<h2>Download Ready</h2>";
    echo "<img src='$videoThumbnail' alt='Video Thumbnail' style='max-width: 100%; height: auto;'><br><br>";
    echo "<strong>Title:</strong> " . htmlspecialchars($videoTitle) . "<br>";
    echo "<strong>URL:</strong> " . htmlspecialchars($videoUrl) . "<br><br>";

    echo "<p>Choose a format:</p>";
    echo "<ul style='list-style: none; padding: 0;'>";
    echo "<li><a href='fake-download.php?format=mp4&url=" . urlencode($videoUrl) . "' target='_blank'>🔽 Download MP4 (720p)</a></li>";
    echo "<li><a href='fake-download.php?format=mp3&url=" . urlencode($videoUrl) . "' target='_blank'>🎵 Download MP3 (Audio Only)</a></li>";
    echo "</ul>";

    echo "<p style='font-size: 0.9em; color: gray;'>Note: This is a simulation only. Real downloading requires video processing tools like <code>yt-dlp</code> or third-party APIs.</p>";
    echo "</div>";
} else {
    // If not a POST request
    echo "<div style='color: red;'>Access Denied: Invalid request method.</div>";
}
?>
