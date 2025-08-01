<?php
session_start();
require_once 'includes/functions.php';

// Check if maintenance mode is enabled
$maintenanceMode = isMaintenanceMode();
$passwordEnabled = getSiteSetting('maintenance_password_enabled', '0');
$maintenancePassword = getSiteSetting('maintenance_password', '');

// Handle password submission for testing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_password'])) {
    $submittedPassword = $_POST['test_password'];
    if ($submittedPassword === $maintenancePassword) {
        $_SESSION['maintenance_access'] = true;
        $successMessage = "✅ Password correct! You would now have access to the site.";
    } else {
        $errorMessage = "❌ Incorrect password. Please try again.";
    }
}

// Clear maintenance access for testing
if (isset($_POST['clear_access'])) {
    unset($_SESSION['maintenance_access']);
    $successMessage = "🔒 Maintenance access cleared. You can test the password again.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Mode Test</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen p-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Maintenance Mode Test Page</h1>
        
        <!-- Status Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Maintenance Mode</h3>
                <div class="flex items-center">
                    <div class="w-3 h-3 rounded-full <?= $maintenanceMode ? 'bg-red-500' : 'bg-green-500' ?> mr-2"></div>
                    <span class="text-sm <?= $maintenanceMode ? 'text-red-600' : 'text-green-600' ?> font-medium">
                        <?= $maintenanceMode ? 'Enabled' : 'Disabled' ?>
                    </span>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Password Protection</h3>
                <div class="flex items-center">
                    <div class="w-3 h-3 rounded-full <?= $passwordEnabled === '1' ? 'bg-blue-500' : 'bg-gray-400' ?> mr-2"></div>
                    <span class="text-sm <?= $passwordEnabled === '1' ? 'text-blue-600' : 'text-gray-600' ?> font-medium">
                        <?= $passwordEnabled === '1' ? 'Enabled' : 'Disabled' ?>
                    </span>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Current Access</h3>
                <div class="flex items-center">
                    <div class="w-3 h-3 rounded-full <?= isset($_SESSION['maintenance_access']) ? 'bg-green-500' : 'bg-red-500' ?> mr-2"></div>
                    <span class="text-sm <?= isset($_SESSION['maintenance_access']) ? 'text-green-600' : 'text-red-600' ?> font-medium">
                        <?= isset($_SESSION['maintenance_access']) ? 'Granted' : 'Blocked' ?>
                    </span>
                </div>
            </div>
        </div>
        
        <!-- Messages -->
        <?php if (isset($successMessage)): ?>
            <div class="bg-green-50 border border-green-200 text-green-800 px-6 py-4 rounded-lg mb-6">
                <?php echo htmlspecialchars($successMessage); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($errorMessage)): ?>
            <div class="bg-red-50 border border-red-200 text-red-800 px-6 py-4 rounded-lg mb-6">
                <?php echo htmlspecialchars($errorMessage); ?>
            </div>
        <?php endif; ?>
        
        <!-- Test Password Form -->
        <?php if ($maintenanceMode && $passwordEnabled === '1'): ?>
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Test Password Access</h3>
            <form method="POST" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Maintenance Password</label>
                    <input type="password" name="test_password" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="Enter the maintenance password">
                </div>
                <div class="flex space-x-4">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                        Test Password
                    </button>
                    <button type="submit" name="clear_access" value="1" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                        Clear Access
                    </button>
                </div>
            </form>
        </div>
        <?php endif; ?>
        
        <!-- Instructions -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">How to Test</h3>
            <div class="space-y-4 text-sm text-gray-600">
                <div class="flex items-start">
                    <div class="w-6 h-6 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-xs font-bold mr-3 mt-0.5">1</div>
                    <div>
                        <p class="font-medium text-gray-900">Enable Maintenance Mode</p>
                        <p>Go to Admin Panel → Settings → Site Settings and enable "Maintenance Mode"</p>
                    </div>
                </div>
                
                <div class="flex items-start">
                    <div class="w-6 h-6 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-xs font-bold mr-3 mt-0.5">2</div>
                    <div>
                        <p class="font-medium text-gray-900">Enable Password Protection</p>
                        <p>Check "Password Protection" and set a maintenance password</p>
                    </div>
                </div>
                
                <div class="flex items-start">
                    <div class="w-6 h-6 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-xs font-bold mr-3 mt-0.5">3</div>
                    <div>
                        <p class="font-medium text-gray-900">Test the Functionality</p>
                        <p>Try accessing any page on the site - you should see the maintenance page with password form</p>
                    </div>
                </div>
                
                <div class="flex items-start">
                    <div class="w-6 h-6 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-xs font-bold mr-3 mt-0.5">4</div>
                    <div>
                        <p class="font-medium text-gray-900">Enter Password</p>
                        <p>Enter the correct password to access the site normally</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Current Settings -->
        <div class="bg-white rounded-lg shadow p-6 mt-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Current Settings</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-600">Maintenance Mode:</span>
                    <span class="font-medium"><?= $maintenanceMode ? 'Enabled' : 'Disabled' ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Password Protection:</span>
                    <span class="font-medium"><?= $passwordEnabled === '1' ? 'Enabled' : 'Disabled' ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Password Set:</span>
                    <span class="font-medium"><?= !empty($maintenancePassword) ? 'Yes' : 'No' ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Session Access:</span>
                    <span class="font-medium"><?= isset($_SESSION['maintenance_access']) ? 'Granted' : 'Not Granted' ?></span>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow p-6 mt-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
            <div class="flex space-x-4">
                <a href="admin/setting.php" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                    Go to Settings
                </a>
                <a href="maintenance.php" class="bg-orange-600 text-white px-4 py-2 rounded-lg hover:bg-orange-700 transition-colors">
                    View Maintenance Page
                </a>
                <a href="index.php" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
                    Go to Homepage
                </a>
            </div>
        </div>
    </div>
