<?php
/**
 * Registration Flow Test Script
 * Tests the complete flow from UI API call to database
 */

// Security: Require access key
$requiredKey = 'test-reg-2026';
$providedKey = $_GET['key'] ?? $_POST['key'] ?? null;

if ($providedKey !== $requiredKey) {
    http_response_code(403);
    header('Content-Type: text/plain');
    die('Access denied. Usage: test-registration-flow.php?key=test-reg-2026');
}

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$results = [
    'timestamp' => date('Y-m-d H:i:s'),
    'tests' => [],
    'server_info' => [
        'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'unknown',
        'script_filename' => $_SERVER['SCRIPT_FILENAME'] ?? 'unknown',
        'current_dir' => __DIR__
    ]
];

// Try to load Laravel, but continue even if it fails
$laravelLoaded = false;
try {
    $possiblePaths = [
        __DIR__.'/../vendor/autoload.php',
        __DIR__.'/../../vendor/autoload.php',
        __DIR__.'/../../../vendor/autoload.php',
    ];
    
    $autoloadPath = null;
    foreach ($possiblePaths as $path) {
        if (file_exists($path)) {
            $autoloadPath = $path;
            break;
        }
    }
    
    if ($autoloadPath) {
        require $autoloadPath;
        
        $bootstrapPaths = [
            __DIR__.'/../bootstrap/app.php',
            __DIR__.'/../../bootstrap/app.php',
            __DIR__.'/../../../bootstrap/app.php',
        ];
        
        $bootstrapPath = null;
        foreach ($bootstrapPaths as $path) {
            if (file_exists($path)) {
                $bootstrapPath = $path;
                break;
            }
        }
        
        if ($bootstrapPath) {
            $app = require_once $bootstrapPath;
            $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
            $laravelLoaded = true;
        }
    }
} catch (Exception $e) {
    $results['tests']['laravel_bootstrap'] = [
        'status' => 'FAILED',
        'message' => 'Could not load Laravel, using direct database connection',
        'error' => $e->getMessage()
    ];
}

// Database connection (works with or without Laravel)
$db = null;
$database = '';
$host = '';
$username = '';
$password = '';

if ($laravelLoaded) {
    // Laravel is loaded, we'll use Illuminate\Support\Facades\DB
    $db = 'laravel';
} else {
    // Direct PDO connection from .env file or config
    try {
        // Try to load .env file
        $envPath = __DIR__.'/../.env';
        if (!file_exists($envPath)) {
            $envPath = __DIR__.'/../../.env';
        }
        if (!file_exists($envPath)) {
            $envPath = __DIR__.'/../../../.env';
        }
        
        $envVars = [];
        if (file_exists($envPath)) {
            $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0) continue;
                if (strpos($line, '=') === false) continue;
                list($name, $value) = explode('=', $line, 2);
                $envVars[trim($name)] = trim($value);
            }
        }
        
        $host = $envVars['DB_HOST'] ?? 'localhost';
        $database = $envVars['DB_DATABASE'] ?? '';
        $username = $envVars['DB_USERNAME'] ?? '';
        $password = $envVars['DB_PASSWORD'] ?? '';
        
        if ($database && $username) {
            $db = new PDO("mysql:host=$host;dbname=$database", $username, $password);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
    } catch (Exception $e) {
        $results['tests']['direct_db_connection'] = [
            'status' => 'FAILED',
            'message' => 'Could not establish direct database connection',
            'error' => $e->getMessage()
        ];
    }
}

// TEST 1: Database Connection
try {
    if ($laravelLoaded) {
        \Illuminate\Support\Facades\DB::connection()->getPdo();
        $dbName = env('DB_DATABASE');
        $dbHost = env('DB_HOST');
    } elseif ($db && $db !== 'laravel') {
        $dbName = $database;
        $dbHost = $host;
    }
    
    $results['tests']['database_connection'] = [
        'status' => 'SUCCESS',
        'message' => 'Database connected successfully',
        'method' => $laravelLoaded ? 'Laravel' : 'PDO',
        'database' => $dbName ?? 'unknown',
        'host' => $dbHost ?? 'unknown'
    ];
} catch (Exception $e) {
    $results['tests']['database_connection'] = [
        'status' => 'FAILED',
        'message' => 'Database connection failed',
        'error' => $e->getMessage()
    ];
}

