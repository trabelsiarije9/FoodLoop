<?php require __DIR__ . '/../partials/app_header.php'; ?>
<main class="section">
    <div class="container panel-card">
        <h2>Lots de toute la plateforme</h2>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Titre</th><th>Business</th><th>Categorie</th><th>Statut</th><th>Action</th></tr></thead>
                <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?php echo e($item['title']); ?></td>
                        <td><?php echo e($item['organization_name']); ?></td>
                        <td><?php echo e($item['category_name']); ?></td>
                        <td><?php echo e($item['status']); ?></td>
                        <td>
                            <form method="post" action="<?php echo e(route('admin/items/delete')); ?>">
                                <input type="hidden" name="_csrf" value="<?php echo e(csrf_token()); ?>">
                                <input type="hidden" name="id" value="<?php echo e((string) $item['id']); ?>">
                                <button class="btn btn-secondary" type="submit">Supprimer</button>
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
