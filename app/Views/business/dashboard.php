<?php require __DIR__ . '/../partials/app_header.php'; ?>
<main class="section">
    <div class="container">
        <div class="section-heading">
            <span class="eyebrow">Business Owner</span>
            <h2><?php echo e($organization['name']); ?></h2>
            <p><?php echo e($organization['city'] . ', ' . $organization['governorate']); ?></p>
        </div>
        <div class="card-grid three">
            <article class="feature-card"><h3>Mes lots</h3><p><?php echo e((string) $stats['items']); ?> lots</p></article>
            <article class="feature-card"><h3>Disponibles</h3><p><?php echo e((string) $stats['available_items']); ?> actifs</p></article>
            <article class="feature-card"><h3>Demandes</h3><p><?php echo e((string) $stats['reservations']); ?> reservations</p></article>
        </div>

        <div class="dashboard-grid top-gap">
            <section class="panel-card">
                <h2>Mes derniers lots</h2>
                <div class="table-wrap">
                    <table class="data-table">
                        <thead><tr><th>Titre</th><th>Quantite</th><th>Statut</th></tr></thead>
                        <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td><?php echo e($item['title']); ?></td>
                                <td><?php echo e((string) $item['quantity']); ?> <?php echo e($item['unit']); ?></td>
                                <td><?php echo e($item['status']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="panel-card">
                <h2>Dernieres reservations</h2>
                <div class="table-wrap">
                    <table class="data-table">
                        <thead><tr><th>Utilisateur</th><th>Lot</th><th>Statut</th></tr></thead>
                        <tbody>
                        <?php foreach ($reservations as $reservation): ?>
                            <tr>
                                <td><?php echo e($reservation['first_name'] . ' ' . $reservation['last_name']); ?></td>
                                <td><?php echo e($reservation['title']); ?></td>
                                <td><?php echo e($reservation['status']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</main>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>
