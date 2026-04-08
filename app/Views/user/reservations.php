<?php require __DIR__ . '/../partials/app_header.php'; ?>
<main class="section">
    <div class="container panel-card">
        <h2>Mes reservations</h2>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Lot</th><th>Business</th><th>Quantite</th><th>Statut</th><th>Date</th></tr></thead>
                <tbody>
                <?php foreach ($reservations as $reservation): ?>
                    <tr>
                        <td><?php echo e($reservation['title']); ?></td>
                        <td><?php echo e($reservation['business_name']); ?></td>
                        <td><?php echo e((string) $reservation['reserved_quantity']); ?> <?php echo e($reservation['unit']); ?></td>
                        <td><?php echo e($reservation['status']); ?></td>
                        <td><?php echo e(format_datetime($reservation['reserved_at'])); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>
