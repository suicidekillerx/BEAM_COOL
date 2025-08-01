<?php
require_once __DIR__.'/../includes/functions.php';
// require_admin_auth();  // Temporarily disabled

$aboutContent = getAllAboutContent(true); // Get editable content
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - <?php echo getSiteSetting('brand_name', 'BeamTheTeam'); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="admin.css">
    <script src="../script.js" defer></script>
    <script>
    // API endpoint for saving content
    const apiEndpoint = 'save_about.php';
    
    // Function to show status messages
    function showStatus(message, bgClass) {
        const statusDiv = document.getElementById('saveStatus');
        if (!statusDiv) return;
        
        statusDiv.className = `save-status ${bgClass}`;
        statusDiv.textContent = message;
        statusDiv.style.display = 'block';
        
        // Hide after 3 seconds
        setTimeout(() => {
            statusDiv.style.display = 'none';
        }, 3000);
    }
    
    // Function to save all changes
    window.saveAllChanges = function() {
        // Get all editable elements
        const editableElements = document.querySelectorAll('[contenteditable="true"]');
        let savedCount = 0;
        let errorCount = 0;
        
        // Show saving status
        showStatus('Saving all changes...', 'bg-blue-500');
        
        // Create promises for all save operations
        const savePromises = Array.from(editableElements).map(element => {
            const contentKey = element.dataset.contentKey;
            const newContent = element.innerHTML;
            
            if (!contentKey) {
                console.error('No data-content-key attribute found on element');
                errorCount++;
                return Promise.resolve();
            }
            
            return fetch(apiEndpoint, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    key: contentKey,
                    value: newContent
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    savedCount++;
                } else {
                    throw new Error(data.message || 'Failed to save content');
                }
            })
            .catch(error => {
                console.error('Error saving content:', error);
                errorCount++;
            });
        });
        
        // Wait for all save operations to complete
        Promise.all(savePromises)
            .then(() => {
                if (errorCount === 0) {
                    showStatus(`All changes saved successfully (${savedCount} items)`, 'bg-green-500');
                } else {
                    showStatus(`Saved ${savedCount} items, ${errorCount} errors`, 'bg-yellow-500');
                }
            })
            .catch((error) => {
                console.error('Error in save operation:', error);
                showStatus('Error saving changes', 'bg-red-500');
            });
    };
    
    // Initialize event listeners when DOM is loaded
    document.addEventListener('DOMContentLoaded', function() {
        // Save on blur (when user finishes editing)
        document.querySelectorAll('[contenteditable="true"]').forEach(element => {
            element.addEventListener('blur', function() {
                const contentKey = this.dataset.contentKey;
                const newContent = this.innerHTML;
                
                if (!contentKey) {
                    console.error('No data-content-key attribute found on element');
                    return;
                }
                
                showStatus('Saving...', 'bg-blue-500');
                
                fetch(apiEndpoint, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        key: contentKey,
                        value: newContent
                    })
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        showStatus('Changes saved successfully', 'bg-green-500');
                    } else {
                        throw new Error(data.message || 'Failed to save changes');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showStatus('Error: ' + error.message, 'bg-red-500');
                });
            });
        });
        
        // Sidebar toggle functionality
        const sidebarToggle = document.getElementById('sidebar-toggle');
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        
        if (sidebarToggle && sidebar && sidebarOverlay) {
            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('open');
                sidebarOverlay.classList.toggle('hidden');
            });
            
            sidebarOverlay.addEventListener('click', function() {
                sidebar.classList.remove('open');
                sidebarOverlay.classList.add('hidden');
            });
        }
    });
    </script>
    <style>
        [contenteditable="true"] {
            outline: 2px dashed rgba(59, 130, 246, 0.3);
            transition: all 0.2s;
            min-height: 40px;
            padding: 8px;
        }

        [contenteditable="true"]:hover {
            outline-color: rgba(59, 130, 246, 0.6);
            background: rgba(59, 130, 246, 0.05);
        }

        [contenteditable="true"]:focus {
            outline-color: rgba(59, 130, 246, 1);
            background: white;
            outline-style: solid;
        }

        .edit-toolbar {
            position: fixed;
            top: 20px;
            right: 20px;
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }

        .save-status {
            position: fixed;
            bottom: 20px;
            left: 20px;
            padding: 10px 20px;
            border-radius: 5px;
            display: none;
        }
        
        /* Mobile responsive styles */
        @media (max-width: 1023px) {
            .admin-sidebar {
                position: fixed;
                top: 0;
                left: -100%;
                width: 280px;
                height: 100vh;
                z-index: 50;
                transition: left 0.3s ease;
            }
            
            .admin-sidebar.open {
                left: 0;
            }
            
            .sidebar-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 40;
                opacity: 0;
                visibility: hidden;
                transition: all 0.3s ease;
            }
            
            .sidebar-overlay.open {
                opacity: 1;
                visibility: visible;
            }
        }
    </style>
