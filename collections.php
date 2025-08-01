<?php
require_once 'includes/functions.php';

// Check maintenance mode before rendering the page
checkMaintenanceMode();

$collections = getCollections();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Collections - <?php echo getSiteSetting('brand_name', 'BeamTheTeam™'); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Removed Magnific Popup CSS -->
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-gray-100 text-gray-900">

<?php require_once 'includes/header.php'; ?>
     
    <!-- Main Content: Collections Gallery -->
    <main class="py-12">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-2">OUR COLLECTIONS</h1>
            <p class="text-gray-600 mb-10">Explore our latest drops and signature looks.</p>
        </div>
        <!-- Collections Filter/Navigation -->
     <section class="bg-white py-4 md:py-6 px-4 md:px-8 lg:px-12 border-t border-gray-200">
        <div class="flex flex-wrap justify-center sm:justify-start gap-x-3 md:gap-x-6 gap-y-2 overflow-x-auto no-scrollbar">
            <a href="shop.php" class="text-xs md:text-sm font-medium text-gray-700 hover:text-black whitespace-nowrap rounded-md p-1 md:p-2">VIEW ALL</a>
            <?php foreach ($collections as $index => $collection): ?>
                <a href="shop.php?collection=<?php echo $collection['id']; ?>" class="text-xs md:text-sm font-medium text-gray-700 hover:text-black whitespace-nowrap rounded-md p-1 md:p-2 <?php echo $index >= 4 ? 'hidden sm:block' : ''; ?> <?php echo $index >= 6 ? 'hidden md:block' : ''; ?> <?php echo $index >= 8 ? 'hidden lg:block' : ''; ?> <?php echo $index >= 10 ? 'hidden xl:block' : ''; ?>"><?php echo $collection['name']; ?></a>
            <?php endforeach; ?>
        </div>
    </section>
        <div class="scrolling-gallery-container collections-gallery">
            <?php
            // Distribute collections evenly across 3 columns
            $columns = [[], [], []];
            foreach ($collections as $i => $collection) {
                $columns[$i % 3][] = $collection;
            }
            $repeat = 5; // Number of times to repeat

            // Helper to shuffle and avoid consecutive duplicates
            function shuffle_no_consecutive($arr, $lastId = null) {
                if (count($arr) < 2) return $arr;
                $shuffled = $arr;
                do {
                    shuffle($shuffled);
                } while ($lastId !== null && $shuffled[0]['id'] === $lastId && count(array_unique(array_column($shuffled, 'id'))) > 1);
                return $shuffled;
            }
            ?>
            <!-- Column 1: Scrolls Down -->
            <div class="gallery-column column-1">
                <?php $lastId = null; for ($r = 0; $r < $repeat; $r++): ?>
                    <?php $set = shuffle_no_consecutive($columns[0], $lastId); $lastId = end($set)['id']; ?>
                    <?php foreach ($set as $collection): ?>
                    <div class="gallery-item">
                        <a href="shop.php?collection=<?php echo $collection['id']; ?>" title="<?php echo htmlspecialchars($collection['name']); ?>">
                            <img src="<?php echo htmlspecialchars($collection['image']); ?>" alt="<?php echo htmlspecialchars($collection['name']); ?>">
                        </a>
                    </div>
                    <?php endforeach; ?>
                <?php endfor; ?>
            </div>

            <!-- Column 2: Scrolls Up -->
            <div class="gallery-column column-2">
                <?php $lastId = null; for ($r = 0; $r < $repeat; $r++): ?>
                    <?php $set = shuffle_no_consecutive($columns[1], $lastId); $lastId = end($set)['id']; ?>
                    <?php foreach ($set as $collection): ?>
                    <div class="gallery-item">
                        <a href="shop.php?collection=<?php echo $collection['id']; ?>" title="<?php echo htmlspecialchars($collection['name']); ?>">
                            <img src="<?php echo htmlspecialchars($collection['image']); ?>" alt="<?php echo htmlspecialchars($collection['name']); ?>">
                        </a>
                    </div>
                    <?php endforeach; ?>
                <?php endfor; ?>
            </div>

            <!-- Column 3: Scrolls Down -->
            <div class="gallery-column column-3">
                <?php $lastId = null; for ($r = 0; $r < $repeat; $r++): ?>
                    <?php $set = shuffle_no_consecutive($columns[2], $lastId); $lastId = end($set)['id']; ?>
                    <?php foreach ($set as $collection): ?>
                    <div class="gallery-item">
                        <a href="shop.php?collection=<?php echo $collection['id']; ?>" title="<?php echo htmlspecialchars($collection['name']); ?>">
                            <img src="<?php echo htmlspecialchars($collection['image']); ?>" alt="<?php echo htmlspecialchars($collection['name']); ?>">
                        </a>
                    </div>
                    <?php endforeach; ?>
                <?php endfor; ?>
            </div>
        </div>
        <!-- Mobile grid of unique collection cards -->
        <div class="collections-mobile-grid">
            <div class="grid grid-cols-2 gap-4">
                <?php foreach ($collections as $collection): ?>
                <div class="bg-white rounded-xl shadow p-3 flex flex-col items-center">
                    <a href="shop.php?collection=<?php echo $collection['id']; ?>" title="<?php echo htmlspecialchars($collection['name']); ?>">
                        <img src="<?php echo htmlspecialchars($collection['image']); ?>" alt="<?php echo htmlspecialchars($collection['name']); ?>" class="w-28 h-28 object-cover rounded-lg mb-2">
                        <div class="text-sm font-semibold text-gray-900 text-center"><?php echo htmlspecialchars($collection['name']); ?></div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <style>
        @media (max-width: 768px) {
            .scrolling-gallery-container { display: none !important; }
            .collections-mobile-grid { display: block !important; margin-top: 2rem; }
        }
        @media (min-width: 769px) {
            .collections-mobile-grid { display: none !important; }
        }
        </style>
    </main>

    <!-- What Makes Us Special Section -->
    <section class="bg-white py-20">
        <div class="container mx-auto px-4 text-center">
            <img src="images/logo.webp" alt="Beam Logo" class="mx-auto h-16 mb-6">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">What Makes Beam Special?</h2>
            <p class="text-gray-600 max-w-2xl mx-auto">
                At Beam, we believe in more than just clothing. We stand for conscious creation, timeless design, and a commitment to quality that you can feel. Every piece is thoughtfully crafted to not only elevate your style but also to endure, becoming a cherished part of your personal collection for years to come.
            </p>
        </div>
    </section>

    <?php require_once 'includes/footer.php'; ?>        </div>
    </section>

    <?php require_once 'includes/footer.php'; ?>
