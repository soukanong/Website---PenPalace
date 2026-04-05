<?php
/**
 * Sale Page
 * Displays products on sale with filtering and pagination
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

// Get filters from query parameters
$category_name = htmlspecialchars($_GET["category"] ?? "");
$category_id = null;
if ($category_name !== "") {
    $category = $functions->getCategoryByName($category_name);
    if ($category) {
        $category_id = $category["id"];
    }
}
$sort = htmlspecialchars($_GET["sort"] ?? "");
$page = max(1, filter_var($_GET["page"] ?? 1, FILTER_VALIDATE_INT));
$limit = 12;
$offset = ($page - 1) * $limit;

try {
    $products = $functions->searchProducts(
        "",
        $category_id,
        $sort,
        $limit,
        $offset,
        "discount_price IS NOT NULL"
    );
    $total_products = $functions->getProductsCount(
        $category_id,
        $sort,
        "discount_price IS NOT NULL"
    );
    $total_pages = ceil($total_products / $limit);
} catch (Exception $e) {
    $error = "Failed to load sale products";
}

$page_title = "Sale";
require_once __DIR__ . "/includes/header.php";
require_once __DIR__ . "/includes/header-nav.php";
?>

<?php
$translations = [
    "en" => [
        "sale" => "Sale",
        "all_categories" => "All Categories",
        "pens" => "Pens",
        "notebooks" => "Notebooks",
        "bags" => "Bags",
        "accessories" => "Accessories",
        "biggest_discount" => "Biggest Discount",
        "smallest_discount" => "Smallest Discount",
        "price_asc" => "Price: Low to High",
        "price_desc" => "Price: High to Low",
    ],
    "vi" => [
        "sale" => "Khuyến Mãi",
        "all_categories" => "Tất Cả Danh Mục",
        "pens" => "Bút",
        "notebooks" => "Sổ Tay",
        "bags" => "Túi",
        "accessories" => "Phụ Kiện",
        "biggest_discount" => "Giảm Giá Nhiều Nhất",
        "smallest_discount" => "Giảm Giá Ít Nhất",
        "price_asc" => "Giá: Thấp đến Cao",
        "price_desc" => "Giá: Cao đến Thấp",
    ],
];
$lang = $_SESSION["lang"] ?? "en";
?>

<div class="page-header">
    <h1><?= htmlspecialchars($translations[$lang]["sale"]) ?></h1>
    <p>Great deals on selected items</p>
</div>

<div class="container">
    <!-- Filters -->
    <div class="filters">
        <form action="sale.php" method="GET" class="filter-form">
            <select name="category" onchange="this.form.submit()" class="filter-select">
                <option value=""><?= htmlspecialchars(
                    $translations[$lang]["all_categories"]
                ) ?></option>
                <option value="pens" <?= $category_name === "pens"
                    ? "selected"
                    : "" ?>><?= htmlspecialchars(
    $translations[$lang]["pens"]
) ?></option>
                <option value="notebooks" <?= $category_name === "notebooks"
                    ? "selected"
                    : "" ?>><?= htmlspecialchars(
    $translations[$lang]["notebooks"]
) ?></option>
                <option value="bags" <?= $category_name === "bags"
                    ? "selected"
                    : "" ?>><?= htmlspecialchars(
    $translations[$lang]["bags"]
) ?></option>
                <option value="accessories" <?= $category_name === "accessories"
                    ? "selected"
                    : "" ?>><?= htmlspecialchars(
    $translations[$lang]["accessories"]
) ?></option>
            </select>

            <select name="sort" onchange="this.form.submit()" class="filter-select">
                <option value="discount_desc" <?= $sort === "discount_desc"
                    ? "selected"
                    : "" ?>><?= htmlspecialchars(
    $translations[$lang]["biggest_discount"]
) ?></option>
                <option value="discount_asc" <?= $sort === "discount_asc"
                    ? "selected"
                    : "" ?>><?= htmlspecialchars(
    $translations[$lang]["smallest_discount"]
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
                <?php $discount_percentage = round(
                    (($product["price"] - $product["discount_price"]) /
                        $product["price"]) *
                        100
                ); ?>
                <div class="product-card sale">
                    <div class="sale-badge">-<?= $discount_percentage ?>%</div>
                    <img src="<?= htmlspecialchars($product["image_url"]) ?>"
                         alt="<?= htmlspecialchars($product["name"]) ?>"
                         loading="lazy"
                         class="product-image">
                    <div class="product-details">
                        <h3 class="product-title"><?= htmlspecialchars(
                            $product["name"]
                        ) ?></h3>
                        <p class="product-price">
                            <span class="original-price">$<?= number_format(
                                $product["price"],
                                2
                            ) ?></span>
                            <span class="sale-price">$<?= number_format(
                                $product["discount_price"],
                                2
                            ) ?></span>
                        </p>
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
                    <a href="?page=<?= $i .
                        ($category_name ? "&category=$category_name" : "") .
                        ($sort ? "&sort=$sort" : "") ?>"
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
