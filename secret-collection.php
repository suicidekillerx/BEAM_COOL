<?php
session_start();
require_once 'includes/functions.php';

// Check if secret collection is enabled
if (getSiteSetting('secret_collection', '0') !== '1') {
    header('Location: index.php');
    exit();
}

// Check if user already has access to secret collection
if (isset($_SESSION['secret_collection_access']) && $_SESSION['secret_collection_access']) {
    // Check if user has an active password session
    if (checkPasswordSession(session_id())) {
        // User already has access, redirect to secret shop
        header('Location: secret-shop.php');
        exit();
    } else {
        // Session expired, clear session variables
        unset($_SESSION['secret_collection_access']);
        unset($_SESSION['secret_collection_password_id']);
    }
}

// Try to restore session by IP if user doesn't have access
if (!isset($_SESSION['secret_collection_access'])) {
    $userIp = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $recentSession = restorePasswordSessionByIp($userIp);
    
    if ($recentSession) {
        // User has a recent session by IP, restore access
        $_SESSION['secret_collection_access'] = true;
        $_SESSION['secret_collection_password_id'] = $recentSession['password_id'];
        
        // Redirect to secret shop
        header('Location: secret-shop.php');
        exit();
    }
}

// Handle password submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    $submittedPassword = $_POST['password'];
    
    // Check if password is valid
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM passwords WHERE password = ? AND is_active = 1 AND (expires_at IS NULL OR expires_at > NOW())");
    $stmt->execute([$submittedPassword]);
    $password = $stmt->fetch();
    
    if ($password) {
        // Check if password is single-use and already used
        if ($password['single_use'] && $password['used_count'] > 0) {
            $error = 'This password has already been used.';
        } else {
            // Check if password has reached max uses
            if ($password['max_uses'] && $password['used_count'] >= $password['max_uses']) {
                $error = 'This password has reached its maximum usage limit.';
            } else {
                // Check if user already has an active session with this password
                $existingSession = getPasswordSession(session_id());
                if ($existingSession && $existingSession['password_id'] == $password['id']) {
                    // User already has access, just redirect
                    $_SESSION['secret_collection_access'] = true;
                    $_SESSION['secret_collection_password_id'] = $password['id'];
                    header('Location: secret-shop.php');
                    exit();
                }
                
                // Increment usage count
                $stmt = $pdo->prepare("UPDATE passwords SET used_count = used_count + 1 WHERE id = ?");
                $stmt->execute([$password['id']]);
                
                // Create password session
                $userIp = $_SERVER['REMOTE_ADDR'] ?? null;
                $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
                $sessionExpiresAt = date('Y-m-d H:i:s', strtotime('+24 hours')); // 24 hour session
                
                createPasswordSession($password['id'], session_id(), $userIp, $userAgent, $sessionExpiresAt);
                
                // Set session access
                $_SESSION['secret_collection_access'] = true;
                $_SESSION['secret_collection_password_id'] = $password['id'];
                
                // Redirect to secret shop
                header('Location: secret-shop.php');
                exit();
            }
        }
    } else {
        $error = 'Invalid password. Please try again.';
    }
}

// Get site settings
$brandName = getSiteSetting('brand_name', 'BEAM™');
$brandLogo = getSiteSetting('brand_logo', 'images/logo.webp');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secret Collection - <?php echo htmlspecialchars($brandName); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .secret-bg {
            background: linear-gradient(135deg, #1a1a1a 0%, #000000 50%, #1a1a1a 100%);
            position: relative;
            overflow: hidden;
        }
        
        .secret-bg::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="0.5"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
            opacity: 0.3;
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
        
        .input-glow:focus {
            box-shadow: 0 0 20px rgba(59, 130, 246, 0.5);
        }
    </style>
</head>
<body class="secret-bg min-h-screen flex items-center justify-center p-4 relative">
    <div class="max-w-md mx-auto text-center relative z-10">
        <!-- Logo -->
        <div class="mb-8 float-animation">
            <?php if (!empty($brandLogo) && file_exists($brandLogo)): ?>
                <img src="<?php echo htmlspecialchars($brandLogo); ?>" alt="<?php echo htmlspecialchars($brandName); ?>" class="h-16 md:h-20 mx-auto glow-animation">
            <?php else: ?>
                <div class="text-4xl md:text-6xl font-bold text-white mb-4 glow-animation"><?php echo htmlspecialchars($brandName); ?></div>
            <?php endif; ?>
        </div>

        <!-- Lock Icon -->
        <div class="mb-8">
            <div class="w-24 h-24 mx-auto bg-white bg-opacity-10 rounded-full flex items-center justify-center">
                <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
            </div>
        </div>

        <!-- Main Content -->
        <div class="text-white mb-8">
            <h1 class="text-3xl md:text-4xl font-bold mb-4">Secret Collection</h1>
            <p class="text-lg md:text-xl text-gray-300 mb-6">
                Enter the password to access our exclusive secret collection.
            </p>
        </div>

        <!-- Password Form -->
        <div class="mb-8">
            <div class="backdrop-filter backdrop-blur-10 bg-white bg-opacity-10 rounded-2xl p-8 border border-white border-opacity-20">
                <h3 class="text-xl font-semibold text-white mb-4">Access Required</h3>
                <p class="text-gray-300 mb-6">Enter the secret password to continue</p>
                
                <?php if (isset($error)): ?>
                <div class="mb-4 p-3 bg-red-500 bg-opacity-20 border border-red-500 border-opacity-30 rounded-lg">
                    <p class="text-red-300 text-sm"><?php echo htmlspecialchars($error); ?></p>
                </div>
                <?php endif; ?>
                
                <form method="POST" class="space-y-4">
                    <div>
                        <input type="password" name="password" 
                               class="w-full px-4 py-3 bg-white bg-opacity-10 border border-white border-opacity-20 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent input-glow transition-all"
                               placeholder="Enter secret password"
                               required
                               autofocus>
                    </div>
                    <button type="submit" 
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition-all duration-200 transform hover:scale-105">
                        Access Secret Collection
                    </button>
                </form>
            </div>
        </div>

        <!-- Back Button -->
        <div class="text-center">
            <a href="index.php" class="text-gray-400 hover:text-white transition-colors">
                ← Back to Home
            </a>
        </div>
    </div>
</body>
</html> 