<?php require __DIR__ . '/../partials/app_header.php'; ?>
<main>
    <section class="page-hero compact">
        <div class="container narrow reveal">
            <span class="eyebrow">Contact</span>
            <h1>Une base solide pour continuer a faire evoluer FoodLoop.</h1>
            <p>Tu peux maintenant brancher de vraies notifications, un workflow plus avance ou un module association ensuite.</p>
        </div>
    </section>

    <section class="section">
        <div class="container contact-layout">
            <div class="contact-info reveal">
                <div class="card-grid one">
                    <article class="contact-card"><h3>Email projet</h3><p>contact@foodloop.tn</p></article>
                    <article class="contact-card"><h3>Stack</h3><p>PHP, MySQL, MVC maison, sessions, formulaires</p></article>
                    <article class="contact-card"><h3>Livrable</h3><p>Accueil, auth, user area, business dashboard et admin dashboard</p></article>
                </div>
            </div>

            <form class="contact-form-modern reveal reveal-delay" method="post" action="<?php echo e(route('contact')); ?>">
                <input type="hidden" name="_csrf" value="<?php echo e(csrf_token()); ?>">
                <div class="field-row">
                    <input type="text" name="fullname" placeholder="Nom complet" value="<?php echo e(old('fullname')); ?>">
                    <input type="email" name="email" placeholder="Adresse email" value="<?php echo e(old('email')); ?>">
                </div>
                <div class="field-row">
                    <input type="text" name="organization" placeholder="Organisation" value="<?php echo e(old('organization')); ?>">
                    <select name="role">
                        <option value="">Type d acteur</option>
                        <?php foreach (['Business Owner', 'Association', 'Regular User'] as $role): ?>
                            <option value="<?php echo e($role); ?>" <?php echo old('role') === $role ? 'selected' : ''; ?>><?php echo e($role); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <textarea name="message" rows="6" placeholder="Quels modules veux-tu ajouter ensuite ?"><?php echo e(old('message')); ?></textarea>
                <button class="btn btn-primary" type="submit">Envoyer le brief</button>
            </form>
        </div>
    </section>
</main>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>
