<?php
require_once 'includes/auth.php';

// This page is for testing session handling
// It will show session status and allow testing session expiration

echo "<h1>Session Test Page</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .info { background: #e3f2fd; padding: 15px; border-radius: 5px; margin: 10px 0; }
    .warning { background: #fff3e0; padding: 15px; border-radius: 5px; margin: 10px 0; }
    .error { background: #ffebee; padding: 15px; border-radius: 5px; margin: 10px 0; }
    .success { background: #e8f5e8; padding: 15px; border-radius: 5px; margin: 10px 0; }
    button { padding: 10px 20px; margin: 5px; border: none; border-radius: 5px; cursor: pointer; }
    .btn-primary { background: #2196f3; color: white; }
    .btn-warning { background: #ff9800; color: white; }
    .btn-danger { background: #f44336; color: white; }
</style>";

if (isAuthenticated()) {
    echo "<div class='success'>";
    echo "<h2>✅ Session is Valid</h2>";
    echo "<p><strong>User ID:</strong> " . $_SESSION['admin_user_id'] . "</p>";
    echo "<p><strong>Username:</strong> " . $_SESSION['admin_username'] . "</p>";
    echo "<p><strong>Full Name:</strong> " . $_SESSION['admin_full_name'] . "</p>";
    echo "<p><strong>Login Time:</strong> " . date('Y-m-d H:i:s', $_SESSION['login_time']) . "</p>";
    echo "<p><strong>Last Activity:</strong> " . date('Y-m-d H:i:s', $_SESSION['last_activity']) . "</p>";
    
    $timeLeft = 604800 - (time() - $_SESSION['last_activity']);
    $daysLeft = floor($timeLeft / 86400);
    $hoursLeft = floor(($timeLeft % 86400) / 3600);
    $minutesLeft = floor(($timeLeft % 3600) / 60);
    
    echo "<p><strong>Time Left:</strong> $daysLeft days, $hoursLeft hours, $minutesLeft minutes</p>";
    
    if ($timeLeft <= 86400) { // 1 day or less
        echo "<div class='warning'>";
        echo "<p><strong>⚠️ Warning:</strong> Session will expire soon!</p>";
        echo "</div>";
    }
    
    echo "</div>";
    
    echo "<div class='info'>";
    echo "<h3>Session Test Actions</h3>";
    echo "<button class='btn-primary' onclick='testSessionCheck()'>Test Session Check (AJAX)</button>";
    echo "<button class='btn-warning' onclick='simulateInactivity()'>Simulate Inactivity</button>";
    echo "<button class='btn-danger' onclick='logout()'>Logout</button>";
    echo "</div>";
    
    echo "<div id='test-results'></div>";
    
    echo "<script>
        async function testSessionCheck() {
            try {
                const response = await fetch('check_session.php', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const result = await response.json();
                
                const resultsDiv = document.getElementById('test-results');
                resultsDiv.innerHTML = '<div class=\"info\"><h4>AJAX Session Check Result:</h4><pre>' + JSON.stringify(result, null, 2) + '</pre></div>';
            } catch (error) {
                document.getElementById('test-results').innerHTML = '<div class=\"error\"><h4>Error:</h4><pre>' + error.message + '</pre></div>';
            }
        }
        
        function simulateInactivity() {
            if (confirm('This will simulate session expiration. Continue?')) {
                // Clear session data
                fetch('logout.php', { method: 'POST' }).then(() => {
                    window.location.href = 'login.php?error=timeout';
                });
            }
        }
        
        function logout() {
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = 'logout.php';
            }
        }
    </script>";
    
} else {
    echo "<div class='error'>";
    echo "<h2>❌ No Valid Session</h2>";
    echo "<p>You are not logged in or your session has expired.</p>";
    echo "<p><a href='login.php'>Go to Login Page</a></p>";
    echo "</div>";
}

echo "<div class='info'>";
echo "<h3>Session Configuration</h3>";
echo "<p><strong>Session Timeout:</strong> 7 days</strong></p>";
echo "<p><strong>Session Regeneration:</strong> Every 5 minutes</strong></p>";
echo "<p><strong>Warning Threshold:</strong> 1 day before expiration</strong></p>";
echo "</div>";
?> 