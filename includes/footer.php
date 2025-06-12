<?php
// includes/footer.php
?>
<footer class="footer-custom mt-auto py-3">
    <div class="container-fluid container-xl">
        <div class="row">
            <div class="col-md-4 mb-3">
                <h5>MSGM Bridal</h5>
                <p>Your destination for exquisite bridal, formal, and partywear dresses.</p>
                <p>&copy; <?php echo date('Y'); ?> MSGM Bridal. All rights reserved.</p>
            </div>
            <div class="col-md-2 mb-3">
                <h5>Categories</h5>
                <ul class="list-unstyled">
                    <li><a href="<?php echo BASE_URL; ?>category.php?name=bridal">Bridal</a></li>
                    <li><a href="<?php echo BASE_URL; ?>category.php?name=formal">Formal</a></li>
                    <li><a href="<?php echo BASE_URL; ?>category.php?name=partywear">Partywear</a></li>
                    <!-- Add Trendy and New Arrivals if desired in footer -->
                </ul>
            </div>
            <div class="col-md-2 mb-3">
                <h5>Quick Links</h5>
                <ul class="list-unstyled">
                    <li><a href="<?php echo BASE_URL; ?>index.php">Home</a></li>
                    <li><a href="#">About Us</a></li> <!-- Empty link -->
                    <li><a href="#">Contact Us</a></li> <!-- Empty link -->
                    <li><a href="#">FAQs</a></li> <!-- Empty link -->
                </ul>
            </div>
            <div class="col-md-2 mb-3">
                <h5>Customer Service</h5>
                <ul class="list-unstyled">
                    <li><a href="#">Shipping & Returns</a></li> <!-- Empty link -->
                    <li><a href="#">Privacy Policy</a></li> <!-- Empty link -->
                    <li><a href="#">Terms of Service</a></li> <!-- Empty link -->
                </ul>
            </div>
            <div class="col-md-2 mb-3">
                <h5>Follow Us</h5>
                <ul class="list-unstyled social-icons">
                    <li><a href="#"><i class="fab fa-facebook-f me-2"></i> Facebook</a></li> <!-- Empty link -->
                    <li><a href="#"><i class="fab fa-instagram me-2"></i> Instagram</a></li> <!-- Empty link -->
                    <li><a href="#"><i class="fab fa-twitter me-2"></i> Twitter</a></li> <!-- Empty link -->
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            Designed with <i class="fas fa-heart text-danger"></i> by Your Name/Company
        </div>
    </div>
</footer>

</body>
</html>
