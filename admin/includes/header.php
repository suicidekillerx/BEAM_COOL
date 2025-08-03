<!-- Header -->
<header class="admin-header px-4 lg:px-6 py-4 flex items-center justify-between">
    <div class="flex items-center space-x-3 lg:space-x-4">
        <button id="sidebar-toggle" class="sidebar-toggle text-white hover:text-gray-300 focus:outline-none p-2 rounded-lg hover:bg-white hover:bg-opacity-10 transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
        </button>
        <h2 class="text-white text-lg lg:text-xl font-semibold"><?php echo $pageTitle ?? 'Dashboard'; ?></h2>
    </div>
    
    <div class="flex items-center space-x-2 lg:space-x-4">
        <!-- Notifications -->
        
        
        <!-- Profile Dropdown -->
        <div class="relative group">
            <button class="flex items-center space-x-2 lg:space-x-3 text-white hover:text-gray-300 focus:outline-none p-2 rounded-lg hover:bg-white hover:bg-opacity-10 transition-colors">
                <div class="w-7 h-7 lg:w-8 lg:h-8 bg-white rounded-full flex items-center justify-center">
                    <svg class="w-4 h-4 lg:w-5 lg:h-5 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <span class="font-medium text-sm lg:text-base hidden sm:block"><?php echo $_SESSION['admin_full_name'] ?? 'Admin'; ?></span>
                <svg class="w-4 h-4 transition-transform group-hover:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            
            <!-- Dropdown Menu -->
            <div class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                <div class="py-2">
                    <div class="px-4 py-2 text-sm text-gray-700 border-b border-gray-100">
                        <div class="font-medium"><?php echo $_SESSION['admin_full_name'] ?? 'Admin'; ?></div>
                        <div class="text-gray-500"><?php echo $_SESSION['admin_role'] ?? 'admin'; ?></div>
                    </div>
                    <a href="logout.php" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                        Sign Out
                    </a>
                </div>
            </div>
        </div>
    </div>
</header> 