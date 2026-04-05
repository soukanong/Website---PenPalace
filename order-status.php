<?php
/**
 * Order Status Page
 * Displays the status of a specific order
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

// Get user details
$user_id = $_SESSION["user_id"];

// Get order ID from query string
if (!isset($_GET["order_id"])) {
    header("Location: my-order.php");
    exit();
}

$order_id = $_GET["order_id"];

// Fetch order status
$order = $functions->getOrderStatus($order_id, $user_id);

if (!$order) {
    header("Location: my-order.php");
    exit();
}

require_once __DIR__ . "/includes/header.php";
?>

<div class="order-status-container">
    <h1>Order Status</h1>

    <div class="order-details">
        <div class="order-header">
            <h2>Order #<?= htmlspecialchars($order["id"]) ?></h2>
            <p>Date: <?= htmlspecialchars($order["created_at"]) ?></p>
            <p>Status: <?= htmlspecialchars($order["status"]) ?></p>
        </div>
        <div class="order-summary">
            <h3>Summary</h3>
            <p>Total: $<?= htmlspecialchars($order["total_amount"]) ?></p>
            <p>Shipping Address: <?= htmlspecialchars(
                $order["shipping_address"]
            ) ?></p>
            <p>Billing Address: <?= htmlspecialchars(
                $order["billing_address"]
            ) ?></p>
        </div>
    </div>

    <a href="index.php" class="btn btn-primary">Return to Store</a>
</div>

<?php require_once __DIR__ . "/includes/footer.php"; ?>
