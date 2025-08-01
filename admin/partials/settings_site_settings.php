<?php
// Site Settings Configuration
$siteSettings = [
    'brand_name' => ['label' => 'Brand Name', 'type' => 'text', 'required' => true, 'description' => 'Your brand name displayed throughout the site'],
    'brand_logo' => ['label' => 'Primary Logo', 'type' => 'image', 'description' => 'Main logo used in header and branding'],
    'brand_logo2' => ['label' => 'Secondary Logo', 'type' => 'image', 'description' => 'Alternative logo for different contexts'],
    'hero_image' => ['label' => 'Hero Image', 'type' => 'image', 'description' => 'Main hero image on homepage'],
    'announcement_text_1' => ['label' => 'Announcement 1', 'type' => 'text', 'description' => 'First announcement banner text'],
    'announcement_text_2' => ['label' => 'Announcement 2', 'type' => 'text', 'description' => 'Second announcement banner text'],
    'announcement_text_3' => ['label' => 'Announcement 3', 'type' => 'text', 'description' => 'Third announcement banner text'],
    'what_makes_special_title' => ['label' => 'Special Section Title', 'type' => 'text', 'description' => 'Title for the "What Makes Us Special" section'],
    'what_makes_special_description' => ['label' => 'Special Section Description', 'type' => 'textarea', 'description' => 'Description for the "What Makes Us Special" section'],
    'shipping_cost' => ['label' => 'Default Shipping Cost', 'type' => 'number', 'step' => '0.01', 'description' => 'Default shipping cost for orders'],
    'tax_rate' => ['label' => 'Tax Rate (%)', 'type' => 'number', 'step' => '0.01', 'description' => 'Tax rate applied to orders'],
    'contact_email' => ['label' => 'Contact Email', 'type' => 'email', 'description' => 'Primary contact email'],
    'contact_phone' => ['label' => 'Contact Phone', 'type' => 'tel', 'description' => 'Primary contact phone number'],
    'address' => ['label' => 'Business Address', 'type' => 'textarea', 'description' => 'Business address for contact information'],
    'currency' => ['label' => 'Currency', 'type' => 'text', 'default' => 'TND', 'description' => 'Default currency for the store'],
    'maintenance_mode' => ['label' => 'Maintenance Mode', 'type' => 'checkbox', 'description' => 'Enable maintenance mode to restrict access']
];

