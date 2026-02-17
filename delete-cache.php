<?php
// Delete Laravel Cache Files
echo "<h2>Laravel Cache Clearing</h2>";
echo "<hr>";

// Laravel is at /home/tibafuhk/public_html
$laravelPath = '/home/tibafuhk/public_html';

echo "<p><strong>Laravel Path:</strong> " . htmlspecialchars($laravelPath) . "</p>";

$deleted = [];
$errors = [];

// Critical cache files to delete
$cacheFiles = [
    'bootstrap/cache/config.php',
    'bootstrap/cache/services.php',
    'bootstrap/cache/packages.php',
    'bootstrap/cache/routes-v7.php',
    'bootstrap/cache/events.php',
];

echo "<h3>Deleting Cache Files:</h3>";
echo "<ul>";

foreach ($cacheFiles as $file) {
    $fullPath = $laravelPath . '/' . $file;
    
    if (file_exists($fullPath)) {
        if (is_writable($fullPath)) {
            if (@unlink($fullPath)) {
                $deleted[] = $file;
                echo "<li style='color:green;'>✅ <strong>DELETED:</strong> " . htmlspecialchars($file) . "</li>";
            } else {
                $errors[] = $file . " (failed to delete)";
                echo "<li style='color:red;'>❌ <strong>FAILED:</strong> " . htmlspecialchars($file) . "</li>";
            }
        } else {
            $errors[] = $file . " (not writable)";
            echo "<li style='color:orange;'>⚠ <strong>NOT WRITABLE:</strong> " . htmlspecialchars($file) . "</li>";
        }
    } else {
        echo "<li style='color:gray;'>ℹ <strong>NOT FOUND:</strong> " . htmlspecialchars($file) . "</li>";
    }
}

echo "</ul>";

// Also clear storage framework cache data
echo "<h3>Clearing Storage Framework Cache:</h3>";
$storageCache = $laravelPath . '/storage/framework/cache/data';
if (is_dir($storageCache)) {
    $files = @glob($storageCache . '/*');
    $count = 0;
    if ($files) {
        foreach ($files as $file) {
            if (is_file($file) && is_writable($file)) {
                if (@unlink($file)) {
                    $count++;
                }
            }
        }
    }
    echo "<p style='color:green;'>✅ Deleted $count cache files from storage/framework/cache/data</p>";
} else {
    echo "<p style='color:gray;'>ℹ storage/framework/cache/data directory not found</p>";
}

// Clear views cache
echo "<h3>Clearing Compiled Views:</h3>";
$viewsCache = $laravelPath . '/storage/framework/views';
if (is_dir($viewsCache)) {
    $files = @glob($viewsCache . '/*');
    $count = 0;
    if ($files) {
        foreach ($files as $file) {
            if (is_file($file) && basename($file) != '.gitignore' && is_writable($file)) {
                if (@unlink($file)) {
                    $count++;
                }
            }
        }
    }
    echo "<p style='color:green;'>✅ Deleted $count compiled view files</p>";
} else {
    echo "<p style='color:gray;'>ℹ storage/framework/views directory not found</p>";
}

echo "<hr>";

if (!empty($deleted)) {
    echo "<h3 style='color:green;'>✅ SUCCESS! Cache Cleared</h3>";
    echo "<p><strong>Deleted files:</strong></p>";
    echo "<ul>";
    foreach ($deleted as $file) {
        echo "<li>" . htmlspecialchars($file) . "</li>";
    }
    echo "</ul>";
} else {
    echo "<h3 style='color:orange;'>⚠ No Cache Files Deleted</h3>";
}

if (!empty($errors)) {
    echo "<h3 style='color:red;'>Errors:</h3>";
    echo "<ul>";
    foreach ($errors as $error) {
        echo "<li>" . htmlspecialchars($error) . "</li>";
    }
    echo "</ul>";
}

echo "<hr>";
echo "<h3>Next Steps:</h3>";
echo "<ol>";
echo "<li><strong style='color:red;'>DELETE THIS FILE immediately for security!</strong></li>";
echo "<li>Test your application: <a href='https://allfycenter.com/register' target='_blank'>https://allfycenter.com/register</a></li>";
echo "<li>If it works, the database connection should now use tibafuhk_allfycenter credentials</li>";
echo "<li>If still getting errors, check .env file permissions (must be readable by PHP)</li>";
echo "</ol>";
?>
