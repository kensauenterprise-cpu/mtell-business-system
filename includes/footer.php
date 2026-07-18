    </main>

    <!-- =========================
         FOOTER
    ========================== -->

    <footer class="footer">

        <div class="container footer-grid">

            <!-- Company -->
            <div class="footer-column">

                <h3>Mtell Online Shopping</h3>

                <p>
                    Shop Smart. Live Better.
                </p>

                <p>
                    Buy smartphones, electronics, accessories,
                    home appliances and much more at affordable prices.
                </p>

            </div>

            <!-- Quick Links -->
            <div class="footer-column">

                <h3>Quick Links</h3>

                <ul>

                    <li><a href="/">Home</a></li>

                    <li><a href="/infinity/shop.php">Shop</a></li>

                    <li><a href="/infinity/about.php">About Us</a></li>

                    <li><a href="/infinity/contact.php">Contact Us</a></li>

                </ul>

            </div>

            <!-- Customer Service -->
            <div class="footer-column">

                <h3>Customer Service</h3>

                <ul>

                    <li><a href="/infinity/customer_login.php">My Account</a></li>

                    <li><a href="/infinity/cart/cart.php">Shopping Cart</a></li>

                    <li><a href="/infinity/privacy.php">Privacy Policy</a></li>

                    <li><a href="/infinity/terms.php">Terms & Conditions</a></li>

                </ul>

            </div>

            <!-- Contact -->
<div class="footer-column">

    <h3>Contact Us</h3>

    <p>
        <i class="fa-solid fa-location-dot"></i>
        Kenya
    </p>

    <p>
        <i class="fa-solid fa-phone"></i>
        <a href="tel:+254106552658">
            +254 106 552 658
        </a>
    </p>

    <p>
        <i class="fa-solid fa-envelope"></i>
        <a href="mailto:kensauenterprise@gmail.com">
            kensauenterprise@gmail.com
        </a>
    </p>

    <div class="social-icons">

        <a href="#" aria-label="Facebook">
            <i class="fab fa-facebook-f"></i>
        </a>

        <a href="#" aria-label="Instagram">
            <i class="fab fa-instagram"></i>
        </a>

        <a href="#" aria-label="X">
            <i class="fa-brands fa-x-twitter"></i>
        </a>

        <a href="#" aria-label="YouTube">
            <i class="fab fa-youtube"></i>
        </a>

    </div>

</div>

        </div>

        <!-- Newsletter -->

        <div class="newsletter-footer">

            <div class="container">

                <h3>Subscribe to Our Newsletter</h3>

                <p>
                    Receive exclusive offers, new arrivals and promotions.
                </p>

                <form action="/infinity/newsletter_subscribe.php" method="POST">

                    <input
                        type="email"
                        name="email"
                        placeholder="Enter your email"
                        required>

                    <button type="submit">

                        Subscribe

                    </button>

                </form>

            </div>

        </div>

        <!-- Bottom Footer -->

        <div class="footer-bottom">

            <div class="container">

                <p>

                    &copy; <?= date('Y'); ?>

                    Mtell Online Shopping.

                    All Rights Reserved.

                </p>

            </div>

        </div>

    </footer>

    <!-- =========================
         WHATSAPP BUTTON
    ========================== -->

    <a
        href="https://wa.me/254106552658"
        class="whatsapp-float"
        target="_blank"
        aria-label="Chat on WhatsApp">

        <i class="fab fa-whatsapp"></i>

    </a>

    <!-- =========================
         BACK TO TOP
    ========================== -->

    <button
        id="backToTop"
        class="back-to-top"
        aria-label="Back to Top">

        <i class="fa-solid fa-arrow-up"></i>

    </button>

    <!-- =========================
         JAVASCRIPT
    ========================== -->

    <script src="/infinity/assets/js/main.js"></script>

</body>

</html>