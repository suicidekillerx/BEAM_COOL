// Security measures for admin panel
document.addEventListener('DOMContentLoaded', function() {
    
    // Prevent browser back button after logout
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }
    
    // Clear form data from sessionStorage to prevent resubmission
    if (sessionStorage.getItem('formData')) {
        sessionStorage.removeItem('formData');
    }
    
    // Add event listener for beforeunload to clear sensitive data
    window.addEventListener('beforeunload', function() {
        // Clear any sensitive data from sessionStorage
        sessionStorage.removeItem('formData');
        sessionStorage.removeItem('tempData');
    });
    
    // Prevent caching of admin pages
    if (window.performance && window.performance.navigation.type === window.performance.navigation.TYPE_BACK_FORWARD) {
        // User navigated back/forward, reload the page to ensure fresh content
        window.location.reload();
    }
    
    // Add logout confirmation
    const logoutLinks = document.querySelectorAll('a[href*="logout"], button[onclick*="logout"]');
    logoutLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to logout?')) {
                e.preventDefault();
                return false;
            }
        });
    });
    
    // Auto-logout after inactivity (7 days)
    let inactivityTimer;
    const INACTIVITY_TIMEOUT = 7 * 24 * 60 * 60 * 1000; // 7 days
    
    function resetInactivityTimer() {
        clearTimeout(inactivityTimer);
        inactivityTimer = setTimeout(function() {
            showSessionExpiredMessage('Session expired after 7 days of inactivity. You will be redirected to login.');
            setTimeout(() => {
                window.location.href = 'logout.php';
            }, 3000);
        }, INACTIVITY_TIMEOUT);
    }
    
    // Reset timer on user activity
    ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'].forEach(function(event) {
        document.addEventListener(event, resetInactivityTimer, true);
    });
    
    // Initialize timer
    resetInactivityTimer();
    
    // Check for session timeout every minute
    setInterval(function() {
        checkSessionStatus();
    }, 60000);
    
    // Function to check session status via AJAX
    async function checkSessionStatus() {
        try {
            const response = await fetch('check_session.php', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const result = await response.json();
            
            if (!result.valid) {
                showSessionExpiredMessage('Your session has expired. Redirecting to login...');
                setTimeout(() => {
                    window.location.href = 'login.php?error=session_expired';
                }, 3000);
            }
        } catch (error) {
            console.log('Session check failed:', error);
        }
    }
    
    // Function to show session expired message
    function showSessionExpiredMessage(message) {
        // Remove existing message if any
        const existingMessage = document.getElementById('session-expired-message');
        if (existingMessage) {
            existingMessage.remove();
        }
        
        // Create and show message
        const messageDiv = document.createElement('div');
        messageDiv.id = 'session-expired-message';
        messageDiv.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: #dc3545;
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            z-index: 9999;
            max-width: 300px;
            font-family: Arial, sans-serif;
        `;
        messageDiv.innerHTML = `
            <div style="display: flex; align-items: center;">
                <svg style="width: 20px; height: 20px; margin-right: 10px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                ${message}
            </div>
        `;
        
        document.body.appendChild(messageDiv);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (messageDiv.parentNode) {
                messageDiv.remove();
            }
        }, 5000);
    }
});

// Function to clear browser cache for admin pages
function clearAdminCache() {
    if ('caches' in window) {
        caches.keys().then(function(names) {
            for (let name of names) {
                caches.delete(name);
            }
        });
    }
}

// Function to prevent form resubmission
function preventFormResubmission() {
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function() {
            // Store form data temporarily
            const formData = new FormData(form);
            const formObject = {};
            formData.forEach((value, key) => {
                formObject[key] = value;
            });
            sessionStorage.setItem('formData', JSON.stringify(formObject));
        });
    });
    
    // Clear stored form data after page load
    if (sessionStorage.getItem('formData')) {
        sessionStorage.removeItem('formData');
    }
}

// Export functions for use in other scripts
window.adminSecurity = {
    clearAdminCache: clearAdminCache,
    preventFormResubmission: preventFormResubmission
}; 