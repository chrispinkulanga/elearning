<?php
/**
 * API & Registration Diagnostic Tool
 * Tests backend server and registration endpoint
 * 
 * Usage: https://allfycenter.com/test-api.php?key=test-2026
 */

$SECRET_KEY = 'test-2026';

if (!isset($_GET['key']) || $_GET['key'] !== $SECRET_KEY) {
    die('<h1>Access Denied</h1>');
}

header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html><html><head>";
echo "<title>API Diagnostic</title>";
echo "<style>
    body { font-family: Arial, sans-serif; max-width: 1200px; margin: 30px auto; padding: 20px; background: #f5f5f5; }
    h2 { color: #333; border-bottom: 3px solid #2196f3; padding-bottom: 10px; }
    h3 { color: #555; margin-top: 30px; }
    .pass { color: green; background: #e8f5e9; padding: 15px; margin: 10px 0; border-left: 4px solid #4caf50; }
    .fail { color: red; background: #ffebee; padding: 15px; margin: 10px 0; border-left: 4px solid #f44336; }
    .warn { color: orange; background: #fff3e0; padding: 15px; margin: 10px 0; border-left: 4px solid #ff9800; }
    .info { color: #1976d2; background: #e3f2fd; padding: 15px; margin: 10px 0; border-left: 4px solid #2196f3; }
    .section { background: white; padding: 20px; margin: 20px 0; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
    code { background: #263238; color: #aed581; padding: 2px 6px; border-radius: 3px; font-size: 12px; }
    pre { background: #263238; color: #aed581; padding: 15px; overflow-x: auto; border-radius: 5px; }
    table { width: 100%; border-collapse: collapse; margin: 15px 0; background: white; }
    th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
    th { background: #2196f3; color: white; }
</style></head><body>";

echo "<h2>🔍 API & Registration Diagnostic</h2>";
echo "<p><em>Generated: " . date('Y-m-d H:i:s') . "</em></p>";

// Find Laravel
$laravelPath = null;
$possiblePaths = [dirname(__DIR__), __DIR__];
foreach ($possiblePaths as $path) {
    if (file_exists($path . '/artisan')) {
        $laravelPath = $path;
        break;
    }
}

if (!$laravelPath) {
    echo "<div class='fail'><h3>❌ Laravel Not Found!</h3></div></body></html>";
    exit;
}

echo "<div class='pass'><strong>✅ Laravel Path:</strong> <code>" . htmlspecialchars($laravelPath) . "</code></div>";

// ===== 1. CHECK SERVER STATUS =====
echo "<div class='section'>";
echo "<h3>1️⃣ Server Status</h3>";
echo "<table>";
echo "<tr><th>Check</th><th>Status</th><th>Details</th></tr>";

// PHP Version
$phpVersion = PHP_VERSION;
$phpOk = version_compare($phpVersion, '8.1.0', '>=');
echo "<tr>";
echo "<td>PHP Version</td>";
echo "<td>" . ($phpOk ? '✅' : '⚠️') . "</td>";
echo "<td><code>$phpVersion</code></td>";
echo "</tr>";

// Laravel Installation
$laravelOk = file_exists($laravelPath . '/artisan');
echo "<tr>";
echo "<td>Laravel Files</td>";
echo "<td>" . ($laravelOk ? '✅' : '❌') . "</td>";
echo "<td>" . ($laravelOk ? 'Present' : 'Missing') . "</td>";
echo "</tr>";

// .env file
$envOk = file_exists($laravelPath . '/.env');
echo "<tr>";
echo "<td>.env Configuration</td>";
echo "<td>" . ($envOk ? '✅' : '❌') . "</td>";
echo "<td>" . ($envOk ? 'Present' : 'Missing') . "</td>";
echo "</tr>";

echo "</table>";
echo "</div>";

// ===== 2. DATABASE CONNECTION =====
echo "<div class='section'>";
echo "<h3>2️⃣ Database Connection</h3>";

if ($envOk) {
    $envContent = file_get_contents($laravelPath . '/.env');
    preg_match('/DB_HOST=(.*)/', $envContent, $m); $dbHost = isset($m[1]) ? trim($m[1]) : 'localhost';
    preg_match('/DB_DATABASE=(.*)/', $envContent, $m); $dbName = isset($m[1]) ? trim($m[1]) : '';
    preg_match('/DB_USERNAME=(.*)/', $envContent, $m); $dbUser = isset($m[1]) ? trim($m[1]) : '';
    preg_match('/DB_PASSWORD=(.*)/', $envContent, $m); $dbPass = isset($m[1]) ? trim($m[1]) : '';
    
    $conn = @new mysqli($dbHost, $dbUser, $dbPass, $dbName);
    
    if ($conn->connect_error) {
        echo "<div class='fail'>";
        echo "<strong>❌ Database Connection FAILED</strong><br>";
        echo "Error: " . htmlspecialchars($conn->connect_error);
        echo "</div>";
    } else {
        echo "<div class='pass'>";
        echo "<strong>✅ Database Connected Successfully</strong><br>";
        echo "Database: <code>$dbName</code>";
        echo "</div>";
        
        // Check critical tables
        $tables = [];
        $result = $conn->query("SHOW TABLES");
        while ($row = $result->fetch_array()) {
            $tables[] = $row[0];
        }
        
        $criticalTables = ['users', 'sessions', 'migrations'];
        echo "<table>";
        echo "<tr><th>Required Table</th><th>Status</th><th>Details</th></tr>";
        
        foreach ($criticalTables as $table) {
            $exists = in_array($table, $tables);
            echo "<tr>";
            echo "<td><code>$table</code></td>";
            echo "<td>" . ($exists ? '✅' : '❌') . "</td>";
            
            if ($exists) {
                $countResult = $conn->query("SELECT COUNT(*) as total FROM `$table`");
                $count = $countResult->fetch_assoc()['total'];
                echo "<td>$count records</td>";
            } else {
                echo "<td style='color:red;'>MISSING - Run migrations!</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
        
        $conn->close();
    }
} else {
    echo "<div class='fail'>❌ Cannot check - .env file missing</div>";
}
echo "</div>";

// ===== 3. TEST API ENDPOINTS =====
echo "<div class='section'>";
echo "<h3>3️⃣ API Endpoints Test</h3>";

$baseUrl = 'https://' . $_SERVER['HTTP_HOST'];
$apiEndpoints = [
    'Health Check' => '/api/health',
    'CSRF Token' => '/sanctum/csrf-cookie',
    'User Info' => '/api/user',
];

echo "<table>";
echo "<tr><th>Endpoint</th><th>Status</th><th>Response</th></tr>";

foreach ($apiEndpoints as $name => $endpoint) {
    $url = $baseUrl . $endpoint;
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    echo "<tr>";
    echo "<td><code>$endpoint</code></td>";
    
    if ($httpCode >= 200 && $httpCode < 300) {
        echo "<td style='color:green;'>✅ $httpCode</td>";
        echo "<td>OK</td>";
    } elseif ($httpCode == 404) {
        echo "<td style='color:orange;'>⚠️ $httpCode</td>";
        echo "<td>Not Found</td>";
    } elseif ($httpCode == 401 || $httpCode == 419) {
        echo "<td style='color:blue;'>ℹ️ $httpCode</td>";
        echo "<td>Auth Required (Expected)</td>";
    } elseif ($error) {
        echo "<td style='color:red;'>❌ Error</td>";
        echo "<td>" . htmlspecialchars($error) . "</td>";
    } else {
        echo "<td style='color:red;'>❌ $httpCode</td>";
        echo "<td>Failed</td>";
    }
    echo "</tr>";
}
echo "</table>";
echo "</div>";

// ===== 4. TEST REGISTRATION ENDPOINT =====
echo "<div class='section'>";
echo "<h3>4️⃣ Registration Endpoint Test</h3>";

$registerUrl = $baseUrl . '/api/register';

echo "<p><strong>Testing:</strong> <code>POST $registerUrl</code></p>";

// Test with dummy data
$testData = json_encode([
    'name' => 'Test User',
    'email' => 'test' . time() . '@example.com',
    'password' => 'TestPassword123!',
    'password_confirmation' => 'TestPassword123!',
    'role' => 'student'
]);

$ch = curl_init($registerUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $testData);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
    'X-Requested-With: XMLHttpRequest'
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "<table>";
echo "<tr><th>Metric</th><th>Value</th></tr>";
echo "<tr><td>HTTP Status Code</td><td><code>$httpCode</code></td></tr>";

if ($error) {
    echo "<tr><td>cURL Error</td><td style='color:red;'>" . htmlspecialchars($error) . "</td></tr>";
}

echo "<tr><td>Response Body</td><td><pre style='max-height:200px;overflow:auto;'>" . htmlspecialchars($response) . "</pre></td></tr>";
echo "</table>";

// Analyze response
if ($httpCode == 201 || $httpCode == 200) {
    echo "<div class='pass'>✅ Registration endpoint is working!</div>";
} elseif ($httpCode == 500) {
    echo "<div class='fail'>";
    echo "<strong>❌ Internal Server Error (500)</strong><br>";
    echo "Possible causes:<br>";
    echo "<ul>";
    echo "<li>Database connection failed</li>";
    echo "<li>Missing 'sessions' table</li>";
    echo "<li>Missing required columns in 'users' table</li>";
    echo "<li>CSRF token mismatch</li>";
    echo "</ul>";
    
    $responseData = json_decode($response, true);
    if (isset($responseData['message'])) {
        echo "<strong>Error Message:</strong> " . htmlspecialchars($responseData['message']);
    }
    echo "</div>";
} elseif ($httpCode == 422) {
    echo "<div class='warn'>⚠️ Validation Error (422) - Expected for test data</div>";
} elseif ($httpCode == 419) {
    echo "<div class='warn'>⚠️ CSRF Token Mismatch (419)</div>";
} else {
    echo "<div class='fail'>❌ Unexpected response: HTTP $httpCode</div>";
}

echo "</div>";

// ===== 5. CHECK LARAVEL LOGS =====
echo "<div class='section'>";
echo "<h3>5️⃣ Recent Laravel Errors</h3>";

$logFile = $laravelPath . '/storage/logs/laravel.log';
if (file_exists($logFile)) {
    $logContent = file_get_contents($logFile);
    $lines = explode("\n", $logContent);
    $recentErrors = array_slice(array_reverse($lines), 0, 20);
    
    $errorLines = array_filter($recentErrors, function($line) {
        return stripos($line, 'error') !== false || 
               stripos($line, 'exception') !== false ||
               stripos($line, 'failed') !== false;
    });
    
    if (empty($errorLines)) {
        echo "<div class='pass'>✅ No recent errors in log file</div>";
    } else {
        echo "<div class='warn'>";
        echo "<strong>⚠️ Recent Errors Found:</strong>";
        echo "<pre style='max-height:300px;overflow:auto;'>";
        foreach ($errorLines as $line) {
            echo htmlspecialchars($line) . "\n";
        }
        echo "</pre>";
        echo "</div>";
    }
} else {
    echo "<div class='info'>ℹ️ Log file not found (no errors logged yet)</div>";
}

echo "</div>";

// ===== 6. CORS CHECK =====
echo "<div class='section'>";
echo "<h3>6️⃣ CORS Configuration</h3>";

$corsMiddleware = $laravelPath . '/app/Http/Middleware/Cors.php';
if (file_exists($corsMiddleware)) {
    echo "<div class='pass'>✅ CORS Middleware exists</div>";
    
    $corsContent = file_get_contents($corsMiddleware);
    $hasAllowOrigin = strpos($corsContent, 'Access-Control-Allow-Origin') !== false;
    $hasAllowMethods = strpos($corsContent, 'Access-Control-Allow-Methods') !== false;
    
    echo "<table>";
    echo "<tr><th>Check</th><th>Status</th></tr>";
    echo "<tr><td>Allow-Origin Header</td><td>" . ($hasAllowOrigin ? '✅ Present' : '❌ Missing') . "</td></tr>";
    echo "<tr><td>Allow-Methods Header</td><td>" . ($hasAllowMethods ? '✅ Present' : '❌ Missing') . "</td></tr>";
    echo "</table>";
} else {
    echo "<div class='warn'>⚠️ Custom CORS middleware not found</div>";
}

echo "</div>";

// ===== SUMMARY & RECOMMENDATIONS =====
echo "<div class='section'>";
echo "<h3>📋 Recommendations</h3>";

echo "<div class='info'>";
echo "<strong>Based on the diagnostic results:</strong>";
echo "<ol>";
echo "<li>If database connection failed: Check .env credentials</li>";
echo "<li>If 'sessions' table is missing: <a href='run-migrations.php?key=migrate-2026-secure'>Run Migrations</a></li>";
echo "<li>If HTTP 500 error: Check Laravel logs above for exact error</li>";
echo "<li>If CSRF error: Clear browser cookies and try again</li>";
echo "<li>If 'Network error' in frontend: Check API endpoints test results</li>";
echo "</ol>";
echo "</div>";

echo "</div>";

echo "<hr>";
echo "<p style='text-align:center;color:#999;'><strong>DELETE THIS FILE after diagnosing!</strong></p>";
echo "</body></html>";
?>
