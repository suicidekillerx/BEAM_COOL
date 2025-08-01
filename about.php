<?php
require_once 'includes/functions.php';

// Check maintenance mode before rendering the page
checkMaintenanceMode();

// Get all about page content
$aboutContent = getAllAboutContent();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - <?php echo getSiteSetting('brand_name', 'BeamTheTeam'); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="style.css">
    <style>
        /* About page specific styles */
        .fade-in {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .fade-in.visible {
            opacity: 1;
            transform: translateY(0);
        }
        
        .slide-in-left {
            opacity: 0;
            transform: translateX(-50px);
            transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .slide-in-left.visible {
            opacity: 1;
            transform: translateX(0);
        }
        
        .slide-in-right {
            opacity: 0;
            transform: translateX(50px);
            transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .slide-in-right.visible {
            opacity: 1;
            transform: translateX(0);
        }
        
        .scale-in {
            opacity: 0;
            transform: scale(0.8);
            transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .scale-in.visible {
            opacity: 1;
            transform: scale(1);
        }
        
        .rotate-in {
            opacity: 0;
            transform: rotate(-10deg) scale(0.8);
            transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .rotate-in.visible {
            opacity: 1;
            transform: rotate(0deg) scale(1);
        }
        
        .parallax-bg {
            background-attachment: fixed;
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;
        }
        
        .text-gradient {
            background: linear-gradient(135deg, #000000 0%, #333333 50%, #000000 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .border-gradient {
            background: linear-gradient(135deg, #000000 0%, #333333 50%, #000000 100%);
            padding: 2px;
        }
        
        .border-gradient > div {
            background: white;
        }
        
        .hover-lift {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .hover-lift:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }
        
        .stats-counter {
            font-size: 3rem;
            font-weight: 900;
            background: linear-gradient(135deg, #000000 0%, #333333 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .team-member-card {
            position: relative;
            overflow: hidden;
        }
        
        .team-member-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }
        
        .team-member-card:hover::before {
            left: 100%;
        }
        
        .timeline-item {
            position: relative;
        }
        
        .timeline-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            width: 3px;
            height: 100%;
            background: linear-gradient(180deg, #000000 0%, #333333 100%);
            transform: scaleY(0);
            transform-origin: top;
            transition: transform 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .timeline-item.visible::before {
            transform: scaleY(1);
        }
        
        @media (max-width: 768px) {
            .parallax-bg {
                background-attachment: scroll;
            }
            
            .stats-counter {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body class="bg-white text-black overflow-x-hidden">

<?php include 'includes/header.php'; ?>

<!-- Hero Section -->
<section class="relative min-h-screen flex items-center justify-center overflow-hidden">
    <div class="absolute inset-0 bg-black opacity-20"></div>
    <div class="absolute inset-0 parallax-bg" style="background-image: url('<?php echo htmlspecialchars($aboutContent['hero']['background_image'] ?? 'images/hero.webp'); ?>'); filter: blur(8px);"></div>
    
    <div class="relative z-10 text-center px-4 max-w-4xl mx-auto">
        <h1 class="text-6xl md:text-8xl font-black mb-6 fade-in">
            <span class="text-gradient"><?php echo htmlspecialchars($aboutContent['hero']['title_line1'] ?? 'ABOUT'); ?></span>
            <br>
            <span class="text-white"><?php echo htmlspecialchars($aboutContent['hero']['title_line2'] ?? 'BEAM'); ?></span>
        </h1>
        <p class="text-xl md:text-2xl text-white mb-8 fade-in" style="animation-delay: 0.2s;">
            <?php echo htmlspecialchars($aboutContent['hero']['subtitle'] ?? 'Crafting the future of fashion, one thread at a time'); ?>
        </p>
        <div class="fade-in" style="animation-delay: 0.4s;">
            <a href="#story" class="inline-block bg-white text-black px-8 py-4 font-bold uppercase tracking-wider hover:bg-gray-100 transition-all duration-300 hover-lift">
                <?php echo htmlspecialchars($aboutContent['hero']['cta_text'] ?? 'Discover Our Story'); ?>
            </a>
        </div>
    </div>
    
    <!-- Scroll indicator -->
    <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 fade-in" style="animation-delay: 0.6s;">
        <div class="w-6 h-10 border-2 border-white rounded-full flex justify-center">
            <div class="w-1 h-3 bg-white rounded-full mt-2 animate-bounce"></div>
        </div>
    </div>
</section>

<!-- Story Section -->
<section id="story" class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
            <div class="slide-in-left">
                <h2 class="text-5xl md:text-6xl font-black mb-8 text-gradient"><?php echo htmlspecialchars($aboutContent['story']['title'] ?? 'Our Story'); ?></h2>
                <p class="text-lg text-gray-700 mb-6 leading-relaxed">
                    <?php echo htmlspecialchars($aboutContent['story']['paragraph1'] ?? 'Born from a passion for innovation and a commitment to excellence, Beam emerged as a revolutionary force in the fashion industry. We believe that clothing is more than just fabric—it\'s a statement, a lifestyle, and an expression of individuality.'); ?>
                </p>
                <p class="text-lg text-gray-700 mb-8 leading-relaxed">
                    <?php echo htmlspecialchars($aboutContent['story']['paragraph2'] ?? 'Founded in 2020, our journey began with a simple vision: to create clothing that transcends trends and speaks to the soul of the modern individual. Every piece we design carries the weight of our values—quality, sustainability, and timeless elegance.'); ?>
                </p>
                <div class="border-gradient">
                    <div class="p-6">
                        <p class="text-xl font-bold text-center">
                            "<?php echo htmlspecialchars($aboutContent['story']['quote'] ?? 'Fashion is not just about looking good, it\'s about feeling powerful.'); ?>"
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="slide-in-right">
                <div class="relative">
                    <div class="w-full h-96 bg-gray-200 rounded-lg overflow-hidden rotate-in">
                        <img src="<?php echo htmlspecialchars($aboutContent['story']['image'] ?? 'images/collection1.webp'); ?>" alt="Our Story" class="w-full h-full object-cover">
                    </div>
                    <div class="absolute -bottom-6 -right-6 w-48 h-48 bg-black rounded-lg flex items-center justify-center">
                        <div class="text-center text-white">
                            <div class="text-3xl font-black"><?php echo htmlspecialchars($aboutContent['story']['year'] ?? '2020'); ?></div>
                            <div class="text-sm uppercase tracking-wider"><?php echo htmlspecialchars($aboutContent['story']['year_label'] ?? 'Founded'); ?></div>
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
            <h2 class="text-5xl md:text-6xl font-black mb-4 fade-in"><?php echo htmlspecialchars($aboutContent['mission_vision']['title'] ?? 'Mission & Vision'); ?></h2>
            <p class="text-xl text-gray-300 max-w-3xl mx-auto fade-in" style="animation-delay: 0.2s;">
                <?php echo htmlspecialchars($aboutContent['mission_vision']['subtitle'] ?? 'We\'re not just creating clothes—we\'re crafting experiences that empower individuals to express their authentic selves.'); ?>
            </p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
            <div class="scale-in hover-lift">
                <div class="bg-white text-black p-8 rounded-lg h-full">
                    <div class="text-6xl font-black mb-4 text-gradient"><?php echo htmlspecialchars($aboutContent['mission_vision']['mission_number'] ?? '01'); ?></div>
                    <h3 class="text-2xl font-bold mb-4"><?php echo htmlspecialchars($aboutContent['mission_vision']['mission_title'] ?? 'Our Mission'); ?></h3>
                    <p class="text-gray-700 leading-relaxed">
                        <?php echo htmlspecialchars($aboutContent['mission_vision']['mission_content'] ?? 'To revolutionize the fashion industry by creating sustainable, high-quality clothing that empowers individuals to express their unique identity while maintaining the highest standards of craftsmanship and ethical production.'); ?>
                    </p>
                </div>
            </div>
            
            <div class="scale-in hover-lift" style="animation-delay: 0.2s;">
                <div class="bg-white text-black p-8 rounded-lg h-full">
                    <div class="text-6xl font-black mb-4 text-gradient"><?php echo htmlspecialchars($aboutContent['mission_vision']['vision_number'] ?? '02'); ?></div>
                    <h3 class="text-2xl font-bold mb-4"><?php echo htmlspecialchars($aboutContent['mission_vision']['vision_title'] ?? 'Our Vision'); ?></h3>
                    <p class="text-gray-700 leading-relaxed">
                        <?php echo htmlspecialchars($aboutContent['mission_vision']['vision_content'] ?? 'To become the global leader in sustainable fashion, setting new standards for quality, innovation, and social responsibility while inspiring a new generation of conscious consumers.'); ?>
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
            <div class="fade-in">
                <div class="stats-counter mb-2" data-target="<?php echo htmlspecialchars($aboutContent['stats']['countries_number'] ?? '50'); ?>">0</div>
                <div class="text-lg font-semibold text-gray-700"><?php echo htmlspecialchars($aboutContent['stats']['countries_label'] ?? 'Countries'); ?></div>
            </div>
            <div class="fade-in" style="animation-delay: 0.1s;">
                <div class="stats-counter mb-2" data-target="<?php echo htmlspecialchars($aboutContent['stats']['products_number'] ?? '1000'); ?>">0</div>
                <div class="text-lg font-semibold text-gray-700"><?php echo htmlspecialchars($aboutContent['stats']['products_label'] ?? 'Products'); ?></div>
            </div>
            <div class="fade-in" style="animation-delay: 0.2s;">
                <div class="stats-counter mb-2" data-target="<?php echo htmlspecialchars($aboutContent['stats']['customers_number'] ?? '10000'); ?>">0</div>
                <div class="text-lg font-semibold text-gray-700"><?php echo htmlspecialchars($aboutContent['stats']['customers_label'] ?? 'Happy Customers'); ?></div>
            </div>
            <div class="fade-in" style="animation-delay: 0.3s;">
                <div class="stats-counter mb-2" data-target="<?php echo htmlspecialchars($aboutContent['stats']['years_number'] ?? '5'); ?>">0</div>
                <div class="text-lg font-semibold text-gray-700"><?php echo htmlspecialchars($aboutContent['stats']['years_label'] ?? 'Years'); ?></div>
            </div>
        </div>
    </div>
</section>

<!-- Values Section -->
<section class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-5xl md:text-6xl font-black mb-4 fade-in"><?php echo htmlspecialchars($aboutContent['values']['title'] ?? 'Our Values'); ?></h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto fade-in" style="animation-delay: 0.2s;">
                <?php echo htmlspecialchars($aboutContent['values']['subtitle'] ?? 'The principles that guide everything we do'); ?>
            </p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="text-center scale-in hover-lift">
                <div class="w-20 h-20 bg-black rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <?php echo $aboutContent['values']['value1_icon'] ?? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>'; ?>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold mb-4"><?php echo htmlspecialchars($aboutContent['values']['value1_title'] ?? 'Quality'); ?></h3>
                <p class="text-gray-600 leading-relaxed">
                    <?php echo htmlspecialchars($aboutContent['values']['value1_content'] ?? 'We never compromise on quality. Every stitch, every fabric, every detail is carefully selected and crafted to perfection.'); ?>
                </p>
            </div>
            
            <div class="text-center scale-in hover-lift" style="animation-delay: 0.2s;">
                <div class="w-20 h-20 bg-black rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <?php echo $aboutContent['values']['value2_icon'] ?? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>'; ?>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold mb-4"><?php echo htmlspecialchars($aboutContent['values']['value2_title'] ?? 'Sustainability'); ?></h3>
                <p class="text-gray-600 leading-relaxed">
                    <?php echo htmlspecialchars($aboutContent['values']['value2_content'] ?? 'We\'re committed to protecting our planet. Our sustainable practices ensure a better future for generations to come.'); ?>
                </p>
            </div>
            
            <div class="text-center scale-in hover-lift" style="animation-delay: 0.4s;">
                <div class="w-20 h-20 bg-black rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <?php echo $aboutContent['values']['value3_icon'] ?? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>'; ?>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold mb-4"><?php echo htmlspecialchars($aboutContent['values']['value3_title'] ?? 'Innovation'); ?></h3>
                <p class="text-gray-600 leading-relaxed">
                    <?php echo htmlspecialchars($aboutContent['values']['value3_content'] ?? 'We constantly push boundaries, exploring new technologies and creative solutions to redefine fashion.'); ?>
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Timeline Section -->
<section class="py-20 bg-black text-white">
    <div class="max-w-7xl mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-5xl md:text-6xl font-black mb-4 fade-in"><?php echo htmlspecialchars($aboutContent['timeline']['title'] ?? 'Our Journey'); ?></h2>
            <p class="text-xl text-gray-300 max-w-3xl mx-auto fade-in" style="animation-delay: 0.2s;">
                <?php echo htmlspecialchars($aboutContent['timeline']['subtitle'] ?? 'From humble beginnings to global recognition'); ?>
            </p>
        </div>
        
        <div class="space-y-12">
            <?php for ($i = 1; $i <= 5; $i++): ?>
            <div class="timeline-item pl-8 fade-in" style="animation-delay: <?php echo ($i - 1) * 0.2; ?>s;">
                <div class="bg-white text-black p-6 rounded-lg">
                    <div class="text-2xl font-black mb-2"><?php echo htmlspecialchars($aboutContent['timeline']['year' . $i] ?? '202' . ($i - 1)); ?></div>
                    <h3 class="text-xl font-bold mb-2"><?php echo htmlspecialchars($aboutContent['timeline']['year' . $i . '_title'] ?? 'Timeline Item ' . $i); ?></h3>
                    <p class="text-gray-700"><?php echo htmlspecialchars($aboutContent['timeline']['year' . $i . '_content'] ?? 'Timeline content for year ' . $i); ?></p>
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
            <h2 class="text-5xl md:text-6xl font-black mb-4 fade-in"><?php echo htmlspecialchars($aboutContent['team']['title'] ?? 'Meet Our Team'); ?></h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto fade-in" style="animation-delay: 0.2s;">
                <?php echo htmlspecialchars($aboutContent['team']['subtitle'] ?? 'The passionate individuals behind our success'); ?>
            </p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php for ($i = 1; $i <= 3; $i++): ?>
            <div class="team-member-card scale-in hover-lift" style="animation-delay: <?php echo ($i - 1) * 0.2; ?>s;">
                <div class="bg-gray-200 rounded-lg overflow-hidden mb-4">
                    <img src="<?php echo htmlspecialchars($aboutContent['team']['member' . $i . '_image'] ?? 'images/collection' . ($i + 1) . '.webp'); ?>" alt="Team Member" class="w-full h-64 object-cover">
                </div>
                <h3 class="text-xl font-bold mb-2"><?php echo htmlspecialchars($aboutContent['team']['member' . $i . '_name'] ?? 'Team Member ' . $i); ?></h3>
                <p class="text-gray-600 mb-2"><?php echo htmlspecialchars($aboutContent['team']['member' . $i . '_position'] ?? 'Position ' . $i); ?></p>
                <p class="text-sm text-gray-500"><?php echo htmlspecialchars($aboutContent['team']['member' . $i . '_description'] ?? 'Description for team member ' . $i); ?></p>
            </div>
            <?php endfor; ?>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-20 bg-black text-white">
    <div class="max-w-4xl mx-auto text-center px-4">
        <h2 class="text-5xl md:text-6xl font-black mb-6 fade-in"><?php echo htmlspecialchars($aboutContent['cta']['title'] ?? 'Join Our Journey'); ?></h2>
        <p class="text-xl text-gray-300 mb-8 fade-in" style="animation-delay: 0.2s;">
            <?php echo htmlspecialchars($aboutContent['cta']['subtitle'] ?? 'Be part of the revolution. Discover our collections and experience the future of fashion.'); ?>
        </p>
        <div class="fade-in" style="animation-delay: 0.4s;">
            <a href="<?php echo htmlspecialchars($aboutContent['cta']['button1_url'] ?? 'shop.php'); ?>" class="inline-block bg-white text-black px-8 py-4 font-bold uppercase tracking-wider hover:bg-gray-100 transition-all duration-300 hover-lift mr-4">
                <?php echo htmlspecialchars($aboutContent['cta']['button1_text'] ?? 'Shop Now'); ?>
            </a>
            <a href="<?php echo htmlspecialchars($aboutContent['cta']['button2_url'] ?? 'collections.php'); ?>" class="inline-block border-2 border-white text-white px-8 py-4 font-bold uppercase tracking-wider hover:bg-white hover:text-black transition-all duration-300 hover-lift">
                <?php echo htmlspecialchars($aboutContent['cta']['button2_text'] ?? 'View Collections'); ?>
            </a>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>

<script>
// Intersection Observer for animations
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('visible');
        }
    });
}, observerOptions);

// Observe all animated elements
document.addEventListener('DOMContentLoaded', () => {
    const animatedElements = document.querySelectorAll('.fade-in, .slide-in-left, .slide-in-right, .scale-in, .rotate-in, .timeline-item');
    animatedElements.forEach(el => observer.observe(el));
    
    // Animate stats counters
    const counters = document.querySelectorAll('.stats-counter');
    counters.forEach(counter => {
        const target = parseInt(counter.getAttribute('data-target'));
        const duration = 2000; // 2 seconds
        const increment = target / (duration / 16); // 60fps
        let current = 0;
        
        const updateCounter = () => {
            current += increment;
            if (current < target) {
                counter.textContent = Math.floor(current);
                requestAnimationFrame(updateCounter);
            } else {
                counter.textContent = target;
            }
        };
        
        // Start animation when counter is visible
        const counterObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    updateCounter();
                    counterObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });
        
        counterObserver.observe(counter);
    });
});

// Smooth scrolling for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Parallax effect for hero section
window.addEventListener('scroll', () => {
    const scrolled = window.pageYOffset;
    const parallax = document.querySelector('.parallax-bg');
    if (parallax) {
        const speed = scrolled * 0.5;
        parallax.style.transform = `translateY(${speed}px)`;
    }
});
</script>

</body>
</html> </body>
</html> 