</head>
<body class="bg-gray-50 font-['Inter']">
    <!-- Mobile Sidebar Overlay -->
    <div id="sidebarOverlay" class="sidebar-overlay fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden hidden"></div>
    
    <div class="flex h-screen">
        <!-- Include Sidebar -->
        <?php 
        $currentPage = 'about-us';
        include 'includes/sidebar.php'; 
        ?>
        
        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Include Header -->
            <?php 
            $pageTitle = 'About Us';
            include 'includes/header.php'; 
            ?>
            
            <!-- Content Area -->
            <main class="content-area flex-1 overflow-y-auto bg-white">

<!-- Editable Hero Section -->
<section class="relative min-h-screen flex items-center justify-center overflow-hidden">
    <div class="absolute inset-0 bg-black opacity-20"></div>
    <div class="absolute inset-0 parallax-bg"
         style="background-image: url('<?= isset($aboutContent['hero']['background_image']) ? '../' . htmlspecialchars($aboutContent['hero']['background_image']) : '../images/hero.webp' ?>');"
         data-content-key="hero.background_image"
         contenteditable="false">
        <div class="absolute top-4 left-4 z-20">
            <form class="inline-block" enctype="multipart/form-data" data-content-key="hero.background_image">
                <input type="file" accept="image/*" name="image" style="display:none" />
                <button type="button" class="bg-white text-black px-2 py-1 rounded shadow">Edit Image</button>
            </form>
        </div>
    </div>
    
    <div class="relative z-10 text-center px-4 max-w-4xl mx-auto">
        <h1 class="text-6xl md:text-8xl font-black mb-6">
            <span class="text-gradient" 
                  data-content-key="hero.title_line1"
                  contenteditable="true">
                <?= htmlspecialchars($aboutContent['hero']['title_line1'] ?? 'ABOUT') ?>
            </span>
            <br>
            <span class="text-white"
                  data-content-key="hero.title_line2"
                  contenteditable="true">
                <?= htmlspecialchars($aboutContent['hero']['title_line2'] ?? 'BEAM') ?>
            </span>
        </h1>
        <p class="text-xl md:text-2xl text-white mb-8"
           data-content-key="hero.subtitle"
           contenteditable="true">
            <?= htmlspecialchars($aboutContent['hero']['subtitle'] ?? 'Crafting the future of fashion, one thread at a time') ?>
        </p>
        <div>
            <div class="inline-block bg-white text-black px-8 py-4 font-bold uppercase tracking-wider hover:bg-gray-100 transition-all duration-300 hover-lift">
                <span data-content-key="hero.cta_text"
                      contenteditable="true">
                    <?= htmlspecialchars($aboutContent['hero']['cta_text'] ?? 'Discover Our Story') ?>
                </span>
            </div>
        </div>
    </div>
    
    <!-- Scroll indicator -->
    <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2">
        <div class="w-6 h-10 border-2 border-white rounded-full flex justify-center">
            <div class="w-1 h-3 bg-white rounded-full mt-2 animate-bounce"></div>
        </div>
    </div>
