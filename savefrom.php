<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $videoUrl = trim($_POST['sf_url']);

    // Basic validation
    if (empty($videoUrl)) {
        echo "<p style='color:red;'>No URL was provided.</p>";
        exit;
    }

    // Simulate video parsing
    echo "<h2>Download Ready</h2>";
    echo "<p>Video URL: " . htmlspecialchars($videoUrl) . "</p>";

    // Simulate different format options
    echo "<ul>";
    echo "<li><a href='fake-download.php?format=mp4&url=" . urlencode($videoUrl) . "'>Download MP4 (720p)</a></li>";
    echo "<li><a href='fake-download.php?format=mp3&url=" . urlencode($videoUrl) . "'>Download MP3 (Audio Only)</a></li>";
    echo "</ul>";
} else {
    echo "<p style='color:red;'>Invalid request.</p>";
}
?>
