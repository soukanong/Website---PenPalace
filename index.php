<?php
/**
 * PenPalace Homepage
 * Features hero section, featured products, new arrivals, and promotional sections
 */

ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL);

require_once __DIR__ . "/includes/auth.php";
require_once __DIR__ . "/includes/functions.php";

$auth = Auth::getInstance();
$functions = Functions::getInstance();

// Set language based on query parameter
if (isset($_GET["lang"]) && in_array($_GET["lang"], ["en", "vi"])) {
    $_SESSION["lang"] = $_GET["lang"];
}

// Fetch featured products
$featured_products = $functions->searchProducts("", null, 4);
// Fetch new arrivals
$new_arrivals = $functions->searchProducts("", null, 4);

$page_title = "Premium Korean & Japanese Stationery";
require_once __DIR__ . "/includes/header.php";
require_once __DIR__ . "/includes/header-nav.php";
?>

<main class="home">
    <!-- Hero Section -->
        <?php
        $hero_translations = [
            "en" => [
                "title" => "Discover the Art of Writing",
                "subtitle" => "Premium Korean and Japanese Stationery",
                "cta" => "Shop Now",
            ],
            "vi" => [
                "title" => "Khám Phá Nghệ Thuật Viết",
                "subtitle" => "Văn Phòng Phẩm Cao Cấp Hàn Quốc và Nhật Bản",
                "cta" => "Mua Ngay",
            ],
        ];
        $lang = $_SESSION["lang"] ?? "en";
        ?>
        <section class="hero">
            <div class="hero-content">
                <h1><?= htmlspecialchars(
                    $hero_translations[$lang]["title"]
                ) ?></h1>
                <p><?= htmlspecialchars(
                    $hero_translations[$lang]["subtitle"]
                ) ?></p>
                <a href="products.php" class="btn btn-primary"><?= htmlspecialchars(
                    $hero_translations[$lang]["cta"]
                ) ?></a>
            </div>
        </section>

    <!-- Benefits Section -->
    <?php $benefits_translations = [
        "en" => [
            "shipping" => [
                "title" => "Free Worldwide Shipping",
                "desc" => "Enjoy free shipping on all orders worldwide.",
            ],
            "returns" => [
                "title" => "Full 30-Day Return Policy",
                "desc" => "Return any item within 30 days for a full refund.",
            ],
            "community" => [
                "title" => "Join 2000+ of Stationery-Addicts",
                "desc" =>
                    "Join our community of over 2000 stationery enthusiasts.",
            ],
        ],
        "vi" => [
            "shipping" => [
                "title" => "Miễn Phí Vận Chuyển Toàn Cầu",
                "desc" =>
                    "Tận hưởng miễn phí vận chuyển cho mọi đơn hàng trên toàn thế giới.",
            ],
            "returns" => [
                "title" => "Chính Sách Đổi Trả 30 Ngày",
                "desc" =>
                    "Hoàn trả bất kỳ sản phẩm nào trong vòng 30 ngày để được hoàn tiền đầy đủ.",
            ],
            "community" => [
                "title" => "Tham Gia Cùng 2000+ Người Yêu Văn Phòng Phẩm",
                "desc" =>
                    "Tham gia cộng đồng của chúng tôi với hơn 2000 người đam mê văn phòng phẩm.",
            ],
        ],
    ]; ?>
    <section class="benefits">
        <div class="benefit-card">
            <i class="ti ti-truck"></i>
            <h3><?= htmlspecialchars(
                $benefits_translations[$lang]["shipping"]["title"]
            ) ?></h3>
            <p><?= htmlspecialchars(
                $benefits_translations[$lang]["shipping"]["desc"]
            ) ?></p>
        </div>
        <div class="benefit-card">
            <i class="ti ti-refresh"></i>
            <h3><?= htmlspecialchars(
                $benefits_translations[$lang]["returns"]["title"]
            ) ?></h3>
            <p><?= htmlspecialchars(
                $benefits_translations[$lang]["returns"]["desc"]
            ) ?></p>
        </div>
        <div class="benefit-card">
            <i class="ti ti-users"></i>
            <h3><?= htmlspecialchars(
                $benefits_translations[$lang]["community"]["title"]
            ) ?></h3>
            <p><?= htmlspecialchars(
                $benefits_translations[$lang]["community"]["desc"]
            ) ?></p>
        </div>
    </section>

    <!-- Featured Products -->
    <?php $section_translations = [
        "en" => [
            "featured" => "Featured Products",
            "categories" => "Shop by Category",
            "new_arrivals" => "New Arrivals",
        ],
        "vi" => [
            "featured" => "Sản Phẩm Nổi Bật",
            "categories" => "Mua Sắm theo Danh Mục",
            "new_arrivals" => "Hàng Mới Về",
        ],
    ]; ?>
    <section class="featured">
        <h2><?= htmlspecialchars(
            $section_translations[$lang]["featured"]
        ) ?></h2>
        <div class="product-grid">
            <?php foreach ($featured_products as $product): ?>
                <div class="product-card">
                    <img src="<?= htmlspecialchars($product["image_url"]) ?>"
                         alt="<?= htmlspecialchars($product["name"]) ?>"
                         loading="lazy">
                    <h3><?= htmlspecialchars($product["name"]) ?></h3>
                    <p class="price">$<?= number_format(
                        $product["price"],
                        2
                    ) ?></p>
                    <a href="product-detail.php?id=<?= $product["id"] ?>"
                       class="btn btn-secondary">View Details</a>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Categories -->
    <section class="categories">
        <h2><?= htmlspecialchars(
            $section_translations[$lang]["categories"]
        ) ?></h2>
        <div class="category-grid">
            <a href="categories.php#pens" class="category-card">
                <img src="assets/images/placeholders/category-pens.jpg" alt="Pens" loading="lazy">
                <h3>Pens</h3>
            </a>
            <a href="categories.php#notebooks" class="category-card">
                <img src="assets/images/placeholders/category-notebooks.jpg" alt="Notebooks" loading="lazy">
                <h3>Notebooks</h3>
            </a>
            <a href="categories.php#bags" class="category-card">
                <img src="assets/images/placeholders/category-bags.jpg" alt="Bags" loading="lazy">
                <h3>Bags</h3>
            </a>
            <a href="categories.php#accessories" class="category-card">
                <img src="assets/images/placeholders/category-accessories.jpg" alt="Accessories" loading="lazy">
                <h3>Accessories</h3>
            </a>
        </div>
    </section>

    <!-- New Arrivals -->
    <section class="new-arrivals">
        <h2><?= htmlspecialchars(
            $section_translations[$lang]["new_arrivals"]
        ) ?></h2>
        <div class="product-grid">
            <?php foreach ($new_arrivals as $product): ?>
                <div class="product-card">
                    <img src="<?= htmlspecialchars($product["image_url"]) ?>"
                         alt="<?= htmlspecialchars($product["name"]) ?>"
                         loading="lazy">
                    <h3><?= htmlspecialchars($product["name"]) ?></h3>
                    <p class="price">$<?= number_format(
                        $product["price"],
                        2
                    ) ?></p>
                    <a href="product-detail.php?id=<?= $product["id"] ?>"
                       class="btn btn-secondary">View Details</a>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
</main>

<?php require_once __DIR__ . "/includes/footer.php"; ?>