</section>

<!-- Story Section -->
<section id="story" class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
            <div>
                <h2 class="text-5xl md:text-6xl font-black mb-8 text-gradient"
                   data-content-key="story.title"
                   contenteditable="true">
                    <?= htmlspecialchars($aboutContent['story']['title'] ?? 'Our Story') ?>
                </h2>
                <p class="text-lg text-gray-700 mb-6 leading-relaxed"
                   data-content-key="story.paragraph1"
                   contenteditable="true">
                    <?= htmlspecialchars($aboutContent['story']['paragraph1'] ?? 'Born from a passion for innovation and a commitment to excellence, Beam emerged as a revolutionary force in the fashion industry. We believe that clothing is more than just fabric—it\'s a statement, a lifestyle, and an expression of individuality.') ?>
                </p>
                <p class="text-lg text-gray-700 mb-8 leading-relaxed"
                   data-content-key="story.paragraph2"
                   contenteditable="true">
                    <?= htmlspecialchars($aboutContent['story']['paragraph2'] ?? 'Founded in 2020, our journey began with a simple vision: to create clothing that transcends trends and speaks to the soul of the modern individual. Every piece we design carries the weight of our values—quality, sustainability, and timeless elegance.') ?>
                </p>
                <div class="border-gradient">
                    <div class="p-6">
                        <p class="text-xl font-bold text-center"
                           data-content-key="story.quote"
                           contenteditable="true">
                            "<?= htmlspecialchars($aboutContent['story']['quote'] ?? 'Fashion is not just about looking good, it\'s about feeling powerful.') ?>"
                        </p>
                    </div>
                </div>
            </div>
            
            <div>
                <div class="relative">
                    <div class="w-full h-96 bg-gray-200 rounded-lg overflow-hidden">
                        <img src="<?= '../' . htmlspecialchars($aboutContent['story']['image'] ?? '../images/collection1.webp') ?>" alt="Our Story" class="w-full h-full object-cover"
                             data-content-key="story.image"
                             contenteditable="false">
                        <div class="absolute top-2 left-2 z-20">
                            <form class="inline-block" enctype="multipart/form-data" data-content-key="story.image">
                                <input type="file" accept="image/*" name="image" style="display:none" />
                                <button type="button" class="bg-white text-black px-2 py-1 rounded shadow">Edit Image</button>
                            </form>
                        </div>
                    </div>
                    <div class="absolute -bottom-6 -right-6 w-48 h-48 bg-black rounded-lg flex items-center justify-center">
                        <div class="text-center text-white">
                            <div class="text-3xl font-black"
                                 data-content-key="story.year"
                                 contenteditable="true">
                                <?= htmlspecialchars($aboutContent['story']['year'] ?? '2020') ?>
                            </div>
                            <div class="text-sm uppercase tracking-wider"
                                 data-content-key="story.year_label"
                                 contenteditable="true">
                                <?= htmlspecialchars($aboutContent['story']['year_label'] ?? 'Founded') ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Mission & Vision Section -->
