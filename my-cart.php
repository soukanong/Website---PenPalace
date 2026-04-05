<?php
/**
 * Shopping Cart Page
 * Displays cart contents and checkout options for both guests and logged-in users
 */

ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL);

require_once __DIR__ . "/includes/auth.php";
require_once __DIR__ . "/includes/functions.php";

$auth = Auth::getInstance();
$functions = Functions::getInstance();

$page_title = "Shopping Cart";

// Get cart items
$user_id = $auth->isLoggedIn() ? $_SESSION["user_id"] : null;
$cart_items = $functions->getCart($user_id);

// Calculate totals
$subtotal = 0;
foreach ($cart_items as $item) {
    $subtotal += $item["price"] * $item["quantity"];
}

// Handle quantity updates and item removal
$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        if (
            !isset($_POST["csrf_token"]) ||
            !$auth->validateCsrfToken($_POST["csrf_token"])
        ) {
            throw new Exception("Invalid request");
        }

        $action = $_POST["action"] ?? "";
        $product_id = isset($_POST["product_id"])
            ? (int) $_POST["product_id"]
            : 0;

        switch ($action) {
            case "update":
                $quantity = isset($_POST["quantity"])
                    ? (int) $_POST["quantity"]
                    : 0;
                if ($quantity > 0) {
                    // Get cart ID
                    $cart_id = $auth->isLoggedIn()
                        ? $functions->getUserCart($_SESSION["user_id"])
                        : $functions->getSessionCart();
                    // Update quantity in the database
                    try {
                        $functions->updateCartItem(
                            $cart_id,
                            $product_id,
                            $quantity
                        );
                    } catch (Exception $e) {
                        $error = $e->getMessage();
                    }
                    // Redirect back
                    header("Location: my-cart.php");
                    exit();
                } else {
                    throw new Exception("Invalid quantity");
                }
                break;

            case "remove":
                // Get cart ID
                $cart_id = $auth->isLoggedIn()
                    ? $functions->getUserCart($_SESSION["user_id"])
                    : $functions->getSessionCart();
                // Remove item from the cart
                $functions->removeCartItem($cart_id, $product_id);
                // Redirect back
                header("Location: my-cart.php");
                exit();
                break;

            default:
                throw new Exception("Invalid action");
        }

        // Refresh cart items after changes
        $cart_items = $functions->getCart($user_id);
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

require_once __DIR__ . "/includes/header.php";
require_once __DIR__ . "/includes/header-nav.php";
?>

<div class="page-header">
    <h1><?= htmlspecialchars($page_title) ?></h1>
</div>

<div class="container">
    <?php if ($error): ?>
        <div class="alert alert-error">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <?php if (empty($cart_items)): ?>
        <div class="cart-empty">
            <p>Your cart is empty</p>
            <a href="products.php" class="btn btn-primary">Continue Shopping</a>
        </div>
    <?php

        // 1 day expiry
        // 1 day expiry
        else: ?>
        <div class="cart-content">
            <!-- Cart Items -->
            <div class="cart-items">
                <?php foreach ($cart_items as $item): ?>
                    <div class="cart-item">
                        <img src="<?= htmlspecialchars($item["image_url"]) ?>"
                             alt="<?= htmlspecialchars($item["name"]) ?>"
                             class="cart-item-image">

                        <div class="item-details">
                            <h3><?= htmlspecialchars($item["name"]) ?></h3>
                            <p class="item-price">$<?= number_format(
                                $item["price"],
                                2
                            ) ?></p>

                            <form method="POST" class="item-quantity">
                                <input type="hidden" name="csrf_token" value="<?= $auth->generateCsrfToken() ?>">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="product_id" value="<?= $item[
                                    "product_id"
                                ] ?>">

                                <label for="quantity-<?= $item[
                                    "product_id"
                                ] ?>">Quantity:</label>
                                <input type="number"
                                       id="quantity-<?= $item["product_id"] ?>"
                                       name="quantity"
                                       value="<?= $item["quantity"] ?>"
                                       min="1"
                                       max="99"
                                       required>
                                <button type="submit" class="btn btn-secondary">Update</button>
                            </form>

                            <form method="POST" class="item-remove">
                                <input type="hidden" name="csrf_token" value="<?= $auth->generateCsrfToken() ?>">
                                <input type="hidden" name="action" value="remove">
                                <input type="hidden" name="product_id" value="<?= $item[
                                    "product_id"
                                ] ?>">
                                <button type="submit" class="btn btn-danger">Remove</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Cart Summary -->
            <div class="cart-summary">
                <h2>Order Summary</h2>
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span>$<?= number_format($subtotal, 2) ?></span>
                </div>

                <?php if ($auth->isLoggedIn()): ?>
                    <?php
                    $checkout_token = bin2hex(random_bytes(32));
                    $_SESSION["checkout_token"] = $checkout_token;
                    $_SESSION["checkout_token_expiry"] = time() + 86400;

                    // 1 day expiry
                    ?>
                    <a href="checkout.php?token=<?= $checkout_token ?>" class="btn btn-primary">Proceed to Checkout</a>
                <?php else: ?>
                    <?php
                    // Generate checkout token for guests
                    $checkout_token = bin2hex(random_bytes(32));
                    $_SESSION["checkout_token"] = $checkout_token;
                    $_SESSION["checkout_token_expiry"] = time() + 86400; // 1 day expiry
                    ?>
                    <div class="guest-options">
                        <a href="checkout.php?token=<?= $checkout_token ?>" class="btn btn-secondary">Continue as Guest</a>
                        <div class="auth-buttons">
                            <a href="login.php" class="btn btn-primary">Login</a>
                            <a href="signup.php" class="btn btn-secondary">Sign Up</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . "/includes/footer.php"; ?>
