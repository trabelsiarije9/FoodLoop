<?php require __DIR__ . '/../partials/app_header.php'; ?>
<main>
    <section class="hero-section">
        <div class="container hero-grid">
            <div class="hero-copy reveal">
                <span class="eyebrow">Plateforme complete</span>
                <h1>FoodLoop gere maintenant tout le parcours: accueil, compte, connexion, dashboards et CRUD.</h1>
                <p>Une application PHP claire pour publier des lots, reserver des invendus et piloter la plateforme selon le role connecte.</p>
                <div class="hero-actions">
                    <a class="btn btn-primary" href="<?php echo e(route('register')); ?>">Creer un compte</a>
                    <a class="btn btn-secondary" href="<?php echo e(route('login')); ?>">Se connecter</a>
                </div>
                <div class="stats-row">
                    <article class="stat-glass"><strong>3</strong><span>User simple, association, business owner</span></article>
                    <article class="stat-glass"><strong>CRUD</strong><span>Lots, users, reservations</span></article>
                    <article class="stat-glass"><strong>MVP</strong><span>Accueil public puis interface privee</span></article>
                </div>
            </div>

            <div class="hero-panel reveal reveal-delay">
                <div class="panel-floating">
                    <span class="badge badge-live">Live app</span>
                    <span class="badge badge-priority">CRUD actif</span>
                </div>
                <h2>Ce que le site couvre</h2>
                <div class="signal-card">
                    <p>Inscription et connexion avec redirection selon le role</p>
                    <strong>User simple, association, business owner</strong>
                </div>
                <div class="signal-card">
                    <p>Gestion des lots et des reservations depuis les espaces prives</p>
                    <strong>Vue metier dediee</strong>
                </div>
                <div class="signal-card">
                    <p>Supervision admin avec gestion des comptes et contenus</p>
                    <strong>Controle global</strong>
                </div>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div class="section-heading reveal">
                <span class="eyebrow">Parcours</span>
                <h2>Depuis la page d accueil, chaque profil trouve son interface.</h2>
            </div>
            <div class="card-grid three">
                <article class="feature-card reveal">
                    <h3>Regular User</h3>
                    <p>Consulte les lots publics, reserve et suit ses demandes dans un espace personnel simple.</p>
                </article>
                <article class="feature-card reveal">
                    <h3>Association</h3>
                    <p>Reserve les lots pour son organisation et suit les demandes depuis un dashboard dedie.</p>
                </article>
                <article class="feature-card reveal">
                    <h3>Business Owner</h3>
                    <p>Ajoute, modifie et supprime ses lots alimentaires depuis son tableau de bord.</p>
                </article>
            </div>
        </div>
    </section>

    <section class="section dark-surface">
        <div class="container showcase-grid">
            <div class="reveal">
                <span class="eyebrow light">Fonctionnement</span>
                <h2>Le MVP couvre le cycle complet d une plateforme alimentaire.</h2>
                <div class="journey-list">
                    <div class="journey-item"><span>1</span><p>Le visiteur decouvre l offre FoodLoop et cree un compte.</p></div>
                    <div class="journey-item"><span>2</span><p>Le business owner publie des lots depuis son dashboard.</p></div>
                    <div class="journey-item"><span>3</span><p>Le user simple ou l association reserve depuis son interface.</p></div>
                    <div class="journey-item"><span>4</span><p>Chaque compte arrive automatiquement sur sa bonne page apres connexion.</p></div>
                </div>
            </div>
            <div class="stacked-cards reveal reveal-delay">
                <article class="showcase-card">
                    <span class="tag-soft">Public</span>
                    <h3>Inscription et connexion</h3>
                    <p>Creation de compte avec choix du role et formulaire adapte.</p>
                    <a href="<?php echo e(route('register')); ?>">Commencer</a>
                </article>
                <article class="showcase-card">
                    <span class="tag-soft">Association</span>
                    <h3>Interface dediee</h3>
                    <p>Catalogue association et suivi des reservations de la structure.</p>
                    <a href="<?php echo e(route('register')); ?>">Creer ce compte</a>
                </article>
                <article class="showcase-card">
                    <span class="tag-soft">Business</span>
                    <h3>CRUD des lots alimentaires</h3>
                    <p>Creation, edition et suppression des stocks disponibles.</p>
                    <a href="<?php echo e(route('login')); ?>">Acceder</a>
                </article>
            </div>
        </div>
    </section>

    <?php if ($featuredItems !== []): ?>
        <section class="section">
            <div class="container">
                <div class="section-heading reveal">
                    <span class="eyebrow">Catalogue</span>
                    <h2>Exemples de lots disponibles</h2>
                </div>
                <div class="card-grid three">
                    <?php foreach ($featuredItems as $item): ?>
                        <article class="feature-card reveal">
                            <h3><?php echo e($item['title']); ?></h3>
                            <p><?php echo e($item['organization_name']); ?>, <?php echo e($item['city']); ?></p>
                            <p><?php echo e($item['quantity']); ?> <?php echo e($item['unit']); ?>, retrait avant <?php echo e(format_datetime($item['pickup_end'])); ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>
</main>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>
