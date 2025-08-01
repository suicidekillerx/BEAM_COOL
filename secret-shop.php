<?php
session_start();
require_once 'includes/functions.php';

// Check if secret collection is enabled
if (getSiteSetting('secret_collection', '0') !== '1') {
    header('Location: index.php');
    exit();
}

// Check if user has access
if (!isset($_SESSION['secret_collection_access']) || !$_SESSION['secret_collection_access']) {
    header('Location: secret-collection.php');
    exit();
}

// Check if user has an active password session
if (!checkPasswordSession(session_id())) {
    // Try to restore session by IP
    $userIp = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $recentSession = restorePasswordSessionByIp($userIp);
    
    if ($recentSession) {
        // Restore session
        $_SESSION['secret_collection_access'] = true;
        $_SESSION['secret_collection_password_id'] = $recentSession['password_id'];
    } else {
        // No valid session found, redirect to password page
        unset($_SESSION['secret_collection_access']);
        unset($_SESSION['secret_collection_password_id']);
        header('Location: secret-collection.php');
        exit();
    }
}

// Get all secret products (no filters)
$products = getSecretProducts();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secret Collection - <?php echo getSiteSetting('brand_name', 'BeamTheTeam'); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background: linear-gradient(135deg, #000000 0%, #1a1a1a 50%, #000000 100%);
            min-height: 100vh;
            color: #fff;
            font-family: 'Inter', sans-serif;
            position: relative;
            overflow-x: hidden;
        }
        .secret-bg-anim {
            position: fixed;
            top: 0; left: 0; width: 100vw; height: 100vh;
            z-index: 0;
            pointer-events: none;
            background: 
                radial-gradient(circle at 30% 20%, rgba(255,255,255,0.08) 0, transparent 50%),
                radial-gradient(circle at 70% 80%, rgba(255,255,255,0.06) 0, transparent 60%),
                radial-gradient(circle at 50% 50%, rgba(255,255,255,0.04) 0, transparent 70%),
                linear-gradient(135deg, rgba(0,0,0,0.98) 0%, rgba(26,26,26,0.98) 100%);
            animation: bgmove 8s ease-in-out infinite alternate;
        }
        @keyframes bgmove {
            0% { 
                background-position: 30% 20%, 70% 80%, 50% 50%;
                filter: contrast(1.3) brightness(0.9);
            }
            100% { 
                background-position: 40% 30%, 60% 70%, 60% 40%;
                filter: contrast(1.5) brightness(0.8);
            }
        }
        .glow {
            text-shadow: 
                0 0 20px #ffffff, 
                0 0 40px #ffffff, 
                0 0 60px #ffffff,
                0 0 80px #ffffff;
            font-weight: 900;
            letter-spacing: 0.1em;
        }
        .glass-card {
            background: rgba(10, 10, 10, 0.95);
            border: 2px solid rgba(255,255,255,0.3);
            box-shadow: 
                0 0 20px rgba(255,255,255,0.1),
                inset 0 0 20px rgba(0,0,0,0.8);
            border-radius: 0;
            backdrop-filter: blur(8px);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }
        .glass-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: linear-gradient(45deg, transparent 30%, rgba(255,255,255,0.05) 50%, transparent 70%);
            transform: translateX(-100%);
            transition: transform 0.6s;
        }
        .glass-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 
                0 0 40px rgba(255,255,255,0.2),
                0 0 80px rgba(255,255,255,0.1),
                inset 0 0 20px rgba(0,0,0,0.9);
            border-color: #ffffff;
        }
        .glass-card:hover::before {
            transform: translateX(100%);
        }
        .logout-btn {
            position: fixed;
            top: 2rem;
            right: 2rem;
            z-index: 20;
            background: linear-gradient(45deg, #ffffff 0%, #cccccc 50%, #ffffff 100%);
            color: #000000;
            font-weight: 700;
            border-radius: 0;
            padding: 1rem 2.5rem;
            box-shadow: 
                0 0 20px rgba(255,255,255,0.3),
                0 4px 8px rgba(0,0,0,0.8);
            transition: all 0.3s;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            border: 2px solid rgba(255,255,255,0.5);
        }
        .logout-btn:hover {
            background: linear-gradient(45deg, #cccccc 0%, #ffffff 50%, #cccccc 100%);
            box-shadow: 
                0 0 40px rgba(255,255,255,0.5),
                0 8px 16px rgba(0,0,0,0.9);
            transform: translateY(-2px);
        }
        .secret-badge {
            display: inline-flex;
            align-items: center;
            background: linear-gradient(45deg, #ffffff 0%, #cccccc 50%, #ffffff 100%);
            color: #000000;
            font-size: 1.1rem;
            font-weight: 900;
            padding: 0.75rem 2rem;
            border-radius: 0;
            box-shadow: 
                0 0 30px rgba(255,255,255,0.3),
                0 4px 8px rgba(0,0,0,0.8);
            margin-bottom: 3rem;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            animation: badgeglow 2s infinite alternate;
            border: 2px solid rgba(255,255,255,0.5);
        }
        @keyframes badgeglow {
            0% { 
                box-shadow: 0 0 30px rgba(255,255,255,0.3), 0 4px 8px rgba(0,0,0,0.8);
                transform: scale(1);
            }
            100% { 
                box-shadow: 0 0 60px rgba(255,255,255,0.5), 0 8px 16px rgba(0,0,0,0.9);
                transform: scale(1.05);
            }
        }
        .back-link {
            display: inline-block;
            margin: 4rem auto 0 auto;
            color: #ffffff;
            font-weight: 700;
            text-align: center;
            font-size: 1.2rem;
            text-decoration: none;
            transition: all 0.3s;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            padding: 1rem 2rem;
            border: 2px solid rgba(255,255,255,0.3);
            background: rgba(10,10,10,0.8);
        }
        .back-link:hover {
            color: #000000;
            background: rgba(255,255,255,0.9);
            border-color: #ffffff;
            box-shadow: 0 0 20px rgba(255,255,255,0.4);
            transform: translateY(-2px);
        }
        .product-title {
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .price-text {
            font-weight: 900;
            text-shadow: 0 0 10px rgba(255,255,255,0.5);
        }
        .sale-badge {
            background: linear-gradient(45deg, #ffffff 0%, #cccccc 100%);
            border-radius: 0;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            box-shadow: 0 0 15px rgba(255,255,255,0.4);
            color: #000000;
        }
    </style>
</head>
<body>
    <div class="secret-bg-anim"></div>
    <main class="relative z-10 flex flex-col items-center justify-center min-h-screen px-4 pt-24 pb-12">
        <span class="secret-badge">
            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
            </svg>
            SECRET ACCESS
        </span>
        <h1 class="text-4xl md:text-5xl font-extrabold mb-4 glow">SECRET COLLECTION</h1>
        <p class="text-lg text-gray-200 mb-10">Welcome to the exclusive, members-only shop.<br>Enjoy our hidden treasures.</p>
        <?php if (!empty($products)): ?>
        <div class="w-full max-w-7xl grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
            <?php foreach ($products as $product): ?>
            <div class="glass-card overflow-hidden flex flex-col">
                <a href="product-view.php?id=<?php echo $product['id']; ?>" class="block">
                                         <div class="relative aspect-square overflow-hidden group">
                         <?php 
                         $productImages = getProductImagesForSlider($product['id']);
                         $primaryImage = $productImages[0]['image_path'] ?? 'images/placeholder.jpg';
                         $secondaryImage = $productImages[1]['image_path'] ?? $primaryImage;
                         ?>
                         <img src="<?php echo $primaryImage; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-full h-full object-cover transition-opacity duration-300 group-hover:opacity-0">
                         <img src="<?php echo $secondaryImage; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="absolute inset-0 w-full h-full object-cover opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                         
                         <!-- Shine effect -->
                         <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent transform -skew-x-12 -translate-x-full group-hover:translate-x-full transition-transform duration-1000"></div>
                         
                         <?php if ($product['is_on_sale']): ?>
                         <div class="absolute top-2 left-2 sale-badge text-white text-xs px-2 py-1 shadow-lg animate-pulse">
                             SALE
                         </div>
                         <?php endif; ?>
                     </div>
                    <div class="p-5 flex-1 flex flex-col justify-between">
                        <h3 class="product-title text-lg mb-1 text-white"><?php echo htmlspecialchars($product['name']); ?></h3>
                        <p class="text-sm text-gray-400 mb-2"><?php echo htmlspecialchars($product['category_name'] ?? ''); ?></p>
                        <div class="flex items-center space-x-2 mb-2">
                            <span class="price-text text-xl text-white"><?php echo formatPrice($product['sale_price'] ?? $product['price']); ?></span>
                            <?php if ($product['sale_price']): ?>
                            <span class="text-sm text-gray-500 line-through"><?php echo formatPrice($product['price']); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="text-center py-24">
            <svg class="w-20 h-20 text-purple-700 mx-auto mb-6 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
            </svg>
            <h3 class="text-2xl font-bold text-white mb-2">No secret products found</h3>
            <p class="text-purple-200">Check back later for more exclusive items.</p>
        </div>
        <?php endif; ?>
        <a href="index.php" class="back-link mt-12">&larr; Back to Home</a>
    </main>
</body>
</html> 