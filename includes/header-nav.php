<?php
/**
 * Static Website Header Navigation
 * Contains the main navigation bar with logo, search, menu items, cart, and account
 */
?>
<header class="header-nav">
    <nav class="nav-container">
        <!-- Left Section: Logo and Search -->
        <div class="nav-left">
            <a href="index.php" class="nav-logo">
                <img src="assets/images/logo.png" alt="PenPalace Logo" height="40">
            </a>
            <button type="button" class="nav-search-toggle" aria-label="Toggle search" onclick="toggleSearchPanel()">
                <i class="ti ti-search"></i>
            </button>
        </div>

        <!-- Middle Section: Main Navigation -->
        <div class="nav-middle">
            <ul class="nav-menu">
                <?php
                $nav_translations = [
                    "en" => [
                        "products" => "Products",
                        "categories" => "Categories",
                        "new_arrivals" => "New Arrivals",
                        "sale" => "Sale",
                    ],
                    "vi" => [
                        "products" => "Sản Phẩm",
                        "categories" => "Danh Mục",
                        "new_arrivals" => "Hàng Mới Về",
                        "sale" => "Khuyến Mãi",
                    ],
                ];
                $lang = $_SESSION["lang"] ?? "en";
                ?>
                <li><a href="products.php"><?= htmlspecialchars(
                    $nav_translations[$lang]["products"]
                ) ?></a></li>
                <li><a href="categories.php"><?= htmlspecialchars(
                    $nav_translations[$lang]["categories"]
                ) ?></a></li>
                <li><a href="new-arrivals.php"><?= htmlspecialchars(
                    $nav_translations[$lang]["new_arrivals"]
                ) ?></a></li>
                <li><a href="sale.php"><?= htmlspecialchars(
                    $nav_translations[$lang]["sale"]
                ) ?></a></li>
            </ul>
        </div>

        <!-- Right Section: Cart, Account, Language -->
        <div class="nav-right">
            <a href="my-cart.php" class="nav-cart" aria-label="Shopping cart">
                <i class="ti ti-shopping-cart"></i>
                <?php if (
                    isset($_SESSION["cart_count"]) &&
                    $_SESSION["cart_count"] > 0
                ): ?>
                    <span class="cart-badge"><?= htmlspecialchars(
                        $_SESSION["cart_count"]
                    ) ?></span>
                <?php endif; ?>
            </a>

            <?php if ($auth->isAdmin()): ?>
                <a href="#" class="nav-admin" aria-label="Admin dashboard">
                    <i class="ti ti-dashboard"></i>
                </a>
            <?php endif; ?>

            <!-- Account Dropdown -->
            <div class="nav-account dropdown">
                <button class="dropdown-toggle" aria-label="Account menu">
                    <i class="ti ti-user"></i>
                </button>
                <div class="dropdown-menu">
                    <?php $account_translations = [
                        "en" => [
                            "my_account" => "My Account",
                            "my_wishlist" => "My Wishlist",
                            "my_order" => "My Order",
                            "logout" => "Logout",
                            "login" => "Login",
                            "signup" => "Signup",
                        ],
                        "vi" => [
                            "my_account" => "Tài Khoản",
                            "my_wishlist" => "Yêu Thích",
                            "my_order" => "Đơn Hàng",
                            "logout" => "Đăng Xuất",
                            "login" => "Đăng Nhập",
                            "signup" => "Đăng Ký",
                        ],
                    ]; ?>

                    <?php if ($auth->isLoggedIn()): ?>
                        <a href="account.php"><?= htmlspecialchars(
                            $account_translations[$lang]["my_account"] ?? ""
                        ) ?></a>
                        <a href="my-wishlist.php"><?= htmlspecialchars(
                            $account_translations[$lang]["my_wishlist"] ?? ""
                        ) ?></a>
                        <a href="my-order.php"><?= htmlspecialchars(
                            $account_translations[$lang]["my_order"] ?? ""
                        ) ?></a>
                        <form action="logout.php" method="POST" class="dropdown-logout">
                            <input type="hidden" name="csrf_token" value="<?= $auth->generateCsrfToken() ?>">
                            <button type="submit"><?= htmlspecialchars(
                                $account_translations[$lang]["logout"] ?? ""
                            ) ?></button>
                        </form>
                    <?php else: ?>
                        <a href="login.php"><?= htmlspecialchars(
                            $account_translations[$lang]["login"] ?? ""
                        ) ?></a>
                        <a href="signup.php"><?= htmlspecialchars(
                            $account_translations[$lang]["signup"] ?? ""
                        ) ?></a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Language Selector -->
            <div class="nav-lang dropdown">
                <button class="dropdown-toggle" aria-label="Language selector">
                    <img src="assets/images/flags/<?= $_SESSION["lang"] ??
                        "en" ?>.svg"
                         alt="<?= $_SESSION["lang"] ?? "en" ?>"
                         width="20"
                         height="15">
                </button>
                <div class="dropdown-menu">
                    <a href="?lang=en" class="<?= !isset($_SESSION["lang"]) ||
                    $_SESSION["lang"] === "en"
                        ? "active"
                        : "" ?>">
                        <img src="assets/images/flags/en.svg" alt="English" width="20" height="15">
                        English
                    </a>
                    <a href="?lang=vi" class="<?= isset($_SESSION["lang"]) &&
                    $_SESSION["lang"] === "vi"
                        ? "active"
                        : "" ?>">
                        <img src="assets/images/flags/vi.svg" alt="Tiếng Việt" width="20" height="15">
                        Tiếng Việt
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Search Panel (Hidden by default) -->
    <div class="search-panel" id="search-panel" style="display: none;">
        <form action="search.php" method="GET" class="search-form">
            <input type="text"
                   name="q"
                   placeholder="Search products..."
                   aria-label="Search products"
                   minlength="2"
                   required>
            <button type="submit" aria-label="Submit search">
                <i class="ti ti-search"></i>
            </button>
            <button type="button" class="search-close" aria-label="Close search" onclick="closeSearchPanel()">
                <i class="ti ti-x"></i>
            </button>
        </form>
    </div>
</header>
