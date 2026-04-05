<?php
/**
 * Categories Page
 * Displays product categories with featured items
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

$lang = $_SESSION["lang"] ?? "en";

// Get featured products for each category
try {
    // Get all categories first
    $all_categories = $functions->getAllCategories();
    $categories = [];

    foreach ($all_categories as $cat) {
        $slug = strtolower(str_replace(" ", "-", $cat["name"]));
        $categories[$slug] = [
            "name" => $cat["name"],
            "description" => $cat["description"] ?? "No description available",
            "image" =>
                $cat["image_url"] ??
                "assets/images/placeholders/category-{$slug}.jpg",
            "products" => $functions->searchProducts(
                "",
                $cat["id"],
                "newest",
                4,
                0
            ),
        ];
    }
} catch (Exception $e) {
    $error = "Failed to load categories";
}

$page_title = "Categories";
require_once __DIR__ . "/includes/header.php";
require_once __DIR__ . "/includes/header-nav.php";
?>

<?php $translations = [
    "en" => [
        "categories" => "Categories",
        "shop_by_category" => "Shop by Category",
        "discover_our_curated_collection" => "Discover our curated collection of premium
stationery",
        "featured_products" => "Featured Products",
        "explore_collection" => "Explore Collection",
    ],
    "vi" => [
        "categories" => "Danh Mục",
        "shop_by_category" => "Mua Sắm theo Danh Mục",
        "discover_our_curated_collection" => "Khám phá bộ sưu tập văn phòng phẩm cao cấp được tuyể
chọn của chúng tôi",
        "featured_products" => "Sản Phẩm Nổi Bật",
        "explore_collection" => "Khám Phá Bộ Sưu Tập",
    ],
]; ?>

<div class="categories-hero">
    <div class="categories-hero-content">
        <h1><?= htmlspecialchars(
            $translations[$lang]["shop_by_category"]
        ) ?></h1>
        <p><?= htmlspecialchars(
            $translations[$lang]["discover_our_curated_collection"]
        ) ?></p>
    </div>
</div>

<div class="categories-container">
    <?php if (isset($error)): ?>
        <div class="alert alert-error">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php else: ?>
        <?php foreach ($categories as $slug => $category): ?>
            <section id="<?= $slug ?>" class="category-section">
                <div class="category-header" style="background-image: url('<?= htmlspecialchars(
                    $category["image"]
                ) ?>')">
                    <div class="category-overlay">
                        <div class="category-info">
                            <h2><?= htmlspecialchars($category["name"]) ?></h2>
                            <p><?= htmlspecialchars(
                                $category["description"] ??
                                    "No description available"
                            ) ?></p>
                            <a href="products.php?category=<?= $slug ?>"
                               class="btn btn-outline"><?= htmlspecialchars(
                                   $translations[$lang]["explore_collection"]
                               ) ?></a>
                        </div>
                    </div>
                </div>

                <div class="featured-products">
                    <h3><?= htmlspecialchars(
                        $translations[$lang]["featured_products"]
                    ) ?></h3>
                    <div class="products-grid">
                    <?php foreach ($category["products"] as $product): ?>
                        <div class="product-card">
                            <img src="<?= htmlspecialchars(
                                $product["image_url"]
                            ) ?>"
                                 alt="<?= htmlspecialchars($product["name"]) ?>"
                                 loading="lazy">
                            <h3><?= htmlspecialchars($product["name"]) ?></h3>
                            <p class="price">$<?= number_format(
                                $product["price"],
                                2
                            ) ?></p>
                            <a href="product-detail.php?id=<?= $product[
                                "id"
                            ] ?>"
                               class="btn btn-secondary">View Details</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . "/includes/footer.php"; ?>
