<?php require __DIR__ . '/../partials/app_header.php'; ?>
<main class="section">
    <div class="container dashboard-grid">
        <section class="panel-card">
            <h2><?php echo $editItem !== null ? 'Modifier un lot' : 'Creer un lot'; ?></h2>
            <form class="stack-form" method="post" action="<?php echo e(route('business/items')); ?>">
                <input type="hidden" name="_csrf" value="<?php echo e(csrf_token()); ?>">
                <input type="hidden" name="id" value="<?php echo e((string) ($editItem['id'] ?? 0)); ?>">
                <input type="text" name="title" placeholder="Titre du lot" value="<?php echo e($editItem['title'] ?? ''); ?>" required>
                <textarea name="description" rows="4" placeholder="Description"><?php echo e($editItem['description'] ?? ''); ?></textarea>
                <div class="field-row">
                    <input type="number" step="0.01" name="quantity" placeholder="Quantite" value="<?php echo e(isset($editItem['quantity']) ? (string) $editItem['quantity'] : ''); ?>" required>
                    <input type="text" name="unit" placeholder="Unite" value="<?php echo e($editItem['unit'] ?? 'portion'); ?>" required>
                </div>
                <select name="category_id" required>
                    <option value="">Categorie</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo e((string) $category['id']); ?>" <?php echo ((int) ($editItem['category_id'] ?? 0) === (int) $category['id']) ? 'selected' : ''; ?>><?php echo e($category['name']); ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="datetime-local" name="expiration_date" value="<?php echo e(isset($editItem['expiration_date']) ? str_replace(' ', 'T', substr((string) $editItem['expiration_date'], 0, 16)) : ''); ?>" required>
                <input type="datetime-local" name="pickup_start" value="<?php echo e(isset($editItem['pickup_start']) ? str_replace(' ', 'T', substr((string) $editItem['pickup_start'], 0, 16)) : ''); ?>" required>
                <input type="datetime-local" name="pickup_end" value="<?php echo e(isset($editItem['pickup_end']) ? str_replace(' ', 'T', substr((string) $editItem['pickup_end'], 0, 16)) : ''); ?>" required>
                <input type="datetime-local" name="priority_until" value="<?php echo e(isset($editItem['priority_until']) && $editItem['priority_until'] !== null ? str_replace(' ', 'T', substr((string) $editItem['priority_until'], 0, 16)) : ''); ?>">
                <select name="status">
                    <?php foreach (['draft', 'available', 'priority_access', 'reserved'] as $status): ?>
                        <option value="<?php echo e($status); ?>" <?php echo (($editItem['status'] ?? 'draft') === $status) ? 'selected' : ''; ?>><?php echo e($status); ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="btn btn-primary" type="submit"><?php echo $editItem !== null ? 'Mettre a jour' : 'Publier'; ?></button>
                <?php if ($editItem !== null): ?>
                    <a class="btn btn-secondary" href="<?php echo e(route('business/items')); ?>">Annuler</a>
                <?php endif; ?>
            </form>
        </section>

        <section class="panel-card">
            <h2>Mes lots publies</h2>
            <div class="table-wrap">
                <table class="data-table">
                    <thead><tr><th>Titre</th><th>Categorie</th><th>Quantite</th><th>Statut</th><th>Action</th></tr></thead>
                    <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?php echo e($item['title']); ?></td>
                            <td><?php echo e($item['category_name']); ?></td>
                            <td><?php echo e((string) $item['quantity']); ?> <?php echo e($item['unit']); ?></td>
                            <td><?php echo e($item['status']); ?></td>
                            <td>
                                <a class="btn btn-secondary" href="<?php echo e(route('business/items', ['edit' => (string) $item['id']])); ?>">Modifier</a>
                                <form method="post" action="<?php echo e(route('business/items/delete')); ?>">
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
        </section>
    </div>
</main>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>
