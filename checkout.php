<?php
/**
 * Checkout Page
 * Handles the checkout process for both logged-in users and guests
 */

ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL);

require_once __DIR__ . "/includes/auth.php";
require_once __DIR__ . "/includes/functions.php";

$auth = Auth::getInstance();
$functions = Functions::getInstance();

// Ensure the page is accessed from my-cart.php
if (
    !isset($_GET["token"]) ||
    !isset($_SESSION["checkout_token"]) ||
    $_GET["token"] !== $_SESSION["checkout_token"] ||
    time() > $_SESSION["checkout_token_expiry"]
) {
    header("Location: my-cart.php");
    exit();
}

$page_title = "Checkout";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Validate CSRF token
        if (!isset($_POST["csrf_token"]) || !$auth->validateCsrfToken($_POST["csrf_token"])) {
            throw new Exception("Invalid request");
        }

        // Validate required fields
        $required_fields = ['shipping_address', 'billing_address', 'payment_info'];
        if (!$auth->isLoggedIn()) {
            $required_fields[] = 'guest_email';
        }

        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("All fields are required");
            }
        }

        if (!$auth->isLoggedIn() && !empty($_POST['guest_email']) && !$functions->validateEmail($_POST['guest_email'])) {
            throw new Exception("Invalid email address");
        }

        // Get user and cart info
        $user_id = $_SESSION["user_id"] ?? null;
        $cart_id = $_SESSION["cart_id"] ?? null;
        
        // Validate cart has items
        $cart_items = $functions->getCart($user_id);
        if (empty($cart_items)) {
            throw new Exception("Your cart is empty");
        }

        // Create order
        $order_id = $functions->createOrder(
            $user_id,
            $cart_id,
            $_POST["shipping_address"],
            $_POST["billing_address"],
            $_POST["payment_info"],
            $_POST["guest_email"] ?? null,
            isset($_POST["create_account"])
        );

        // Generate a single-use unique token for order confirmation
        $token = bin2hex(random_bytes(32));
        $expiry = time() + 3600; // 1 hour

        $_SESSION["order_tokens"][$order_id] = [
            "token" => $token,
            "expiry" => $expiry,
        ];

        header("Location: order-confirmation.php?order_id=" . $order_id . "&token=" . $token);
        exit();
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}

$cart_items = $functions->getCart($_SESSION["user_id"] ?? null);
$total = array_reduce(
    $cart_items,
    function ($carry, $item) {
        return $carry + $item["price"] * $item["quantity"];
    },
    0
);

require_once __DIR__ . "/includes/header.php";
require_once __DIR__ . "/includes/header-nav.php";
?>

<div class="page-header">
    <h1><?= htmlspecialchars($page_title) ?></h1>
</div>

<div class="container checkout-container">
    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($error_message) ?>
        </div>
    <?php endif; ?>

    <form action="checkout.php?token=<?= htmlspecialchars($_GET['token']) ?>" method="POST" class="checkout-form">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($auth->generateCsrfToken()) ?>">
        <div class="form-section">
            <h2>Guest Checkout</h2>
            <div class="form-group">
                <label for="guest_email">Email:</label>
                <input type="email" id="guest_email" name="guest_email" required>
            </div>
            <div class="form-group">
                <label for="create_account">
                    <input type="checkbox" id="create_account" name="create_account">
                    Create an account after checkout
                </label>
            </div>
        </div>

        <div class="form-section">
            <h2>Shipping Address</h2>
            <div class="form-group">
                <label for="shipping_address">Address:</label>
                <input type="text" id="shipping_address" name="shipping_address" required>
            </div>
        </div>

        <div class="form-section">
            <h2>Billing Address</h2>
            <div class="form-group">
                <label for="billing_address">Address:</label>
                <input type="text" id="billing_address" name="billing_address" required>
            </div>
        </div>

        <div class="form-section">
            <h2>Payment Information</h2>
            <div class="form-group">
                <label for="payment_info">Payment Details:</label>
                <input type="text" id="payment_info" name="payment_info" required>
            </div>
        </div>

        <div class="form-section">
            <h2>Order Summary</h2>
            <div class="order-summary">
                <?php foreach ($cart_items as $item): ?>
                    <div class="order-item">
                        <span><?= htmlspecialchars($item["name"]) ?></span>
                        <span><?= htmlspecialchars(
                            $item["quantity"]
                        ) ?> x $<?= number_format($item["price"], 2) ?></span>
                    </div>
                <?php endforeach; ?>
                <p>Total: $<?= number_format($total, 2) ?></p>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Place Order</button>
        <a href="my-cart.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<?php require_once __DIR__ . "/includes/footer.php"; ?>
