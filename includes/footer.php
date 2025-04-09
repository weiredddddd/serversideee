<footer class="bg-dark text-white py-4 mt-5">
    <div class="container">
        <div class="row">
            <!-- Logo and About -->
            <div class="col-md-4 mb-3">
                <h5>NoiceFoodie</h5>
                <p class="small">Discover delicious recipes and connect with food enthusiasts.</p>
                <div class="social-links">
                    <a href="#" class="text-white me-3"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="text-white me-3"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="text-white"><i class="fab fa-twitter"></i></a>
                </div>
            </div>
            
            <!-- Quick Links -->
            <div class="col-md-4 mb-3">
                <h5>Quick Links</h5>
                <ul class="list-unstyled">
                    <li><a href="<?php echo isset($base_url) ? $base_url : ''; ?>/index.php" class="text-white text-decoration-none">Home</a></li>
                    <li><a href="<?php echo isset($base_url) ? $base_url : ''; ?>/recipes/index.php" class="text-white text-decoration-none">Recipes</a></li>
                    <li><a href="<?php echo isset($base_url) ? $base_url : ''; ?>/community/community.php" class="text-white text-decoration-none">Community</a></li>
                    <li><a href="<?php echo isset($base_url) ? $base_url : ''; ?>/contact.php" class="text-white text-decoration-none">Contact</a></li>
                </ul>
            </div>
            
            <!-- Newsletter -->
            <div class="col-md-4">
                <h5>Stay Updated</h5>
                <p class="small">Subscribe for new recipes:</p>
                <div class="input-group">
                    <input type="email" class="form-control" placeholder="Your email">
                    <button class="btn btn-primary" type="button">Subscribe</button>
                </div>
            </div>
        </div>
        
        <!-- Copyright -->
        <div class="row mt-3 pt-3 border-top">
            <div class="col-md-12 text-center">
                <p class="small mb-0">&copy; <?php echo date('Y'); ?> NoiceFoodie. All rights reserved.</p>
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Font Awesome -->
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</body>
</html>