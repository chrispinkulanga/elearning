<?php
/**
 * Run Laravel Migrations via Browser
 * 
 * SECURITY WARNING: This file allows running migrations via web browser.
 * DELETE IMMEDIATELY after use!
 * 
 * Usage: https://yourdomain.com/run-migrations.php?key=YOUR_SECRET_KEY
 */

// Security: Change this to something random
$SECRET_KEY = 'migrate-2026-secure';

// Check secret key
if (!isset($_GET['key']) || $_GET['key'] !== $SECRET_KEY) {
    die('<h1>Access Denied</h1><p>Invalid or missing secret key.</p>');
}

echo "<!DOCTYPE html>";
echo "<html><head>";
echo "<title>Laravel Migration Runner</title>";
echo "<style>
    body { font-family: Arial, sans-serif; max-width: 1000px; margin: 30px auto; padding: 20px; }
    h2 { color: #333; }
    .success { color: green; background: #e8f5e9; padding: 15px; margin: 10px 0; border-left: 4px solid #4caf50; }
    .error { color: red; background: #ffebee; padding: 15px; margin: 10px 0; border-left: 4px solid #f44336; }
    .warning { color: orange; background: #fff3e0; padding: 15px; margin: 10px 0; border-left: 4px solid #ff9800; }
    .info { color: #1976d2; background: #e3f2fd; padding: 15px; margin: 10px 0; border-left: 4px solid #2196f3; }
    pre { background: #f5f5f5; padding: 15px; overflow-x: auto; border: 1px solid #ddd; }
    .output { background: #263238; color: #aed581; padding: 20px; border-radius: 5px; font-family: 'Courier New', monospace; }
    .alert { background: #ffebee; border: 2px solid #f44336; padding: 20px; margin: 20px 0; }
</style>";
echo "</head><body>";

echo "<h2>🔄 Laravel Migration Runner</h2>";
echo "<hr>";

// Find Laravel installation
$currentDir = __DIR__;
$possiblePaths = [
    dirname($currentDir),
    $currentDir,
    dirname(dirname($currentDir)),
];

$laravelPath = null;
foreach ($possiblePaths as $path) {
    if (file_exists($path . '/artisan') && file_exists($path . '/.env')) {
        $laravelPath = $path;
        break;
    }
}

if (!$laravelPath) {
    echo "<div class='error'>";
    echo "<h3>❌ Laravel Installation Not Found</h3>";
    echo "</div></body></html>";
    exit;
}

echo "<div class='info'>";
echo "<strong>✅ Laravel Found:</strong> <code>" . htmlspecialchars($laravelPath) . "</code>";
echo "</div>";

// Check if artisan file exists and is readable
$artisanPath = $laravelPath . '/artisan';
if (!file_exists($artisanPath)) {
    echo "<div class='error'><h3>❌ Artisan file not found!</h3></div></body></html>";
    exit;
}

// Change to Laravel directory
chdir($laravelPath);

echo "<h3>📦 Running Migrations...</h3>";
echo "<div class='output'>";

// Capture output
ob_start();

try {
    // Define artisan
    define('LARAVEL_START', microtime(true));
    
    // Bootstrap Laravel using detected $laravelPath
    require $laravelPath . '/vendor/autoload.php';
    $app = require_once $laravelPath . '/bootstrap/app.php';
    
    // Create Kernel
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    
    // Run migrations
    $status = $kernel->call('migrate', [
        '--force' => true,
        '--no-interaction' => true,
    ]);
    
    // Get output
    $output = $kernel->output();
    
    echo htmlspecialchars($output);
    
    echo "\n\n✅ Migration Status Code: " . $status;
    
} catch (Exception $e) {
    echo "❌ ERROR: " . htmlspecialchars($e->getMessage());
    echo "\n\nStack Trace:\n";
    echo htmlspecialchars($e->getTraceAsString());
}

$consoleOutput = ob_get_clean();
echo nl2br(htmlspecialchars($consoleOutput));

echo "</div>";

// Show migration status
echo "<h3>📊 Migration Status</h3>";
echo "<p>Checking migrations table...</p>";

try {
    // Get database connection details from .env
    $envFile = $laravelPath . '/.env';
    if (file_exists($envFile)) {
        $envContent = file_get_contents($envFile);
        preg_match('/DB_HOST=(.*)/', $envContent, $matches);
        $dbHost = isset($matches[1]) ? trim($matches[1]) : 'localhost';
        preg_match('/DB_DATABASE=(.*)/', $envContent, $matches);
        $dbDatabase = isset($matches[1]) ? trim($matches[1]) : '';
        preg_match('/DB_USERNAME=(.*)/', $envContent, $matches);
        $dbUsername = isset($matches[1]) ? trim($matches[1]) : '';
        preg_match('/DB_PASSWORD=(.*)/', $envContent, $matches);
        $dbPassword = isset($matches[1]) ? trim($matches[1]) : '';
        
        if ($dbDatabase && $dbUsername) {
            // Connect to database
            $conn = @new mysqli($dbHost, $dbUsername, $dbPassword, $dbDatabase);
            
            if ($conn->connect_error) {
                echo "<div class='error'>❌ Database connection failed: " . htmlspecialchars($conn->connect_error) . "</div>";
            } else {
                // Check sessions table
                $result = $conn->query("SHOW TABLES LIKE 'sessions'");
                if ($result && $result->num_rows > 0) {
                    echo "<div class='success'>";
                    echo "<h4>✅ Sessions Table Exists!</h4>";
                    
                    // Get table structure
                    $structure = $conn->query("DESCRIBE sessions");
                    if ($structure) {
                        echo "<p><strong>Table Structure:</strong></p>";
                        echo "<table border='1' cellpadding='8' style='border-collapse:collapse;'>";
                        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
                        while ($row = $structure->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
                            echo "</tr>";
                        }
                        echo "</table>";
                    }
                    
                    // Count records
                    $count = $conn->query("SELECT COUNT(*) as total FROM sessions");
                    if ($count) {
                        $row = $count->fetch_assoc();
                        echo "<p><strong>Current sessions:</strong> " . $row['total'] . "</p>";
                    }
                    
                    echo "</div>";
                } else {
                    echo "<div class='warning'>⚠️ Sessions table still does not exist. Check migration output above for errors.</div>";
                }
                
                // List all tables
                $tables = $conn->query("SHOW TABLES");
                if ($tables) {
                    echo "<h4>Database Tables:</h4>";
                    echo "<ul>";
                    while ($row = $tables->fetch_array()) {
                        echo "<li>" . htmlspecialchars($row[0]) . "</li>";
                    }
                    echo "</ul>";
                }
                
                $conn->close();
            }
        }
    }
} catch (Exception $e) {
    echo "<div class='error'>Error checking database: " . htmlspecialchars($e->getMessage()) . "</div>";
}

echo "<hr>";
echo "<div class='alert'>";
echo "<h3>⚠️ CRITICAL - Delete This File!</h3>";
echo "<ol>";
echo "<li><strong style='color:red;'>DELETE THIS FILE IMMEDIATELY!</strong></li>";
echo "<li>This file can run ANY database migration</li>";
echo "<li>Never commit this to version control</li>";
echo "</ol>";
echo "</div>";

echo "<hr>";
echo "<h3>✅ Next Steps:</h3>";
echo "<ol>";
echo "<li>Delete this file from your server</li>";
echo "<li>Test registration: <a href='https://allfycenter.com/register' target='_blank'>https://allfycenter.com/register</a></li>";
echo "<li>If sessions table exists, the error should be resolved</li>";
echo "</ol>";

echo "<p style='text-align:center;color:#999;margin-top:30px;'>Generated: " . date('Y-m-d H:i:s') . "</p>";
echo "</body></html>";
?>