// Get existing settings
$settings = [];
foreach ($site_settings as $setting) {
    $settings[$setting['setting_key']] = $setting['setting_value'];
}
?>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-gray-900">Site Settings</h2>
        <p class="text-sm text-gray-600 mt-1">Configure your website's appearance, branding, and business settings</p>
    </div>
    
    <form id="siteSettingsForm" class="p-6">
        <!-- Branding Section -->
        <div class="mb-8">
            <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zM21 5a2 2 0 00-2-2h-4a2 2 0 00-2 2v12a4 4 0 004 4h4a2 2 0 002-2V5z"></path>
                </svg>
                Branding & Identity
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Brand Name -->
                <div class="form-group">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Brand Name
                        <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="brand_name" value="<?= htmlspecialchars($settings['brand_name'] ?? '') ?>" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-black focus:border-black" required>
                    <p class="text-xs text-gray-500 mt-1">Your brand name displayed throughout the site</p>
                </div>
                
                <!-- Primary Logo -->
                <div class="form-group">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Primary Logo</label>
                    <div class="flex items-center space-x-4">
                        <div class="flex-shrink-0">
                            <div class="w-16 h-16 bg-gray-100 rounded-lg border-2 border-dashed border-gray-300 flex items-center justify-center overflow-hidden">
                                <?php if (!empty($settings['brand_logo'])): ?>
                                    <img src="../<?= htmlspecialchars($settings['brand_logo']) ?>" alt="Primary Logo" class="w-full h-full object-contain">
                                <?php else: ?>
                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="flex-1">
                            <input type="hidden" name="brand_logo" value="<?= htmlspecialchars($settings['brand_logo'] ?? '') ?>" id="brand_logo_input">
                            <div class="flex space-x-2">
                                <input type="file" id="brand_logo_file" accept="image/*" class="hidden" onchange="uploadLogo('brand_logo', this)">
                                <button type="button" onclick="document.getElementById('brand_logo_file').click()" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                                    Upload Logo
                                </button>
                                <button type="button" onclick="removeLogo('brand_logo')" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                    Remove
                                </button>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Main logo used in header and branding</p>
                        </div>
                    </div>
                </div>
                
                <!-- Secondary Logo -->
                <div class="form-group">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Secondary Logo</label>
                    <div class="flex items-center space-x-4">
                        <div class="flex-shrink-0">
                            <div class="w-16 h-16 bg-gray-100 rounded-lg border-2 border-dashed border-gray-300 flex items-center justify-center overflow-hidden">
                                <?php if (!empty($settings['brand_logo2'])): ?>
                                    <img src="../<?= htmlspecialchars($settings['brand_logo2']) ?>" alt="Secondary Logo" class="w-full h-full object-contain">
                                <?php else: ?>
                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="flex-1">
                            <input type="hidden" name="brand_logo2" value="<?= htmlspecialchars($settings['brand_logo2'] ?? '') ?>" id="brand_logo2_input">
                            <div class="flex space-x-2">
                                <input type="file" id="brand_logo2_file" accept="image/*" class="hidden" onchange="uploadLogo('brand_logo2', this)">
                                <button type="button" onclick="document.getElementById('brand_logo2_file').click()" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                                    Upload Logo
                                </button>
                                <button type="button" onclick="removeLogo('brand_logo2')" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                    Remove
                                </button>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Alternative logo for different contexts</p>
                        </div>
                    </div>
                </div>
                
                <!-- Hero Image -->
                <div class="form-group">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Hero Image</label>
                    <div class="flex items-center space-x-4">
                        <div class="flex-shrink-0">
                            <div class="w-16 h-16 bg-gray-100 rounded-lg border-2 border-dashed border-gray-300 flex items-center justify-center overflow-hidden">
                                <?php if (!empty($settings['hero_image'])): ?>
                                    <img src="../<?= htmlspecialchars($settings['hero_image']) ?>" alt="Hero Image" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="flex-1">
                            <input type="hidden" name="hero_image" value="<?= htmlspecialchars($settings['hero_image'] ?? '') ?>" id="hero_image_input">
                            <div class="flex space-x-2">
                                <input type="file" id="hero_image_file" accept="image/*" class="hidden" onchange="uploadLogo('hero_image', this)">
                                <button type="button" onclick="document.getElementById('hero_image_file').click()" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                                    Upload Image
                                </button>
                                <button type="button" onclick="removeLogo('hero_image')" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                    Remove
                                </button>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Main hero image on homepage</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Announcements Section -->
        <div class="mb-8">
            <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path>
                </svg>
                Announcements
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="form-group">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Announcement 1</label>
                    <input type="text" name="announcement_text_1" value="<?= htmlspecialchars($settings['announcement_text_1'] ?? '') ?>" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-black focus:border-black"
                           placeholder="SHIPPING TO TUNISIA ON ORDERS +$150">
                    <p class="text-xs text-gray-500 mt-1">First announcement banner text</p>
                </div>
                <div class="form-group">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Announcement 2</label>
                    <input type="text" name="announcement_text_2" value="<?= htmlspecialchars($settings['announcement_text_2'] ?? '') ?>" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-black focus:border-black"
                           placeholder="EASY RETURNS">
                    <p class="text-xs text-gray-500 mt-1">Second announcement banner text</p>
                </div>
                <div class="form-group">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Announcement 3</label>
                    <input type="text" name="announcement_text_3" value="<?= htmlspecialchars($settings['announcement_text_3'] ?? '') ?>" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-black focus:border-black"
                           placeholder="FREE SHIPPING TO TUNISIA ON ORDERS +$150">
                    <p class="text-xs text-gray-500 mt-1">Third announcement banner text</p>
                </div>
            </div>
        </div>
        
        <!-- Content Section -->
        <div class="mb-8">
            <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Content
            </h3>
            <div class="space-y-4">
                <div class="form-group">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Special Section Title</label>
                    <input type="text" name="what_makes_special_title" value="<?= htmlspecialchars($settings['what_makes_special_title'] ?? '') ?>" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-black focus:border-black"
                           placeholder="What Makes Beam Special?">
                    <p class="text-xs text-gray-500 mt-1">Title for the "What Makes Us Special" section</p>
                </div>
                <div class="form-group">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Special Section Description</label>
                    <textarea name="what_makes_special_description" rows="4" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-black focus:border-black"><?= htmlspecialchars($settings['what_makes_special_description'] ?? '') ?></textarea>
                    <p class="text-xs text-gray-500 mt-1">Description for the "What Makes Us Special" section</p>
                </div>
            </div>
        </div>
        
        <!-- Business Settings Section -->
        <div class="mb-8">
            <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
                Business Settings
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="form-group">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Default Shipping Cost</label>
                    <input type="number" name="shipping_cost" value="<?= htmlspecialchars($settings['shipping_cost'] ?? '') ?>" step="0.01"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-black focus:border-black"
                           placeholder="8.00">
                    <p class="text-xs text-gray-500 mt-1">Default shipping cost for orders</p>
                </div>
                <div class="form-group">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tax Rate (%)</label>
                    <input type="number" name="tax_rate" value="<?= htmlspecialchars($settings['tax_rate'] ?? '') ?>" step="0.01"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-black focus:border-black"
                           placeholder="15.00">
                    <p class="text-xs text-gray-500 mt-1">Tax rate applied to orders</p>
                </div>
                <div class="form-group">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Currency</label>
                    <input type="text" name="currency" value="<?= htmlspecialchars($settings['currency'] ?? 'TND') ?>" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-black focus:border-black"
                           placeholder="TND">
                    <p class="text-xs text-gray-500 mt-1">Default currency for the store</p>
                </div>
                <div class="form-group">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Contact Email</label>
                    <input type="email" name="contact_email" value="<?= htmlspecialchars($settings['contact_email'] ?? '') ?>" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-black focus:border-black"
                           placeholder="contact@beam.com">
                    <p class="text-xs text-gray-500 mt-1">Primary contact email</p>
                </div>
                <div class="form-group">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Contact Phone</label>
                    <input type="tel" name="contact_phone" value="<?= htmlspecialchars($settings['contact_phone'] ?? '') ?>" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-black focus:border-black"
                           placeholder="+216 XX XXX XXX">
                    <p class="text-xs text-gray-500 mt-1">Primary contact phone number</p>
                </div>
                <div class="form-group">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Business Address</label>
                    <textarea name="address" rows="3" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-black focus:border-black"
                              placeholder="Enter your business address"><?= htmlspecialchars($settings['address'] ?? '') ?></textarea>
                    <p class="text-xs text-gray-500 mt-1">Business address for contact information</p>
                </div>
            </div>
        </div>
        
        <!-- System Settings Section -->
        <div class="mb-8">
            <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                System Settings
            </h3>
            <div class="form-group">
                <div class="flex items-center">
                    <input type="hidden" name="maintenance_mode" value="0">
                    <input type="checkbox" name="maintenance_mode" value="1" 
                           class="h-4 w-4 text-black focus:ring-black border-gray-300 rounded" 
                           <?= !empty($settings['maintenance_mode']) ? 'checked' : '' ?>>
                    <label class="ml-2 block text-sm text-gray-700">Maintenance Mode</label>
                </div>
                <p class="text-xs text-gray-500 mt-1">Enable maintenance mode to restrict access</p>
            </div>
        </div>
        
        <!-- Save Button -->
        <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
            <button type="button" id="saveSettings" class="px-6 py-2 bg-black text-white rounded-md hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-black transition-colors">
                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                Save Changes
            </button>
        </div>
    </form>
