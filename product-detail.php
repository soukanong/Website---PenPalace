<?php
/**
 * Product Detail Page
 * Displays detailed product information with add to cart and wishlist functionality
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

// Get product ID from query parameter
$product_id = filter_var($_GET["id"] ?? "", FILTER_VALIDATE_INT);

if (!$product_id) {
    header("Location: products.php");
    exit();
}

// Get product details
try {
    $product = $functions->getProductById($product_id);

    if (!$product) {
        header("Location: products.php");
        exit();
    }

    // Get related products from same category
    $related_products = $functions->getRelatedProducts(
        $product["category_id"],
        $product_id,
        4
    );

    // Check if product is in user's wishlist
    $in_wishlist = false;
    if ($auth->isLoggedIn()) {
        $in_wishlist = $functions->isProductInWishlist(
            $_SESSION["user_id"],
            $product_id
        );
    }
} catch (Exception $e) {
    $error = "Failed to load product details";
}

$page_title = $product ? $product["name"] : "Product Not Found";
require_once __DIR__ . "/includes/header.php";
require_once __DIR__ . "/includes/header-nav.php";
?>

<div class="container product-detail">
    <?php if (isset($error)): ?>
        <div class="alert alert-error">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php else: ?>
        <!-- Product Info -->
        <div class="product-info-grid">
            <!-- Product Images -->
            <div class="product-images">
                <img src="<?= htmlspecialchars($product["image_url"]) ?>"
                    alt="<?= htmlspecialchars($product["name"]) ?>"
                    class="product-main-image">
                <?php if (!empty($product["additional_images"])): ?>
                    <div class="product-thumbnails">
                        <?php foreach (
                            json_decode($product["additional_images"])
                            as $image
                        ): ?>
                            <img src="<?= htmlspecialchars($image) ?>"
                                alt="<?= htmlspecialchars($product["name"]) ?>"
                                class="product-thumbnail"
                                loading="lazy">
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Product Details -->
            <div class="product-details">
                <nav class="breadcrumb">
                    <a href="products.php">Products</a> &gt;
                    <a href="products.php?category=<?= urlencode(
                        $product["category_name"]
                    ) ?>">
                        <?= htmlspecialchars($product["category_name"]) ?>
                    </a> &gt;
                    <span><?= htmlspecialchars($product["name"]) ?></span>
                </nav>

                <h1><?= htmlspecialchars($product["name"]) ?></h1>

                <div class="product-price">
                    <?php if ($product["discount_price"]): ?>
                        <span class="original-price">$<?= number_format(
                            $product["price"],
                            2
                        ) ?></span>
                        <span class="sale-price">$<?= number_format(
                            $product["discount_price"],
                            2
                        ) ?></span>
                    <?php else: ?>
                        <span class="price">$<?= number_format(
                            $product["price"],
                            2
                        ) ?></span>
                    <?php endif; ?>
                </div>

                <?php if ($product["stock"] > 0): ?>
                    <div class="product-stock in-stock">
                        <i class="ti ti-check"></i> In Stock
                    </div>
                <?php else: ?>
                    <div class="product-stock out-of-stock">
                        <i class="ti ti-x"></i> Out of Stock
                    </div>
                <?php endif; ?>

                <div class="product-description">
                    <?= nl2br(htmlspecialchars($product["description"])) ?>
                </div>

                <?php if ($product["stock"] > 0): ?>
                    <form action="add-to-cart.php" method="POST" class="add-to-cart-form">
                        <input type="hidden" name="csrf_token"
                            value="<?= htmlspecialchars(
                                $auth->generateCsrfToken()
                            ) ?>">
                        <input type="hidden" name="product_id"
                            value="<?= $product["id"] ?>">
                            
                        <div class="quantity-selector">
                            <label for="quantity">Quantity:</label>
                            <input type="number" 
                                   id="quantity" 
                                   name="quantity" 
                                   value="1" 
                                   min="1" 
                                   max="<?= $product["stock"] ?>" 
                                   required>
                        </div>

                        <button type="submit" class="btn btn-primary add-to-cart">
                            <i class="ti ti-shopping-cart"></i> Add to Cart
                        </button>
                    </form>
                <?php endif; ?>

                <!-- Wishlist Button -->
                <?php if ($auth->isLoggedIn()): ?>
                    <form action="<?= $in_wishlist
                        ? "#"
                        : "#" ?>"
                        method="POST" class="wishlist-form">
                        <input type="hidden" name="csrf_token"
                            value="<?= htmlspecialchars(
                                $auth->generateCsrfToken()
                            ) ?>">
                        <input type="hidden" name="product_id"
                            value="<?= $product["id"] ?>">

                        <button type="submit" class="btn btn-secondary wishlist-btn">
                            <i class="ti ti-heart<?= $in_wishlist
                                ? "-filled"
                                : "" ?>"></i>
                            <?= $in_wishlist
                                ? "Remove from Wishlist"
                                : "Add to Wishlist" ?>
                        </button>
                    </form>
                <?php endif; ?>

                <!-- Product Specifications -->
                <?php if (!empty($product["specifications"])): ?>
                    <div class="product-specs">
                        <h2>Specifications</h2>
                        <table>
                            <?php foreach (
                                json_decode($product["specifications"], true)
                                as $spec => $value
                            ): ?>
                                <tr>
                                    <th><?= htmlspecialchars($spec) ?></th>
                                    <td><?= htmlspecialchars($value) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Related Products -->
        <?php if (!empty($related_products)): ?>
            <section class="related-products">
                <h2>Related Products</h2>
                <div class="products-grid">
                    <?php foreach ($related_products as $related): ?>
                        <div class="product-card">
                            <img src="<?= htmlspecialchars(
                                $related["image_url"]
                            ) ?>"
                                alt="<?= htmlspecialchars($related["name"]) ?>"
                                loading="lazy">
                            <h3><?= htmlspecialchars($related["name"]) ?></h3>
                            <p class="price">$<?= number_format(
                                $related["price"],
                                2
                            ) ?></p>
                            <a href="product-detail.php?id=<?= $related[
                                "id"
                            ] ?>"
                            class="btn btn-secondary">View Details</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . "/includes/footer.php"; ?>
