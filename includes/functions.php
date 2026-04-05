<?php
/**
 * Generic Website Functions Handler
 * Manages input validation/sanitization, search, cart, checkout,
 * order status, newsletters, wishlist, and account management
 */

require_once __DIR__ . "/../config/config.php";

class Functions
{
    private $config;
    private $auth;

    public function getConfig()
    {
        return $this->config;
    }

    public function validateCurrentPassword($user_id, $current_password)
    {
        $sql = "SELECT password_hash FROM users WHERE id = ? LIMIT 1";
        $user = $this->config->fetchOne($sql, [$user_id]);

        if (
            !$user ||
            !password_verify($current_password, $user["password_hash"])
        ) {
            return false;
        }
        return true;
    }

    private static $instance = null;

    private function __construct()
    {
        $this->config = Config::getInstance();
        $this->auth = Auth::getInstance();
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Input Validation Methods
     */
    public function sanitizeInput($input)
    {
        if (is_array($input)) {
            return array_map([$this, "sanitizeInput"], $input);
        }
        return htmlspecialchars(trim($input), ENT_QUOTES, "UTF-8");
    }

    public function validateEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public function validatePassword($password)
    {
        // At least 8 chars, 1 uppercase, 1 lowercase, 1 number, 1 special char
        return strlen($password) >= 8 &&
            preg_match("/[A-Z]/", $password) &&
            preg_match("/[a-z]/", $password) &&
            preg_match("/[0-9]/", $password) &&
            preg_match("/[^A-Za-z0-9]/", $password);
    }

    /**
     * Search Methods
     */
    public function searchProducts(
        $query,
        $category_id = null,
        $sort = "",
        $limit = 10,
        $offset = 0,
        $additional_filter = ""
    ) {
        $params = [];
        $sql = "SELECT * FROM products";

        if (!empty($query)) {
            $sql .= " WHERE (name LIKE ? OR description LIKE ?)";
            $params[] = "%" . $query . "%";
            $params[] = "%" . $query . "%";
        }

        if ($category_id !== null) {
            if (!empty($query)) {
                $sql .= " AND category_id = ?";
            } else {
                $sql .= " WHERE category_id = ?";
            }
            $params[] = $category_id;
        }

        if (!empty($additional_filter)) {
            if (!empty($query) || $category_id !== null) {
                $sql .= " AND " . $additional_filter;
            } else {
                $sql .= " WHERE " . $additional_filter;
            }
        }

        switch ($sort) {
            case "newest":
                $sql .= " ORDER BY created_at DESC";
                break;
            case "price_asc":
                $sql .= " ORDER BY price ASC";
                break;
            case "price_desc":
                $sql .= " ORDER BY price DESC";
                break;
            case "name_asc":
                $sql .= " ORDER BY name ASC";
                break;
            case "discount_desc":
                $sql .=
                    " ORDER BY ((price - discount_price) / price) * 100 DESC";
                break;
            case "discount_asc":
                $sql .=
                    " ORDER BY ((price - discount_price) / price) * 100 ASC";
                break;
            default:
                $sql .= " ORDER BY id ASC";
        }

        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        return $this->config->fetchAll($sql, $params);
    }

    /**
     * Get total number of products for pagination
     */
    public function getProductsCount(
        $category_id = null,
        $sort = "",
        $additional_filter = ""
    ) {
        $params = [];
        $sql = "SELECT COUNT(*) as total FROM products";

        if ($category_id !== null || !empty($additional_filter)) {
            $sql .= " WHERE";
            $conditions = [];

            if ($category_id !== null) {
                $conditions[] = "category_id = ?";
                $params[] = $category_id;
            }

            if (!empty($additional_filter)) {
                $conditions[] = $additional_filter;
            }

            $sql .= " " . implode(" AND ", $conditions);
        }

        switch ($sort) {
            case "newest":
                $sql .= " ORDER BY created_at DESC";
                break;
            case "price_asc":
                $sql .= " ORDER BY price ASC";
                break;
            case "price_desc":
                $sql .= " ORDER BY price DESC";
                break;
            case "name_asc":
                $sql .= " ORDER BY name ASC";
                break;
        }

        $result = $this->config->fetchOne($sql, $params);
        return $result["total"];
    }

    public function getAllCategories()
    {
        $sql = "SELECT * FROM categories ORDER BY name ASC";
        return $this->config->fetchAll($sql);
    }

    public function getCategoryByName($name)
    {
        $sql = "SELECT * FROM categories WHERE name = ?";
        return $this->config->fetchOne($sql, [$name]);
    }

    /**
     * Cart Methods
     */
    public function getCart($user_id = null)
    {
        $cart_id = $user_id
            ? $this->getUserCart($user_id)
            : $this->getSessionCart();

        $sql = "SELECT ci.*, p.name, p.price, p.image_url
                FROM cart_items ci
                JOIN products p ON ci.product_id = p.id
                WHERE ci.cart_id = ?";
        return $this->config->fetchAll($sql, [$cart_id]);
    }

    public function getUserCart($user_id)
    {
        $sql =
            "SELECT id FROM carts WHERE user_id = ? AND status = 'active' LIMIT 1";
        $cart = $this->config->fetchOne($sql, [$user_id]);

        if (!$cart) {
            $sql = "INSERT INTO carts (user_id, status) VALUES (?, 'active')";
            return $this->config->insert($sql, [$user_id]);
        }

        return $cart["id"];
    }

    public function getSessionCart()
    {
        if (!isset($_SESSION["cart_id"])) {
            $sql = "INSERT INTO carts (status) VALUES ('active')";
            $_SESSION["cart_id"] = $this->config->insert($sql);
        }
        return $_SESSION["cart_id"];
    }

    public function updateCartItem($cart_id, $product_id, $quantity)
    {
        $sql =
            "UPDATE cart_items SET quantity = ?, updated_at = CURRENT_TIMESTAMP WHERE cart_id = ? AND product_id = ?";
        return $this->config->executeQuery($sql, [
            $quantity,
            $cart_id,
            $product_id,
        ]);
    }

    public function removeCartItem($cart_id, $product_id)
    {
        $sql = "DELETE FROM cart_items WHERE cart_id = ? AND product_id = ?";
        return $this->config->executeQuery($sql, [$cart_id, $product_id]);
    }

    /**
     * Checkout Methods
     */
    public function createOrder(
        $user_id,
        $cart_id,
        $shipping_address,
        $billing_address,
        $payment_info,
        $guest_email = null,
        $create_account = false
    ) {
        try {
            $this->config->beginTransaction();

            // Calculate total amount
            $cart_items = $this->getCart($user_id);
            $total_amount = 0;
            foreach ($cart_items as $item) {
                $total_amount += $item['price'] * $item['quantity'];
            }

            // Create order
            $sql = "INSERT INTO orders (user_id, status, shipping_address, billing_address, total_amount)
                    VALUES (?, 'pending', ?, ?, ?)";
            $order_id = $this->config->insert($sql, [
                $user_id,
                $shipping_address,
                $billing_address,
                $total_amount
            ]);

            // Move cart items to order items
            foreach ($cart_items as $item) {
                $sql = "INSERT INTO order_items (order_id, product_id, quantity, price)
                        VALUES (?, ?, ?, ?)";
                $this->config->executeQuery($sql, [
                    $order_id,
                    $item['product_id'],
                    $item['quantity'],
                    $item['price']
                ]);

                // Update product stock
                $sql = "UPDATE products 
                        SET stock = stock - ? 
                        WHERE id = ? AND stock >= ?";
                $result = $this->config->executeQuery($sql, [
                    $item['quantity'],
                    $item['product_id'],
                    $item['quantity']
                ]);
                
                if ($result->rowCount() === 0) {
                    throw new Exception("Not enough stock available for some items");
                }
            }

            // Process payment
            if (!$this->processPayment($order_id, $payment_info)) {
                throw new Exception("Payment processing failed");
            }

            // Clear cart
            $sql = "DELETE FROM cart_items WHERE cart_id = ?";
            $this->config->executeQuery($sql, [$cart_id]);

            // Mark cart as completed
            $sql = "UPDATE carts SET status = 'completed' WHERE id = ?";
            $this->config->executeQuery($sql, [$cart_id]);

            $this->config->commit();

            if ($create_account && $guest_email) {
                $this->createAccountAfterCheckout($guest_email);
            }

            return $order_id;
        } catch (Exception $e) {
            $this->config->rollback();
            throw $e;
        }
    }

    public function createAccountAfterCheckout($email)
    {
        $password = bin2hex(random_bytes(8)); // Generate a random password
        $sql =
            "INSERT INTO users (email, password_hash, role) VALUES (?, ?, 'user')";
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $this->config->executeQuery($sql, [$email, $password_hash]);

        // Send email with account details
        // Implement your email sending logic here
    }

    private function processPayment($order_id, $payment_info)
    {
        // Implement payment processing logic here
        return true; // Placeholder
    }

    /**
     * Order Status Methods
     */
    public function getOrderStatus($order_id, $email = null)
    {
        $sql = "SELECT o.*, u.email
                FROM orders o
                LEFT JOIN users u ON o.user_id = u.id
                WHERE o.id = ?";
        $params = [$order_id];

        if ($email) {
            $sql .= " AND u.email = ?";
            $params[] = $email;
        }

        return $this->config->fetchOne($sql, $params);
    }

    /**
     * Newsletter Methods
     */
    public function subscribeNewsletter($email)
    {
        if (!$this->validateEmail($email)) {
            throw new Exception("Invalid email address");
        }

        $token = bin2hex(random_bytes(32));
        $sql = "INSERT INTO newsletter_subscribers (email, token)
                VALUES (?, ?)
                ON DUPLICATE KEY UPDATE token = ?";

        return $this->config->executeQuery($sql, [$email, $token, $token]);
    }

    public function handleNewsletterSubscription($email)
    {
        try {
            $this->subscribeNewsletter($email);
            return true;
        } catch (Exception $e) {
            error_log("Newsletter subscription failed: " . $e->getMessage());
            return false;
        }
    }

    public function unsubscribeNewsletter($token)
    {
        $sql = "UPDATE newsletter_subscribers
                SET unsubscribed_at = CURRENT_TIMESTAMP
                WHERE token = ? AND unsubscribed_at IS NULL";
        return $this->config->executeQuery($sql, [$token]);
    }

    /**
     * Wishlist Methods
     */
    public function getWishlist($user_id)
    {
        $sql = "SELECT w.*, p.name, p.price, p.image_url
                FROM wishlist_items w
                JOIN products p ON w.product_id = p.id
                WHERE w.user_id = ?";
        return $this->config->fetchAll($sql, [$user_id]);
    }

    public function addToWishlist($user_id, $product_id)
    {
        $sql =
            "INSERT IGNORE INTO wishlist_items (user_id, product_id) VALUES (?, ?)";
        return $this->config->executeQuery($sql, [$user_id, $product_id]);
    }

    public function removeFromWishlist($user_id, $product_id)
    {
        $sql =
            "DELETE FROM wishlist_items WHERE user_id = ? AND product_id = ?";
        return $this->config->executeQuery($sql, [$user_id, $product_id]);
    }

    /**
     * Account Management Methods
     */
    public function updateAccount($user_id, $data)
    {
        $allowed_fields = ["name", "email", "phone", "password"];
        $updates = [];
        $params = [];

        foreach ($data as $field => $value) {
            if (in_array($field, $allowed_fields)) {
                if ($field === "password") {
                    if (!$this->validatePassword($value)) {
                        throw new Exception("Invalid password format");
                    }
                    $field = "password_hash"; // Change field name to match database column
                    $value = password_hash($value, PASSWORD_DEFAULT);
                }
                if ($field === "email" && !$this->validateEmail($value)) {
                    throw new Exception("Invalid email format");
                }

                $updates[] = "$field = ?";
                $params[] = $value;
            }
        }

        if (empty($updates)) {
            return false;
        }

        $params[] = $user_id;
        $sql = "UPDATE users SET " . implode(", ", $updates) . " WHERE id = ?";
        return $this->config->executeQuery($sql, $params);
    }

    public function exportAccountData($user_id)
    {
        $data = [];

        // Get user details
        $sql = "SELECT name, email, phone, created_at FROM users WHERE id = ?";
        $data["user"] = $this->config->fetchOne($sql, [$user_id]);

        // Get orders
        $sql = "SELECT * FROM orders WHERE user_id = ?";
        $data["orders"] = $this->config->fetchAll($sql, [$user_id]);

        // Get wishlist
        $data["wishlist"] = $this->getWishlist($user_id);

        return json_encode($data, JSON_PRETTY_PRINT);
    }

    public function deleteAccount($user_id)
    {
        try {
            $this->config->beginTransaction();

            // Delete related data
            $tables = ["wishlist_items", "cart_items", "orders", "users"];
            foreach ($tables as $table) {
                $sql = "DELETE FROM $table WHERE ";

                switch ($table) {
                    case "cart_items":
                        $sql .=
                            "cart_id IN (SELECT id FROM carts WHERE user_id = ?)";
                        break;
                    case "users":
                        $sql .= "id = ?";
                        break;
                    default:
                        $sql .= "user_id = ?";
                }

                $this->config->executeQuery($sql, [$user_id]);
            }

            $this->config->commit();
            return true;
        } catch (Exception $e) {
            $this->config->rollback();
            throw $e;
        }
    }

    /**
     * Cart Methods
     */
    public function addToCart($product_id, $quantity)
    {
        try {
            // Validate product and quantity
            if (!$product_id || !$quantity || $quantity < 1) {
                throw new Exception("Invalid product or quantity");
            }

            // Get product details and validate stock
            $product = $this->getProductById($product_id);
            if (!$product || !$product["is_active"]) {
                throw new Exception("Product not found");
            }

            if ($product["stock"] < $quantity) {
                throw new Exception("Not enough stock available");
            }

            // Get cart ID (create if needed)
            $cart_id = $this->auth->isLoggedIn()
                ? $this->getUserCart($_SESSION["user_id"])
                : $this->getSessionCart();

            // Check if product already in cart
            $sql =
                "SELECT quantity FROM cart_items WHERE cart_id = ? AND product_id = ?";
            $existing_item = $this->config->fetchOne($sql, [
                $cart_id,
                $product_id,
            ]);

            if ($existing_item) {
                // Update quantity if already in cart
                $new_quantity = $existing_item["quantity"] + $quantity;
                if ($new_quantity > $product["stock"]) {
                    throw new Exception(
                        "Cannot add more units - stock limit reached"
                    );
                }

                $sql = "UPDATE cart_items
                        SET quantity = ?, updated_at = CURRENT_TIMESTAMP
                        WHERE cart_id = ? AND product_id = ?";
                $this->config->executeQuery($sql, [
                    $new_quantity,
                    $cart_id,
                    $product_id,
                ]);
            } else {
                // Add new item to cart
                $sql = "INSERT INTO cart_items (cart_id, product_id, quantity, price)
                        VALUES (?, ?, ?, ?)";
                $this->config->executeQuery($sql, [
                    $cart_id,
                    $product_id,
                    $quantity,
                    $product["price"],
                ]);
            }

            return true;
        } catch (Exception $e) {
            error_log("Add to cart failed: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get orders for a user
     */
    public function getProductById($product_id)
    {
        $sql = "SELECT p.*, c.name AS category_name
                FROM products p
                JOIN categories c ON p.category_id = c.id
                WHERE p.id = ?";
        return $this->config->fetchOne($sql, [$product_id]);
    }

    public function getRelatedProducts($category_id, $product_id, $limit = 4)
    {
        $sql = "SELECT * FROM products
                WHERE category_id = ? AND id != ?
                ORDER BY RAND()
                LIMIT ?";
        return $this->config->fetchAll($sql, [
            $category_id,
            $product_id,
            $limit,
        ]);
    }

    public function isProductInWishlist($user_id, $product_id)
    {
        $sql = "SELECT COUNT(*) as count
                FROM wishlist_items
                WHERE user_id = ? AND product_id = ?";
        $result = $this->config->fetchOne($sql, [$user_id, $product_id]);
        return $result["count"] > 0;
    }

    public function getOrders($user_id)
    {
        $sql =
            "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC";
        $orders = $this->config->fetchAll($sql, [$user_id]);

        foreach ($orders as &$order) {
            $sql = "SELECT oi.*, p.name, p.price
                    FROM order_items oi
                    JOIN products p ON oi.product_id = p.id
                    WHERE oi.order_id = ?";
            $order["items"] = $this->config->fetchAll($sql, [$order["id"]]);
        }

        return $orders;
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
