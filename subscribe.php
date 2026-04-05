<?php
/**
 * Newsletter Subscription Confirmation Page
 * Displays a thank you message and a button to return to the store
 */
ob_start();
ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL);

require_once __DIR__ . "/includes/auth.php";
require_once __DIR__ . "/includes/functions.php";

$auth = Auth::getInstance();
$functions = Functions::getInstance();

if (isset($_GET["email"])) {
    $email = $_GET["email"];
    if ($functions->handleNewsletterSubscription($email)) {
        $page_title = "Thank You for Subscribing";
        require_once __DIR__ . "/includes/header.php";
        require_once __DIR__ . "/includes/header-nav.php";
        echo '<div class="subscribe-container">';
        echo '<div class="subscribe-box">';
        echo "<h1>Thank You for Subscribing!</h1>";
        echo "<p>You have successfully subscribed to our newsletter. Stay tuned for the latest updates, promotions, and more.</p>";
        echo '<a href="index.php" class="btn btn-primary">Return to Store</a>';
        echo "</div>";
        echo "</div>";
        require_once __DIR__ . "/includes/footer.php";
    } else {
        header("Location: index.php");
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}
?>