// TEST 2: Check Users Table
try {
    if ($laravelLoaded) {
        $userCount = \Illuminate\Support\Facades\DB::table('users')->count();
    } elseif ($db && $db !== 'laravel') {
        $stmt = $db->query("SELECT COUNT(*) as count FROM users");
        $userCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    }
    
    $results['tests']['users_table'] = [
        'status' => 'SUCCESS',
        'message' => 'Users table exists and accessible',
        'user_count' => $userCount ?? 0
    ];
} catch (Exception $e) {
    $results['tests']['users_table'] = [
        'status' => 'FAILED',
        'message' => 'Cannot access users table',
        'error' => $e->getMessage()
    ];
}

// TEST 3: Check Roles Table
try {
    if ($laravelLoaded) {
        $roles = \Illuminate\Support\Facades\DB::table('roles')->select('id', 'name')->get();
        $roleNames = $roles->pluck('name')->toArray();
    } elseif ($db && $db !== 'laravel') {
        $stmt = $db->query("SELECT id, name FROM roles");
        $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $roleNames = array_column($roles, 'name');
    }
    
    $results['tests']['roles_table'] = [
        'status' => 'SUCCESS',
        'message' => 'Roles table exists',
        'roles' => $roleNames ?? []
    ];
} catch (Exception $e) {
    $results['tests']['roles_table'] = [
        'status' => 'FAILED',
        'message' => 'Cannot access roles table',
        'error' => $e->getMessage()
    ];
}

// TEST 4: Test Registration Endpoint
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        $results['tests']['registration_attempt'] = [
            'status' => 'TESTING',
            'received_data' => $input
        ];
        
        // Validate required fields
        $required = ['name', 'email', 'password', 'role'];
        $missing = [];
        foreach ($required as $field) {
            if (empty($input[$field])) {
                $missing[] = $field;
            }
        }
        
        if (!empty($missing)) {
            $results['tests']['registration_attempt'] = [
                'status' => 'FAILED',
                'message' => 'Missing required fields',
                'missing_fields' => $missing
            ];
        } else {
            // Check if email exists
            $existingUser = null;
            if ($laravelLoaded) {
                $existingUser = \Illuminate\Support\Facades\DB::table('users')
                    ->where('email', $input['email'])
                    ->first();
            } elseif ($db && $db !== 'laravel') {
                $stmt = $db->prepare("SELECT id, name, email FROM users WHERE email = ?");
                $stmt->execute([$input['email']]);
                $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);
            }
            
            if ($existingUser) {
                $results['tests']['registration_attempt'] = [
                    'status' => 'FAILED',
                    'message' => 'Email already exists',
                    'existing_user' => [
                        'id' => $existingUser->id ?? $existingUser['id'],
                        'name' => $existingUser->name ?? $existingUser['name'],
                        'email' => $existingUser->email ?? $existingUser['email']
                    ]
                ];
            } else {
                // Try to insert user
                try {
                    if ($laravelLoaded) {
                        $userId = \Illuminate\Support\Facades\DB::table('users')->insertGetId([
                            'name' => $input['name'],
                            'email' => $input['email'],
                            'password' => password_hash($input['password'], PASSWORD_DEFAULT),
                            'email_verified_at' => null,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);
                        
                        // Assign role
                        $role = \Illuminate\Support\Facades\DB::table('roles')->where('name', $input['role'])->first();
                        if ($role) {
                            \Illuminate\Support\Facades\DB::table('model_has_roles')->insert([
                                'role_id' => $role->id,
                                'model_type' => 'App\\Models\\User',
                                'model_id' => $userId
                            ]);
                        }
                    } elseif ($db && $db !== 'laravel') {
                        $hashedPassword = password_hash($input['password'], PASSWORD_DEFAULT);
                        $stmt = $db->prepare("INSERT INTO users (name, email, password, created_at, updated_at) VALUES (?, ?, ?, ?, ?)");
                        $now = date('Y-m-d H:i:s');
                        $stmt->execute([$input['name'], $input['email'], $hashedPassword, $now, $now]);
                        $userId = $db->lastInsertId();
                        
                        // Assign role
                        $stmt = $db->prepare("SELECT id, name FROM roles WHERE name = ?");
                        $stmt->execute([$input['role']]);
                        $role = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        if ($role) {
                            $stmt = $db->prepare("INSERT INTO model_has_roles (role_id, model_type, model_id) VALUES (?, ?, ?)");
                            $stmt->execute([$role['id'], 'App\\Models\\User', $userId]);
                        }
                    }
                    
                    $results['tests']['registration_attempt'] = [
                        'status' => 'SUCCESS',
                        'message' => 'User registered successfully',
                        'user_id' => $userId,
                        'role_assigned' => $role ? ($role->name ?? $role['name']) : 'none'
                    ];
                } catch (Exception $e) {
                    $results['tests']['registration_attempt'] = [
                        'status' => 'FAILED',
                        'message' => 'Failed to insert user',
                        'error' => $e->getMessage()
                    ];
                }
            }
        }
    } catch (Exception $e) {
        $results['tests']['registration_attempt'] = [
            'status' => 'FAILED',
            'message' => 'Registration test error',
            'error' => $e->getMessage()
        ];
    }
}

