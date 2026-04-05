<?php
/**
 * Static Website Footer
 * Contains footer sections, copyright, social media, payment methods
 */
?>
    </main>
    <footer class="footer">
        <div class="footer-content">
            <?php
            $footer_translations = [
                "en" => [
                    "shop" => [
                        "title" => "Shop",
                        "all_products" => "All Products",
                        "categories" => "Categories",
                        "new_arrivals" => "New Arrivals",
                        "sale" => "Sale",
                    ],
                    "customer_service" => [
                        "title" => "Customer Service",
                        "contact" => "Contact Us",
                        "track_order" => "Track My Order",
                        "shipping" => "Shipping & Returns",
                        "faq" => "FAQ",
                    ],
                    "company" => [
                        "title" => "Company",
                        "about" => "About",
                        "careers" => "Careers",
                        "privacy" => "Privacy Policy",
                        "terms" => "Terms of Service",
                    ],
                ],
                "vi" => [
                    "shop" => [
                        "title" => "Mua Sắm",
                        "all_products" => "Tất Cả Sản Phẩm",
                        "categories" => "Danh Mục",
                        "new_arrivals" => "Hàng Mới Về",
                        "sale" => "Khuyến Mãi",
                    ],
                    "customer_service" => [
                        "title" => "Hỗ Trợ Khách Hàng",
                        "contact" => "Liên Hệ",
                        "track_order" => "Theo Dõi Đơn Hàng",
                        "shipping" => "Vận Chuyển & Đổi Trả",
                        "faq" => "Câu Hỏi Thường Gặp",
                    ],
                    "company" => [
                        "title" => "Công Ty",
                        "about" => "Giới Thiệu",
                        "careers" => "Tuyển Dụng",
                        "privacy" => "Chính Sách Bảo Mật",
                        "terms" => "Điều Khoản Dịch Vụ",
                    ],
                ],
            ];
            $lang = $_SESSION["lang"] ?? "en";
            ?>
            <!-- Shop Section -->
            <div class="footer-section">
                <h3><?= htmlspecialchars(
                    $footer_translations[$lang]["shop"]["title"]
                ) ?></h3>
                <ul>
                    <li><a href="products.php"><?= htmlspecialchars(
                        $footer_translations[$lang]["shop"]["all_products"]
                    ) ?></a></li>
                    <li><a href="categories.php"><?= htmlspecialchars(
                        $footer_translations[$lang]["shop"]["categories"]
                    ) ?></a></li>
                    <li><a href="new-arrivals.php"><?= htmlspecialchars(
                        $footer_translations[$lang]["shop"]["new_arrivals"]
                    ) ?></a></li>
                    <li><a href="sale.php"><?= htmlspecialchars(
                        $footer_translations[$lang]["shop"]["sale"]
                    ) ?></a></li>
                </ul>
            </div>

            <!-- Customer Service Section -->
            <div class="footer-section">
                <h3><?= htmlspecialchars(
                    $footer_translations[$lang]["customer_service"]["title"]
                ) ?></h3>
                <ul>
                    <li><a href="#"><?= htmlspecialchars(
                        $footer_translations[$lang]["customer_service"][
                            "contact"
                        ]
                    ) ?></a></li>
                    <li><a href="track-my-order.php"><?= htmlspecialchars(
                        $footer_translations[$lang]["customer_service"][
                            "track_order"
                        ]
                    ) ?></a></li>
                    <li><a href="#"><?= htmlspecialchars(
                        $footer_translations[$lang]["customer_service"][
                            "shipping"
                        ]
                    ) ?></a></li>
                    <li><a href="#"><?= htmlspecialchars(
                        $footer_translations[$lang]["customer_service"]["faq"]
                    ) ?></a></li>
                </ul>
            </div>

            <!-- Company Section -->
            <div class="footer-section">
                <h3><?= htmlspecialchars(
                    $footer_translations[$lang]["company"]["title"]
                ) ?></h3>
                <ul>
                    <li><a href="#"><?= htmlspecialchars(
                        $footer_translations[$lang]["company"]["about"]
                    ) ?></a></li>
                    <li><a href="#"><?= htmlspecialchars(
                        $footer_translations[$lang]["company"]["careers"]
                    ) ?></a></li>
                    <li><a href="#"><?= htmlspecialchars(
                        $footer_translations[$lang]["company"]["privacy"]
                    ) ?></a></li>
                    <li><a href="#"><?= htmlspecialchars(
                        $footer_translations[$lang]["company"]["terms"]
                    ) ?></a></li>
                </ul>
            </div>

            <!-- Newsletter Section -->
            <div class="footer-section">
                <h3>Stay Connected</h3>
                <form action="subscribe.php" method="GET" class="newsletter-form">
                    <input type="email" name="email" placeholder="Enter your email" required>
                    <button type="submit" class="btn btn-primary">Subscribe</button>
                </form>
            </div>
        </div>

        <!-- Bottom Footer -->
        <div class="footer-bottom">
            <div class="copyright">
                &copy; <?= date("Y") ?> PenPalace. All rights reserved.
            </div>

            <!-- Social Media Icons -->
            <div class="social-media">
                <a href="#" aria-label="Facebook"><i class="ti ti-brand-facebook"></i></a>
                <a href="#" aria-label="Instagram"><i class="ti ti-brand-instagram"></i></a>
                <a href="#" aria-label="Twitter"><i class="ti ti-brand-twitter"></i></a>
            </div>

            <!-- Payment Methods -->
            <div class="payment-methods">
                <i class="ti ti-brand-visa"></i>
                <i class="ti ti-brand-mastercard"></i>
                <i class="ti ti-brand-paypal"></i>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="assets/js/main.js"></script>
</body>
</html>
