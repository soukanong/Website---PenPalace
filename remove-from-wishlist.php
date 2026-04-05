<?php
/**
 * Remove from Wishlist Handler
 * Processes remove from wishlist form submissions from product-detail.php
 * Only for logged-in users
 */

require_once __DIR__ . "/includes/auth.php";
require_once __DIR__ . "/includes/functions.php";

$auth = Auth::getInstance();
$functions = Functions::getInstance();

try {
    // Verify user is logged in
    if (!$auth->isLoggedIn()) {
        header("Location: login.php");
        exit();
    }

    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !$auth->validateCsrfToken($_POST['csrf_token'])) {
        throw new Exception("Invalid request");
    }

    // Validate product_id
    $product_id = filter_var($_POST['product_id'] ?? '', FILTER_VALIDATE_INT);
    if (!$product_id) {
        throw new Exception("Invalid product");
    }

    // Remove from wishlist
    $functions->removeFromWishlist($_SESSION['user_id'], $product_id);

    // Redirect back with success message
    header("Location: product-detail.php?id=$product_id&status=removed");
    exit();

} catch (Exception $e) {
    // Redirect back with error message 
    header("Location: product-detail.php?id=$product_id&error=" . urlencode($e->getMessage()));
    exit();
}
