<?php
// Find Laravel Installation
echo "<h2>Laravel Directory Diagnostic</h2>";
echo "<hr>";

$currentDir = __DIR__;
$parentDir = dirname(__DIR__);
$grandParentDir = dirname(dirname(__DIR__));

echo "<h3>Directory Structure:</h3>";
echo "<ul>";
echo "<li><strong>Current (__DIR__):</strong> " . htmlspecialchars($currentDir) . "</li>";
echo "<li><strong>Parent (dirname(__DIR__)):</strong> " . htmlspecialchars($parentDir) . "</li>";
echo "<li><strong>GrandParent:</strong> " . htmlspecialchars($grandParentDir) . "</li>";
echo "</ul>";

echo "<h3>Laravel Key Files Check:</h3>";
echo "<ul>";

$laravelFiles = [
    'artisan' => $parentDir . '/artisan',
    '.env' => $parentDir . '/.env',
    'bootstrap/cache' => $parentDir . '/bootstrap/cache',
    'bootstrap/cache/config.php' => $parentDir . '/bootstrap/cache/config.php',
    'storage/framework/cache' => $parentDir . '/storage/framework/cache',
];

foreach ($laravelFiles as $name => $path) {
    $exists = file_exists($path);
    $color = $exists ? 'green' : 'red';
    $icon = $exists ? '✅' : '❌';
    echo "<li style='color:$color;'>$icon <strong>$name:</strong> ";
    echo htmlspecialchars($path);
    if ($exists) {
        echo " (EXISTS)";
        if (is_dir($path)) {
            $files = @scandir($path);
            if ($files) {
                $fileCount = count($files) - 2; // exclude . and ..
                echo " - $fileCount files";
            }
        }
    } else {
        echo " (NOT FOUND)";
    }
    echo "</li>";
}

echo "</ul>";

echo "<h3>Bootstrap Cache Directory Contents:</h3>";
$cacheDir = $parentDir . '/bootstrap/cache';
if (is_dir($cacheDir)) {
    $files = @scandir($cacheDir);
    if ($files) {
        echo "<ul>";
        foreach ($files as $file) {
            if ($file != '.' && $file != '..') {
                $filePath = $cacheDir . '/' . $file;
                $size = filesize($filePath);
                echo "<li>$file (" . number_format($size) . " bytes)</li>";
            }
        }
        echo "</ul>";
    }
} else {
    echo "<p style='color:red;'>Bootstrap cache directory not found!</p>";
}

echo "<h3>.env File Check:</h3>";
$envFile = $parentDir . '/.env';
if (file_exists($envFile)) {
    echo "<p style='color:green;'>✅ .env file exists</p>";
    // Read DB config (safely, without showing password)
    $envContent = file_get_contents($envFile);
    preg_match('/DB_CONNECTION=(.*)/', $envContent, $matches);
    $dbConnection = $matches[1] ?? 'not found';
    preg_match('/DB_HOST=(.*)/', $envContent, $matches);
    $dbHost = $matches[1] ?? 'not found';
    preg_match('/DB_DATABASE=(.*)/', $envContent, $matches);
    $dbDatabase = $matches[1] ?? 'not found';
    preg_match('/DB_USERNAME=(.*)/', $envContent, $matches);
    $dbUsername = $matches[1] ?? 'not found';
    
    echo "<ul>";
    echo "<li><strong>DB_CONNECTION:</strong> " . htmlspecialchars(trim($dbConnection)) . "</li>";
    echo "<li><strong>DB_HOST:</strong> " . htmlspecialchars(trim($dbHost)) . "</li>";
    echo "<li><strong>DB_DATABASE:</strong> " . htmlspecialchars(trim($dbDatabase)) . "</li>";
    echo "<li><strong>DB_USERNAME:</strong> " . htmlspecialchars(trim($dbUsername)) . "</li>";
    echo "<li><strong>DB_PASSWORD:</strong> [hidden]</li>";
    echo "</ul>";
} else {
    echo "<p style='color:red;'>❌ .env file NOT FOUND!</p>";
}

echo "<hr>";
echo "<p><strong>Next Steps:</strong></p>";
echo "<ol>";
echo "<li>Verify the paths above are correct</li>";
echo "<li>If bootstrap/cache/config.php EXISTS, we need to delete it</li>";
echo "<li>Verify .env has correct database credentials</li>";
echo "</ol>";
?>
