<?php
session_start();
require_once 'includes/functions.php';

// Check if maintenance mode is actually enabled
if (!isMaintenanceMode()) {
    // If maintenance mode is disabled, redirect to homepage
    // Get the base path for proper redirect
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $basePath = dirname($scriptName);
    if ($basePath === '.') {
        $basePath = '';
    }
    
    $homepageUrl = $basePath . '/';
    header('Location: ' . $homepageUrl);
    exit();
}



// Get site settings for the maintenance page
$brandName = getSiteSetting('brand_name', 'BEAM™');
$contactEmail = getSiteSetting('contact_email', 'contact@beam.com');
$contactPhone = getSiteSetting('contact_phone', '');
$brandLogo = getSiteSetting('brand_logo', 'images/logo.webp');

// Get the URL they were trying to access
$redirectUrl = $_GET['redirect'] ?? '/';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Under Maintenance - <?php echo htmlspecialchars($brandName); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .maintenance-bg {
            background: linear-gradient(135deg, #000000 0%, #1a1a1a 50%, #000000 100%);
            position: relative;
            overflow: hidden;
        }
        
        .maintenance-bg::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(255,255,255,0.03)" stroke-width="0.5"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
            opacity: 0.3;
        }
        
        .pulse-animation {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        
        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: .5;
            }
        }
        
        .float-animation {
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes float {
            0% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-20px);
            }
            100% {
                transform: translateY(0px);
            }
        }
        
        .glow-animation {
            animation: glow 3s ease-in-out infinite alternate;
        }
        
        @keyframes glow {
            from {
                box-shadow: 0 0 20px rgba(255, 255, 255, 0.1);
            }
            to {
                box-shadow: 0 0 30px rgba(255, 255, 255, 0.2);
            }
        }
        

    </style>
</head>
<body class="maintenance-bg min-h-screen flex items-center justify-center p-4 relative">
    <div class="max-w-2xl mx-auto text-center relative z-10">
        <!-- Logo -->
        <div class="mb-8 float-animation">
            <?php if (!empty($brandLogo) && file_exists($brandLogo)): ?>
                <img src="<?php echo htmlspecialchars($brandLogo); ?>" alt="<?php echo htmlspecialchars($brandName); ?>" class="h-16 md:h-20 mx-auto glow-animation">
            <?php else: ?>
                <div class="text-4xl md:text-6xl font-bold text-white mb-4 glow-animation"><?php echo htmlspecialchars($brandName); ?></div>
            <?php endif; ?>
        </div>

        <!-- Maintenance Icon -->
        <div class="mb-8">
            <div class="w-24 h-24 mx-auto bg-white bg-opacity-10 rounded-full flex items-center justify-center pulse-animation">
                <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
            </div>
        </div>

        <!-- Main Content -->
        <div class="text-white mb-8">
            <h1 class="text-3xl md:text-4xl font-bold mb-4">We're Making Things Better</h1>
            <p class="text-lg md:text-xl text-gray-300 mb-6">
                Our website is currently undergoing scheduled maintenance to improve your experience.
            </p>
            <p class="text-base text-gray-400 mb-8">
                We'll be back shortly with exciting updates and improvements. Thank you for your patience!
            </p>
        </div>



        <!-- Progress Bar -->
        <div class="mb-8">
            <div class="w-full bg-white bg-opacity-20 rounded-full h-2 mb-4">
                <div class="bg-white h-2 rounded-full pulse-animation" style="width: 75%"></div>
            </div>
            <p class="text-sm text-gray-400">Estimated completion: <span class="text-white">Soon</span></p>
        </div>

        <!-- Contact Information -->
        <?php if (!empty($contactEmail) || !empty($contactPhone)): ?>
        <div class="mb-8 p-6 bg-white bg-opacity-5 rounded-lg border border-white border-opacity-10">
            <h3 class="text-lg font-semibold text-white mb-4">Need Immediate Assistance?</h3>
            <div class="space-y-2">
                <?php if (!empty($contactEmail)): ?>
                <div class="flex items-center justify-center space-x-2">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    <a href="mailto:<?php echo htmlspecialchars($contactEmail); ?>" class="text-gray-300 hover:text-white transition-colors">
                        <?php echo htmlspecialchars($contactEmail); ?>
                    </a>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($contactPhone)): ?>
                <div class="flex items-center justify-center space-x-2">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                    </svg>
                    <a href="tel:<?php echo htmlspecialchars($contactPhone); ?>" class="text-gray-300 hover:text-white transition-colors">
                        <?php echo htmlspecialchars($contactPhone); ?>
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Social Media Links -->
        <div class="mb-8">
            <p class="text-sm text-gray-400 mb-4">Follow us for updates</p>
            <div class="flex justify-center space-x-4">
                <a href="#" class="w-10 h-10 bg-white bg-opacity-10 rounded-full flex items-center justify-center hover:bg-opacity-20 transition-colors">
                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                    </svg>
                </a>
                <a href="#" class="w-10 h-10 bg-white bg-opacity-10 rounded-full flex items-center justify-center hover:bg-opacity-20 transition-colors">
                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                    </svg>
                </a>
                <a href="#" class="w-10 h-10 bg-white bg-opacity-10 rounded-full flex items-center justify-center hover:bg-opacity-20 transition-colors">
                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                    </svg>
                </a>
            </div>
        </div>

        <!-- Refresh Button -->
        <div class="text-center">
            <button onclick="location.reload()" class="bg-white text-black px-6 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors">
                Check Again
            </button>
        </div>
    </div>

    <!-- Auto-refresh script -->
    <script>
        // Auto-refresh every 30 seconds to check if maintenance is done
        setTimeout(function() {
            location.reload();
        }, 30000);
    </script>
</body>
</html> 