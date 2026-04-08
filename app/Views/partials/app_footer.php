    <footer class="site-footer">
        <div class="container footer-shell">
            <div>
                <a class="brand" href="<?php echo e(route('home')); ?>">FoodLoop</a>
                <p class="footer-copy">Plateforme de redistribution alimentaire avec comptes, dashboards et flux CRUD complets.</p>
            </div>
            <div class="footer-links">
                <a href="<?php echo e(route('platform')); ?>">Plateforme</a>
                <a href="<?php echo e(route('actors')); ?>">Roles</a>
                <a href="<?php echo e(route('contact')); ?>">Contact</a>
            </div>
            <p class="status-line" aria-live="polite">MVP web complet en PHP avec espace public, user, business owner et admin.</p>
        </div>
    </footer>
    <script src="<?php echo e(asset('assets/js/app.js')); ?>"></script>
</body>
</html>
