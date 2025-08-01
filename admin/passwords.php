<?php
session_start();
require_once '../includes/functions.php';

// Check if admin is logged in
//if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
//    header('Location: index.php');
//    exit();
//}

$currentPage = 'passwords';
$pageTitle = 'Password Management';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add_password') {
        $password = $_POST['password'] ?? '';
        $name = $_POST['name'] ?? '';
        $singleUse = isset($_POST['single_use']) ? 1 : 0;
        $maxUses = $_POST['max_uses'] ?? null;
        $expiresAt = $_POST['expires_at'] ?? null;
        
        if (!empty($password)) {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("INSERT INTO passwords (password, name, single_use, max_uses, expires_at) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$password, $name, $singleUse, $maxUses, $expiresAt]);
            
            $success = 'Password added successfully!';
        } else {
            $error = 'Password is required.';
        }
    } elseif ($action === 'delete_password') {
        $passwordId = $_POST['password_id'] ?? '';
        
        if (!empty($passwordId)) {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("DELETE FROM passwords WHERE id = ?");
            $stmt->execute([$passwordId]);
            
            $success = 'Password deleted successfully!';
        }
    } elseif ($action === 'clear_all_sessions') {
        deactivateAllPasswordSessions();
        $success = 'All password sessions cleared successfully!';
    }
}

// Get all passwords
$pdo = getDBConnection();
$stmt = $pdo->query("SELECT * FROM passwords ORDER BY created_at DESC");
$passwords = $stmt->fetchAll();

// Get active password sessions
$activeSessions = getActivePasswordSessions();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Management - Beam Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="admin.css">
</head>
<body class="bg-gray-50 font-['Inter']">
    <div class="flex h-screen">
        <?php $currentPage = 'passwords'; include 'includes/sidebar.php'; ?>
        <div class="flex-1 flex flex-col overflow-hidden">
            <?php $pageTitle = 'Password Management'; include 'includes/header.php'; ?>
            <main class="content-area flex-1 overflow-y-auto p-6">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-3xl font-bold">Password Management</h1>
                    <div class="flex items-center space-x-4">
                        <button onclick="clearAllSessions()" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors">
                            Clear All Sessions
                        </button>
                        <button onclick="document.getElementById('addPasswordModal').classList.remove('hidden')" class="bg-black text-white px-4 py-2 rounded-lg hover:bg-gray-800 transition-colors">
                            Add Password
                        </button>
                    </div>
                </div>

                <?php if (isset($success)): ?>
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                    <?php echo htmlspecialchars($success); ?>
                </div>
                <?php endif; ?>

                <?php if (isset($error)): ?>
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                    <?php echo htmlspecialchars($error); ?>
                </div>
                <?php endif; ?>

                <!-- Passwords Table -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-900">Secret Collection Passwords</h2>
                        <p class="text-sm text-gray-600 mt-1">Manage passwords for accessing secret collections</p>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Password</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usage</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($passwords as $password): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <span class="font-mono text-sm bg-gray-100 px-2 py-1 rounded"><?php echo htmlspecialchars($password['password']); ?></span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo htmlspecialchars($password['name'] ?? 'Unnamed'); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if ($password['single_use']): ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                Single Use
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                Multi Use
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo $password['used_count']; ?>
                                        <?php if ($password['max_uses']): ?>
                                            / <?php echo $password['max_uses']; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php 
                                        $isExpired = $password['expires_at'] && strtotime($password['expires_at']) < time();
                                        $isMaxedOut = $password['max_uses'] && $password['used_count'] >= $password['max_uses'];
                                        $isSingleUsed = $password['single_use'] && $password['used_count'] > 0;
                                        
                                        if ($isExpired || $isMaxedOut || $isSingleUsed): ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                Inactive
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                Active
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo date('M j, Y', strtotime($password['created_at'])); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this password?')">
                                            <input type="hidden" name="action" value="delete_password">
                                            <input type="hidden" name="password_id" value="<?php echo $password['id']; ?>">
                                            <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Active Sessions Table -->
                <div class="bg-white rounded-lg shadow overflow-hidden mt-8">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-900">Active Password Sessions</h2>
                        <p class="text-sm text-gray-600 mt-1">Currently active password sessions</p>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Password</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Session ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP Address</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User Agent</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Accessed</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expires</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php if (empty($activeSessions)): ?>
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                        No active sessions
                                    </td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($activeSessions as $session): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <span class="font-mono text-sm bg-gray-100 px-2 py-1 rounded"><?php echo htmlspecialchars($session['password']); ?></span>
                                            <?php if ($session['password_name']): ?>
                                                <span class="ml-2 text-sm text-gray-500">(<?php echo htmlspecialchars($session['password_name']); ?>)</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="font-mono text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded"><?php echo htmlspecialchars(substr($session['session_id'], 0, 20)) . '...'; ?></span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo htmlspecialchars($session['user_ip'] ?? 'Unknown'); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <span class="truncate max-w-xs block" title="<?php echo htmlspecialchars($session['user_agent'] ?? ''); ?>">
                                            <?php echo htmlspecialchars(substr($session['user_agent'] ?? 'Unknown', 0, 50)) . (strlen($session['user_agent'] ?? '') > 50 ? '...' : ''); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo date('M j, Y H:i', strtotime($session['accessed_at'])); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php if ($session['expires_at']): ?>
                                            <?php echo date('M j, Y H:i', strtotime($session['expires_at'])); ?>
                                        <?php else: ?>
                                            <span class="text-gray-400">No expiration</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Add Password Modal -->
    <div id="addPasswordModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Add New Password</h3>
                <form method="POST">
                    <input type="hidden" name="action" value="add_password">
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                        <input type="text" name="password" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent">
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Name (Optional)</label>
                        <input type="text" name="name" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent">
                    </div>
                    
                    <div class="mb-4">
                        <label class="flex items-center">
                            <input type="checkbox" name="single_use" class="h-4 w-4 text-black focus:ring-black border-gray-300 rounded">
                            <span class="ml-2 text-sm text-gray-700">Single Use Only</span>
                        </label>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Max Uses (Optional)</label>
                        <input type="number" name="max_uses" min="1" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent">
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Expires At (Optional)</label>
                        <input type="datetime-local" name="expires_at" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent">
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="document.getElementById('addPasswordModal').classList.add('hidden')" class="px-4 py-2 text-gray-600 hover:text-gray-800">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-black text-white rounded-md hover:bg-gray-800">
                            Add Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Close modal when clicking outside
        document.getElementById('addPasswordModal').addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.add('hidden');
            }
        });
        
        // Clear all sessions function
        function clearAllSessions() {
            if (confirm('Are you sure you want to clear all active password sessions? This will log out all users from secret collections.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = '<input type="hidden" name="action" value="clear_all_sessions">';
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html> 