<?php
/**
 * Complete Deployment Diagnostic Tool
 * Checks everything before running migrations
 * 
 * Usage: https://yourdomain.com/check-deployment.php?key=check-2026
 */

$SECRET_KEY = 'check-2026';

if (!isset($_GET['key']) || $_GET['key'] !== $SECRET_KEY) {
    die('<h1>Access Denied</h1>');
}

echo "<!DOCTYPE html><html><head>";
echo "<title>Deployment Diagnostic</title>";
echo "<style>
    body { font-family: Arial, sans-serif; max-width: 1200px; margin: 30px auto; padding: 20px; background: #f5f5f5; }
    h2 { color: #333; border-bottom: 3px solid #2196f3; padding-bottom: 10px; }
    h3 { color: #555; margin-top: 30px; }
    .pass { color: green; background: #e8f5e9; padding: 10px; margin: 5px 0; border-left: 4px solid #4caf50; }
    .fail { color: red; background: #ffebee; padding: 10px; margin: 5px 0; border-left: 4px solid #f44336; }
    .warn { color: orange; background: #fff3e0; padding: 10px; margin: 5px 0; border-left: 4px solid #ff9800; }
    .info { color: #1976d2; background: #e3f2fd; padding: 10px; margin: 5px 0; border-left: 4px solid #2196f3; }
    table { width: 100%; border-collapse: collapse; margin: 15px 0; background: white; }
    th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
    th { background: #2196f3; color: white; }
    tr:nth-child(even) { background: #f9f9f9; }
    .section { background: white; padding: 20px; margin: 20px 0; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
    code { background: #263238; color: #aed581; padding: 2px 6px; border-radius: 3px; }
    ul { list-style: none; padding: 0; }
    li { padding: 5px 0; }
</style></head><body>";

echo "<h2>🔍 Complete Deployment Diagnostic</h2>";
echo "<p><em>Generated: " . date('Y-m-d H:i:s') . "</em></p>";

// Find Laravel
$laravelPath = null;
$possiblePaths = [dirname(__DIR__), __DIR__, dirname(dirname(__DIR__))];
foreach ($possiblePaths as $path) {
    if (file_exists($path . '/artisan') && file_exists($path . '/.env')) {
        $laravelPath = $path;
        break;
    }
}

if (!$laravelPath) {
    echo "<div class='fail'><h3>❌ CRITICAL: Laravel Not Found!</h3></div></body></html>";
    exit;
}

echo "<div class='pass'><strong>✅ Laravel Found:</strong> <code>" . htmlspecialchars($laravelPath) . "</code></div>";

$results = [
    'critical' => 0,
    'warnings' => 0,
    'passed' => 0
];

// ===== 1. CHECK .ENV FILE =====
echo "<div class='section'>";
echo "<h3>1️⃣ Environment Configuration (.env)</h3>";
$envFile = $laravelPath . '/.env';
if (file_exists($envFile)) {
    $envContent = @file_get_contents($envFile);
    if ($envContent) {
        preg_match('/DB_CONNECTION=(.*)/', $envContent, $m); $dbConn = isset($m[1]) ? trim($m[1]) : 'N/A';
        preg_match('/DB_HOST=(.*)/', $envContent, $m); $dbHost = isset($m[1]) ? trim($m[1]) : 'N/A';
        preg_match('/DB_DATABASE=(.*)/', $envContent, $m); $dbName = isset($m[1]) ? trim($m[1]) : 'N/A';
        preg_match('/DB_USERNAME=(.*)/', $envContent, $m); $dbUser = isset($m[1]) ? trim($m[1]) : 'N/A';
        preg_match('/DB_PASSWORD=(.*)/', $envContent, $m); $dbPass = isset($m[1]) ? trim($m[1]) : '';
        
        echo "<table>";
        echo "<tr><th>Setting</th><th>Value</th><th>Status</th></tr>";
        echo "<tr><td>DB_CONNECTION</td><td><code>$dbConn</code></td><td>" . ($dbConn === 'mysql' ? '✅' : '⚠️') . "</td></tr>";
        echo "<tr><td>DB_HOST</td><td><code>$dbHost</code></td><td>" . ($dbHost === 'localhost' ? '✅' : '⚠️') . "</td></tr>";
        echo "<tr><td>DB_DATABASE</td><td><code>$dbName</code></td><td>" . ($dbName !== 'N/A' ? '✅' : '❌') . "</td></tr>";
        echo "<tr><td>DB_USERNAME</td><td><code>$dbUser</code></td><td>" . ($dbUser !== 'N/A' && $dbUser !== 'root' ? '✅' : '⚠️') . "</td></tr>";
        echo "</table>";
        
        if ($dbUser === 'root') {
            echo "<div class='warn'>⚠️ WARNING: Using 'root' user. Should use dedicated database user.</div>";
            $results['warnings']++;
        } else {
            $results['passed']++;
        }
    } else {
        echo "<div class='fail'>❌ CRITICAL: Cannot read .env file!</div>";
        $results['critical']++;
    }
} else {
    echo "<div class='fail'>❌ CRITICAL: .env file not found!</div>";
    $results['critical']++;
}
echo "</div>";

// ===== 2. DATABASE CONNECTION TEST =====
echo "<div class='section'>";
echo "<h3>2️⃣ Database Connection Test</h3>";
try {
    $conn = @new mysqli($dbHost ?? 'localhost', $dbUser ?? '', $dbPass ?? '', $dbName ?? '');
    if ($conn->connect_error) {
        echo "<div class='fail'>❌ FAILED: " . htmlspecialchars($conn->connect_error) . "</div>";
        $results['critical']++;
    } else {
        echo "<div class='pass'>✅ SUCCESS: Connected to database <code>$dbName</code></div>";
        
        // Check existing tables
        $tablesResult = $conn->query("SHOW TABLES");
        $tables = [];
        while ($row = $tablesResult->fetch_array()) {
            $tables[] = $row[0];
        }
        
        echo "<p><strong>Existing Tables:</strong> " . count($tables) . "</p>";
        
        $criticalTables = ['users', 'migrations', 'sessions', 'courses', 'categories'];
        echo "<table>";
        echo "<tr><th>Required Table</th><th>Status</th></tr>";
        foreach ($criticalTables as $table) {
            $exists = in_array($table, $tables);
            echo "<tr><td><code>$table</code></td><td>" . ($exists ? '✅ Exists' : '❌ Missing') . "</td></tr>";
            if (!$exists && $table === 'migrations') {
                $results['critical']++;
            } elseif (!$exists) {
                $results['warnings']++;
            } else {
                $results['passed']++;
            }
        }
        echo "</table>";
        
        // Check migrations table
        if (in_array('migrations', $tables)) {
            $migrationsRun = $conn->query("SELECT COUNT(*) as total FROM migrations");
            $count = $migrationsRun->fetch_assoc()['total'];
            echo "<div class='info'>ℹ️ Migrations run: <strong>$count</strong></div>";
        }
        
        $conn->close();
    }
} catch (Exception $e) {
    echo "<div class='fail'>❌ ERROR: " . htmlspecialchars($e->getMessage()) . "</div>";
    $results['critical']++;
}
echo "</div>";

// ===== 3. CHECK APPSERVICEPROVIDER =====
echo "<div class='section'>";
echo "<h3>3️⃣ AppServiceProvider Fix (MySQL Key Length)</h3>";
$appServiceProvider = $laravelPath . '/app/Providers/AppServiceProvider.php';
if (file_exists($appServiceProvider)) {
    $content = @file_get_contents($appServiceProvider);
    if ($content) {
        $hasSchemaImport = strpos($content, 'use Illuminate\Support\Facades\Schema') !== false;
        $hasDefaultStringLength = strpos($content, 'Schema::defaultStringLength') !== false;
        
        if ($hasSchemaImport && $hasDefaultStringLength) {
            echo "<div class='pass'>✅ FIXED: Schema::defaultStringLength(191) is present</div>";
            $results['passed']++;
        } else {
            echo "<div class='fail'>❌ MISSING: Schema::defaultStringLength(191) fix not applied!</div>";
            echo "<p>Add this to AppServiceProvider.php boot() method:</p>";
            echo "<pre>Schema::defaultStringLength(191);</pre>";
            $results['critical']++;
        }
    }
} else {
    echo "<div class='fail'>❌ File not found!</div>";
    $results['critical']++;
}
echo "</div>";

// ===== 4. CHECK MIGRATIONS FOLDER =====
echo "<div class='section'>";
echo "<h3>4️⃣ Migrations Folder Check</h3>";
$migrationsPath = $laravelPath . '/database/migrations';
if (is_dir($migrationsPath)) {
    $files = @scandir($migrationsPath);
    $migrationFiles = array_filter($files, function($f) { return strpos($f, '.php') !== false; });
    $count = count($migrationFiles);
    
    echo "<div class='pass'>✅ Found <strong>$count migration files</strong></div>";
    
    // Check critical migrations
    $criticalMigrations = [
        'create_sessions_table' => false,
        'add_missing_columns_to_courses' => false,
        'create_users_table' => false,
        'create_courses_table' => false
    ];
    
    foreach ($migrationFiles as $file) {
        foreach ($criticalMigrations as $key => $val) {
            if (strpos($file, $key) !== false) {
                $criticalMigrations[$key] = true;
            }
        }
    }
    
    echo "<table>";
    echo "<tr><th>Critical Migration</th><th>Status</th></tr>";
    foreach ($criticalMigrations as $name => $exists) {
        echo "<tr><td><code>$name</code></td><td>" . ($exists ? '✅ Found' : '❌ Missing') . "</td></tr>";
        if ($exists) {
            $results['passed']++;
        } else {
            $results['critical']++;
        }
    }
    echo "</table>";
    
} else {
    echo "<div class='fail'>❌ Migrations folder not found!</div>";
    $results['critical']++;
}
echo "</div>";

// ===== 5. CHECK FOLDER PERMISSIONS =====
echo "<div class='section'>";
echo "<h3>5️⃣ Folder Permissions</h3>";
$folders = [
    'storage/logs',
    'storage/framework/cache',
    'storage/framework/sessions',
    'storage/framework/views',
    'bootstrap/cache'
];

echo "<table>";
echo "<tr><th>Folder</th><th>Exists</th><th>Writable</th></tr>";
foreach ($folders as $folder) {
    $path = $laravelPath . '/' . $folder;
    $exists = is_dir($path);
    $writable = $exists && is_writable($path);
    echo "<tr>";
    echo "<td><code>$folder</code></td>";
    echo "<td>" . ($exists ? '✅' : '❌') . "</td>";
    echo "<td>" . ($writable ? '✅' : '❌') . "</td>";
    echo "</tr>";
    
    if ($exists && $writable) {
        $results['passed']++;
    } elseif ($exists) {
        $results['warnings']++;
    } else {
        $results['critical']++;
    }
}
echo "</table>";
echo "</div>";

// ===== 6. CHECK VENDOR FOLDER =====
echo "<div class='section'>";
echo "<h3>6️⃣ Dependencies (Composer)</h3>";
$vendorPath = $laravelPath . '/vendor';
if (is_dir($vendorPath)) {
    $autoload = $vendorPath . '/autoload.php';
    if (file_exists($autoload)) {
        echo "<div class='pass'>✅ Composer dependencies installed</div>";
        $results['passed']++;
    } else {
        echo "<div class='fail'>❌ vendor/autoload.php missing!</div>";
        $results['critical']++;
    }
} else {
    echo "<div class='fail'>❌ CRITICAL: vendor/ folder missing! Run: composer install</div>";
    $results['critical']++;
}
echo "</div>";

// ===== SUMMARY =====
echo "<div class='section'>";
echo "<h3>📊 Summary Report</h3>";

$total = $results['critical'] + $results['warnings'] + $results['passed'];
$criticalPct = $total > 0 ? round(($results['critical'] / $total) * 100) : 0;
$warningPct = $total > 0 ? round(($results['warnings'] / $total) * 100) : 0;
$passedPct = $total > 0 ? round(($results['passed'] / $total) * 100) : 0;

echo "<table>";
echo "<tr><th>Status</th><th>Count</th><th>Percentage</th></tr>";
echo "<tr style='background:#ffebee;'><td>❌ Critical Issues</td><td><strong>" . $results['critical'] . "</strong></td><td>$criticalPct%</td></tr>";
echo "<tr style='background:#fff3e0;'><td>⚠️ Warnings</td><td><strong>" . $results['warnings'] . "</strong></td><td>$warningPct%</td></tr>";
echo "<tr style='background:#e8f5e9;'><td>✅ Passed</td><td><strong>" . $results['passed'] . "</strong></td><td>$passedPct%</td></tr>";
echo "</table>";

if ($results['critical'] === 0) {
    echo "<div class='pass'>";
    echo "<h3>✅ READY TO DEPLOY!</h3>";
    echo "<p>All critical checks passed. You can proceed with:</p>";
    echo "<ol>";
    echo "<li>Clear cache: <a href='delete-cache.php'>delete-cache.php</a></li>";
    echo "<li>Run migrations: <a href='run-migrations.php?key=migrate-2026-secure'>run-migrations.php</a></li>";
    echo "</ol>";
    echo "</div>";
} else {
    echo "<div class='fail'>";
    echo "<h3>❌ NOT READY</h3>";
    echo "<p><strong>" . $results['critical'] . " critical issue(s)</strong> must be fixed before deployment.</p>";
    echo "</div>";
}
echo "</div>";

echo "<hr>";
echo "<p style='text-align:center;color:#999;'><strong>DELETE THIS FILE after checking!</strong></p>";
echo "</body></html>";
?>
