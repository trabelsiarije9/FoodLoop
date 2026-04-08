<?php require __DIR__ . '/partials/header.php'; ?>
<main>
    <section class="page-hero compact">
        <div class="container narrow reveal">
            <span class="eyebrow">Plateforme</span>
            <h1>Une experience web structuree autour des operations metier.</h1>
            <p>Chaque module de FoodLoop a ete pense pour simplifier la redistribution et rendre les actions plus lisibles.</p>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div class="section-heading reveal">
                <span class="eyebrow">Modules</span>
                <h2>Le coeur fonctionnel de la plateforme</h2>
            </div>
            <div class="card-grid three">
                <?php foreach ($modules as $module): ?>
                    <article class="feature-card reveal">
                        <h3><?php echo htmlspecialchars($module['name'], ENT_QUOTES, 'UTF-8'); ?></h3>
                        <p><?php echo htmlspecialchars($module['text'], ENT_QUOTES, 'UTF-8'); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container workflow-shell">
            <?php foreach ($workflow as $item): ?>
                <article class="workflow-card reveal">
                    <span class="workflow-step"><?php echo htmlspecialchars($item['step'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <h3><?php echo htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                    <p><?php echo htmlspecialchars($item['text'], ENT_QUOTES, 'UTF-8'); ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="section">
        <div class="container metric-band reveal">
            <?php foreach ($metrics as $metric): ?>
                <article class="metric-card-modern">
                    <span><?php echo htmlspecialchars($metric['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <strong><?php echo htmlspecialchars($metric['value'], ENT_QUOTES, 'UTF-8'); ?></strong>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
</main>
<?php require __DIR__ . '/partials/footer.php'; ?>
