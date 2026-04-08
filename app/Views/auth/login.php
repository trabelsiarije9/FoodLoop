<?php require __DIR__ . '/../partials/app_header.php'; ?>
<main>
    <section class="page-hero compact">
        <div class="container narrow reveal">
            <span class="eyebrow">Connexion</span>
            <h1>Accede a ton espace FoodLoop.</h1>
            <p>Connecte-toi avec ton compte pour ouvrir ton dashboard ou ton catalogue prive.</p>
        </div>
    </section>

    <section class="section">
        <div class="container form-shell reveal">
            <form class="contact-form-modern auth-form" method="post" action="<?php echo e(route('login')); ?>">
                <input type="hidden" name="_csrf" value="<?php echo e(csrf_token()); ?>">
                <label>Email</label>
                <input type="email" name="email" value="<?php echo e(old('email')); ?>" required>
                <label>Mot de passe</label>
                <input type="password" name="password" required>
                <button class="btn btn-primary" type="submit">Se connecter</button>
                <p class="form-note">Pas encore de compte ? <a href="<?php echo e(route('register')); ?>">Inscris-toi ici</a>.</p>
            </form>
        </div>
    </section>
</main>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>
