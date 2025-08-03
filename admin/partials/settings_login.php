<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Debug: Check if we can connect to database
try {
    $pdo = getDBConnection();
    
    // Check if admin_users table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'admin_users'");
    $tableExists = $stmt->rowCount() > 0;
    
    if (!$tableExists) {
        echo '<div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-red-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                    <div>
                        <p class="text-sm font-medium text-red-800">Database Error</p>
                        <p class="text-xs text-red-600">Admin users table does not exist. Please run the setup script.</p>
                    </div>
                </div>
            </div>';
        return;
    }
    
    // Get admin users from database
    $stmt = $pdo->query("SELECT id, username, email, password, full_name, role, is_active, last_login, created_at FROM admin_users ORDER BY id ASC");
    $adminUsers = $stmt->fetchAll();
    
    // Debug: Show session info
    $sessionInfo = '<div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
        <div class="flex items-center">
            <svg class="w-5 h-5 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div>
                <p class="text-sm font-medium text-blue-800">Debug Info</p>
                <p class="text-xs text-blue-600">Session ID: ' . session_id() . '</p>
                <p class="text-xs text-blue-600">Admin User ID in Session: ' . ($_SESSION['admin_user_id'] ?? 'Not set') . '</p>
                <p class="text-xs text-blue-600">Total Admin Users: ' . count($adminUsers) . '</p>
            </div>
        </div>
    </div>';
    
    // Get current session info
    $currentUser = null;
    if (isset($_SESSION['admin_user_id'])) {
        $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE id = ?");
        $stmt->execute([$_SESSION['admin_user_id']]);
        $currentUser = $stmt->fetch();
    }
    
} catch (Exception $e) {
    echo '<div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-red-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
                <div>
                    <p class="text-sm font-medium text-red-800">Database Error</p>
                    <p class="text-xs text-red-600">' . htmlspecialchars($e->getMessage()) . '</p>
                </div>
            </div>
        </div>';
    return;
}
?>

<!-- Debug Info -->
<?= $sessionInfo ?>

