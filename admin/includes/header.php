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
        <button class="text-white hover:text-gray-300 focus:outline-none relative p-2 rounded-lg hover:bg-white hover:bg-opacity-10 transition-colors">
            <svg class="w-5 h-5 lg:w-6 lg:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
            </svg>
            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-4 h-4 lg:w-5 lg:h-5 flex items-center justify-center">3</span>
        </button>
        
        <!-- Profile -->
        <div class="flex items-center space-x-2 lg:space-x-3">
            <div class="w-7 h-7 lg:w-8 lg:h-8 bg-white rounded-full flex items-center justify-center">
                <svg class="w-4 h-4 lg:w-5 lg:h-5 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
            </div>
            <span class="text-white font-medium text-sm lg:text-base hidden sm:block">Admin</span>
        </div>
    </div>
</header> 