<footer class="bg-dark text-white py-4 mt-5">
    <div class="container">
        <div class="row">
            <!-- Logo and About -->
            <div class="col-md-4 mb-3">
                <h5>NoiceFoodie</h5>
                <p class="small">Discover delicious recipes and connect with food enthusiasts.</p>
                <div class="social-links">
                    <a href="https://www.facebook.com/allrecipes" class="text-white me-3"><i class="fab fa-facebook-f"></i></a>
                    <a href="https://www.instagram.com/allrecipes/" class="text-white me-3"><i class="fab fa-instagram"></i></a>
                    <a href="https://x.com/Allrecipes?ref_src=twsrc%5Egoogle%7Ctwcamp%5Eserp%7Ctwgr%5Eauthor" class="text-white"><i class="fab fa-twitter"></i></a>
                </div>
            </div>
            
            <!-- Quick Links -->
            <div class="col-md-4 mb-3">
                <h5>Quick Links</h5>
                <ul class="list-unstyled">
                    <li><a href="<?php echo isset($base_url) ? $base_url : ''; ?>/asm/index.php" class="text-white text-decoration-none">Home</a></li>
                    <li><a href="<?php echo isset($base_url) ? $base_url : ''; ?>/asm/recipes/recipes.php" class="text-white text-decoration-none">Recipes</a></li>
                    <li><a href="<?php echo isset($base_url) ? $base_url : ''; ?>/asm/meal/schedule.php" class="text-white text-decoration-none">Meal Plans</a></li>
                    <li><a href="<?php echo isset($base_url) ? $base_url : ''; ?>/asm/community/community.php" class="text-white text-decoration-none">Community</a></li>
                    <li><a href="<?php echo isset($base_url) ? $base_url : ''; ?>/asm/competition/competitions.php" class="text-white text-decoration-none">Competition</a></li>
                </ul>
            </div>
            
            <!-- Newsletter -->
            <div class="col-md-4">
                <h5>Stay Updated</h5>
                <p class="small">Subscribe for new recipes:</p>
                <form action="<?php echo isset($base_url) ? $base_url : ''; ?>/asm/users/register.php" method="get">
                    <div class="input-group">
                        <input type="email" class="form-control" name="email" placeholder="Your email">
                        <button class="btn btn-primary" type="submit">Subscribe</button>
                    </div>
                </form>
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

<!-- Font Awesome -->
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</body>
</html>