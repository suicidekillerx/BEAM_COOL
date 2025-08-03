<?php
// Get all social media links
$social_media_links = $social_media;
?>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex justify-between items-center">
            <h2 class="text-lg font-medium text-gray-900">Social Media Links</h2>
            <button type="button" onclick="openAddSocialModal()" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-black hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-black">
                Add Social Media
            </button>
        </div>
    </div>
    
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Platform</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Icon</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">URL</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($social_media_links)): ?>
                    <tr><td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">No social media links found</td></tr>
                <?php else: ?>
                    <?php foreach ($social_media_links as $social): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= $social['id'] ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= htmlspecialchars(ucfirst($social['platform'])) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="w-6 h-6">
                                    <svg viewBox="0 0 24 24" class="w-full h-full fill-current">
                                        <?= $social['icon_svg'] ?>
                                    </svg>
            </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 truncate max-w-xs"><?= htmlspecialchars($social['link_url']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $social['is_active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                    <?= $social['is_active'] ? 'Active' : 'Inactive' ?>
                                </span>
                            </td>
                                                         <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                 <button onclick="deleteItem('social_media', <?= $social['id'] ?>)" class="text-red-600 hover:text-red-900">Delete</button>
                                 <button onclick="toggleActive('social_media', <?= $social['id'] ?>, <?= $social['is_active'] ? 0 : 1 ?>)" class="ml-3 text-<?= $social['is_active'] ? 'yellow' : 'green' ?>-600 hover:text-<?= $social['is_active'] ? 'yellow' : 'green' ?>-900">
                                     <?= $social['is_active'] ? 'Deactivate' : 'Activate' ?>
                                 </button>
                             </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
            </div>
            </div>
            
<!-- Add/Edit Social Media Modal -->
<div id="socialModal" class="hidden fixed z-10 inset-0 overflow-y-auto">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <h3 class="text-lg font-medium text-gray-900 mb-4" id="modalTitle">Add Social Media</h3>
            </div>
            <form id="socialForm" class="bg-white px-4 pb-4 sm:p-6 sm:pb-4">
                <input type="hidden" name="id" id="socialId">
                <div class="mb-4">
                    <label for="platform" class="block text-sm font-medium text-gray-700">Platform</label>
                    <input type="text" name="platform" id="platform" required 
                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-black focus:border-black sm:text-sm"
                           placeholder="e.g., Facebook, Twitter, Instagram">
            </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Social Media Icon</label>
                    <div class="grid grid-cols-4 gap-3 mb-3">
                        <div class="icon-option cursor-pointer p-3 border-2 border-gray-200 rounded-lg hover:border-black transition-colors" data-icon="instagram">
                            <div class="w-8 h-8 mx-auto mb-1">
                                <svg viewBox="0 0 24 24" class="w-full h-full fill-current text-pink-500">
                                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                                </svg>
        </div>
                            <p class="text-xs text-center text-gray-600">Instagram</p>
        </div>
                        <div class="icon-option cursor-pointer p-3 border-2 border-gray-200 rounded-lg hover:border-black transition-colors" data-icon="facebook">
                            <div class="w-8 h-8 mx-auto mb-1">
                                <svg viewBox="0 0 24 24" class="w-full h-full fill-current text-blue-600">
                                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                </svg>
