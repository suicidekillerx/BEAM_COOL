<?php
require_once __DIR__ . '/../config/database.php';

try {
    $pdo = getDBConnection();
    
    // Check if admin_users table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'admin_users'");
    if ($stmt->rowCount() == 0) {
        die("Admin users table does not exist.");
    }
    
    // Get current count
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM admin_users");
    $currentCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    echo "<h2>Removing Test Users</h2>";
    echo "<p>Current users in database: $currentCount</p>";
    
    // Find the original admin user (usually the first one created)
    $stmt = $pdo->query("SELECT id, username, full_name FROM admin_users ORDER BY id ASC LIMIT 1");
    $originalUser = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$originalUser) {
        echo "<p>❌ No users found in database</p>";
        return;
    }
    
    echo "<p>🔒 Keeping original user: <strong>{$originalUser['username']}</strong> - {$originalUser['full_name']}</p>";
    
    // Delete all users except the original one
    $stmt = $pdo->prepare("DELETE FROM admin_users WHERE id != ?");
    $result = $stmt->execute([$originalUser['id']]);
    
    if ($result) {
        $deletedCount = $stmt->rowCount();
        
        // Get final count
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM admin_users");
        $finalCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        echo "<h3>Results:</h3>";
        echo "<p>✅ Successfully deleted: $deletedCount test users</p>";
        echo "<p>📊 Remaining users in database: $finalCount</p>";
        echo "<p>🔒 Original admin user preserved: <strong>{$originalUser['username']}</strong></p>";
        
        // Show the remaining user
        $stmt = $pdo->query("SELECT username, full_name, role, created_at FROM admin_users");
        $remainingUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>Remaining Users:</h3>";
        echo "<ul>";
        foreach ($remainingUsers as $user) {
            echo "<li><strong>{$user['username']}</strong> - {$user['full_name']} ({$user['role']}) - Created: " . date('M j, Y', strtotime($user['created_at'])) . "</li>";
        }
        echo "</ul>";
        
    } else {
        echo "<p>❌ Error deleting test users</p>";
    }
    
    echo "<p><a href='setting.php' style='color: blue; text-decoration: underline;'>← Back to Settings</a></p>";
    
} catch (Exception $e) {
    echo "<h2>Error:</h2>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

<style>
body {
    font-family: Arial, sans-serif;
    max-width: 800px;
    margin: 20px auto;
    padding: 20px;
    background-color: #f5f5f5;
}

h2, h3 {
    color: #333;
}

p {
    margin: 10px 0;
    line-height: 1.5;
}

ul {
    background: white;
    padding: 15px;
    border-radius: 5px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

li {
    margin: 5px 0;
    padding: 5px 0;
    border-bottom: 1px solid #eee;
}

li:last-child {
    border-bottom: none;
}
</style> 