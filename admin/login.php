<?php
require_once 'includes/auth.php';
require_once '../config/database.php';

// If already logged in, redirect to dashboard
if (isAuthenticated()) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        try {
            $pdo = getDBConnection();
            
            $stmt = $pdo->prepare("
                SELECT id, username, email, password, full_name, role, is_active 
                FROM admin_users 
                WHERE username = ? AND is_active = 1
            ");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                // Update last login
                $updateStmt = $pdo->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?");
                $updateStmt->execute([$user['id']]);
                
                // Create secure session
                createSecureSession($user);
                
                // Redirect to dashboard
                header('Location: index.php');
                exit;
            } else {
                $error = 'Invalid username or password.';
            }
        } catch(PDOException $e) {
            $error = 'Database connection error. Please try again.';
        }
    }
}

// Handle error messages from URL parameters
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'session_expired':
            $error = 'Your session has expired. Please log in again to continue.';
            break;
        case 'timeout':
            $error = 'Session timeout after 7 days of inactivity. Please log in again.';
            break;
        case 'invalid_session':
            $error = 'Invalid session detected. Please log in again.';
            break;
        case 'logout':
            $success = 'You have been successfully logged out.';
            break;
        default:
            $error = 'Please log in to continue.';
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - BEAM</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../style.css">
    <style>
        .login-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .glass-effect {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .form-input {
            transition: all 0.3s ease;
        }
        
        .form-input:focus {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .login-btn {
            transition: all 0.3s ease;
        }
        
        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }
        
        .floating-animation {
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        
        .pulse-glow {
            animation: pulse-glow 2s infinite;
        }
        
        @keyframes pulse-glow {
            0%, 100% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.7); }
            50% { box-shadow: 0 0 0 10px rgba(59, 130, 246, 0); }
        }
    </style>
</head>
<body class="login-bg min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Logo and Brand -->
        <div class="text-center mb-8 floating-animation">
            <div class="w-24 h-24 mx-auto mb-4 bg-white rounded-full flex items-center justify-center shadow-lg">
                <svg class="w-12 h-12 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-white mb-2">BEAM</h1>
            <p class="text-white/80 text-lg">Admin Panel</p>
        </div>
        
        <!-- Login Form -->
        <div class="glass-effect rounded-2xl p-8 shadow-2xl">
            <div class="text-center mb-8">
                <h2 class="text-2xl font-bold text-white mb-2">Welcome Back</h2>
                <p class="text-white/70">Sign in to your admin account</p>
            </div>
            
            <?php if ($error): ?>
                <div class="bg-red-500/20 border border-red-500/30 text-red-200 px-4 py-3 rounded-lg mb-6">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="bg-green-500/20 border border-green-500/30 text-green-200 px-4 py-3 rounded-lg mb-6">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="space-y-6">
                <div>
                    <label for="username" class="block text-sm font-medium text-white/90 mb-2">
                        Username
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            required
                            class="form-input w-full pl-10 pr-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-white/50 focus:border-transparent"
                            placeholder="Enter your username"
                            value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                        >
                    </div>
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-medium text-white/90 mb-2">
                        Password
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                        </div>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            required
                            class="form-input w-full pl-10 pr-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-white/50 focus:border-transparent"
                            placeholder="Enter your password"
                        >
                    </div>
                </div>
                
                <button 
                    type="submit" 
                    class="login-btn w-full bg-gradient-to-r from-blue-500 to-purple-600 text-white font-semibold py-3 px-6 rounded-lg hover:from-blue-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:ring-offset-2 focus:ring-offset-transparent"
                >
                    <div class="flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                        </svg>
                        Sign In
                    </div>
                </button>
            </form>
            
            <div class="mt-6 text-center">
                <p class="text-white/60 text-sm">
                    Default credentials: <strong class="text-white">admin</strong> / <strong class="text-white">admin123</strong>
                </p>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="text-center mt-8">
            <p class="text-white/50 text-sm">
                © 2024 BEAM. All rights reserved.
            </p>
        </div>
    </div>
    
    <script>
        // Prevent browser back button after logout
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
        
        // Add some interactive effects
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('.form-input');
            const loginBtn = document.querySelector('.login-btn');
            
            // Add focus effects
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.classList.add('pulse-glow');
                });
                
                input.addEventListener('blur', function() {
                    this.parentElement.classList.remove('pulse-glow');
                });
            });
            
            // Add loading state to button
            const form = document.querySelector('form');
            form.addEventListener('submit', function() {
                const btn = this.querySelector('button[type="submit"]');
                btn.innerHTML = `
                    <div class="flex items-center justify-center">
                        <svg class="animate-spin w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Signing In...
                    </div>
                `;
                btn.disabled = true;
            });
        });
    </script>
</body>
</html> 