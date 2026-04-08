<?php require __DIR__ . '/../partials/app_header.php'; ?>
<main class="section">
    <div class="container">
        <div class="section-heading">
            <span class="eyebrow">Catalogue Association</span>
            <h2>Lots disponibles pour votre structure</h2>
        </div>
        <div class="card-grid three">
            <?php foreach ($items as $item): ?>
                <article class="feature-card">
                    <h3><?php echo e($item['title']); ?></h3>
                    <p><?php echo e($item['organization_name']); ?>, <?php echo e($item['city']); ?></p>
                    <p><?php echo e((string) $item['quantity']); ?> <?php echo e($item['unit']); ?>, statut <?php echo e($item['status']); ?></p>
                    <form class="stack-form" method="post" action="<?php echo e(route('association/reserve')); ?>">
                        <input type="hidden" name="_csrf" value="<?php echo e(csrf_token()); ?>">
                        <input type="hidden" name="food_item_id" value="<?php echo e((string) $item['id']); ?>">
                        <input type="number" step="0.01" name="reserved_quantity" value="1" min="1">
                        <textarea name="notes" rows="3" placeholder="Message optionnel"></textarea>
                        <button class="btn btn-primary" type="submit">Reserver pour l association</button>
                    </form>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</main>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>