// TEST 5: Check API Routes
try {
    $routes = [
        '/api/auth/register',
        '/api/auth/login',
        '/api/register',
        '/api/login'
    ];
    
    $results['tests']['api_routes'] = [
        'status' => 'INFO',
        'message' => 'Available registration endpoints',
        'routes' => $routes
    ];
} catch (Exception $e) {
    $results['tests']['api_routes'] = [
        'status' => 'FAILED',
        'error' => $e->getMessage()
    ];
}

// TEST 6: Check Laravel Configuration
$results['tests']['laravel_config'] = [
    'status' => 'INFO',
    'app_url' => env('APP_URL'),
    'app_env' => env('APP_ENV'),
    'app_debug' => env('APP_DEBUG'),
    'sanctum_stateful_domains' => env('SANCTUM_STATEFUL_DOMAINS'),
    'session_driver' => env('SESSION_DRIVER')
];

// TEST 7: Check Recent Users
try {
    if ($laravelLoaded) {
        $recentUsers = \Illuminate\Support\Facades\DB::table('users')
            ->select('id', 'name', 'email', 'created_at')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        $userList = $recentUsers->map(function($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'created' => $user->created_at
            ];
        })->toArray();
    } elseif ($db && $db !== 'laravel') {
        $stmt = $db->query("SELECT id, name, email, created_at FROM users ORDER BY created_at DESC LIMIT 5");
        $userList = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    $results['tests']['recent_users'] = [
        'status' => 'INFO',
        'message' => 'Last 5 registered users',
        'users' => $userList ?? []
    ];
} catch (Exception $e) {
    $results['tests']['recent_users'] = [
        'status' => 'FAILED',
        'error' => $e->getMessage()
    ];
}

// TEST 8: Check Logs
try {
    $logFile = storage_path('logs/laravel.log');
    if (file_exists($logFile)) {
        $logContent = file_get_contents($logFile);
        $logLines = explode("\n", $logContent);
        $recentLogs = array_slice(array_reverse($logLines), 0, 10);
        
        $results['tests']['recent_logs'] = [
            'status' => 'INFO',
            'message' => 'Last 10 log entries',
            'logs' => array_filter($recentLogs)
        ];
    }
} catch (Exception $e) {
    $results['tests']['recent_logs'] = [
        'status' => 'FAILED',
        'error' => $e->getMessage()
    ];
}

// Summary
$passedTests = 0;
$failedTests = 0;
foreach ($results['tests'] as $test) {
    if (isset($test['status'])) {
        if ($test['status'] === 'SUCCESS') $passedTests++;
        if ($test['status'] === 'FAILED') $failedTests++;
    }
}

$results['summary'] = [
    'total_tests' => count($results['tests']),
    'passed' => $passedTests,
    'failed' => $failedTests,
    'info' => count($results['tests']) - $passedTests - $failedTests
];

echo json_encode($results, JSON_PRETTY_PRINT);
