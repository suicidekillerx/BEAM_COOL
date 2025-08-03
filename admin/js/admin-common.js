// Admin Common JavaScript
// Prevents form resubmission warnings and handles common admin functionality

// Security measures
if (window.history.replaceState) {
    window.history.replaceState(null, null, window.location.href);
}

// Prevent caching of admin pages
if (window.performance && window.performance.navigation.type === window.performance.navigation.TYPE_BACK_FORWARD) {
    window.location.reload();
}

// Clear form data after successful submission
document.addEventListener('DOMContentLoaded', function() {
    // Handle form submissions
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function() {
            // Store form data in sessionStorage to prevent resubmission
            const formData = new FormData(form);
            const formObject = {};
            formData.forEach((value, key) => {
                formObject[key] = value;
            });
            sessionStorage.setItem('formData', JSON.stringify(formObject));
        });
    });
    
    // Clear form data on page load
    sessionStorage.removeItem('formData');
    
    // Add active class to current menu item
    const currentPage = document.body.dataset.currentPage;
    if (currentPage) {
        const menuItems = document.querySelectorAll('.menu-item');
        menuItems.forEach(item => {
            if (item.getAttribute('href') && item.getAttribute('href').includes(currentPage)) {
                item.classList.add('active');
            }
        });
    }
    
    // Mobile sidebar functionality
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('open');
            sidebarOverlay.classList.toggle('open');
        });
    }
    
    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', function() {
            sidebar.classList.remove('open');
            sidebarOverlay.classList.remove('open');
        });
    }
    
    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(event) {
        if (window.innerWidth < 1024) { // Only on mobile
            const isClickInsideSidebar = sidebar && sidebar.contains(event.target);
            const isClickOnToggle = sidebarToggle && sidebarToggle.contains(event.target);
            
            if (!isClickInsideSidebar && !isClickOnToggle && sidebar && sidebar.classList.contains('open')) {
                sidebar.classList.remove('open');
                sidebarOverlay.classList.remove('open');
            }
        }
    });
    
    // Close modals when clicking outside
    document.addEventListener('click', function(event) {
        if (event.target.classList.contains('modal')) {
            event.target.classList.remove('show');
        }
    });
    
    // Close modals with Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            document.querySelectorAll('.modal').forEach(modal => {
                modal.classList.remove('show');
            });
        }
    });
    
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
    
    // Auto-logout after inactivity (30 minutes)
    let inactivityTimer;
    const INACTIVITY_TIMEOUT = 30 * 60 * 1000; // 30 minutes
    
    function resetInactivityTimer() {
        clearTimeout(inactivityTimer);
        inactivityTimer = setTimeout(function() {
            alert('Session expired due to inactivity. You will be redirected to login.');
            window.location.href = 'logout.php';
        }, INACTIVITY_TIMEOUT);
    }
    
    // Reset timer on user activity
    ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'].forEach(function(event) {
        document.addEventListener(event, resetInactivityTimer, true);
    });
    
    // Initialize timer
    resetInactivityTimer();
});

// Utility functions
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 transform translate-x-full transition-transform duration-300 ${
        type === 'success' ? 'bg-green-500 text-white' : 
        type === 'error' ? 'bg-red-500 text-white' : 
        type === 'warning' ? 'bg-yellow-500 text-white' : 
        'bg-blue-500 text-white'
    }`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    // Animate in
    setTimeout(() => {
        notification.classList.remove('translate-x-full');
    }, 100);
    
    // Remove after 3 seconds
    setTimeout(() => {
        notification.classList.add('translate-x-full');
        setTimeout(() => {
            if (document.body.contains(notification)) {
                document.body.removeChild(notification);
            }
        }, 300);
    }, 3000);
}

// Debounce function for search inputs
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
} 