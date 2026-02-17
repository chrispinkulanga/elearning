<?php

/**
 * Pre-Deployment Validation Script
 * Run this before deploying to production to catch common issues
 */

echo "🔍 Running Pre-Deployment Checks...\n\n";

$errors = [];
$warnings = [];

// 1. Check PHP Version
echo "1. Checking PHP version...\n";
$phpVersion = phpversion();
$requiredVersion = '8.2.0';
if (version_compare($phpVersion, $requiredVersion, '<')) {
    $errors[] = "PHP version $phpVersion is below required $requiredVersion";
    echo "   ❌ PHP version too old: $phpVersion (required: $requiredVersion)\n";
} else {
    echo "   ✅ PHP version OK: $phpVersion\n";
}

// 2. Check Composer autoload
echo "\n2. Checking Composer autoload...\n";
if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    $errors[] = "Composer dependencies not installed. Run 'composer install'";
    echo "   ❌ Composer dependencies missing\n";
} else {
    echo "   ✅ Composer dependencies installed\n";
    
    // Check for PSR-4 violations
    echo "   Checking PSR-4 compliance...\n";
    $output = shell_exec('composer dump-autoload 2>&1');
    if (strpos($output, 'does not comply with psr-4') !== false) {
        $errors[] = "PSR-4 autoloading violations detected. Check composer output above.";
        echo "   ❌ PSR-4 violations found!\n";
        echo "   Run: composer dump-autoload\n";
    } else {
        echo "   ✅ PSR-4 compliance OK\n";
    }
}

// 3. Check .env file
echo "\n3. Checking environment configuration...\n";
if (!file_exists(__DIR__ . '/.env')) {
    $warnings[] = ".env file missing. Copy from .env.example";
    echo "   ⚠️  .env file not found\n";
} else {
    echo "   ✅ .env file exists\n";
    
    // Check critical environment variables
    $envContent = file_get_contents(__DIR__ . '/.env');
    $requiredVars = ['APP_KEY', 'DB_CONNECTION', 'DB_HOST', 'DB_DATABASE', 'DB_USERNAME'];
    foreach ($requiredVars as $var) {
        if (strpos($envContent, $var . '=') === false || 
            preg_match("/^{$var}=(\s*|$)/m", $envContent)) {
            $errors[] = "Required environment variable $var is missing or empty";
            echo "   ❌ $var not set\n";
        }
    }
    if (empty($errors)) {
        echo "   ✅ Critical environment variables set\n";
    }
}

// 4. Check storage permissions
echo "\n4. Checking storage permissions...\n";
$storageDirs = [
    'storage/app',
    'storage/framework/cache',
    'storage/framework/sessions',
    'storage/framework/views',
    'storage/logs',
    'bootstrap/cache'
];

foreach ($storageDirs as $dir) {
    $path = __DIR__ . '/' . $dir;
    if (!is_dir($path)) {
        $errors[] = "Directory $dir does not exist";
        echo "   ❌ $dir missing\n";
    } elseif (!is_writable($path)) {
        $warnings[] = "Directory $dir is not writable";
        echo "   ⚠️  $dir not writable\n";
    }
}
if (empty($errors)) {
    echo "   ✅ Storage directories OK\n";
}

// 5. Check database connection
echo "\n5. Checking database connection...\n";
try {
    require __DIR__ . '/vendor/autoload.php';
    $app = require_once __DIR__ . '/bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
    
    DB::connection()->getPdo();
    echo "   ✅ Database connection OK\n";
} catch (Exception $e) {
    $warnings[] = "Database connection failed: " . $e->getMessage();
    echo "   ⚠️  Database connection failed (may be expected if DB not configured)\n";
}

// 6. Check for common issues
echo "\n6. Checking for common issues...\n";

// Check if APP_DEBUG is set to false for production
if (file_exists(__DIR__ . '/.env')) {
    $envContent = file_get_contents(__DIR__ . '/.env');
    if (strpos($envContent, 'APP_DEBUG=true') !== false) {
        $warnings[] = "APP_DEBUG is set to true. Should be false in production";
        echo "   ⚠️  APP_DEBUG is true (should be false in production)\n";
    } else {
        echo "   ✅ APP_DEBUG is false\n";
    }
}

// Check if migrations are up to date
echo "\n7. Checking migrations...\n";
try {
    $migrations = DB::table('migrations')->count();
    echo "   ✅ Migrations table exists ($migrations migrations)\n";
} catch (Exception $e) {
    $warnings[] = "Migrations table not found. Run 'php artisan migrate'";
    echo "   ⚠️  Migrations table not found\n";
}

// Summary
echo "\n" . str_repeat("=", 50) . "\n";
echo "📊 SUMMARY\n";
echo str_repeat("=", 50) . "\n";

if (empty($errors) && empty($warnings)) {
    echo "✅ All checks passed! Ready for deployment.\n";
    exit(0);
}

if (!empty($errors)) {
    echo "❌ ERRORS FOUND (" . count($errors) . "):\n";
    foreach ($errors as $error) {
        echo "   - $error\n";
    }
    echo "\n⚠️  Fix these errors before deploying!\n";
}

if (!empty($warnings)) {
    echo "\n⚠️  WARNINGS (" . count($warnings) . "):\n";
    foreach ($warnings as $warning) {
        echo "   - $warning\n";
    }
    echo "\n💡 Review these warnings before deploying.\n";
}

exit(empty($errors) ? 0 : 1);

