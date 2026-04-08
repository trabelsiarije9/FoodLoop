<?php require __DIR__ . '/../partials/app_header.php'; ?>
<main class="section">
    <div class="container">
        <div class="section-heading">
            <span class="eyebrow">Admin</span>
            <h2>Dashboard administrateur</h2>
        </div>
        <div class="card-grid three">
            <article class="feature-card"><h3>Utilisateurs</h3><p><?php echo e((string) $stats['users']); ?> comptes</p></article>
            <article class="feature-card"><h3>Businesses</h3><p><?php echo e((string) $stats['businesses']); ?> structures</p></article>
            <article class="feature-card"><h3>Reservations</h3><p><?php echo e((string) $stats['reservations']); ?> demandes</p></article>
        </div>

        <div class="dashboard-grid top-gap">
            <section class="panel-card">
                <h2>Derniers utilisateurs</h2>
                <div class="table-wrap">
                    <table class="data-table">
                        <thead><tr><th>Nom</th><th>Email</th><th>Role</th></tr></thead>
                        <tbody>
                        <?php foreach ($users as $account): ?>
                            <tr>
                                <td><?php echo e($account['first_name'] . ' ' . $account['last_name']); ?></td>
                                <td><?php echo e($account['email']); ?></td>
                                <td><?php echo e($account['role_name']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="panel-card">
                <h2>Derniers briefs contact</h2>
                <div class="table-wrap">
                    <table class="data-table">
                        <thead><tr><th>Nom</th><th>Email</th><th>Message</th></tr></thead>
                        <tbody>
                        <?php foreach ($contacts as $contact): ?>
                            <tr>
                                <td><?php echo e($contact['fullname']); ?></td>
                                <td><?php echo e($contact['email']); ?></td>
                                <td><?php echo e($contact['message']); ?></td>
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
