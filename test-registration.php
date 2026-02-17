<?php
/**
 * Test Registration Form Submission
 * This simulates a real registration POST request from the frontend
 */

header('Content-Type: text/html; charset=utf-8');

// Security key
$securityKey = 'test-reg-2026';
if (!isset($_GET['key']) || $_GET['key'] !== $securityKey) {
    die('Access denied. Usage: test-registration.php?key=' . $securityKey);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Registration - AllFy Center</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #4CAF50;
            padding-bottom: 10px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        input, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 10px;
        }
        button:hover {
            background-color: #45a049;
        }
        .result {
            margin-top: 20px;
            padding: 15px;
            border-radius: 4px;
            display: none;
        }
        .success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .error {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        .info {
            background-color: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
        }
        pre {
            background-color: #f4f4f4;
            padding: 10px;
            border-radius: 4px;
            overflow-x: auto;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .loading {
            display: none;
            text-align: center;
            margin: 20px 0;
        }
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #4CAF50;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Test Registration Form</h1>
        <p><strong>Purpose:</strong> Test POST request to /api/register endpoint exactly as the frontend does.</p>
        
        <form id="registrationForm">
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" value="Chrispin Kulanga" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="chrispinkulanga@gmail.com" required>
            </div>
            
            <div class="form-group">
                <label for="phone">Phone Number (Optional)</label>
                <input type="tel" id="phone" name="phone" value="+255744497698">
            </div>
            
            <div class="form-group">
                <label for="role">I want to</label>
                <select id="role" name="role" required>
                    <option value="student">Learn courses as a student</option>
                    <option value="instructor">Teach courses as an instructor</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" value="Test123!@#" required>
            </div>
            
            <div class="form-group">
                <label for="password_confirmation">Confirm Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" value="Test123!@#" required>
            </div>
            
            <button type="submit">Submit Registration</button>
        </form>
        
        <div class="loading" id="loading">
            <div class="spinner"></div>
            <p>Sending registration request...</p>
        </div>
        
        <div id="result" class="result"></div>
    </div>

    <script>
        document.getElementById('registrationForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const resultDiv = document.getElementById('result');
            const loadingDiv = document.getElementById('loading');
            
            // Show loading
            loadingDiv.style.display = 'block';
            resultDiv.style.display = 'none';
            
            // Collect form data
            const formData = {
                name: document.getElementById('name').value,
                email: document.getElementById('email').value,
                phone: document.getElementById('phone').value,
                role: document.getElementById('role').value,
                password: document.getElementById('password').value,
                password_confirmation: document.getElementById('password_confirmation').value
            };
            
            try {
                // Step 1: Get CSRF token
                const csrfResponse = await fetch('/sanctum/csrf-cookie', {
                    method: 'GET',
                    credentials: 'include'
                });
                
                console.log('CSRF Response:', csrfResponse.status);
                
                // Step 2: Try registration
                const response = await fetch('/api/register', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'include',
                    body: JSON.stringify(formData)
                });
                
                const responseData = await response.json().catch(() => null);
                
                // Hide loading
                loadingDiv.style.display = 'none';
                resultDiv.style.display = 'block';
                
                if (response.ok) {
                    resultDiv.className = 'result success';
                    resultDiv.innerHTML = `
                        <h3>✅ Registration Successful!</h3>
                        <p><strong>Status:</strong> ${response.status} ${response.statusText}</p>
                        <p><strong>Response:</strong></p>
                        <pre>${JSON.stringify(responseData, null, 2)}</pre>
                    `;
                } else {
                    resultDiv.className = 'result error';
                    resultDiv.innerHTML = `
                        <h3>❌ Registration Failed</h3>
                        <p><strong>Status:</strong> ${response.status} ${response.statusText}</p>
                        <p><strong>Error Details:</strong></p>
                        <pre>${JSON.stringify(responseData, null, 2)}</pre>
                        
                        <hr>
                        <h4>Common Issues:</h4>
                        <ul>
                            <li><strong>405 Method Not Allowed:</strong> Route cache not cleared. Delete bootstrap/cache/routes-v7.php</li>
                            <li><strong>422 Validation Error:</strong> Form data validation failed</li>
                            <li><strong>500 Server Error:</strong> Check Laravel logs in storage/logs/</li>
                            <li><strong>419 CSRF Token Mismatch:</strong> CSRF protection issue</li>
                        </ul>
                    `;
                }
                
                // Log all response headers
                console.log('Response Headers:');
                response.headers.forEach((value, key) => {
                    console.log(key + ': ' + value);
                });
                
            } catch (error) {
                loadingDiv.style.display = 'none';
                resultDiv.style.display = 'block';
                resultDiv.className = 'result error';
                resultDiv.innerHTML = `
                    <h3>❌ Network Error</h3>
                    <p><strong>Error:</strong> ${error.message}</p>
                    <p>Could not connect to server. Check your internet connection or server status.</p>
                `;
                console.error('Registration error:', error);
            }
        });
        
        // Show info message on load
        window.addEventListener('load', function() {
            const resultDiv = document.getElementById('result');
            resultDiv.className = 'result info';
            resultDiv.style.display = 'block';
            resultDiv.innerHTML = `
                <h4>ℹ️ Instructions:</h4>
                <ol>
                    <li>Fill in the registration form above</li>
                    <li>Click "Submit Registration" button</li>
                    <li>View the API response below</li>
                </ol>
                <p><strong>Note:</strong> This tests the exact same endpoint your frontend uses: <code>POST /api/register</code></p>
                <p><strong>Current Issue:</strong> If you see 405 error, the route cache hasn't been cleared yet.</p>
            `;
        });
    </script>
</body>
</html>
