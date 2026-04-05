<?php
/**
 * New Arrivals Page
 * Displays recently added products with filtering and pagination
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

// Get filters from query parameters
$sort = htmlspecialchars($_GET["sort"] ?? "");
$page = max(1, filter_var($_GET["page"] ?? 1, FILTER_VALIDATE_INT));
$limit = 12;
$offset = ($page - 1) * $limit;

// Get new arrivals (products added in the last 30 days)
try {
    $products = $functions->searchProducts(
        "",
        null,
        $sort,
        $limit,
        $offset,
        "created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
    );
    $total_products = $functions->getProductsCount(
        null,
        $sort,
        "created_at >= DATE_SUB(NOW(),
INTERVAL 30 DAY)"
    );
    $total_pages = ceil($total_products / $limit);
} catch (Exception $e) {
    $error = "Failed to load new arrivals";
}

$page_title = "New Arrivals";
require_once __DIR__ . "/includes/header.php";
require_once __DIR__ . "/includes/header-nav.php";
?>

<?php $translations = [
    "en" => [
        "new_arrivals" => "New Arrivals",
        "check_out_latest" =>
            "Check out our latest products added in the last 30 days",
        "newest" => "Newest First",
        "price_asc" => "Price: Low to High",
        "price_desc" => "Price: High to Low",
    ],
    "vi" => [
        "new_arrivals" => "Hàng Mới Về",
        "check_out_latest" =>
            "Hãy xem các sản phẩm mới nhất được thêm vào trong 30 ngày qua",
        "newest" => "Mới Nhất",
        "price_asc" => "Giá: Thấp đến Cao",
        "price_desc" => "Giá: Cao đến Thấp",
    ],
]; ?>

<div class="page-header">
    <h1><?= htmlspecialchars($translations[$lang]["new_arrivals"]) ?></h1>
    <p><?= htmlspecialchars($translations[$lang]["check_out_latest"]) ?></p>
</div>

<div class="container">
    <!-- Filters -->
    <div class="filters">
        <form action="new-arrivals.php" method="GET" class="filter-form">
            <select name="sort" onchange="this.form.submit()" class="filter-select">
                <option value="newest" <?= $sort === "newest"
                    ? "selected"
                    : "" ?>><?= htmlspecialchars(
    $translations[$lang]["newest"]
) ?></option>
                <option value="price_asc" <?= $sort === "price_asc"
                    ? "selected"
                    : "" ?>><?= htmlspecialchars(
    $translations[$lang]["price_asc"]
) ?></option>
                <option value="price_desc" <?= $sort === "price_desc"
                    ? "selected"
                    : "" ?>><?= htmlspecialchars(
    $translations[$lang]["price_desc"]
) ?></option>
            </select>
        </form>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-error">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php else: ?>
        <!-- Products Grid -->
        <div class="products-grid">
            <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <img src="<?= htmlspecialchars($product["image_url"]) ?>"
                         alt="<?= htmlspecialchars($product["name"]) ?>"
                         loading="lazy"
                         class="product-image">
                    <div class="product-details">
                        <h3 class="product-title"><?= htmlspecialchars(
                            $product["name"]
                        ) ?></h3>
                        <p class="product-price">$<?= number_format(
                            $product["price"],
                            2
                        ) ?></p>
                        <a href="product-detail.php?id=<?= $product["id"] ?>"
                           class="btn btn-secondary product-link">View Details</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?= $i . ($sort ? "&sort=$sort" : "") ?>"
                       class="pagination-link <?= $page === $i
                           ? "active"
                           : "" ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . "/includes/footer.php"; ?>
