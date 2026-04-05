<?php
/**
 * Login Page
 * Handles user authentication
 */

ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL);

require_once __DIR__ . "/includes/auth.php";
require_once __DIR__ . "/includes/functions.php";

$auth = Auth::getInstance();
$functions = Functions::getInstance();

// Redirect if already logged in
if ($auth->isLoggedIn()) {
    header("Location: index.php");
    exit();
}

// Process form submission before including headers
$error = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        if (
            !isset($_POST["csrf_token"]) ||
            !$auth->validateCsrfToken($_POST["csrf_token"])
        ) {
            throw new Exception("Invalid request");
        }

        $email = $functions->sanitizeInput($_POST["email"]);
        $password = $_POST["password"];

        if (!$functions->validateEmail($email)) {
            throw new Exception("Invalid email format");
        }

        if ($auth->login($email, $password)) {
            header("Location: index.php");
            exit();
        } else {
            throw new Exception("Invalid credentials");
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Now include header files
require_once __DIR__ . "/includes/header.php";
require_once __DIR__ . "/includes/header-nav.php";
?>

<div class="auth-container">
    <div class="auth-box">
        <h1>Login</h1>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php" class="auth-form">
            <input type="hidden" name="csrf_token" value="<?= $auth->generateCsrfToken() ?>">

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email"
                       id="email"
                       name="email"
                       required
                       autocomplete="email"
                       value="<?= isset($_POST["email"])
                           ? htmlspecialchars($_POST["email"])
                           : "" ?>">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password"
                       id="password"
                       name="password"
                       required
                       autocomplete="current-password">
            </div>

            <button type="submit" class="btn btn-primary">Login</button>
        </form>

        <div class="auth-links">
            <p>Don't have an account? <a href="signup.php">Sign up</a></p>
            <!-- Add password reset link when that functionality is implemented -->
            <!-- <p><a href="/forgot-password.php">Forgot your password?</a></p> -->
        </div>
    </div>
</div>

<?php require_once __DIR__ . "/includes/footer.php"; ?>