<section class="py-20 bg-black text-white">
    <div class="max-w-7xl mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-5xl md:text-6xl font-black mb-4"
                data-content-key="mission_vision.title"
                contenteditable="true">
                <?= htmlspecialchars($aboutContent['mission_vision']['title'] ?? 'Mission & Vision') ?>
            </h2>
            <p class="text-xl text-gray-300 max-w-3xl mx-auto"
               data-content-key="mission_vision.subtitle"
               contenteditable="true">
                <?= htmlspecialchars($aboutContent['mission_vision']['subtitle'] ?? 'We\'re not just creating clothes—we\'re crafting experiences that empower individuals to express their authentic selves.') ?>
            </p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
            <div class="hover-lift">
                <div class="bg-white text-black p-8 rounded-lg h-full">
                    <div class="text-6xl font-black mb-4 text-gradient"
                         data-content-key="mission_vision.mission_number"
                         contenteditable="true">
                        <?= htmlspecialchars($aboutContent['mission_vision']['mission_number'] ?? '01') ?>
                    </div>
                    <h3 class="text-2xl font-bold mb-4"
                        data-content-key="mission_vision.mission_title"
                        contenteditable="true">
                        <?= htmlspecialchars($aboutContent['mission_vision']['mission_title'] ?? 'Our Mission') ?>
                    </h3>
                    <p class="text-gray-700 leading-relaxed"
                       data-content-key="mission_vision.mission_content"
                       contenteditable="true">
                        <?= htmlspecialchars($aboutContent['mission_vision']['mission_content'] ?? 'To revolutionize the fashion industry by creating sustainable, high-quality clothing that empowers individuals to express their unique identity while maintaining the highest standards of craftsmanship and ethical production.') ?>
                    </p>
                </div>
            </div>
            
            <div class="hover-lift">
                <div class="bg-white text-black p-8 rounded-lg h-full">
                    <div class="text-6xl font-black mb-4 text-gradient"
                         data-content-key="mission_vision.vision_number"
                         contenteditable="true">
                        <?= htmlspecialchars($aboutContent['mission_vision']['vision_number'] ?? '02') ?>
                    </div>
                    <h3 class="text-2xl font-bold mb-4"
                        data-content-key="mission_vision.vision_title"
                        contenteditable="true">
                        <?= htmlspecialchars($aboutContent['mission_vision']['vision_title'] ?? 'Our Vision') ?>
                    </h3>
                    <p class="text-gray-700 leading-relaxed"
                       data-content-key="mission_vision.vision_content"
                       contenteditable="true">
                        <?= htmlspecialchars($aboutContent['mission_vision']['vision_content'] ?? 'To become the global leader in sustainable fashion, setting new standards for quality, innovation, and social responsibility while inspiring a new generation of conscious consumers.') ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
            <div>
                <div class="stats-counter mb-2"
                     data-content-key="stats.countries_number"
                     contenteditable="true">
                    <?= htmlspecialchars($aboutContent['stats']['countries_number'] ?? '50') ?>
                </div>
                <div class="text-lg font-semibold text-gray-700"
                     data-content-key="stats.countries_label"
                     contenteditable="true">
                    <?= htmlspecialchars($aboutContent['stats']['countries_label'] ?? 'Countries') ?>
                </div>
            </div>
            <div>
                <div class="stats-counter mb-2"
                     data-content-key="stats.products_number"
                     contenteditable="true">
                    <?= htmlspecialchars($aboutContent['stats']['products_number'] ?? '1000') ?>
                </div>
                <div class="text-lg font-semibold text-gray-700"
                     data-content-key="stats.products_label"
                     contenteditable="true">
                    <?= htmlspecialchars($aboutContent['stats']['products_label'] ?? 'Products') ?>
                </div>
            </div>
            <div>
                <div class="stats-counter mb-2"
                     data-content-key="stats.customers_number"
                     contenteditable="true">
                    <?= htmlspecialchars($aboutContent['stats']['customers_number'] ?? '10000') ?>
                </div>
                <div class="text-lg font-semibold text-gray-700"
                     data-content-key="stats.customers_label"
                     contenteditable="true">
                    <?= htmlspecialchars($aboutContent['stats']['customers_label'] ?? 'Happy Customers') ?>
                </div>
            </div>
            <div>
                <div class="stats-counter mb-2"
                     data-content-key="stats.years_number"
                     contenteditable="true">
                    <?= htmlspecialchars($aboutContent['stats']['years_number'] ?? '5') ?>
                </div>
                <div class="text-lg font-semibold text-gray-700"
                     data-content-key="stats.years_label"
                     contenteditable="true">
                    <?= htmlspecialchars($aboutContent['stats']['years_label'] ?? 'Years') ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Values Section -->