</div>
                            <p class="text-xs text-center text-gray-600">Facebook</p>
    </div>
                        <div class="icon-option cursor-pointer p-3 border-2 border-gray-200 rounded-lg hover:border-black transition-colors" data-icon="twitter">
                            <div class="w-8 h-8 mx-auto mb-1">
                                <svg viewBox="0 0 24 24" class="w-full h-full fill-current text-blue-400">
                                    <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                </svg>
                            </div>
                            <p class="text-xs text-center text-gray-600">Twitter</p>
            </div>
                        <div class="icon-option cursor-pointer p-3 border-2 border-gray-200 rounded-lg hover:border-black transition-colors" data-icon="youtube">
                            <div class="w-8 h-8 mx-auto mb-1">
                                <svg viewBox="0 0 24 24" class="w-full h-full fill-current text-red-600">
                                    <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                                </svg>
                            </div>
                            <p class="text-xs text-center text-gray-600">YouTube</p>
                        </div>
                        <div class="icon-option cursor-pointer p-3 border-2 border-gray-200 rounded-lg hover:border-black transition-colors" data-icon="tiktok">
                            <div class="w-8 h-8 mx-auto mb-1">
                                <svg viewBox="0 0 24 24" class="w-full h-full fill-current text-black">
                                    <path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 2.31-3.59 3.99-6.12 4.51-2.56.52-5.18-.06-7.44-1.61-2.26-1.55-3.96-3.87-4.47-6.46-.51-2.59.06-5.21 1.61-7.47 1.55-2.26 3.87-3.96 6.46-4.47 2.59-.51 5.21.06 7.47 1.61.57.39 1.1.82 1.62 1.26.01-2.92-.01-5.84.02-8.75z"/>
                                </svg>
                            </div>
                            <p class="text-xs text-center text-gray-600">TikTok</p>
                        </div>
                        <div class="icon-option cursor-pointer p-3 border-2 border-gray-200 rounded-lg hover:border-black transition-colors" data-icon="linkedin">
                            <div class="w-8 h-8 mx-auto mb-1">
                                <svg viewBox="0 0 24 24" class="w-full h-full fill-current text-blue-700">
                                    <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                                </svg>
                            </div>
                            <p class="text-xs text-center text-gray-600">LinkedIn</p>
                        </div>
                        <div class="icon-option cursor-pointer p-3 border-2 border-gray-200 rounded-lg hover:border-black transition-colors" data-icon="pinterest">
                            <div class="w-8 h-8 mx-auto mb-1">
                                <svg viewBox="0 0 24 24" class="w-full h-full fill-current text-red-500">
                                    <path d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 5.079 3.158 9.417 7.618 11.174-.105-.949-.199-2.403.041-3.439.219-.937 1.406-5.957 1.406-5.957s-.359-.72-.359-1.781c0-1.663.967-2.911 2.168-2.911 1.024 0 1.518.769 1.518 1.688 0 1.029-.653 2.567-.992 3.992-.285 1.193.6 2.165 1.775 2.165 2.128 0 3.768-2.245 3.768-5.487 0-2.861-2.063-4.869-5.008-4.869-3.41 0-5.409 2.562-5.409 5.199 0 1.033.394 2.143.889 2.741.099.12.112.225.085.345-.09.375-.293 1.199-.334 1.363-.053.225-.172.271-.402.165-1.495-.69-2.433-2.878-2.433-4.646 0-3.776 2.748-7.252 7.92-7.252 4.158 0 7.392 2.967 7.392 6.923 0 4.135-2.607 7.462-6.233 7.462-1.214 0-2.357-.629-2.75-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146C9.57 23.812 10.763 24.009 12.017 24.009c6.624 0 11.99-5.367 11.99-11.988C24.007 5.367 18.641.001 12.017.001z"/>
                                </svg>
                            </div>
                            <p class="text-xs text-center text-gray-600">Pinterest</p>
                        </div>
                        <div class="icon-option cursor-pointer p-3 border-2 border-gray-200 rounded-lg hover:border-black transition-colors" data-icon="snapchat">
                            <div class="w-8 h-8 mx-auto mb-1">
                                <svg viewBox="0 0 24 24" class="w-full h-full fill-current text-yellow-400">
                                    <path d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 5.079 3.158 9.417 7.618 11.174-.105-.949-.199-2.403.041-3.439.219-.937 1.406-5.957 1.406-5.957s-.359-.72-.359-1.781c0-1.663.967-2.911 2.168-2.911 1.024 0 1.518.769 1.518 1.688 0 1.029-.653 2.567-.992 3.992-.285 1.193.6 2.165 1.775 2.165 2.128 0 3.768-2.245 3.768-5.487 0-2.861-2.063-4.869-5.008-4.869-3.41 0-5.409 2.562-5.409 5.199 0 1.033.394 2.143.889 2.741.099.12.112.225.085.345-.09.375-.293 1.199-.334 1.363-.053.225-.172.271-.402.165-1.495-.69-2.433-2.878-2.433-4.646 0-3.776 2.748-7.252 7.92-7.252 4.158 0 7.392 2.967 7.392 6.923 0 4.135-2.607 7.462-6.233 7.462-1.214 0-2.357-.629-2.75-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146C9.57 23.812 10.763 24.009 12.017 24.009c6.624 0 11.99-5.367 11.99-11.988C24.007 5.367 18.641.001 12.017.001z"/>
                                </svg>
                            </div>
                            <p class="text-xs text-center text-gray-600">Snapchat</p>
                        </div>
                    </div>
                    <input type="hidden" name="icon_svg" id="icon_svg" required>
                    <div class="text-xs text-gray-500">Click on an icon above to select it</div>
                </div>
                <div class="mb-4">
                    <label for="social_link_url" class="block text-sm font-medium text-gray-700">Profile URL</label>
                    <input type="url" name="link_url" id="social_link_url" required 
                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-black focus:border-black sm:text-sm" 
                           placeholder="https://">
                </div>
                <div class="mb-4">
                    <label for="social_sort_order" class="block text-sm font-medium text-gray-700">Sort Order</label>
                    <input type="number" name="sort_order" id="social_sort_order" 
                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-black focus:border-black sm:text-sm">
                </div>
                <div class="flex items-center">
                    <input type="checkbox" name="is_active" id="social_is_active" class="h-4 w-4 text-black focus:ring-black border-gray-300 rounded">
                    <label for="social_is_active" class="ml-2 block text-sm text-gray-700">Active</label>
                </div>
            </form>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" id="saveSocialBtn" 
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-black text-base font-medium text-white hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-black sm:ml-3 sm:w-auto sm:text-sm">
                    Save
                </button>
                <button type="button" onclick="closeModal('socialModal')" 
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-black sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Open modal for adding new social media
function openAddSocialModal() {
    currentAction = 'add';
    currentId = null;
    document.getElementById('socialForm').reset();
    document.getElementById('modalTitle').textContent = 'Add Social Media';
    document.getElementById('socialModal').classList.remove('hidden');
}



