# PowerShell Script for Pre-Deployment Validation
# Run this script before deploying to catch issues early

Write-Host "🔍 Running Pre-Deployment Validation..." -ForegroundColor Cyan
Write-Host ""

$errors = @()
$warnings = @()

# 1. Check PHP Version
Write-Host "1. Checking PHP version..." -ForegroundColor Yellow
try {
    $phpVersion = (php -v 2>&1 | Select-String "PHP (\d+\.\d+\.\d+)" | ForEach-Object { $_.Matches.Groups[1].Value })
    if ($phpVersion) {
        $requiredVersion = "8.2.0"
        if ([version]$phpVersion -lt [version]$requiredVersion) {
            $errors += "PHP version $phpVersion is below required $requiredVersion"
            Write-Host "   ❌ PHP version too old: $phpVersion (required: $requiredVersion)" -ForegroundColor Red
        } else {
            Write-Host "   ✅ PHP version OK: $phpVersion" -ForegroundColor Green
        }
    } else {
        $errors += "PHP not found or version cannot be determined"
        Write-Host "   ❌ PHP not found" -ForegroundColor Red
    }
} catch {
    $errors += "Error checking PHP version: $_"
    Write-Host "   ❌ Error checking PHP" -ForegroundColor Red
}

# 2. Check Composer
Write-Host "`n2. Checking Composer..." -ForegroundColor Yellow
try {
    $composerVersion = (composer --version 2>&1 | Select-String "Composer version (\d+\.\d+\.\d+)" | ForEach-Object { $_.Matches.Groups[1].Value })
    if ($composerVersion) {
        Write-Host "   ✅ Composer installed: $composerVersion" -ForegroundColor Green
        
        # Check if vendor directory exists
        if (Test-Path "vendor\autoload.php") {
            Write-Host "   ✅ Composer dependencies installed" -ForegroundColor Green
            
            # Check for PSR-4 issues
            Write-Host "   Checking PSR-4 compliance..." -ForegroundColor Yellow
            $composerOutput = composer dump-autoload 2>&1 | Out-String
            if ($composerOutput -match "does not comply with psr-4") {
                $errors += "PSR-4 autoloading violations detected"
                Write-Host "   ❌ PSR-4 violations found!" -ForegroundColor Red
            } else {
                Write-Host "   ✅ PSR-4 compliance OK" -ForegroundColor Green
            }
        } else {
            $errors += "Composer dependencies not installed. Run 'composer install'"
            Write-Host "   ❌ Composer dependencies missing" -ForegroundColor Red
        }
    } else {
        $errors += "Composer not found"
        Write-Host "   ❌ Composer not found" -ForegroundColor Red
    }
} catch {
    $errors += "Error checking Composer: $_"
    Write-Host "   ❌ Error checking Composer" -ForegroundColor Red
}

# 3. Check .env file
Write-Host "`n3. Checking environment configuration..." -ForegroundColor Yellow
if (Test-Path ".env") {
    Write-Host "   ✅ .env file exists" -ForegroundColor Green
    
    $envContent = Get-Content ".env" -Raw
    $requiredVars = @('APP_KEY', 'DB_CONNECTION', 'DB_HOST', 'DB_DATABASE', 'DB_USERNAME')
    
    foreach ($var in $requiredVars) {
        if ($envContent -notmatch "^$var\s*=" -or $envContent -match "^$var\s*=\s*$") {
            $errors += "Required environment variable $var is missing or empty"
            Write-Host "   ❌ $var not set" -ForegroundColor Red
        }
    }
    
    if ($envContent -match "APP_DEBUG\s*=\s*true") {
        $warnings += "APP_DEBUG is set to true. Should be false in production"
        Write-Host "   ⚠️  APP_DEBUG is true (should be false in production)" -ForegroundColor Yellow
    } else {
        Write-Host "   ✅ APP_DEBUG is false" -ForegroundColor Green
    }
} else {
    $warnings += ".env file missing. Copy from .env.example"
    Write-Host "   ⚠️  .env file not found" -ForegroundColor Yellow
}

# 4. Check storage directories
Write-Host "`n4. Checking storage permissions..." -ForegroundColor Yellow
$storageDirs = @(
    "storage\app",
    "storage\framework\cache",
    "storage\framework\sessions",
    "storage\framework\views",
    "storage\logs",
    "bootstrap\cache"
)

$allStorageOK = $true
foreach ($dir in $storageDirs) {
    if (-not (Test-Path $dir)) {
        $errors += "Directory $dir does not exist"
        Write-Host "   ❌ $dir missing" -ForegroundColor Red
        $allStorageOK = $false
    }
}

if ($allStorageOK) {
    Write-Host "   ✅ Storage directories OK" -ForegroundColor Green
}

# 5. Check for common Laravel files
Write-Host "`n5. Checking Laravel structure..." -ForegroundColor Yellow
$requiredFiles = @(
    "artisan",
    "composer.json",
    "app",
    "config",
    "routes"
)

$allFilesOK = $true
foreach ($file in $requiredFiles) {
    if (-not (Test-Path $file)) {
        $errors += "Required file/directory $file is missing"
        Write-Host "   ❌ $file missing" -ForegroundColor Red
        $allFilesOK = $false
    }
}

if ($allFilesOK) {
    Write-Host "   ✅ Laravel structure OK" -ForegroundColor Green
}

# Summary
Write-Host "`n" + ("=" * 50) -ForegroundColor Cyan
Write-Host "📊 SUMMARY" -ForegroundColor Cyan
Write-Host ("=" * 50) -ForegroundColor Cyan

if ($errors.Count -eq 0 -and $warnings.Count -eq 0) {
    Write-Host "✅ All checks passed! Ready for deployment." -ForegroundColor Green
    exit 0
}

if ($errors.Count -gt 0) {
    Write-Host "`n❌ ERRORS FOUND ($($errors.Count)):" -ForegroundColor Red
    foreach ($error in $errors) {
        Write-Host "   - $error" -ForegroundColor Red
    }
    Write-Host "`n⚠️  Fix these errors before deploying!" -ForegroundColor Yellow
}

if ($warnings.Count -gt 0) {
    Write-Host "`n⚠️  WARNINGS ($($warnings.Count)):" -ForegroundColor Yellow
    foreach ($warning in $warnings) {
        Write-Host "   - $warning" -ForegroundColor Yellow
    }
    Write-Host "`n💡 Review these warnings before deploying." -ForegroundColor Yellow
}

exit ($errors.Count -gt 0 ? 1 : 0)

