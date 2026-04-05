<?php
/**
 * My Wishlist Page
 * Displays wishlist items for logged-in users
 */

ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL);

require_once __DIR__ . "/includes/auth.php";
require_once __DIR__ . "/includes/functions.php";

$auth = Auth::getInstance();
$functions = Functions::getInstance();

// Redirect guests to login
if (!$auth->isLoggedIn()) {
    header("Location: login.php");
    exit();
}

// Get user's wishlist
$user_id = $_SESSION["user_id"];
$wishlist = $functions->getWishlist($user_id);

require_once __DIR__ . "/includes/header.php";
require_once __DIR__ . "/includes/header-nav.php";
?>

<div class="wishlist-container">
    <h1>My Wishlist</h1>

    <?php if (empty($wishlist)): ?>
        <div class="wishlist-empty">
            <p>Your wishlist is empty.</p>
            <a href="products.php" class="btn btn-primary">Start Shopping</a>
        </div>
    <?php else: ?>
        <div class="wishlist-list">
            <?php foreach ($wishlist as $item): ?>
                <div class="wishlist-card">
                    <div class="wishlist-image">
                        <img src="<?= htmlspecialchars($item["image_url"]) ?>" 
                             alt="<?= htmlspecialchars($item["name"]) ?>">
                    </div>
                    <div class="wishlist-details">
                        <h2><?= htmlspecialchars($item["name"]) ?></h2>
                        <p class="price">$<?= htmlspecialchars($item["price"]) ?></p>
                        <div class="wishlist-actions">
                            <form action="add-to-cart.php" method="POST">
                                <input type="hidden" name="csrf_token" 
                                       value="<?= $auth->generateCsrfToken() ?>">
                                <input type="hidden" name="product_id" 
                                       value="<?= htmlspecialchars($item["product_id"]) ?>">
                                <button type="submit" class="btn btn-primary">Add to Cart</button>
                            </form>
                            <form action="remove-from-wishlist.php" method="POST">
                                <input type="hidden" name="csrf_token" 
                                       value="<?= $auth->generateCsrfToken() ?>">
                                <input type="hidden" name="product_id" 
                                       value="<?= htmlspecialchars($item["product_id"]) ?>">
                                <button type="submit" class="btn btn-secondary">Remove</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . "/includes/footer.php"; ?>