</div>

<script>
document.getElementById('saveSettings').addEventListener('click', function() {
    const form = document.getElementById('siteSettingsForm');
    const formData = new FormData(form);
    const data = {};
    
    // Convert form data to object
    for (let [key, value] of formData.entries()) {
        // Handle checkboxes
        if (form.elements[key].type === 'checkbox') {
            data[key] = form.elements[key].checked ? '1' : '0';
        } else {
            data[key] = value;
        }
    }
    
    // Send data to server
    fetch('setting.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'save_site_settings',
            data: data
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Settings saved successfully!', 'success');
        } else {
            showAlert('Error saving settings: ' + (data.error || 'Unknown error'), 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Error saving settings. Please try again.', 'error');
    });
});

function showAlert(message, type = 'success') {
    // Create alert element if it doesn't exist
    let alert = document.getElementById('settingsAlert');
    if (!alert) {
        alert = document.createElement('div');
        alert.id = 'settingsAlert';
        alert.className = 'fixed top-4 right-4 p-4 rounded-md shadow-lg z-50';
        document.body.appendChild(alert);
    }
    
    // Set alert content and style
    alert.textContent = message;
    alert.className = `fixed top-4 right-4 p-4 rounded-md shadow-lg z-50 ${type === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`;
    
    // Remove alert after 3 seconds
    setTimeout(() => {
        alert.remove();
    }, 3000);
}

