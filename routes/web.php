<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Storage;

// Serve storage files (must come before catch-all)
Route::get('/storage/{path}', function ($path) {
    $filePath = 'public/' . $path;
    
    if (!Storage::disk('public')->exists($path)) {
        abort(404);
    }
    
    $file = Storage::disk('public')->get($path);
    $mimeType = Storage::disk('public')->mimeType($path);
    
    return response($file, 200)
        ->header('Content-Type', $mimeType)
        ->header('Cache-Control', 'public, max-age=31536000'); // Cache for 1 year
})->where('path', '.*');

// Email verification route (clicked from email) - must come before catch-all
Route::get('/verify-email', function (Request $request) {
    $token = $request->query('token');
    $email = $request->query('email');
    
    if (!$token || !$email) {
        return view('emails.verification-failed', [
            'message' => 'Invalid verification link'
        ]);
    }
    
    // Make API call to verify email
    $response = app(AuthController::class)->verifyEmail(new \Illuminate\Http\Request([
        'token' => $token,
        'email' => $email
    ]));
    
    $data = $response->getData(true);
    
    if ($data['status'] === 'success') {
        return view('emails.verification-success', [
            'message' => $data['message']
        ]);
    } else {
        return view('emails.verification-failed', [
            'message' => $data['message']
        ]);
    }
});

// Serve static assets (must come before catch-all)
Route::get('/assets/{file}', function ($file) {
    $filePath = public_path('assets/' . $file);
    
    if (!file_exists($filePath) || !is_file($filePath)) {
        abort(404, 'Asset file not found');
    }
    
    // Determine MIME type based on file extension
    $extension = pathinfo($filePath, PATHINFO_EXTENSION);
    $mimeTypes = [
        'js' => 'application/javascript',
        'css' => 'text/css',
        'json' => 'application/json',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'svg' => 'image/svg+xml',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf' => 'font/ttf',
        'eot' => 'application/vnd.ms-fontobject',
        'html' => 'text/html',
        'ico' => 'image/x-icon',
    ];
    
    $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';
    
    // Read and serve the file
    $content = file_get_contents($filePath);
    return response($content, 200)
        ->header('Content-Type', $mimeType)
        ->header('Cache-Control', 'public, max-age=31536000'); // Cache for 1 year
})->where('file', '.*');

// Serve the Vue.js SPA for the root route
Route::get('/', function () {
    return file_get_contents(public_path('index.html'));
});

// Catch-all route for Vue Router (MUST be last)
// This ensures all non-API routes return the SPA index.html
// Vue Router will then handle client-side routing to show Home.vue, Courses.vue, etc.
Route::get('/{any}', function ($any) {
    // Exclude API routes - these are handled by api.php
    if (str_starts_with($any, 'api/')) {
        abort(404);
    }
    
    // Check if the requested path is a static file that exists
    $filePath = public_path($any);
    if (file_exists($filePath) && is_file($filePath)) {
        // Determine MIME type based on file extension
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $mimeTypes = [
            'js' => 'application/javascript',
            'css' => 'text/css',
            'json' => 'application/json',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf' => 'font/ttf',
            'eot' => 'application/vnd.ms-fontobject',
            'html' => 'text/html',
            'ico' => 'image/x-icon',
        ];
        
        $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';
        
        // Read and serve the file
        $content = file_get_contents($filePath);
        return response($content, 200)
            ->header('Content-Type', $mimeType)
            ->header('Cache-Control', 'public, max-age=31536000'); // Cache for 1 year
    }
    
    // If requesting an asset file that doesn't exist, return 404
    // This prevents serving index.html for missing JS/CSS files
    if (str_starts_with($any, 'assets/')) {
        abort(404, 'Asset file not found. Please rebuild the frontend and copy dist files to public folder.');
    }
    
    // For all other routes (SPA routes), return the SPA index.html
    // Vue Router will handle client-side routing
    return file_get_contents(public_path('index.html'));
})->where('any', '.*');
