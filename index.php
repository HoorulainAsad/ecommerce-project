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
<section class="hero-section">
    <?php foreach ($heroImages as $index => $image): ?>
        <div class="hero-slide <?php echo ($index === 0) ? 'active' : ''; ?>" style="background-image: url('<?php echo $image; ?>');"></div>
    <?php endforeach; ?>
    <div class="hero-overlay">
        <h1>Elegance Redefined.</h1>
        <p>Discover your dream dress for every special occasion.</p>
        <a href="<?php echo BASE_URL; ?>category.php?name=all" class="btn hero-button">Shop Now</a>
    </div>
    <div class="hero-indicators"></div>
</section>

<!-- All other sections remain unchanged from your original code -->
<?php
$allCategories = $categoryManager->getAllCategories();

?>

<section class="category-section">
    <div class="container">
        <h2 class="section-heading">Explore By Categories</h2>
        <div class="oval-categories-grid">
            <!-- Dynamic Categories -->
             <?php
                function getCategoryImage($categoryName) {
                    $name = strtolower(trim($categoryName));
                    $filename = $name . '.jpg'; // or .png depending on what you're using
                    $path = 'assets/img/' . $filename;

                    // Use placeholder if image doesn't exist
                    return WEB_ROOT_URL . $path;
                }
                ?>

            <?php foreach ($allCategories as $category): ?>
                <a href="<?php echo BASE_URL . 'category.php?name=' . urlencode($category['name']); ?>" class="category-oval-card">
                    <div class="category-oval-image-wrapper">
                        <div class="floral-border">
        <img src="<?php echo getCategoryImage($category['name']); ?>" alt="<?php echo htmlspecialchars($category['name']); ?>" onerror="this.src='https://placehold.co/200x250?text=No+Image';">
        </div>
                    </div>
                    <div class="category-oval-name"><?php echo htmlspecialchars($category['name']); ?></div>
                </a>
            <?php endforeach; ?>

            <!-- New Arrivals -->
            <a href="<?php echo BASE_URL . 'category.php?name=new_arrivals'; ?>" class="category-oval-card">
                <div class="category-oval-image-wrapper">
                    <div class="floral-border">
                    <img src="<?php echo WEB_ROOT_URL . 'images/new_arrivals_placeholder.png'; ?>" alt="New Arrivals" onerror="this.src='https://placehold.co/200x250?text=New+Arrivals';">
                    </div>
                </div>
                <div class="category-oval-name">New Arrivals</div>
            </a>

            <!-- Trending -->
            <a href="<?php echo BASE_URL . 'category.php?name=trendy'; ?>" class="category-oval-card">
                <div class="category-oval-image-wrapper">
                    <div class="floral-border">
                    <img src="<?php echo WEB_ROOT_URL . 'images/trending_placeholder.png'; ?>" alt="Trending Products" onerror="this.src='https://placehold.co/200x250?text=Trending';">
                     <img src="assets/img/floral-frame.png" class="floral-overlay">
                    </div>
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
    document.addEventListener('DOMContentLoaded', function() {
        const slides = document.querySelectorAll('.hero-slide');
        const indicatorsContainer = document.querySelector('.hero-indicators');
        let currentSlide = 0;
        let slideInterval;

        function showSlide(index) {
            slides.forEach((slide, i) => {
                slide.classList.remove('active');
                if (indicatorsContainer) {
                    indicatorsContainer.children[i].classList.remove('active');
                }
            });
            slides[index].classList.add('active');
            if (indicatorsContainer) {
                indicatorsContainer.children[index].classList.add('active');
            }
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
