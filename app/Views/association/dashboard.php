<?php require __DIR__ . '/../partials/app_header.php'; ?>
<main class="section">
    <div class="container">
        <div class="section-heading">
            <span class="eyebrow">Association</span>
            <h2><?php echo e($organization['name']); ?></h2>
            <p>Interface dediee aux associations pour reserver et suivre les lots solidaires.</p>
        </div>
        <div class="card-grid three">
            <article class="feature-card"><h3>Demandes</h3><p><?php echo e((string) $stats['reservations']); ?> reservations</p></article>
            <article class="feature-card"><h3>Validees</h3><p><?php echo e((string) $stats['approved']); ?> approuvees</p></article>
            <article class="feature-card"><h3>Lots disponibles</h3><p><?php echo e((string) $stats['available_items']); ?> visibles</p></article>
        </div>
    </div>
</main>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>
