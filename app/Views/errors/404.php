<?php require __DIR__ . '/../partials/app_header.php'; ?>
<main>
    <section class="page-hero compact">
        <div class="container narrow reveal">
            <span class="eyebrow">404</span>
            <h1>La page demandee est introuvable.</h1>
            <p>Retourne a l accueil pour continuer la navigation.</p>
            <a class="btn btn-primary" href="<?php echo e(route('home')); ?>">Retour accueil</a>
        </div>
    </section>
</main>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>
