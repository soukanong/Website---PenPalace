<?php
/**
 * Search Page
 * Displays search results with filtering and pagination
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

// Get search parameters from query string
$query = htmlspecialchars($_GET["q"] ?? "");
$category_name = htmlspecialchars($_GET["category"] ?? "");
$sort = htmlspecialchars($_GET["sort"] ?? "");
$page = max(1, filter_var($_GET["page"] ?? 1, FILTER_VALIDATE_INT));
$limit = 12;
$offset = ($page - 1) * $limit;

// Retrieve category ID based on category name
$category_id = null;
if ($category_name !== "") {
    $category_data = $functions->getCategoryByName($category_name);
    if ($category_data) {
        $category_id = $category_data["id"];
    }
}

// Require at least 2 characters for search
if (strlen($query) < 2) {
    header("Location: products.php");
    exit();
}

// Get search results
try {
    $products = $functions->searchProducts(
        $query,
        $category_id, // Pass category ID instead of name
        $sort,
        $limit,
        $offset
    );
    $total_products = $functions->getProductsCount(
        $category_id, // Pass category ID
        $sort,
        "discount_price IS NULL" // Adjust additional filter if necessary
    );
    $total_pages = ceil($total_products / $limit);
} catch (Exception $e) {
    $error = "Failed to load search results";
    error_log("Search error: " . $e->getMessage());
}

$page_title = "Search Results for \"" . htmlspecialchars($query) . "\"";
require_once __DIR__ . "/includes/header.php";
require_once __DIR__ . "/includes/header-nav.php";
?>

<?php $translations = [
    "en" => [
        "search_results" => "Search Results",
        "showing_results_for" => "Showing results for",
        "all_categories" => "All Categories",
        "pens" => "Pens",
        "notebooks" => "Notebooks",
        "bags" => "Bags",
        "accessories" => "Accessories",
        "most_relevant" => "Most Relevant",
        "newest" => "Newest First",
        "price_asc" => "Price: Low to High",
        "price_desc" => "Price: High to Low",
        "name_asc" => "Name: A to Z",
        "no_results_found" => "No results found",
        "try_adjusting_search_criteria" => "Try adjusting your search criteria or browse our products below",
        "browse_all_products" => "Browse All Products",
    ],
    "vi" => [
        "search_results" => "Kết Quả Tìm Kiếm",
        "showing_results_for" => "Hiển thị kết quả cho",
        "all_categories" => "Tất Cả Danh Mục",
        "pens" => "Bút",
        "notebooks" => "Sổ Tay",
        "bags" => "Túi",
        "accessories" => "Phụ Kiện",
        "most_relevant" => "Phù Hợp Nhất",
        "newest" => "Mới Nhất",
        "price_asc" => "Giá: Thấp đến Cao",
        "price_desc" => "Giá: Cao đến Thấp",
        "name_asc" => "Tên: A đến Z",
        "no_results_found" => "Không tìm thấy kết quả",
        "try_adjusting_search_criteria" => "Hãy thử điều chỉnh tiêu chí tìm kiếm hoặc duyệt qua các sản phẩm của chúng tôi bên dưới",
        "browse_all_products" => "Duyệt Qua Tất Cả Sản Phẩm",
    ],
]; ?>

<div class="page-header">
    <h1><?= htmlspecialchars($translations[$lang]["search_results"]) ?></h1>
    <p><?= htmlspecialchars($translations[$lang]["showing_results_for"]) ?> "<?= htmlspecialchars($query) ?>"</p>
</div>

<div class="container">
    <!-- Filters -->
    <div class="filters">
        <form action="search.php" method="GET">
            <input type="hidden" name="q" value="<?= htmlspecialchars($query) ?>">

            <select name="category" onchange="this.form.submit()" class="filter-select">
                <option value=""><?= htmlspecialchars($translations[$lang]["all_categories"]) ?></option>
                <option value="pens" <?= $category_name === "pens" ? "selected" : "" ?>><?= htmlspecialchars($translations[$lang]["pens"]) ?></option>
                <option value="notebooks" <?= $category_name === "notebooks" ? "selected" : "" ?>><?= htmlspecialchars($translations[$lang]["notebooks"]) ?></option>
                <option value="bags" <?= $category_name === "bags" ? "selected" : "" ?>><?= htmlspecialchars($translations[$lang]["bags"]) ?></option>
                <option value="accessories" <?= $category_name === "accessories" ? "selected" : "" ?>><?= htmlspecialchars($translations[$lang]["accessories"]) ?></option>
            </select>

            <select name="sort" onchange="this.form.submit()" class="filter-select">
                <option value="relevance" <?= $sort === "relevance" ? "selected" : "" ?>><?= htmlspecialchars($translations[$lang]["most_relevant"]) ?></option>
                <option value="newest" <?= $sort === "newest" ? "selected" : "" ?>><?= htmlspecialchars($translations[$lang]["newest"]) ?></option>
                <option value="price_asc" <?= $sort === "price_asc" ? "selected" : "" ?>><?= htmlspecialchars($translations[$lang]["price_asc"]) ?></option>
                <option value="price_desc" <?= $sort === "price_desc" ? "selected" : "" ?>><?= htmlspecialchars($translations[$lang]["price_desc"]) ?></option>
                <option value="name_asc" <?= $sort === "name_asc" ? "selected" : "" ?>><?= htmlspecialchars($translations[$lang]["name_asc"]) ?></option>
            </select>
        </form>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-error">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php else: ?>
        <?php if (empty($products)): ?>
            <div class="no-results">
                <h2><?= htmlspecialchars($translations[$lang]["no_results_found"]) ?></h2>
                <p><?= htmlspecialchars($translations[$lang]["try_adjusting_search_criteria"]) ?></p>
                <a href="products.php" class="btn btn-primary"><?= htmlspecialchars($translations[$lang]["browse_all_products"]) ?></a>
            </div>
        <?php else: ?>
            <!-- Products Grid -->
            <div class="products-grid">
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <img src="<?= htmlspecialchars($product["image_url"]) ?>" alt="<?= htmlspecialchars($product["name"]) ?>" loading="lazy" class="product-image">
                        <div class="product-details">
                            <h3 class="product-title"><?= htmlspecialchars($product["name"]) ?></h3>
                            <p class="product-price">$<?= number_format($product["price"], 2) ?></p>
                            <a href="product-detail.php?id=<?= $product["id"] ?>" class="btn btn-secondary product-link">View Details</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?q=<?= urlencode($query) ?>&page=<?= $i . ($category_name ? "&category=$category_name" : "") . ($sort ? "&sort=$sort" : "") ?>" class="pagination-link <?= $page === $i ? "active" : "" ?>"><?= $i ?></a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . "/includes/footer.php"; ?>