// Logo upload function
function uploadLogo(logoType, fileInput) {
    if (!fileInput.files[0]) {
        showAlert('Please select a file', 'error');
        return;
    }
    
    console.log('Uploading logo:', logoType, 'File:', fileInput.files[0].name);
    
    const formData = new FormData();
    formData.append('image', fileInput.files[0]);
    
    fetch('upload_image.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        console.log('Upload response:', data);
        if (data.success) {
            // Update the hidden input
            const hiddenInput = document.getElementById(logoType + '_input');
            hiddenInput.value = data.file_path;
            console.log('Updated hidden input:', logoType + '_input', 'with value:', data.file_path);
            
            // Update the image preview
            const previewContainer = fileInput.closest('.form-group').querySelector('.flex-shrink-0 .w-16.h-16');
            previewContainer.innerHTML = `<img src="../${data.file_path}" alt="${logoType}" class="w-full h-full object-contain">`;
            
            showAlert('Logo uploaded successfully! Auto-saving to database...', 'success');
            
            // Auto-save to database
            autoSaveLogo(logoType, data.file_path);
        } else {
            showAlert('Error uploading logo: ' + data.error, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Error uploading logo. Please try again.', 'error');
    });
    
    // Reset file input
    fileInput.value = '';
}

// Auto-save logo to database
function autoSaveLogo(logoType, filePath) {
    console.log('Auto-saving logo:', logoType, 'Path:', filePath);
    
    // Get current form data
    const form = document.getElementById('siteSettingsForm');
    const formData = new FormData(form);
    const data = {};
    
    // Convert form data to object
    for (let [key, value] of formData.entries()) {
        // Handle checkboxes
        if (form.elements[key].type === 'checkbox') {
            data[key] = form.elements[key].checked ? '1' : '0';
        } else {
            data[key] = value;
        }
    }
    
    // Ensure the logo path is included
    data[logoType] = filePath;
    
    console.log('Saving data:', data);
    
    // Send data to server
    fetch('setting.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'save_site_settings',
            data: data
        })
    })
    .then(response => response.json())
    .then(data => {
        console.log('Save response:', data);
        if (data.success) {
            showAlert('Logo saved to database successfully!', 'success');
        } else {
            showAlert('Error saving logo to database: ' + (data.error || 'Unknown error'), 'error');
        }
    })
    .catch(error => {
        console.error('Error saving logo:', error);
        showAlert('Error saving logo to database. Please try again.', 'error');
    });
}

// Logo removal function
function removeLogo(logoType) {
    if (confirm('Are you sure you want to remove this logo?')) {
        console.log('Removing logo:', logoType);
        
        // Clear the hidden input
        document.getElementById(logoType + '_input').value = '';
        
        // Reset the image preview
        const previewContainer = document.getElementById(logoType + '_file').closest('.form-group').querySelector('.flex-shrink-0 .w-16.h-16');
        previewContainer.innerHTML = `
            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
        `;
        
        showAlert('Logo removed successfully! Auto-saving to database...', 'success');
        
        // Auto-save to database
        autoSaveLogo(logoType, '');
    }
}
</script>