<section class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-5xl md:text-6xl font-black mb-4"
                data-content-key="values.title"
                contenteditable="true">
                <?= htmlspecialchars($aboutContent['values']['title'] ?? 'Our Values') ?>
            </h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto"
               data-content-key="values.subtitle"
               contenteditable="true">
                <?= htmlspecialchars($aboutContent['values']['subtitle'] ?? 'These core principles guide everything we do, from design to delivery.') ?>
            </p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <?php for ($i = 1; $i <= 3; $i++): ?>
            <div class="text-center scale-in hover-lift">
                <div class="w-20 h-20 bg-black rounded-full flex items-center justify-center mx-auto mb-6 relative">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <?= $aboutContent['values']['value'.$i.'_icon'] ?? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>'; ?>
                    </svg>
                    <button type="button" class="absolute bottom-0 right-0 bg-white text-black px-1 py-0.5 rounded shadow text-xs icon-picker-btn" data-value-index="<?= $i ?>">Edit Icon</button>
                </div>
                <h3 class="text-2xl font-bold mb-4" data-content-key="values.value<?= $i; ?>_title" contenteditable="true">
                    <?= htmlspecialchars($aboutContent['values']['value'.$i.'_title'] ?? 'Value Title') ?>
                </h3>
                <p class="text-gray-600 leading-relaxed" data-content-key="values.value<?= $i; ?>_content" contenteditable="true">
                    <?= htmlspecialchars($aboutContent['values']['value'.$i.'_content'] ?? 'Value content goes here.') ?>
                </p>
            </div>
            <?php endfor; ?>
        </div>
    </div>
</section>

<!-- Timeline Section -->
<section class="py-20 bg-black text-white">
    <div class="max-w-7xl mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-5xl md:text-6xl font-black mb-4" data-content-key="timeline.title" contenteditable="true">
                <?= htmlspecialchars($aboutContent['timeline']['title'] ?? 'Our Journey') ?>
            </h2>
            <p class="text-xl text-gray-300 max-w-3xl mx-auto" data-content-key="timeline.subtitle" contenteditable="true">
                <?= htmlspecialchars($aboutContent['timeline']['subtitle'] ?? 'From humble beginnings to global recognition') ?>
            </p>
        </div>
        <div class="space-y-12">
            <?php for ($i = 1; $i <= 5; $i++): ?>
            <div class="timeline-item pl-8 fade-in">
                <div class="bg-white text-black p-6 rounded-lg">
                    <div class="text-2xl font-black mb-2" data-content-key="timeline.year<?= $i ?>" contenteditable="true">
                        <?= htmlspecialchars($aboutContent['timeline']['year'.$i] ?? '202'.($i-1)) ?>
                    </div>
                    <h3 class="text-xl font-bold mb-2" data-content-key="timeline.year<?= $i ?>_title" contenteditable="true">
                        <?= htmlspecialchars($aboutContent['timeline']['year'.$i.'_title'] ?? 'Timeline Item '.$i) ?>
                    </h3>
                    <p class="text-gray-700" data-content-key="timeline.year<?= $i ?>_content" contenteditable="true">
                        <?= htmlspecialchars($aboutContent['timeline']['year'.$i.'_content'] ?? 'Timeline content for year '.$i) ?>
                    </p>
                </div>
            </div>
            <?php endfor; ?>
        </div>
    </div>
</section>