<!-- Login Information -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-gray-900">Login Information</h2>
        <p class="text-sm text-gray-600 mt-1">Manage admin login accounts and settings</p>
    </div>
    
    <div class="p-6">
        <!-- Current Session Info -->
        <div class="mb-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Current Session</h3>
            <?php if ($currentUser): ?>
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-green-800">Logged in as: <span class="font-bold"><?= htmlspecialchars($currentUser['full_name'] ?? $currentUser['username']) ?></span></p>
                            <p class="text-xs text-green-600">Role: <?= htmlspecialchars($currentUser['role'] ?? 'admin') ?></p>
                            <p class="text-xs text-green-600">Last login: <?= date('M j, Y g:i A', strtotime($currentUser['last_login'] ?? 'now')) ?></p>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-yellow-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                        <p class="text-sm text-yellow-800">No active session found</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Admin Users List -->
        <div class="mb-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Admin Users</h3>
            <?php if (empty($adminUsers)): ?>
                <div class="text-center py-8">
                    <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    <p class="text-gray-500 text-sm">No admin users found</p>
                    <p class="text-xs text-gray-400 mt-2">You may need to create the first admin user manually</p>
                </div>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($adminUsers as $user): ?>
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center mb-2">
                                        <h4 class="text-lg font-semibold text-gray-900">
                                            <?= htmlspecialchars($user['full_name'] ?? $user['username']) ?>
                                            <?php if ($currentUser && $currentUser['id'] == $user['id']): ?>
                                                <span class="ml-2 text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full">Current User</span>
                                            <?php endif; ?>
                                        </h4>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                        <div>
                                            <span class="font-medium text-gray-700">Username:</span>
                                            <span class="text-gray-600"><?= htmlspecialchars($user['username']) ?></span>
                                        </div>
                                        <div>
                                            <span class="font-medium text-gray-700">Role:</span>
                                            <span class="text-gray-600"><?= htmlspecialchars($user['role'] ?? 'admin') ?></span>
                                        </div>
                                        <div>
                                            <span class="font-medium text-gray-700">Created:</span>
                                            <span class="text-gray-600"><?= date('M j, Y', strtotime($user['created_at'])) ?></span>
                                        </div>
                                    </div>
                                    <?php if ($user['last_login']): ?>
                                        <div class="mt-2 text-xs text-gray-500">
                                            Last login: <?= date('M j, Y g:i A', strtotime($user['last_login'])) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="flex space-x-2">
                                    <button type="button" onclick="editAdminUser(<?= $user['id'] ?>)" 
                                            class="px-3 py-1 bg-blue-600 text-white text-xs rounded hover:bg-blue-700">
                                        Edit
                                    </button>
                                    <?php if ($currentUser && $currentUser['id'] != $user['id']): ?>
                                        <button type="button" onclick="deleteAdminUser(<?= $user['id'] ?>)" 
                                                class="px-3 py-1 bg-red-600 text-white text-xs rounded hover:bg-red-700">
                                            Delete
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Test Users Section -->
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-yellow-800">Test Users</h3>
                    <p class="text-sm text-yellow-600">Create or remove test users for testing purposes</p>
                </div>
                <div class="flex space-x-2">
                    <a href="create_test_users_simple.php" 
                       class="px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                        Create 1000 Test Users
                    </a>
                    <a href="remove_test_users.php" 
                       class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        Remove All Test Users
                    </a>
                </div>
            </div>
        </div>

        <!-- Unit Tests Section -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-blue-800">Unit Tests</h3>
                    <p class="text-sm text-blue-600">Run comprehensive client-side and server-side tests</p>
                </div>
                <div class="flex space-x-2">
                    <a href="tests/client_side_tests.html" target="_blank"
                       class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Client-Side Tests
                    </a>
                    <a href="tests/admin_panel_tests.html" target="_blank"
                       class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Admin Panel Tests
                    </a>
                    <a href="tests/run_tests.php" target="_blank"
                       class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        Server Tests
                    </a>
                </div>
            </div>
        </div>

        <!-- Shop Pages Tests Section -->
        <div class="bg-purple-50 border border-purple-200 rounded-lg p-4 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-purple-800">Shop Pages Tests</h3>
                    <p class="text-sm text-purple-600">Test all shop pages functionality (HOME, SHOP, COLLECTIONS, SECRET COLLECTION, ABOUT US)</p>
                </div>
                <div class="flex space-x-2">
                    <a href="tests/shop_pages_tests.html" target="_blank"
                       class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                        Client-Side Shop Tests
                    </a>
                    <a href="tests/shop_pages_server_tests.php" target="_blank"
                       class="px-4 py-2 bg-pink-600 text-white rounded-md hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500">
                        Server-Side Shop Tests
                    </a>
                </div>
            </div>
        </div>

        <!-- Add New Admin User -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Add New Admin User</h3>
                <p class="text-sm text-gray-600 mt-1">Create a new admin account (default role: admin)</p>
            </div>
            
            <form id="addAdminForm" class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Full Name -->
                    <div class="form-group">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                        <input type="text" name="admin_full_name" placeholder="e.g., John Doe" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-black focus:border-black">
                    </div>
                    
                    <!-- Username -->
                    <div class="form-group">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Username</label>
                        <input type="text" name="admin_username" placeholder="e.g., admin" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-black focus:border-black">
                    </div>
                    
                    <!-- Password -->
                    <div class="form-group">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                        <input type="password" name="admin_password" placeholder="Enter password" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-black focus:border-black">
                    </div>
                    
                    <!-- Role (Hidden - Default: admin) -->
                    <input type="hidden" name="admin_role" value="admin">
                </div>
                
                <!-- Submit Button -->
                <div class="flex justify-end mt-6">
                    <button type="submit" class="px-6 py-2 bg-black text-white rounded-md hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-black">
                        Add Admin User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Add new admin user form submission
document.getElementById('addAdminForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('action', 'add_admin_user');
    
    fetch('../ajax_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Admin user added successfully!', 'success');
            this.reset();
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification('Error adding admin user: ' + data.message, 'error');
        }
    })
    .catch(error => {
        showNotification('Error adding admin user', 'error');
        console.error('Error:', error);
    });
});

// Edit admin user
function editAdminUser(userId) {
    // For now, just show a message - you can implement edit functionality later
    showNotification('Edit functionality coming soon!', 'info');
}

// Delete admin user
function deleteAdminUser(userId) {
    if (confirm('Are you sure you want to delete this admin user? This action cannot be undone.')) {
        fetch('../ajax_handler.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=delete_admin_user&user_id=${userId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Admin user deleted successfully!', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showNotification('Error deleting admin user: ' + data.message, 'error');
            }
        })
        .catch(error => {
            showNotification('Error deleting admin user', 'error');
            console.error('Error:', error);
        });
    }
}

// Notification function
function showNotification(message, type) {
    const notification = document.createElement('div');
    let bgColor = 'bg-red-600';
    if (type === 'success') bgColor = 'bg-green-600';
    if (type === 'info') bgColor = 'bg-blue-600';
    
    notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg text-white z-50 ${bgColor}`;
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}
</script> 