<?php
/**
 * Track My Order Page
 * Allows guests to track their order status using order number + email or tracking token
 */

ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL);

require_once __DIR__ . "/includes/auth.php";
require_once __DIR__ . "/includes/functions.php";

$auth = Auth::getInstance();
$functions = Functions::getInstance();

$error_message = null;
$order_status = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $order_number = filter_var($_POST["order_number"] ?? "", FILTER_VALIDATE_INT);
    $email = filter_var($_POST["email"] ?? "", FILTER_VALIDATE_EMAIL);
    $tracking_token = filter_var($_POST["tracking_token"] ?? "", FILTER_SANITIZE_STRING);

    if ($order_number && $email) {
        $order_status = $functions->getOrderStatus($order_number, $email);
    } elseif ($tracking_token) {
        $order_status = $functions->getOrderStatusByToken($tracking_token);
    } else {
        $error_message = "Please provide either order number + email or tracking token.";
    }

    if (!$order_status) {
        $error_message = "Order not found. Please check your details.";
    }
}

require_once __DIR__ . "/includes/header.php";
require_once __DIR__ . "/includes/header-nav.php";
?>

<div class="track-order-container">
    <h1>Track My Order</h1>
    <?php if ($error_message): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>
    <form action="track-my-order.php" method="POST" class="track-order-form">
        <div class="form-group">
            <label for="order_number">Order Number:</label>
            <input type="text" id="order_number" name="order_number" required>
        </div>
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div class="form-group">
            <label for="tracking_token">Or Tracking Token:</label>
            <input type="text" id="tracking_token" name="tracking_token">
        </div>
        <button type="submit" class="btn btn-primary">Track Order</button>
    </form>

    <?php if ($order_status): ?>
        <div class="order-status-details">
            <h2>Order Status</h2>
            <p>Order Number: <?= htmlspecialchars($order_status["id"]) ?></p>
            <p>Status: <?= htmlspecialchars($order_status["status"]) ?></p>
            <p>Date: <?= htmlspecialchars($order_status["created_at"]) ?></p>
            <p>Total: $<?= htmlspecialchars($order_status["total_amount"]) ?></p>
            <p>Shipping Address: <?= htmlspecialchars($order_status["shipping_address"]) ?></p>
            <p>Billing Address: <?= htmlspecialchars($order_status["billing_address"]) ?></p>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . "/includes/footer.php"; ?>
