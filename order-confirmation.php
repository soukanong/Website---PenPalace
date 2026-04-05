<?php
ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL);

require_once __DIR__ . "/includes/auth.php";
require_once __DIR__ . "/includes/functions.php";

$auth = Auth::getInstance();
$functions = Functions::getInstance();

// Ensure the page is accessed with a valid order_id and token
if (!isset($_GET['order_id']) || !isset($_GET['token']) || !isset($_SESSION['order_tokens'][$_GET['order_id']]) || $_SESSION['order_tokens'][$_GET['order_id']]['token'] !== $_GET['token'] || time() > $_SESSION['order_tokens'][$_GET['order_id']]['expiry']) {
    header("Location: index.php");
    exit();
}

// Remove the token from the session to make it single-use
unset($_SESSION['order_tokens'][$_GET['order_id']]);

$order_id = $_GET['order_id'];
$order = $functions->getOrderStatus($order_id);

if (!$order) {
    header("Location: index.php");
    exit();
}

$page_title = "Order Confirmation";

require_once __DIR__ . "/includes/header.php";
?>

<div class="order-confirmation-container">
    <h1>Order Confirmation</h1>
    <div class="order-details">
        <div class="thank-you-message">
            <p>Thank you for your order!</p>
        </div>
        <div class="order-header">
            <h2>Order #<?= htmlspecialchars($order['id']) ?></h2>
            <p>Status: <?= htmlspecialchars($order['status']) ?></p>
        </div>
        <div class="order-summary">
            <p>Total Amount: $<?= htmlspecialchars($order['total_amount']) ?></p>
            <p>Shipping Address: <?= htmlspecialchars($order['shipping_address']) ?></p>
            <p>Billing Address: <?= htmlspecialchars($order['billing_address']) ?></p>
        </div>
    </div>
    <a href="index.php" class="btn btn-primary">Return to Home</a>
</div>

<?php require_once "includes/footer.php"; ?>