<!-- Team Section -->
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-5xl md:text-6xl font-black mb-4" data-content-key="team.title" contenteditable="true">
                <?= htmlspecialchars($aboutContent['team']['title'] ?? 'Meet Our Team') ?>
            </h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto" data-content-key="team.subtitle" contenteditable="true">
                <?= htmlspecialchars($aboutContent['team']['subtitle'] ?? 'The passionate individuals behind our success') ?>
            </p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php for ($i = 1; $i <= 3; $i++): ?>
            <div class="team-member-card scale-in hover-lift">
                <div class="bg-gray-200 rounded-lg overflow-hidden mb-4 relative">
                    <img src="<?= '../' . htmlspecialchars($aboutContent['team']['member'.$i.'_image'] ?? '../images/collection'.($i+1).'.webp') ?>" alt="Team Member" class="w-full h-64 object-cover" data-content-key="team.member<?= $i ?>_image" contenteditable="false">
                    <form class="absolute top-2 left-2" enctype="multipart/form-data" data-content-key="team.member<?= $i ?>_image">
                        <input type="file" accept="image/*" name="image" style="display:none" />
                        <button type="button" class="bg-white text-black px-2 py-1 rounded shadow">Edit Image</button>
                    </form>
                </div>
                <h3 class="text-xl font-bold mb-2" data-content-key="team.member<?= $i ?>_name" contenteditable="true">
                    <?= htmlspecialchars($aboutContent['team']['member'.$i.'_name'] ?? 'Team Member '.$i) ?>
                </h3>
                <p class="text-gray-600 mb-2" data-content-key="team.member<?= $i ?>_position" contenteditable="true">
                    <?= htmlspecialchars($aboutContent['team']['member'.$i.'_position'] ?? 'Position '.$i) ?>
                </p>
                <p class="text-sm text-gray-500" data-content-key="team.member<?= $i ?>_description" contenteditable="true">
                    <?= htmlspecialchars($aboutContent['team']['member'.$i.'_description'] ?? 'Description for team member '.$i) ?>
                </p>
            </div>
            <?php endfor; ?>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-20 bg-black text-white">
    <div class="max-w-4xl mx-auto text-center px-4">
        <h2 class="text-5xl md:text-6xl font-black mb-6" data-content-key="cta.title" contenteditable="true">
            <?= htmlspecialchars($aboutContent['cta']['title'] ?? 'Join Our Journey') ?>
        </h2>
        <p class="text-xl text-gray-300 mb-8" data-content-key="cta.subtitle" contenteditable="true">
            <?= htmlspecialchars($aboutContent['cta']['subtitle'] ?? 'Be part of the revolution. Discover our collections and experience the future of fashion.') ?>
        </p>
        <div>
            <a href="#" class="inline-block bg-white text-black px-8 py-4 font-bold uppercase tracking-wider hover:bg-gray-100 transition-all duration-300 hover-lift mr-4" data-content-key="cta.button1_url" contenteditable="true">
                <?= htmlspecialchars($aboutContent['cta']['button1_text'] ?? 'Shop Now') ?>
            </a>
            <a href="#" class="inline-block border-2 border-white text-white px-8 py-4 font-bold uppercase tracking-wider hover:bg-white hover:text-black transition-all duration-300 hover-lift" data-content-key="cta.button2_url" contenteditable="true">
                <?= htmlspecialchars($aboutContent['cta']['button2_text'] ?? 'View Collections') ?>
            </a>
        </div>
    </div>
</section>