// Save social media
document.getElementById('saveSocialBtn').addEventListener('click', function() {
    const form = document.getElementById('socialForm');
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());
    
    // Add is_active value (checkbox handling)
    data.is_active = form.elements.is_active.checked ? 1 : 0;
    
    fetch('setting.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            action: 'add',
            table: 'social_media',
            data: data
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Social media added successfully!', 'success');
            closeModal('socialModal');
            setTimeout(() => location.reload(), 1000);
        } else {
            showAlert('Error: ' + (data.error || 'Unknown error'), 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Error saving. Please try again.', 'error');
    });
});

// Close modal
function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}

// Icon mapping
const iconMap = {
    'instagram': '<path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>',
    'facebook': '<path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>',
    'twitter': '<path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>',
    'youtube': '<path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>',
    'tiktok': '<path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 2.31-3.59 3.99-6.12 4.51-2.56.52-5.18-.06-7.44-1.61-2.26-1.55-3.96-3.87-4.47-6.46-.51-2.59.06-5.21 1.61-7.47 1.55-2.26 3.87-3.96 6.46-4.47 2.59-.51 5.21.06 7.47 1.61.57.39 1.1.82 1.62 1.26.01-2.92-.01-5.84.02-8.75z"/>',
    'linkedin': '<path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>',
    'pinterest': '<path d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 5.079 3.158 9.417 7.618 11.174-.105-.949-.199-2.403.041-3.439.219-.937 1.406-5.957 1.406-5.957s-.359-.72-.359-1.781c0-1.663.967-2.911 2.168-2.911 1.024 0 1.518.769 1.518 1.688 0 1.029-.653 2.567-.992 3.992-.285 1.193.6 2.165 1.775 2.165 2.128 0 3.768-2.245 3.768-5.487 0-2.861-2.063-4.869-5.008-4.869-3.41 0-5.409 2.562-5.409 5.199 0 1.033.394 2.143.889 2.741.099.12.112.225.085.345-.09.375-.293 1.199-.334 1.363-.053.225-.172.271-.402.165-1.495-.69-2.433-2.878-2.433-4.646 0-3.776 2.748-7.252 7.92-7.252 4.158 0 7.392 2.967 7.392 6.923 0 4.135-2.607 7.462-6.233 7.462-1.214 0-2.357-.629-2.75-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146C9.57 23.812 10.763 24.009 12.017 24.009c6.624 0 11.99-5.367 11.99-11.988C24.007 5.367 18.641.001 12.017.001z"/>',
    'snapchat': '<path d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 5.079 3.158 9.417 7.618 11.174-.105-.949-.199-2.403.041-3.439.219-.937 1.406-5.957 1.406-5.957s-.359-.72-.359-1.781c0-1.663.967-2.911 2.168-2.911 1.024 0 1.518.769 1.518 1.688 0 1.029-.653 2.567-.992 3.992-.285 1.193.6 2.165 1.775 2.165 2.128 0 3.768-2.245 3.768-5.487 0-2.861-2.063-4.869-5.008-4.869-3.41 0-5.409 2.562-5.409 5.199 0 1.033.394 2.143.889 2.741.099.12.112.225.085.345-.09.375-.293 1.199-.334 1.363-.053.225-.172.271-.402.165-1.495-.69-2.433-2.878-2.433-4.646 0-3.776 2.748-7.252 7.92-7.252 4.158 0 7.392 2.967 7.392 6.923 0 4.135-2.607 7.462-6.233 7.462-1.214 0-2.357-.629-2.75-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146C9.57 23.812 10.763 24.009 12.017 24.009c6.624 0 11.99-5.367 11.99-11.988C24.007 5.367 18.641.001 12.017.001z"/>'
};

// Icon selection handler
document.addEventListener('DOMContentLoaded', function() {
    const iconOptions = document.querySelectorAll('.icon-option');
    const iconInput = document.getElementById('icon_svg');
    
    iconOptions.forEach(option => {
        option.addEventListener('click', function() {
            // Remove active class from all options
            iconOptions.forEach(opt => {
                opt.classList.remove('border-black', 'bg-gray-50');
                opt.classList.add('border-gray-200');
            });
            
            // Add active class to selected option
            this.classList.remove('border-gray-200');
            this.classList.add('border-black', 'bg-gray-50');
            
            // Set the SVG path
            const iconName = this.dataset.icon;
            iconInput.value = iconMap[iconName];
        });
    });
});

// Show alert message
function showAlert(message, type = 'success') {
    let alert = document.getElementById('alertMessage') || document.createElement('div');
    alert.id = 'alertMessage';
    alert.className = `fixed top-4 right-4 p-4 rounded-md shadow-lg z-50 ${type === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`;
    alert.textContent = message;
    if (!document.getElementById('alertMessage')) {
        document.body.appendChild(alert);
    }
    setTimeout(() => alert.remove(), 3000);
}
</script> 