</body>
</html> 
session_start();
require_once 'includes/functions.php';

// Check if maintenance mode is enabled
$maintenanceMode = isMaintenanceMode();
$passwordEnabled = getSiteSetting('maintenance_password_enabled', '0');
$maintenancePassword = getSiteSetting('maintenance_password', '');

// Handle password submission for testing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_password'])) {
    $submittedPassword = $_POST['test_password'];
    if ($submittedPassword === $maintenancePassword) {
        $_SESSION['maintenance_access'] = true;
        $successMessage = "✅ Password correct! You would now have access to the site.";
    } else {
        $errorMessage = "❌ Incorrect password. Please try again.";
    }
}

// Clear maintenance access for testing
if (isset($_POST['clear_access'])) {
    unset($_SESSION['maintenance_access']);
    $successMessage = "🔒 Maintenance access cleared. You can test the password again.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Mode Test</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen p-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Maintenance Mode Test Page</h1>
        
        <!-- Status Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Maintenance Mode</h3>
                <div class="flex items-center">
                    <div class="w-3 h-3 rounded-full <?= $maintenanceMode ? 'bg-red-500' : 'bg-green-500' ?> mr-2"></div>
                    <span class="text-sm <?= $maintenanceMode ? 'text-red-600' : 'text-green-600' ?> font-medium">
                        <?= $maintenanceMode ? 'Enabled' : 'Disabled' ?>
                    </span>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Password Protection</h3>
                <div class="flex items-center">
                    <div class="w-3 h-3 rounded-full <?= $passwordEnabled === '1' ? 'bg-blue-500' : 'bg-gray-400' ?> mr-2"></div>
                    <span class="text-sm <?= $passwordEnabled === '1' ? 'text-blue-600' : 'text-gray-600' ?> font-medium">
                        <?= $passwordEnabled === '1' ? 'Enabled' : 'Disabled' ?>
                    </span>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Current Access</h3>
                <div class="flex items-center">
                    <div class="w-3 h-3 rounded-full <?= isset($_SESSION['maintenance_access']) ? 'bg-green-500' : 'bg-red-500' ?> mr-2"></div>
                    <span class="text-sm <?= isset($_SESSION['maintenance_access']) ? 'text-green-600' : 'text-red-600' ?> font-medium">
                        <?= isset($_SESSION['maintenance_access']) ? 'Granted' : 'Blocked' ?>
                    </span>
                </div>
            </div>
        </div>
        
        <!-- Messages -->
        <?php if (isset($successMessage)): ?>
            <div class="bg-green-50 border border-green-200 text-green-800 px-6 py-4 rounded-lg mb-6">
                <?php echo htmlspecialchars($successMessage); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($errorMessage)): ?>
            <div class="bg-red-50 border border-red-200 text-red-800 px-6 py-4 rounded-lg mb-6">
                <?php echo htmlspecialchars($errorMessage); ?>
            </div>
        <?php endif; ?>
        
        <!-- Test Password Form -->
        <?php if ($maintenanceMode && $passwordEnabled === '1'): ?>
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Test Password Access</h3>
            <form method="POST" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Maintenance Password</label>
                    <input type="password" name="test_password" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="Enter the maintenance password">
                </div>
                <div class="flex space-x-4">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                        Test Password
                    </button>
                    <button type="submit" name="clear_access" value="1" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                        Clear Access
                    </button>
                </div>
            </form>
        </div>
        <?php endif; ?>
        
        <!-- Instructions -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">How to Test</h3>
            <div class="space-y-4 text-sm text-gray-600">
                <div class="flex items-start">
                    <div class="w-6 h-6 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-xs font-bold mr-3 mt-0.5">1</div>
                    <div>
                        <p class="font-medium text-gray-900">Enable Maintenance Mode</p>
                        <p>Go to Admin Panel → Settings → Site Settings and enable "Maintenance Mode"</p>
                    </div>
                </div>
                
                <div class="flex items-start">
                    <div class="w-6 h-6 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-xs font-bold mr-3 mt-0.5">2</div>
                    <div>
                        <p class="font-medium text-gray-900">Enable Password Protection</p>
                        <p>Check "Password Protection" and set a maintenance password</p>
                    </div>
                </div>
                
                <div class="flex items-start">
                    <div class="w-6 h-6 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-xs font-bold mr-3 mt-0.5">3</div>
                    <div>
                        <p class="font-medium text-gray-900">Test the Functionality</p>
                        <p>Try accessing any page on the site - you should see the maintenance page with password form</p>
                    </div>
                </div>
                
                <div class="flex items-start">
                    <div class="w-6 h-6 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-xs font-bold mr-3 mt-0.5">4</div>
                    <div>
                        <p class="font-medium text-gray-900">Enter Password</p>
                        <p>Enter the correct password to access the site normally</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Current Settings -->
        <div class="bg-white rounded-lg shadow p-6 mt-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Current Settings</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-600">Maintenance Mode:</span>
                    <span class="font-medium"><?= $maintenanceMode ? 'Enabled' : 'Disabled' ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Password Protection:</span>
                    <span class="font-medium"><?= $passwordEnabled === '1' ? 'Enabled' : 'Disabled' ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Password Set:</span>
                    <span class="font-medium"><?= !empty($maintenancePassword) ? 'Yes' : 'No' ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Session Access:</span>
                    <span class="font-medium"><?= isset($_SESSION['maintenance_access']) ? 'Granted' : 'Not Granted' ?></span>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow p-6 mt-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
            <div class="flex space-x-4">
                <a href="admin/setting.php" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                    Go to Settings
                </a>
                <a href="maintenance.php" class="bg-orange-600 text-white px-4 py-2 rounded-lg hover:bg-orange-700 transition-colors">
                    View Maintenance Page
                </a>
                <a href="index.php" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
                    Go to Homepage
                </a>
            </div>
        </div>
    </div>
</body>
</html> 
 
 