<!-- Icon Picker Modal -->
<div id="iconPickerModal" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 hidden">
    <div class="bg-white p-6 rounded-lg max-w-md w-full">
        <h3 class="text-lg font-bold mb-4">Select an Icon</h3>
        <div class="grid grid-cols-4 gap-4 mb-4">
            <button class="icon-option" data-icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>'><svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></button>
            <button class="icon-option" data-icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>'><svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg></button>
            <button class="icon-option" data-icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>'><svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg></button>
            <button class="icon-option" data-icon='<circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none" /><path d="M8 12l2 2 4-4" stroke="currentColor" stroke-width="2" fill="none" />'><svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none" /><path d="M8 12l2 2 4-4" stroke="currentColor" stroke-width="2" fill="none" /></svg></button>
            <button class="icon-option" data-icon='<rect x="4" y="4" width="16" height="16" rx="4" stroke="currentColor" stroke-width="2" fill="none" /><path d="M8 12l2 2 4-4" stroke="currentColor" stroke-width="2" fill="none" />'><svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="4" y="4" width="16" height="16" rx="4" stroke="currentColor" stroke-width="2" fill="none" /><path d="M8 12l2 2 4-4" stroke="currentColor" stroke-width="2" fill="none" /></svg></button>
            <button class="icon-option" data-icon='<path d="M12 4v16m8-8H4" stroke="currentColor" stroke-width="2" fill="none" />'><svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4" stroke="currentColor" stroke-width="2" fill="none" /></svg></button>
            <button class="icon-option" data-icon='<circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none" /><path d="M12 8v4l3 3" stroke="currentColor" stroke-width="2" fill="none" />'><svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none" /><path d="M12 8v4l3 3" stroke="currentColor" stroke-width="2" fill="none" /></svg></button>
            <button class="icon-option" data-icon='<rect x="4" y="4" width="16" height="16" rx="4" stroke="currentColor" stroke-width="2" fill="none" /><path d="M16 8l-4 4-2-2" stroke="currentColor" stroke-width="2" fill="none" />'><svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="4" y="4" width="16" height="16" rx="4" stroke="currentColor" stroke-width="2" fill="none" /><path d="M16 8l-4 4-2-2" stroke="currentColor" stroke-width="2" fill="none" /></svg></button>
            <button class="icon-option" data-icon='<circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none" /><path d="M9 9h6v6H9z" stroke="currentColor" stroke-width="2" fill="none" />'><svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none" /><path d="M9 9h6v6H9z" stroke="currentColor" stroke-width="2" fill="none" /></svg></button>
            <button class="icon-option" data-icon='<path d="M12 2a10 10 0 100 20 10 10 0 000-20zm1 14.59L16.59 13 18 14.41l-5 5-5-5L7.41 13 11 16.59V7h2v9.59z"/>'><svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 2a10 10 0 100 20 10 10 0 000-20zm1 14.59L16.59 13 18 14.41l-5 5-5-5L7.41 13 11 16.59V7h2v9.59z"/></svg></button>
            <button class="icon-option" data-icon='<path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-13h2v6h-2zm0 8h2v2h-2z"/>'><svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-13h2v6h-2zm0 8h2v2h-2z"/>'><svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg></button>
            <button class="icon-option" data-icon='<path d="M19 21H5a2 2 0 01-2-2V7a2 2 0 012-2h4l2-2 2 2h4a2 2 0 012 2v12a2 2 0 01-2 2z"/>'><svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 01-2-2V7a2 2 0 012-2h4l2-2 2 2h4a2 2 0 012 2v12a2 2 0 01-2 2z"/></svg></button>
            <button class="icon-option" data-icon='<path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>'><svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg></button>
            <button class="icon-option" data-icon='<path d="M12 2l4 8h-8l4-8zm0 20l-4-8h8l-4 8z"/>'><svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 2l4 8h-8l4-8zm0 20l-4-8h8l-4 8z"/></svg></button>
            <button class="icon-option" data-icon='<path d="M2 12h20M12 2v20" stroke="currentColor" stroke-width="2" fill="none" />'><svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M2 12h20M12 2v20" stroke="currentColor" stroke-width="2" fill="none" /></svg></button>
            <button class="icon-option" data-icon='<circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none" /><path d="M15 9l-6 6" stroke="currentColor" stroke-width="2" fill="none" />'><svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none" /><path d="M15 9l-6 6" stroke="currentColor" stroke-width="2" fill="none" /></svg></button>
            <button class="icon-option" data-icon='<rect x="4" y="4" width="16" height="16" rx="4" stroke="currentColor" stroke-width="2" fill="none" /><path d="M8 8h8v8H8z" stroke="currentColor" stroke-width="2" fill="none" />'><svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="4" y="4" width="16" height="16" rx="4" stroke="currentColor" stroke-width="2" fill="none" /><path d="M8 8h8v8H8z" stroke="currentColor" stroke-width="2" fill="none" /></svg></button>
            <button class="icon-option" data-icon='<path d="M12 2a10 10 0 100 20 10 10 0 000-20zm0 18a8 8 0 110-16 8 8 0 010 16zm-1-13h2v6h-2zm0 8h2v2h-2z"/>'><svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 2a10 10 0 100 20 10 10 0 000-20zm0 18a8 8 0 110-16 8 8 0 010 16zm-1-13h2v6h-2zm0 8h2v2h-2z"/></svg></button>
        </div>
        <div class="flex justify-end">
            <button id="closeIconPicker" class="px-4 py-2 bg-gray-200 rounded">Cancel</button>
        </div>
    </div>
