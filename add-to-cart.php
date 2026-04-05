<?php
/**
 * Add to Cart Handler
 * Processes add to cart form submissions from product-detail.php
 * Supports both logged-in users and guests
 */

ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL);

require_once __DIR__ . "/includes/auth.php";
require_once __DIR__ . "/includes/functions.php";

$auth = Auth::getInstance();
$functions = Functions::getInstance();

try {
    // Validate CSRF token
    if (
        !isset($_POST["csrf_token"]) ||
        !$auth->validateCsrfToken($_POST["csrf_token"])
    ) {
        throw new Exception("Invalid request");
    }

    // Get and validate inputs
    $product_id = filter_var($_POST["product_id"] ?? "", FILTER_VALIDATE_INT);
    $quantity = filter_var($_POST["quantity"] ?? "", FILTER_VALIDATE_INT);

    // Add to cart
    $functions->addToCart($product_id, $quantity);

    // Redirect back with success message
    header("Location: product-detail.php?id=$product_id&status=added");
    exit();

} catch (Exception $e) {
    // Redirect back with error message
    header(
        "Location: product-detail.php?id=$product_id&error=" .
            urlencode($e->getMessage())
    );
    exit();
}
