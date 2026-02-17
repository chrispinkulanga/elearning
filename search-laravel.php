<?php
// Search for Laravel Installation
echo "<h2>Searching for Laravel Installation</h2>";
echo "<hr>";

$currentDir = __DIR__;
echo "<p><strong>Starting from:</strong> " . htmlspecialchars($currentDir) . "</p>";

// Possible Laravel locations
$possiblePaths = [
    'Same directory' => $currentDir,
    'Parent directory' => dirname($currentDir),
    'Grandparent directory' => dirname(dirname($currentDir)),
    'Subdirectory (elearning-backend)' => $currentDir . '/elearning-backend',
    'Parent subdirectory' => dirname($currentDir) . '/elearning-backend',
    'Home directory' => '/home/tibafuhk/elearning-backend',
    'Document root' => $_SERVER['DOCUMENT_ROOT'] ?? 'N/A',
    'Document root parent' => dirname($_SERVER['DOCUMENT_ROOT'] ?? ''),
];

echo "<h3>Checking Possible Laravel Locations:</h3>";
echo "<table border='1' cellpadding='10' style='border-collapse:collapse;'>";
echo "<tr><th>Location</th><th>Path</th><th>artisan</th><th>.env</th><th>bootstrap/cache</th></tr>";

$foundPath = null;

foreach ($possiblePaths as $name => $path) {
    if (empty($path)) continue;
    
    $artisanExists = file_exists($path . '/artisan');
    $envExists = file_exists($path . '/.env');
    $bootstrapExists = file_exists($path . '/bootstrap/cache');
    
    $artisanIcon = $artisanExists ? '✅' : '❌';
    $envIcon = $envExists ? '✅' : '❌';
    $bootstrapIcon = $bootstrapExists ? '✅' : '❌';
    
    $rowColor = ($artisanExists && $envExists) ? '#d4edda' : '#ffffff';
    
    echo "<tr style='background-color:$rowColor;'>";
    echo "<td><strong>" . htmlspecialchars($name) . "</strong></td>";
    echo "<td style='font-size:11px;'>" . htmlspecialchars($path) . "</td>";
    echo "<td>$artisanIcon</td>";
    echo "<td>$envIcon</td>";
    echo "<td>$bootstrapIcon</td>";
    echo "</tr>";
    
    if ($artisanExists && $envExists && !$foundPath) {
        $foundPath = $path;
    }
}

echo "</table>";

if ($foundPath) {
    echo "<hr>";
    echo "<h3 style='color:green;'>✅ Laravel Found at: " . htmlspecialchars($foundPath) . "</h3>";
    
    // Check bootstrap cache contents
    $bootstrapCache = $foundPath . '/bootstrap/cache';
    if (is_dir($bootstrapCache)) {
        echo "<h4>Bootstrap Cache Files:</h4>";
        $files = @scandir($bootstrapCache);
        if ($files) {
            echo "<ul>";
            foreach ($files as $file) {
                if ($file != '.' && $file != '..') {
                    $filePath = $bootstrapCache . '/' . $file;
                    $size = @filesize($filePath);
                    $writable = is_writable($filePath) ? '✅ writable' : '❌ not writable';
                    echo "<li><strong>$file</strong> (" . number_format($size) . " bytes) - $writable</li>";
                }
            }
            echo "</ul>";
        }
    }
    
    // Show .env database config
    $envFile = $foundPath . '/.env';
    if (file_exists($envFile)) {
        echo "<h4>.env Database Configuration:</h4>";
        $envContent = @file_get_contents($envFile);
        if ($envContent) {
            preg_match('/DB_CONNECTION=(.*)/', $envContent, $matches);
            $dbConnection = isset($matches[1]) ? trim($matches[1]) : 'not found';
            preg_match('/DB_HOST=(.*)/', $envContent, $matches);
            $dbHost = isset($matches[1]) ? trim($matches[1]) : 'not found';
            preg_match('/DB_DATABASE=(.*)/', $envContent, $matches);
            $dbDatabase = isset($matches[1]) ? trim($matches[1]) : 'not found';
            preg_match('/DB_USERNAME=(.*)/', $envContent, $matches);
            $dbUsername = isset($matches[1]) ? trim($matches[1]) : 'not found';
            
            echo "<table border='1' cellpadding='5'>";
            echo "<tr><td><strong>DB_CONNECTION</strong></td><td>" . htmlspecialchars($dbConnection) . "</td></tr>";
            echo "<tr><td><strong>DB_HOST</strong></td><td>" . htmlspecialchars($dbHost) . "</td></tr>";
            echo "<tr><td><strong>DB_DATABASE</strong></td><td>" . htmlspecialchars($dbDatabase) . "</td></tr>";
            echo "<tr><td><strong>DB_USERNAME</strong></td><td>" . htmlspecialchars($dbUsername) . "</td></tr>";
            echo "<tr><td><strong>DB_PASSWORD</strong></td><td>[hidden]</td></tr>";
            echo "</table>";
        }
    }
    
    // Generate cache clear script
    echo "<hr>";
    echo "<h3>Next Step: Cache Clearing Script</h3>";
    echo "<p>Copy this path to use in the cache clearing script:</p>";
    echo "<pre style='background:#f0f0f0;padding:10px;'>" . htmlspecialchars($foundPath) . "</pre>";
    
} else {
    echo "<hr>";
    echo "<h3 style='color:red;'>❌ Laravel Installation NOT FOUND!</h3>";
    echo "<p>Please verify:</p>";
    echo "<ul>";
    echo "<li>Laravel files were uploaded to the server</li>";
    echo "<li>The document root is correctly configured</li>";
    echo "<li>File permissions allow reading .env and artisan</li>";
    echo "</ul>";
}

echo "<hr>";
echo "<p><strong>Server Info:</strong></p>";
echo "<ul>";
echo "<li><strong>DOCUMENT_ROOT:</strong> " . htmlspecialchars($_SERVER['DOCUMENT_ROOT'] ?? 'N/A') . "</li>";
echo "<li><strong>SCRIPT_FILENAME:</strong> " . htmlspecialchars($_SERVER['SCRIPT_FILENAME'] ?? 'N/A') . "</li>";
echo "<li><strong>PHP Version:</strong> " . PHP_VERSION . "</li>";
echo "</ul>";
?>