</div>

<script>
function uploadImage(fileInput, contentKey, imgEl) {
    const file = fileInput.files[0];
    if (!file) return;
    const formData = new FormData();
    formData.append('image', file);
    formData.append('key', contentKey);
    fetch('upload_image.php', {
        method: 'POST',
        body: formData,
        headers: {'X-Requested-With': 'XMLHttpRequest'}
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (imgEl) imgEl.src = '../' + data.filePath;
        } else {
            alert(data.message || 'Image upload failed');
        }
    })
    .catch(() => alert('Image upload failed'));
}

document.addEventListener('DOMContentLoaded', function() {
    // Image upload
    document.querySelectorAll('form[enctype="multipart/form-data"]').forEach(form => {
        const fileInput = form.querySelector('input[type="file"]');
        const button = form.querySelector('button[type="button"]');
        const contentKey = form.dataset.contentKey;
        let imgEl = null;
        if (form.parentElement.querySelector('img[data-content-key]')) {
            imgEl = form.parentElement.querySelector('img[data-content-key]');
        }
        if (fileInput && button && contentKey) {
            button.addEventListener('click', function() {
                fileInput.click();
            });
            fileInput.addEventListener('change', function() {
                uploadImage(fileInput, contentKey, imgEl);
            });
        }
    });

    // Icon picker
    let currentValueIndex = null;
    document.querySelectorAll('.icon-picker-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            currentValueIndex = btn.dataset.valueIndex;
            document.getElementById('iconPickerModal').classList.remove('hidden');
        });
    });
    document.getElementById('closeIconPicker').addEventListener('click', function() {
        document.getElementById('iconPickerModal').classList.add('hidden');
    });
    document.querySelectorAll('.icon-option').forEach(option => {
        option.addEventListener('click', function() {
            if (!currentValueIndex) return;
            const iconSvg = option.dataset.icon;
            const key = `values.value${currentValueIndex}_icon`;
            fetch('save_about.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ key, value: iconSvg })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update the icon in the UI
                    const svg = document.querySelector(`button[data-value-index="${currentValueIndex}"]`).parentElement.querySelector('svg');
                    svg.innerHTML = iconSvg;
                    document.getElementById('iconPickerModal').classList.add('hidden');
                } else {
                    alert(data.message || 'Failed to update icon');
                }
            });
        });
    });
});
</script>
    </main>
    </div>
</div>
    
<script>
// Mobile sidebar functionality (copied from index.php)
document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('open');
            sidebarOverlay.classList.toggle('open');
            sidebarOverlay.classList.toggle('hidden');
        });
    }
    
    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', function() {
            sidebar.classList.remove('open');
            sidebarOverlay.classList.remove('open');
            sidebarOverlay.classList.add('hidden');
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
                sidebarOverlay.classList.add('hidden');
            }
        }
    });
});
</script>
</body>
</html>