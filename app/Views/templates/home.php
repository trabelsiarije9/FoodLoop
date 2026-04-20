<?php
declare(strict_types=1);

$escape = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
$selectedActor = $currentRole['key'];
$buildUrl = static function (array $overrides = []) use ($filters, $selectedActor): string {
    $query = [
        'actor' => $overrides['actor'] ?? $selectedActor,
        'q' => $overrides['q'] ?? $filters['q'],
        'category' => $overrides['category'] ?? $filters['category'],
        'zone' => $overrides['zone'] ?? $filters['zone'],
    ];

    $query = array_filter(
        $query,
        static fn (mixed $value): bool => $value !== null && $value !== ''
    );

    return '?' . http_build_query($query);
};
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $escape($platform['name']); ?> | Modele UML connecte</title>
    <meta name="description" content="<?= $escape($platform['description']); ?>">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="site-header">
        <div class="container nav">
            <a class="brand" href="#top">FoodLoop</a>
            <nav class="nav-links" aria-label="Navigation principale">
                <a href="#roles">Roles</a>
                <a href="#annonces">Annonces</a>
                <a href="#workflow">Workflow</a>
                <a href="#uml">UML + DB</a>
            </nav>
            <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="mobile-menu">
                <span></span>
                <span></span>
            </button>
        </div>
        <nav class="mobile-menu" id="mobile-menu" aria-label="Navigation mobile">
            <a href="#roles">Roles</a>
            <a href="#annonces">Annonces</a>
            <a href="#workflow">Workflow</a>
            <a href="#uml">UML + DB</a>
        </nav>
    </header>

    <main id="top">
        <section class="hero">
            <div class="container hero-grid">
                <div class="hero-copy">
                    <p class="eyebrow">Refonte UML + base de donnees</p>
                    <h1><?= $escape($platform['tagline']); ?></h1>
                    <p class="lead"><?= $escape($platform['headline']); ?></p>
                    <p class="sublead"><?= $escape($platform['description']); ?></p>
                    <div class="hero-actions">
                        <a class="button button-primary" href="#annonces">Explorer les annonces</a>
                        <a class="button button-secondary" href="#uml">Verifier la couverture UML</a>
                    </div>
                    <ul class="hero-stats">
                        <?php foreach ($platform['stats'] as $stat): ?>
                            <li>
                                <strong><?= $escape($stat['value']); ?></strong>
                                <span><?= $escape($stat['label']); ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <aside class="dashboard-card reveal-target">
                    <div class="dashboard-header">
                        <div>
                            <p class="card-label">Etat courant</p>
                            <h2><?= $escape($currentRole['label']); ?></h2>
                        </div>
                        <span><?= $currentRole['connected'] ? 'authentifie' : 'public'; ?></span>
                    </div>
                    <p class="dashboard-text"><?= $escape($currentActor['resume']); ?></p>
                    <div class="actor-highlight">
                        <strong><?= $escape($currentActor['nom_affiche']); ?></strong>
                        <span><?= $escape($currentActor['detail_principal']); ?></span>
                    </div>
                    <p class="card-note">Reference de demo: <?= $escape($referenceNow); ?>. Le role invite peut consulter et rechercher, mais ne reserve rien.</p>
                    <div class="actor-switches">
                        <?php foreach ($roles as $role): ?>
                            <a class="actor-switch <?= $role['key'] === $selectedActor ? 'is-active' : ''; ?>" href="<?= $escape($buildUrl(['actor' => $role['key']])); ?>">
                                <?= $escape($role['label']); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </aside>
            </div>
        </section>

        <section class="section" id="roles">
            <div class="container">
                <div class="section-heading">
                    <p class="eyebrow">Regles d acces</p>
                    <h2>Le projet distingue l invite non connecte et les profils authentifies du diagramme.</h2>
                </div>
                <div class="role-grid">
                    <?php foreach ($roles as $role): ?>
                        <article class="role-card reveal-target <?= $role['key'] === $selectedActor ? 'is-active' : ''; ?>">
                            <div class="role-top">
                                <span class="role-badge"><?= $escape($role['badge']); ?></span>
                                <strong><?= $escape($role['label']); ?></strong>
                            </div>
                            <p><?= $escape($role['summary']); ?></p>
                            <div class="role-columns">
                                <div>
                                    <h3>Actions</h3>
                                    <ul>
                                        <?php foreach ($role['actions'] as $action): ?>
                                            <li><?= $escape($action); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                                <div>
                                    <h3>Limites</h3>
                                    <ul>
                                        <?php foreach ($role['restrictions'] as $restriction): ?>
                                            <li><?= $escape($restriction); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="section accent-section" id="annonces">
            <div class="container">
                <div class="section-heading">
                    <p class="eyebrow">Recherche et consultation</p>
                    <h2>Les annonces viennent de la base MySQL et respectent categories, zones et proprietaires du diagramme.</h2>
                </div>

                <form class="filter-form reveal-target" method="get">
                    <input type="hidden" name="actor" value="<?= $escape($selectedActor); ?>">
                    <label>
                        <span>Recherche</span>
                        <input type="search" name="q" value="<?= $escape($filters['q']); ?>" placeholder="titre, type, commerce, localisation">
                    </label>
                    <label>
                        <span>Categorie</span>
                        <select name="category">
                            <option value="">Toutes</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $escape((string) $category['id_cat']); ?>" <?= $filters['category'] === (int) $category['id_cat'] ? 'selected' : ''; ?>>
                                    <?= $escape($category['nom']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>
                        <span>Zone</span>
                        <select name="zone">
                            <option value="">Toutes</option>
                            <?php foreach ($zones as $zone): ?>
                                <option value="<?= $escape((string) $zone['id_zone']); ?>" <?= $filters['zone'] === (int) $zone['id_zone'] ? 'selected' : ''; ?>>
                                    <?= $escape($zone['nom']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <button class="button button-primary" type="submit">Filtrer</button>
                </form>

                <?php if ($preferences !== []): ?>
                    <div class="info-strip reveal-target">
                        <strong>Filtres memorises pour <?= $escape($preferences['utilisateur']); ?></strong>
                        <span>Categories: <?= $escape($preferences['categories'] ?? ''); ?> | Zones: <?= $escape($preferences['zones'] ?? ''); ?></span>
                    </div>
                <?php endif; ?>

                <div class="listing-grid">
                    <?php foreach ($announcements as $announcement): ?>
                        <article class="listing-card reveal-target">
                            <div class="listing-top">
                                <p><?= $escape($announcement['categorie_nom']); ?></p>
                                <span><?= $escape($announcement['statut']); ?></span>
                            </div>
                            <h3><?= $escape($announcement['titre']); ?></h3>
                            <p class="listing-owner"><?= $escape($announcement['nom_commerce']); ?> | <?= $escape($announcement['type_commerce']); ?></p>
                            <dl class="listing-meta">
                                <div>
                                    <dt>Quantite</dt>
                                    <dd><?= $escape((string) $announcement['quantite']); ?> <?= $escape($announcement['unite']); ?></dd>
                                </div>
                                <div>
                                    <dt>Zone</dt>
                                    <dd><?= $escape($announcement['zone_nom']); ?>, <?= $escape($announcement['ville_nom']); ?></dd>
                                </div>
                                <div>
                                    <dt>Localisation</dt>
                                    <dd><?= $escape($announcement['localisation']); ?></dd>
                                </div>
                                <div>
                                    <dt>Visible utilisateur</dt>
                                    <dd><?= $escape($announcement['date_visibilite_utilisateur']); ?></dd>
                                </div>
                                <div>
                                    <dt>Expiration</dt>
                                    <dd><?= $escape($announcement['date_expiration']); ?></dd>
                                </div>
                                <div>
                                    <dt>Type aliment</dt>
                                    <dd><?= $escape($announcement['type_aliment']); ?></dd>
                                </div>
                            </dl>
                            <button class="action-pill action-pill-<?= $announcement['action']['tone']; ?>" type="button" <?= $announcement['action']['enabled'] ? '' : 'disabled'; ?>>
                                <?= $escape($announcement['action']['label']); ?>
                            </button>
                        </article>
                    <?php endforeach; ?>
                </div>

                <?php if ($consultations !== []): ?>
                    <div class="consultation-card reveal-target">
                        <div class="section-heading compact">
                            <p class="eyebrow">Consultations utilisateur</p>
                            <h2>Historique recent de la relation Utilisateur consulte Annonce.</h2>
                        </div>
                        <div class="timeline-list">
                            <?php foreach ($consultations as $consultation): ?>
                                <article>
                                    <strong><?= $escape($consultation['titre']); ?></strong>
                                    <span><?= $escape($consultation['categorie_nom']); ?> | <?= $escape($consultation['zone_nom']); ?></span>
                                    <time><?= $escape($consultation['date_consultation']); ?></time>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <section class="section" id="workflow">
            <div class="container">
                <div class="section-heading">
                    <p class="eyebrow">Reservation, paiement, distribution</p>
                    <h2>Le workflow relie les classes Reservation, Paiement, Recu et Distribution avec les bons types d acteurs.</h2>
                </div>
                <div class="workflow-grid">
                    <?php foreach ($reservationFlow as $reservation): ?>
                        <article class="workflow-card reveal-target">
                            <span class="workflow-step">Reservation #<?= $escape((string) $reservation['id_reservation']); ?></span>
                            <h3><?= $escape($reservation['annonce_titre']); ?></h3>
                            <p><?= $escape($reservation['demandeur']); ?> | <?= $escape($reservation['demandeur_type']); ?></p>
                            <dl class="listing-meta compact-meta">
                                <div>
                                    <dt>Statut</dt>
                                    <dd><?= $escape($reservation['statut']); ?></dd>
                                </div>
                                <div>
                                    <dt>Quantite</dt>
                                    <dd><?= $escape((string) $reservation['quantite_reservee']); ?></dd>
                                </div>
                                <div>
                                    <dt>Pickup</dt>
                                    <dd><?= $escape($reservation['date_pickup']); ?></dd>
                                </div>
                                <div>
                                    <dt>Paiement</dt>
                                    <dd><?= $escape($reservation['paiement_statut'] ?? 'non requis'); ?></dd>
                                </div>
                            </dl>
                            <p class="workflow-note">Montant: <?= $escape($reservation['montant'] !== null ? (string) $reservation['montant'] : '0'); ?> | Methode: <?= $escape($reservation['methode_paiement'] ?? 'aucune'); ?></p>
                            <div class="tag-list">
                                <?php foreach ($reservation['distribution_statuts'] as $status): ?>
                                    <span><?= $escape($status); ?></span>
                                <?php endforeach; ?>
                                <?php if ($reservation['date_emission'] !== null): ?>
                                    <span>recu <?= $escape($reservation['date_emission']); ?></span>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="section analytics-section">
            <div class="container twin-grid">
                <div class="stack-card reveal-target">
                    <div class="section-heading compact">
                        <p class="eyebrow">Notifications</p>
                        <h2>Flux personnalise selon le role connecte.</h2>
                    </div>
                    <?php if ($notifications === []): ?>
                        <p class="empty-state">Aucune notification personnelle dans ce contexte. C est normal pour l invite et le superadmin.</p>
                    <?php else: ?>
                        <div class="timeline-list">
                            <?php foreach ($notifications as $notification): ?>
                                <article>
                                    <strong><?= $escape($notification['message_notification']); ?></strong>
                                    <span><?= $escape($notification['annonce_titre'] ?? 'sans annonce'); ?></span>
                                    <time><?= $escape($notification['date_envoi']); ?> | <?= $notification['est_lue'] ? 'lue' : 'non lue'; ?></time>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="stack-card reveal-target">
                    <div class="section-heading compact">
                        <p class="eyebrow">Suggestion IA</p>
                        <h2>Les suggestions sont generees depuis les annonces et concernent commerces ou associations.</h2>
                    </div>
                    <div class="timeline-list">
                        <?php foreach ($suggestions as $suggestion): ?>
                            <article>
                                <strong><?= $escape($suggestion['annonce_titre']); ?></strong>
                                <span><?= $escape($suggestion['nom_commerce']); ?><?php if (!empty($suggestion['nom_association'])): ?> | <?= $escape($suggestion['nom_association']); ?><?php endif; ?></span>
                                <p><?= $escape($suggestion['message']); ?></p>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </section>

        <section class="section" id="uml">
            <div class="container">
                <div class="section-heading">
                    <p class="eyebrow">Rapports, base et couverture UML</p>
                    <h2>Chaque table et chaque section de l interface ont ete rattachees a une ou plusieurs classes du diagramme.</h2>
                </div>

                <div class="twin-grid">
                    <div class="analytics-panel reveal-target">
                        <?php foreach ($reports as $report): ?>
                            <article>
                                <strong>Rapport #<?= $escape((string) $report['id_rapport']); ?></strong>
                                <span><?= $escape($report['report_type']); ?> | <?= $escape($report['periode']); ?></span>
                                <p><?= $escape((string) $report['total_food_saved_kg']); ?> kg sauves | <?= $escape((string) $report['total_reservations']); ?> reservations | <?= $escape((string) $report['total_distributions']); ?> distributions</p>
                                <p>Consultations commerce: <?= $escape((string) $report['lectures_commerce']); ?> | superadmin: <?= $escape((string) $report['lectures_super_admin']); ?></p>
                            </article>
                        <?php endforeach; ?>
                    </div>

                    <div class="security-card reveal-target">
                        <ul class="security-list">
                            <?php foreach ($umlNotes as $note): ?>
                                <li><?= $escape($note); ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <div class="coverage-list">
                            <?php foreach ($databaseCoverage as $entry): ?>
                                <article>
                                    <strong><?= $escape($entry['table']); ?></strong>
                                    <span><?= $escape($entry['description']); ?></span>
                                    <em><?= $escape((string) $entry['rows']); ?> lignes</em>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="uml-grid">
                    <?php foreach ($umlClasses as $class): ?>
                        <article class="uml-card reveal-target">
                            <div class="uml-top">
                                <strong><?= $escape($class['name']); ?></strong>
                                <span><?= $escape($class['table']); ?></span>
                            </div>
                            <div class="uml-section">
                                <h3>Attributs</h3>
                                <ul>
                                    <?php foreach ($class['attributes'] as $attribute): ?>
                                        <li><?= $escape($attribute); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <div class="uml-section">
                                <h3>Operations</h3>
                                <ul>
                                    <?php foreach ($class['operations'] as $operation): ?>
                                        <li><?= $escape($operation); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    </main>

    <footer class="site-footer">
        <div class="container footer-content">
            <p>FoodLoop aligne sur le diagramme de classes + invite non connecte.</p>
            <p>Base auto-initialisee via MySQL et PDO.</p>
        </div>
    </footer>

    <script src="assets/js/app.js"></script>
</body>
</html>
