<?php require __DIR__ . '/../partials/app_header.php'; ?>
<main>
    <section class="page-hero compact">
        <div class="container narrow reveal">
            <span class="eyebrow">Plateforme</span>
            <h1>Une architecture simple pour gerer les comptes, les lots et les reservations.</h1>
            <p>FoodLoop s appuie sur un MVC PHP leger, une base MySQL relationnelle et des vues separees par role.</p>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div class="card-grid three">
                <article class="feature-card reveal"><h3>Authentification</h3><p>Connexion, inscription, session et protection basique des formulaires par token CSRF.</p></article>
                <article class="feature-card reveal"><h3>Interfaces</h3><p>Redirection automatique selon le role vers l espace user simple, association ou business owner.</p></article>
                <article class="feature-card reveal"><h3>CRUD</h3><p>Lots, comptes et reservations sont manipulables depuis les interfaces dediees.</p></article>
            </div>
        </div>
    </section>
</main>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>
