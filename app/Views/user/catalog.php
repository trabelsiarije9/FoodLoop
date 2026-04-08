<?php require __DIR__ . '/../partials/app_header.php'; ?>
<main class="section">
    <div class="container">
        <div class="card-grid three">
            <article class="feature-card"><h3>Lots disponibles</h3><p><?php echo e((string) $stats['available_items']); ?> lots</p></article>
            <article class="feature-card"><h3>Mes demandes</h3><p><?php echo e((string) $stats['reservations']); ?> reservations</p></article>
            <article class="feature-card"><h3>Approuvees</h3><p><?php echo e((string) $stats['approved']); ?> demandes</p></article>
        </div>

        <div class="section-heading">
            <span class="eyebrow">Catalogue</span>
            <h2>Lots disponibles</h2>
        </div>
        <div class="card-grid three">
            <?php foreach ($items as $item): ?>
                <article class="feature-card">
                    <h3><?php echo e($item['title']); ?></h3>
                    <p><?php echo e($item['organization_name']); ?>, <?php echo e($item['city']); ?></p>
                    <p><?php echo e((string) $item['quantity']); ?> <?php echo e($item['unit']); ?>, statut <?php echo e($item['status']); ?></p>
                    <form class="stack-form" method="post" action="<?php echo e(route('reserve')); ?>">
                        <input type="hidden" name="_csrf" value="<?php echo e(csrf_token()); ?>">
                        <input type="hidden" name="food_item_id" value="<?php echo e((string) $item['id']); ?>">
                        <input type="number" step="0.01" name="reserved_quantity" value="1" min="1">
                        <textarea name="notes" rows="3" placeholder="Message optionnel"></textarea>
                        <button class="btn btn-primary" type="submit">Reserver</button>
                    </form>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</main>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>
