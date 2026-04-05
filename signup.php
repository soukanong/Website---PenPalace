<?php
ob_start();
/**
 * Signup Page
 * Handles new user registration
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

        $name = $functions->sanitizeInput($_POST["name"]);
        $email = $functions->sanitizeInput($_POST["email"]);
        $password = $_POST["password"];
        $confirm_password = $_POST["confirm_password"];

        // Validate input
        if (empty($name) || strlen($name) > 100) {
            throw new Exception("Invalid name");
        }

        if (!$functions->validateEmail($email)) {
            throw new Exception("Invalid email format");
        }

        if (!$functions->validatePassword($password)) {
            throw new Exception(
                "Password must be at least 8 characters long and contain uppercase, lowercase, number, and special character"
            );
        }

        if ($password !== $confirm_password) {
            throw new Exception("Passwords do not match");
        }

        // Create user account
        $sql =
            "INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, 'user')";
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        try {
            $config = Config::getInstance();
            $config->executeQuery($sql, [$name, $email, $password_hash]);

            // Auto login after successful signup
            if ($auth->login($email, $password)) {
                header("Location: index.php");
                exit();
            }
        } catch (PDOException $e) {
            if ($e->getCode() == 23000 && strpos($e->getMessage(), 'Duplicate entry') !== false) {
                throw new Exception("This email address is already registered. Please use a different email or try logging in.");
            }
            throw new Exception("Registration failed. Please try again later.");
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
        <h1>Create Account</h1>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="signup.php" class="auth-form">
            <input type="hidden" name="csrf_token" value="<?= $auth->generateCsrfToken() ?>">

            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text"
                       id="name"
                       name="name"
                       required
                       maxlength="100"
                       autocomplete="name"
                       value="<?= isset($_POST["name"])
                           ? htmlspecialchars($_POST["name"])
                           : "" ?>">
            </div>

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
                       autocomplete="new-password"
                       pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^\da-zA-Z]).{8,}$"
                       title="Must be at least 8 characters long and contain uppercase, lowercase, number, and special character">
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password"
                       id="confirm_password"
                       name="confirm_password"
                       required
                       autocomplete="new-password">
            </div>

            <button type="submit" class="btn btn-primary">Create Account</button>
        </form>

        <div class="auth-links">
            <p>Already have an account? <a href="login.php">Login</a></p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . "/includes/footer.php"; ?>
