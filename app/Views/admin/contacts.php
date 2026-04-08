<?php require __DIR__ . '/../partials/app_header.php'; ?>
<main class="section">
    <div class="container panel-card">
        <h2>Briefs envoyes depuis Contact</h2>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Nom</th><th>Email</th><th>Organisation</th><th>Role</th><th>Message</th><th>Date</th></tr></thead>
                <tbody>
                <?php foreach ($contacts as $contact): ?>
                    <tr>
                        <td><?php echo e($contact['fullname']); ?></td>
                        <td><?php echo e($contact['email']); ?></td>
                        <td><?php echo e($contact['organization'] ?? '-'); ?></td>
                        <td><?php echo e($contact['role_label'] ?? '-'); ?></td>
                        <td><?php echo e($contact['message']); ?></td>
                        <td><?php echo e(format_datetime($contact['created_at'])); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>
