<?php
/**
 * Authentication and Authorization Handler
 * Manages user sessions, CSRF protection, and rate limiting
 */

require_once __DIR__ . "/../config/config.php";

class Auth
{
    private $config;
    private static $instance = null;
    private const SESSION_NAME = "PENPALACE_SESSION";
    private const SESSION_LIFETIME = 7200; // 2 hours
    private const MAX_ATTEMPTS = 5;
    private const LOCKOUT_TIME = 900; // 15 minutes
    private const CSRF_TOKEN_LENGTH = 32;

    private function __construct()
    {
        $this->config = Config::getInstance();
        $this->initializeSession();
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialize secure session settings
     */
    private function initializeSession()
    {
        if (session_status() === PHP_SESSION_NONE) {
            ini_set("session.use_strict_mode", 1);
            ini_set("session.use_only_cookies", 1);
            ini_set("session.cookie_httponly", 1);
            ini_set("session.cookie_secure", 1);
            ini_set("session.cookie_samesite", "Lax");
            ini_set("session.gc_maxlifetime", self::SESSION_LIFETIME);

            session_name(self::SESSION_NAME);
            session_start();

            // Regenerate session ID periodically
            if (!isset($_SESSION["last_regeneration"])) {
                $this->regenerateSession();
            } elseif (time() - $_SESSION["last_regeneration"] > 1800) {
                // 30 minutes
                $this->regenerateSession();
            }
        }
    }

    /**
     * Regenerate session ID safely
     */
    private function regenerateSession()
    {
        session_regenerate_id(true);
        $_SESSION["last_regeneration"] = time();
    }

    /**
     * Login user with rate limiting
     */
    public function login($email, $password)
    {
        if ($this->isRateLimited($_SERVER["REMOTE_ADDR"])) {
            throw new Exception(
                "Too many login attempts. Please try again later."
            );
        }

        $sql =
            "SELECT id, password_hash, role FROM users WHERE email = ? AND is_active = 1 LIMIT 1";
        $user = $this->config->fetchOne($sql, [$email]);

        if ($user && password_verify($password, $user["password_hash"])) {
            $this->resetLoginAttempts($_SERVER["REMOTE_ADDR"]);

            $_SESSION["user_id"] = $user["id"];
            $_SESSION["role"] = $user["role"];
            $_SESSION["last_activity"] = time();

            $this->regenerateSession();
            return true;
        }

        $this->incrementLoginAttempts($_SERVER["REMOTE_ADDR"]);
        return false;
    }

    /**
     * Logout user
     */
    public function logout()
    {
        $_SESSION = [];

        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), "", time() - 3600, "/");
        }

        session_destroy();
    }

    /**
     * Check if user is logged in
     */
    public function isLoggedIn()
    {
        if (
            !isset($_SESSION["user_id"]) ||
            !isset($_SESSION["last_activity"])
        ) {
            return false;
        }

        if (time() - $_SESSION["last_activity"] > self::SESSION_LIFETIME) {
            $this->logout();
            return false;
        }

        $_SESSION["last_activity"] = time();
        return true;
    }

    /**
     * Check if user is admin
     */
    public function isAdmin()
    {
        return $this->isLoggedIn() &&
            isset($_SESSION["role"]) &&
            $_SESSION["role"] === "admin";
    }

    /**
     * Generate CSRF token
     */
    public function generateCsrfToken()
    {
        if (!isset($_SESSION["csrf_token"])) {
            $_SESSION["csrf_token"] = bin2hex(
                random_bytes(self::CSRF_TOKEN_LENGTH)
            );
        }
        return $_SESSION["csrf_token"];
    }

    /**
     * Validate CSRF token
     */
    public function validateCsrfToken($token)
    {
        if (
            !isset($_SESSION["csrf_token"]) ||
            !hash_equals($_SESSION["csrf_token"], $token)
        ) {
            throw new Exception("Invalid CSRF token");
        }
        return true;
    }

    /**
     * Check if IP is rate limited
     */
    private function isRateLimited($ip)
    {
        $sql =
            "SELECT attempts, last_attempt FROM login_attempts WHERE ip = ? LIMIT 1";
        $attempt = $this->config->fetchOne($sql, [$ip]);

        if (!$attempt) {
            return false;
        }

        if ($attempt["attempts"] >= self::MAX_ATTEMPTS) {
            $lockoutExpires =
                strtotime($attempt["last_attempt"]) + self::LOCKOUT_TIME;
            if (time() < $lockoutExpires) {
                return true;
            }
            $this->resetLoginAttempts($ip);
        }

        return false;
    }

    /**
     * Increment login attempts
     */
    private function incrementLoginAttempts($ip)
    {
        $sql = "INSERT INTO login_attempts (ip, attempts, last_attempt)
                VALUES (?, 1, NOW())
                ON DUPLICATE KEY UPDATE
                attempts = attempts + 1,
                last_attempt = NOW()";
        $this->config->executeQuery($sql, [$ip]);
    }

    /**
     * Reset login attempts
     */
    private function resetLoginAttempts($ip)
    {
        $sql = "DELETE FROM login_attempts WHERE ip = ?";
        $this->config->executeQuery($sql, [$ip]);
    }

    // Prevent cloning of the instance
    private function __clone()
    {
    }

    // Prevent unserialize of the instance
    public function __wakeup()
    {
    }
}
