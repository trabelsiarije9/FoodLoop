<?php require __DIR__ . '/partials/header.php'; ?>
<main>
    <section class="page-hero compact">
        <div class="container narrow reveal">
            <span class="eyebrow">Acteurs</span>
            <h1>Trois profils, une seule chaine de valeur solidaire.</h1>
            <p>Le design met en avant les responsabilites et la priorite de chaque type d’utilisateur.</p>
        </div>
    </section>

    <section class="section">
        <div class="container tabs-shell reveal">
            <div class="role-tabs">
                <?php foreach ($roles as $index => $role): ?>
                    <button class="role-tab<?php echo $index === 0 ? ' is-active' : ''; ?>" type="button" data-role-target="<?php echo htmlspecialchars($role['id'], ENT_QUOTES, 'UTF-8'); ?>">
                        <?php echo htmlspecialchars($role['name'], ENT_QUOTES, 'UTF-8'); ?>
                    </button>
                <?php endforeach; ?>
            </div>
            <div class="role-panels">
                <?php foreach ($roles as $index => $role): ?>
                    <article class="role-panel<?php echo $index === 0 ? ' is-active' : ''; ?>" data-role-panel="<?php echo htmlspecialchars($role['id'], ENT_QUOTES, 'UTF-8'); ?>">
                        <p class="panel-kicker"><?php echo htmlspecialchars($role['headline'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <h2><?php echo htmlspecialchars($role['name'], ENT_QUOTES, 'UTF-8'); ?></h2>
                        <p><?php echo htmlspecialchars($role['text'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <ul class="check-list">
                            <?php foreach ($role['points'] as $point): ?>
                                <li><?php echo htmlspecialchars($point, ENT_QUOTES, 'UTF-8'); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div class="section-heading reveal">
                <span class="eyebrow">Questions frequentes</span>
                <h2>Une UX qui explique vite et rassure.</h2>
            </div>
            <div class="faq-grid">
                <?php foreach ($faq as $item): ?>
                    <article class="faq-card reveal">
                        <h3><?php echo htmlspecialchars($item['q'], ENT_QUOTES, 'UTF-8'); ?></h3>
                        <p><?php echo htmlspecialchars($item['a'], ENT_QUOTES, 'UTF-8'); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</main>
<?php require __DIR__ . '/partials/footer.php'; ?>
