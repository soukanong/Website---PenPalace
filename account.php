<?php
/**
 * My Account Dashboard
 * Provides account management functionality for logged-in users
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
$sql = "SELECT name, email, phone FROM users WHERE id = ?";
$user = $functions->getConfig()->fetchOne($sql, [$user_id]);

if (!$user) {
    error_log("User not found for ID: " . $user_id);
    throw new Exception("User not found");
}

// Handle form submissions
$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        if (
            !isset($_POST["csrf_token"]) ||
            !$auth->validateCsrfToken($_POST["csrf_token"])
        ) {
            throw new Exception("Invalid request");
        }

        $action = $_POST["action"] ?? "";

        switch ($action) {
            case "update_account":
                $data = [
                    "name" => $functions->sanitizeInput($_POST["name"]),
                    "email" => $functions->sanitizeInput($_POST["email"]),
                    "phone" => $functions->sanitizeInput($_POST["phone"]),
                ];

                try {
                    if ($functions->updateAccount($user_id, $data)) {
                        $success = "Account details updated successfully";
                        $user = $functions
                            ->getConfig()
                            ->fetchOne($sql, [$user_id]); // Refresh user data
                    }
                } catch (Exception $e) {
                    if (strpos($e->getMessage(), "Duplicate entry") !== false) {
                        throw new Exception(
                            "This email address is already in use by another account. Please use a different email address."
                        );
                    }
                    throw $e;
                }
                break;

            case "update_password":
                if (
                    empty($_POST["current_password"]) ||
                    empty($_POST["new_password"]) ||
                    empty($_POST["confirm_password"])
                ) {
                    throw new Exception("All password fields are required");
                }

                if (
                    !$functions->validateCurrentPassword(
                        $user_id,
                        $_POST["current_password"]
                    )
                ) {
                    throw new Exception("Current password is incorrect");
                }

                if ($_POST["new_password"] !== $_POST["confirm_password"]) {
                    throw new Exception("New passwords do not match");
                }

                if (!$functions->validatePassword($_POST["new_password"])) {
                    throw new Exception(
                        "Password must be at least 8 characters long and contain uppercase, lowercase, number, and special character"
                    );
                }

                $data = ["password" => $_POST["new_password"]];
                if ($functions->updateAccount($user_id, $data)) {
                    $success = "Password updated successfully";
                }
                break;

            case "export_data":
                if (
                    !$functions->validateCurrentPassword(
                        $user_id,
                        $_POST["download_password"]
                    )
                ) {
                    throw new Exception("Password is incorrect");
                }
                $data = $functions->exportAccountData($user_id);
                header("Content-Type: application/json");
                header(
                    'Content-Disposition: attachment; filename="account_data.json"'
                );
                echo $data;
                exit();

            case "delete_account":
                if (
                    !$functions->validateCurrentPassword(
                        $user_id,
                        $_POST["delete_password"]
                    )
                ) {
                    throw new Exception("Password is incorrect");
                }
                if ($functions->deleteAccount($user_id)) {
                    $auth->logout();
                    header("Location: index.php");
                    exit();
                }
                break;
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

require_once __DIR__ . "/includes/header.php";
require_once __DIR__ . "/includes/header-nav.php";
?>

<div class="account-container">
    <aside class="account-sidebar">
        <h2>My Account</h2>
        <nav>
            <ul>
                <li class="active"><a href="#details">Account Details</a></li>
                <li><a href="#password">Change Password</a></li>
                <li><a href="#addresses">Addresses</a></li>
                <li><a href="#payment">Payment Methods</a></li>
                <li><a href="#download">Download Data</a></li>
                <li class="danger"><a href="#delete">Delete Account</a></li>
            </ul>
        </nav>
    </aside>

    <main class="account-content">
        <?php if ($success): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- Account Details Section -->
        <section id="details" class="account-section">
            <h3>Account Details</h3>
            <form method="POST" class="account-form">
                <input type="hidden" name="csrf_token" value="<?= $auth->generateCsrfToken() ?>">
                <input type="hidden" name="action" value="update_account">

                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text"
                           id="name"
                           name="name"
                           value="<?= htmlspecialchars($user["name"]) ?>"
                           required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email"
                           id="email"
                           name="email"
                           value="<?= htmlspecialchars($user["email"]) ?>"
                           required>
                </div>

                <div class="form-group">
                    <label for="phone">Phone (optional)</label>
                    <input type="tel"
                           id="phone"
                           name="phone"
                           value="<?= htmlspecialchars(
                               $user["phone"] ?? ""
                           ) ?>">
                </div>

                <button type="submit" class="btn btn-primary">Update Details</button>
            </form>
        </section>

        <!-- Change Password Section -->
        <section id="password" class="account-section">
            <h3>Change Password</h3>
            <form method="POST" class="account-form">
                <input type="hidden" name="csrf_token" value="<?= $auth->generateCsrfToken() ?>">
                <input type="hidden" name="action" value="update_password">

                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <input type="password"
                           id="current_password"
                           name="current_password"
                           required>
                </div>

                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password"
                           id="new_password"
                           name="new_password"
                           pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^\da-zA-Z]).{8,}$"
                           title="Must be at least 8 characters long and contain uppercase, lowercase, number, and special character"
                           required>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password"
                           id="confirm_password"
                           name="confirm_password"
                           required>
                </div>

                <button type="submit" class="btn btn-primary">Change Password</button>
            </form>
        </section>

        <!-- Download Data Section -->
        <section id="download" class="account-section">
            <h3>Download Your Data</h3>
            <p>Download a copy of your personal data including account details, orders, and wishlist.</p>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= $auth->generateCsrfToken() ?>">
                <input type="hidden" name="action" value="export_data">
                <div class="form-group">
                    <label for="download_password">Password</label>
                    <input type="password" id="download_password" name="download_password" required>
                </div>
                <button type="submit" class="btn btn-secondary">Download Data</button>
            </form>
        </section>

        <!-- Delete Account Section -->
        <section id="delete" class="account-section">
            <h3>Delete Account</h3>
            <div class="alert alert-warning">
                <strong>Warning:</strong> This action cannot be undone. All your data will be permanently deleted.
            </div>
            <form method="POST" onsubmit="return confirm('Are you sure you want to delete your account? This action cannot be undone.');">
                <input type="hidden" name="csrf_token" value="<?= $auth->generateCsrfToken() ?>">
                <input type="hidden" name="action" value="delete_account">
                <div class="form-group">
                    <label for="delete_password">Password</label>
                    <input type="password" id="delete_password" name="delete_password" required>
                </div>
                <button type="submit" class="btn btn-danger">Delete My Account</button>
            </form>
        </section>
    </main>
</div>

<?php require_once __DIR__ . "/includes/footer.php"; ?>
