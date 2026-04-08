<?php require __DIR__ . '/partials/header.php'; ?>
<main>
    <section class="hero-section">
        <div class="container hero-grid">
            <div class="hero-copy reveal">
                <span class="eyebrow">Plateforme anti-gaspillage</span>
                <h1>Le site FoodLoop, moderne, dynamique et pense pour l’impact reel.</h1>
                <p>
                    Une experience web premium pour connecter commerces, associations et utilisateurs
                    autour d’une redistribution alimentaire plus rapide, plus lisible et plus humaine.
                </p>
                <div class="hero-actions">
                    <a class="btn btn-primary" href="index.php?page=platform">Explorer la plateforme</a>
                    <a class="btn btn-secondary" href="index.php?page=actors">Voir les acteurs</a>
                </div>
                <div class="stats-row">
                    <?php foreach ($heroStats as $stat): ?>
                        <article class="stat-glass">
                            <strong><?php echo htmlspecialchars($stat['value'], ENT_QUOTES, 'UTF-8'); ?></strong>
                            <span><?php echo htmlspecialchars($stat['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="hero-panel reveal reveal-delay">
                <div class="panel-floating">
                    <span class="badge badge-live">Live</span>
                    <span class="badge badge-priority">Priorite association</span>
                </div>
                <h2>Tableau d’activite</h2>
                <div class="signal-card">
                    <p>27 nouveaux lots publies aujourd’hui</p>
                    <strong>+14% vs hier</strong>
                </div>
                <div class="signal-card">
                    <p>3 collectes optimisees par suggestion IA</p>
                    <strong>Avant 19:00</strong>
                </div>
                <div class="signal-card">
                    <p>19 retraits programmes sur la journee</p>
                    <strong>Fluidite elevee</strong>
                </div>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div class="section-heading reveal">
                <span class="eyebrow">Highlights</span>
                <h2>Une interface qui inspire confiance des la premiere visite</h2>
            </div>
            <div class="card-grid three">
                <?php foreach ($highlights as $item): ?>
                    <article class="feature-card reveal">
                        <h3><?php echo htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                        <p><?php echo htmlspecialchars($item['text'], ENT_QUOTES, 'UTF-8'); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="section dark-surface">
        <div class="container showcase-grid">
            <div class="reveal">
                <span class="eyebrow light">Surplus en vedette</span>
                <h2>Des cartes de lots claires, rapides a parcourir et visuellement fortes.</h2>
                <div class="journey-list">
                    <?php foreach ($journey as $index => $step): ?>
                        <div class="journey-item">
                            <span><?php echo $index + 1; ?></span>
                            <p><?php echo htmlspecialchars($step, ENT_QUOTES, 'UTF-8'); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="stacked-cards reveal reveal-delay">
                <?php foreach ($showcaseCards as $card): ?>
                    <article class="showcase-card">
                        <span class="tag-soft"><?php echo htmlspecialchars($card['tag'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <h3><?php echo htmlspecialchars($card['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                        <p><?php echo htmlspecialchars($card['meta'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <a href="index.php?page=platform">Voir le workflow</a>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</main>
<?php require __DIR__ . '/partials/footer.php'; ?>
