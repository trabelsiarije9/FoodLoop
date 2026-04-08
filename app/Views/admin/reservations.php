<?php require __DIR__ . '/../partials/app_header.php'; ?>
<main class="section">
    <div class="container panel-card">
        <h2>Reservations de la plateforme</h2>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Lot</th><th>Utilisateur</th><th>Business</th><th>Statut</th><th>Action</th></tr></thead>
                <tbody>
                <?php foreach ($reservations as $reservation): ?>
                    <tr>
                        <td><?php echo e($reservation['title']); ?></td>
                        <td><?php echo e($reservation['first_name'] . ' ' . $reservation['last_name']); ?></td>
                        <td><?php echo e($reservation['organization_name']); ?></td>
                        <td><?php echo e($reservation['status']); ?></td>
                        <td>
                            <form method="post" action="<?php echo e(route('admin/reservations')); ?>" class="inline-form">
                                <input type="hidden" name="_csrf" value="<?php echo e(csrf_token()); ?>">
                                <input type="hidden" name="id" value="<?php echo e((string) $reservation['id']); ?>">
                                <select name="status">
                                    <option>pending</option><option>approved</option><option>rejected</option><option>picked_up</option><option>completed</option><option>cancelled</option>
                                </select>
                                <button class="btn btn-secondary" type="submit">Mettre a jour</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>
