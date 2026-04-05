<?php
/**
 * Newsletter Unsubscription Handler
 * Processes unsubscribe requests with a unique token
 */
ob_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . "/includes/functions.php";
$functions = Functions::getInstance();

$page_title = "Unsubscribe from Newsletter";
require_once __DIR__ . "/includes/header.php";

try {
    if (!isset($_GET['token'])) {
        throw new Exception("Invalid unsubscribe link");
    }

    $token = filter_var($_GET['token'], FILTER_SANITIZE_STRING);
    
    if ($functions->unsubscribeNewsletter($token)) {
        echo '<div class="subscribe-container">';
        echo '<div class="subscribe-box">';
        echo "<h1>Unsubscribed Successfully</h1>";
        echo "<p>You have been successfully unsubscribed from our newsletter. We're sorry to see you go!</p>";
        echo '<p>If you change your mind, you can always subscribe again from our website.</p>';
        echo '<a href="index.php" class="btn btn-primary">Return to Store</a>';
        echo "</div>";
        echo "</div>";
    } else {
        throw new Exception("Invalid or expired unsubscribe link");
    }

} catch (Exception $e) {
    echo '<div class="subscribe-container">';
    echo '<div class="subscribe-box">';
    echo "<h1>Error</h1>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo '<a href="index.php" class="btn btn-primary">Return to Store</a>';
    echo "</div>";
    echo "</div>";
}

require_once __DIR__ . "/includes/footer.php";
?>
