<?php
/**
 * My Orders Page
 * Displays orders for logged-in users
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
$orders = $functions->getOrders($user_id);

require_once __DIR__ . "/includes/header.php";
require_once __DIR__ . "/includes/header-nav.php";
?>

<div class="orders-container">
    <h1>My Orders</h1>

    <?php if (empty($orders)): ?>
        <div class="orders-empty">
            <p>You have no orders yet.</p>
            <a href="products.php" class="btn btn-primary">Start Shopping</a>
        </div>
    <?php else: ?>
        <div class="orders-list">
            <?php foreach ($orders as $order): ?>
                <div class="order-card">
                    <div class="order-header">
                        <h2>Order #<?= htmlspecialchars($order["id"]) ?></h2>
                        <p>Date: <?= htmlspecialchars($order["created_at"]) ?></p>
                        <p>Status: <?= htmlspecialchars($order["status"]) ?></p>
                    </div>
                    <div class="order-details">
                        <div class="order-items">
                            <h3>Items</h3>
                            <ul>
                                <?php foreach ($order["items"] as $item): ?>
                                    <li>
                                        <span><?= htmlspecialchars($item["name"]) ?></span>
                                        <span><?= htmlspecialchars($item["quantity"]) ?> x $<?= htmlspecialchars($item["price"]) ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <div class="order-summary">
                            <h3>Summary</h3>
                            <p>Total: $<?= htmlspecialchars($order["total_amount"]) ?></p>
                            <p>Shipping Address: <?= htmlspecialchars($order["shipping_address"]) ?></p>
                            <p>Billing Address: <?= htmlspecialchars($order["billing_address"]) ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . "/includes/footer.php"; ?>
