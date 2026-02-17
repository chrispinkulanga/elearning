<?php
// Manual Cache Clear via File Deletion
// DELETE THIS FILE AFTER USE!

echo "<h2>Manual Laravel Cache Clear</h2>";
echo "<hr>";

$deleted = [];
$errors = [];

// Base path - adjust if needed
$basePath = dirname(__DIR__);

// Files and directories to delete
$cachePaths = [
    'bootstrap/cache/config.php',
    'bootstrap/cache/services.php',
    'bootstrap/cache/packages.php',
    'bootstrap/cache/routes-v7.php',
    'storage/framework/cache/data',
    'storage/framework/views',
];

foreach ($cachePaths as $path) {
    $fullPath = $basePath . '/' . $path;
    
    if (file_exists($fullPath)) {
        if (is_dir($fullPath)) {
            // Delete directory contents
            $files = glob($fullPath . '/*');
            $count = 0;
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                    $count++;
                }
            }
            $deleted[] = "$path (deleted $count files)";
        } else {
            // Delete single file
            if (unlink($fullPath)) {
                $deleted[] = $path;
            } else {
                $errors[] = $path . " (failed to delete)";
            }
        }
    } else {
        $errors[] = $path . " (not found)";
    }
}

// Display results
echo "<h3>Deleted Cache Files:</h3>";
if (!empty($deleted)) {
    echo "<ul style='color:green;'>";
    foreach ($deleted as $item) {
        echo "<li>✅ " . htmlspecialchars($item) . "</li>";
    }
    echo "</ul>";
} else {
    echo "<p>No cache files found to delete.</p>";
}

if (!empty($errors)) {
    echo "<h3>Notices:</h3>";
    echo "<ul style='color:orange;'>";
    foreach ($errors as $error) {
        echo "<li>⚠ " . htmlspecialchars($error) . "</li>";
    }
    echo "</ul>";
}

echo "<hr>";
echo "<h3 style='color:green;'>✅ Cache clearing complete!</h3>";
echo "<p><strong>IMPORTANT:</strong></p>";
echo "<ol>";
echo "<li><strong style='color:red;'>DELETE this file immediately for security!</strong></li>";
echo "<li>Visit: <a href='https://allfycenter.com/register'>https://allfycenter.com/register</a></li>";
echo "<li>If still getting errors, check .env file has correct database credentials</li>";
echo "</ol>";
echo "<p>Base path detected: <code>" . htmlspecialchars($basePath) . "</code></p>";
?>
