<?php
// index.php (Home Page)

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/classes/ProductFrontendManager.php';
require_once __DIR__ . '/classes/CategoryFrontendManager.php';

$productManager = new ProductFrontendManager();
$categoryManager = new CategoryFrontendManager();

$bridalProducts = $productManager->getFilteredProducts('bridal', 4);
$formalProducts = $productManager->getFilteredProducts('formal', 4);
$partywearProducts = $productManager->getFilteredProducts('partywear', 4);
$trendyProducts = $productManager->getFilteredProducts('trendy', 4);
$newArrivalsProducts = $productManager->getFilteredProducts('new_arrivals', 4);

$heroImages = [
    BASE_URL . 'assets/img/hero1.jpg',
    BASE_URL . 'assets/img/hero2.jpg',
    BASE_URL . 'assets/img/hero3.jpg',
    BASE_URL . 'assets/img/hero4.jpg',
];
?>

<!-- Hero Section (Image Slider) -->
<section class="hero-section" style="position: relative; height: 90vh; overflow: hidden;">
    <?php foreach ($heroImages as $index => $image): ?>
        <div class="hero-slide <?php echo ($index === 0) ? 'active' : ''; ?>" 
             style="position:absolute; top:0; left:0; width:100%; height:100%; background-size:cover; background-position:center; transition: opacity 0.5s; opacity:<?php echo ($index === 0) ? '1' : '0'; ?>; background-image: url('<?php echo $image; ?>');">
        </div>
    <?php endforeach; ?>

    <div class="hero-overlay" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: white; text-align: center; z-index: 2;">
        <h1>Elegance Redefined.</h1>
        <p>Discover your dream dress for every special occasion.</p>
        <a href="<?php echo BASE_URL; ?>category.php?name=all" class="btn hero-button">Shop Now</a>
    </div>
    <div class="hero-indicators" style="position: absolute; bottom: 20px; left: 50%; transform: translateX(-50%); display: flex; gap: 10px; z-index: 3;"></div>
</section>

<?php $allCategories = $categoryManager->getAllCategories(); ?>

<section class="category-section">
    <div class="container">
        <h2 class="section-heading">Explore By Categories</h2>
        <div class="oval-categories-grid">
            <?php
            function getCategoryImage($categoryName) {
                $name = strtolower(trim($categoryName));
                return WEB_ROOT_URL . 'assets/img/' . $name . '.jpg';
            }
            ?>

           <?php foreach ($allCategories as $category): ?>
    <?php
        $slug = strtolower(str_replace(' ', '_', trim($category['name']))); // clean slug
    ?>
    <a href="<?php echo BASE_URL . 'category.php?name=' . urlencode($slug); ?>" class="category-oval-card">
        <div class="category-oval-image-wrapper">
            <img src="<?php echo getCategoryImage($category['name']); ?>" alt="<?php echo htmlspecialchars($category['name']); ?>" onerror="this.src='https://placehold.co/200x250?text=No+Image';">
        </div>
        <div class="category-oval-name"><?php echo htmlspecialchars($category['name']); ?></div>
    </a>
<?php endforeach; ?>


            <!-- New Arrivals -->
            <a href="<?php echo BASE_URL . 'category.php?name=new_arrivals'; ?>" class="category-oval-card">
                <div class="category-oval-image-wrapper">
                    <img src="<?php echo WEB_ROOT_URL . 'assets/img/newarrivals.jpg'; ?>" alt="New Arrivals" onerror="this.src='https://placehold.co/200x250?text=New+Arrivals';">
                </div>
                <div class="category-oval-name">New Arrivals</div>
            </a>

            <!-- Trending -->
            <a href="<?php echo BASE_URL . 'category.php?name=trendy'; ?>" class="category-oval-card">
                <div class="category-oval-image-wrapper">
                    <img src="<?php echo WEB_ROOT_URL . 'assets/img/trending.jpg'; ?>" alt="Trending Products" onerror="this.src='https://placehold.co/200x250?text=Trending';">
                </div>
                <div class="category-oval-name">Trending Products</div>
            </a>
        </div>
    </div>
</section>

<section class="shop-now-section">
    <div class="container-fluid container-xl">
        <h2>Ready to Find Your Perfect Outfit?</h2>
        <a href="<?php echo BASE_URL; ?>category.php?name=all" class="btn shop-now-button">Shop All Dresses</a>
    </div>
</section>

<!-- Hero Slider Script -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const slides = document.querySelectorAll('.hero-slide');
        const indicatorsContainer = document.querySelector('.hero-indicators');
        let currentSlide = 0;
        let slideInterval;

        function showSlide(index) {
            slides.forEach((slide, i) => {
                slide.style.opacity = (i === index) ? '1' : '0';
                if (indicatorsContainer && indicatorsContainer.children[i]) {
                    indicatorsContainer.children[i].classList.toggle('active', i === index);
                }
            });
        }

        function nextSlide() {
            currentSlide = (currentSlide + 1) % slides.length;
            showSlide(currentSlide);
        }

        function startSlider() {
            if (slides.length > 0) {
                showSlide(currentSlide);
                slideInterval = setInterval(nextSlide, 5000);
            }
        }

        function stopSlider() {
            clearInterval(slideInterval);
        }

        if (slides.length > 0) {
            if (indicatorsContainer) {
                slides.forEach((_, i) => {
                    const indicator = document.createElement('div');
                    indicator.classList.add('hero-indicator');
                    indicator.style.width = '12px';
                    indicator.style.height = '12px';
                    indicator.style.borderRadius = '50%';
                    indicator.style.background = '#fff';
                    indicator.style.opacity = '0.6';
                    indicator.style.cursor = 'pointer';
                    indicator.addEventListener('click', () => {
                        stopSlider();
                        currentSlide = i;
                        showSlide(currentSlide);
                        startSlider();
                    });
                    indicatorsContainer.appendChild(indicator);
                });
            }
            startSlider();

            const heroSection = document.querySelector('.hero-section');
            if (heroSection) {
                heroSection.addEventListener('mouseenter', stopSlider);
                heroSection.addEventListener('mouseleave', startSlider);
            }
        }
    });
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
