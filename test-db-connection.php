<?php
// Database Connection Test
// DELETE THIS FILE AFTER TESTING!

$host = 'localhost';
$database = 'tibafuhk_allfycenter';
$username = 'tibafuhk_allfycenter';
$password = 'teoPiOQexc5u';
$port = 3306;

echo "<h2>Testing MySQL Connection...</h2>";
echo "<hr>";

// Test 1: Basic mysqli connection
echo "<h3>Test 1: MySQLi Connection</h3>";
$conn = new mysqli($host, $username, $password, $database, $port);

if ($conn->connect_error) {
    echo "<p style='color:red;'>❌ Connection FAILED: " . $conn->connect_error . "</p>";
    echo "<p>Error Code: " . $conn->connect_errno . "</p>";
} else {
    echo "<p style='color:green;'>✅ Connection SUCCESSFUL!</p>";
    echo "<p>Connected to database: <strong>" . $database . "</strong></p>";
    echo "<p>MySQL Version: " . $conn->server_info . "</p>";
    
    // Test 2: Check if sessions table exists
    echo "<hr><h3>Test 2: Check Sessions Table</h3>";
    $result = $conn->query("SHOW TABLES LIKE 'sessions'");
    if ($result && $result->num_rows > 0) {
        echo "<p style='color:green;'>✅ Sessions table EXISTS</p>";
        
        // Get table structure
        $structure = $conn->query("DESCRIBE sessions");
        if ($structure) {
            echo "<p><strong>Table Structure:</strong></p>";
            echo "<table border='1' cellpadding='5'>";
            echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
            while ($row = $structure->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row['Field'] . "</td>";
                echo "<td>" . $row['Type'] . "</td>";
                echo "<td>" . $row['Null'] . "</td>";
                echo "<td>" . $row['Key'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    } else {
        echo "<p style='color:red;'>❌ Sessions table NOT FOUND</p>";
    }
    
    // Test 3: Check migrations table
    echo "<hr><h3>Test 3: Check Migrations</h3>";
    $result = $conn->query("SELECT COUNT(*) as count FROM migrations");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "<p style='color:green;'>✅ Migrations table found with " . $row['count'] . " migrations</p>";
    } else {
        echo "<p style='color:orange;'>⚠ Migrations table not found or error: " . $conn->error . "</p>";
    }
    
    // Test 4: List all tables
    echo "<hr><h3>Test 4: All Tables in Database</h3>";
    $result = $conn->query("SHOW TABLES");
    if ($result) {
        echo "<p><strong>Total Tables: " . $result->num_rows . "</strong></p>";
        echo "<ul>";
        while ($row = $result->fetch_row()) {
            echo "<li>" . $row[0] . "</li>";
        }
        echo "</ul>";
    }
    
    $conn->close();
}

// Test 5: PDO connection (Laravel uses PDO)
echo "<hr><h3>Test 5: PDO Connection (Laravel Method)</h3>";
try {
    $dsn = "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    echo "<p style='color:green;'>✅ PDO Connection SUCCESSFUL!</p>";
    echo "<p>PDO Driver: " . $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) . "</p>";
    echo "<p>Server Version: " . $pdo->getAttribute(PDO::ATTR_SERVER_VERSION) . "</p>";
} catch (PDOException $e) {
    echo "<p style='color:red;'>❌ PDO Connection FAILED: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p style='color:red;'><strong>⚠️ IMPORTANT: DELETE THIS FILE AFTER TESTING!</strong></p>";
echo "<p>File location: public/test-db-connection.php</p>";
?>
