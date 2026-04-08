<?php require __DIR__ . '/../partials/app_header.php'; ?>
<main>
    <section class="page-hero compact">
        <div class="container narrow reveal">
            <span class="eyebrow">Roles</span>
            <h1>Trois profils principaux, trois experiences adaptees.</h1>
            <p>Le site distingue clairement les usages pour simplifier la navigation et l execution metier.</p>
        </div>
    </section>

    <section class="section">
        <div class="container tabs-shell reveal">
            <div class="role-tabs">
                <button class="role-tab is-active" type="button" data-role-target="user">User simple</button>
                <button class="role-tab" type="button" data-role-target="association">Association</button>
                <button class="role-tab" type="button" data-role-target="business">Business Owner</button>
            </div>
            <div class="role-panels">
                <article class="role-panel is-active" data-role-panel="user">
                    <p class="panel-kicker">Recherche et reservation</p>
                    <h2>User simple</h2>
                    <p>L utilisateur parcourt le catalogue, reserve des lots et suit son historique personnel.</p>
                    <ul class="check-list"><li>Catalogue public</li><li>Reservation rapide</li><li>Historique personnel</li></ul>
                </article>
                <article class="role-panel" data-role-panel="association">
                    <p class="panel-kicker">Structure solidaire</p>
                    <h2>Association</h2>
                    <p>L association reserve des lots pour son organisation et suit ses demandes depuis une interface dediee.</p>
                    <ul class="check-list"><li>Catalogue association</li><li>Reservations de structure</li><li>Suivi centralise</li></ul>
                </article>
                <article class="role-panel" data-role-panel="business">
                    <p class="panel-kicker">Depot et pilotage</p>
                    <h2>Business Owner</h2>
                    <p>Le commercant publie ses surplus, gere les statuts et repond aux reservations entrantes.</p>
                    <ul class="check-list"><li>Creation de lots</li><li>Edition des lots</li><li>Suivi des demandes</li></ul>
                </article>
            </div>
        </div>
    </section>
</main>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>
