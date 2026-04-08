<?php require __DIR__ . '/partials/header.php'; ?>
<main>
    <section class="page-hero compact">
        <div class="container narrow reveal">
            <span class="eyebrow">Contact</span>
            <h1>Lancer la version complete de FoodLoop avec une base claire.</h1>
            <p>Cette page met en scene un brief moderne pour continuer vers login, dashboard et modules CRUD.</p>
        </div>
    </section>

    <section class="section">
        <div class="container contact-layout">
            <div class="contact-info reveal">
                <div class="card-grid one">
                    <?php foreach ($contactCards as $card): ?>
                        <article class="contact-card">
                            <h3><?php echo htmlspecialchars($card['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                            <p><?php echo htmlspecialchars($card['text'], ENT_QUOTES, 'UTF-8'); ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>
                <div class="mini-process">
                    <?php foreach ($steps as $index => $step): ?>
                        <div class="mini-process-item">
                            <span><?php echo $index + 1; ?></span>
                            <p><?php echo htmlspecialchars($step, ENT_QUOTES, 'UTF-8'); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <form class="contact-form-modern reveal reveal-delay">
                <div class="field-row">
                    <input type="text" name="fullname" placeholder="Nom complet">
                    <input type="email" name="email" placeholder="Adresse email">
                </div>
                <div class="field-row">
                    <input type="text" name="organization" placeholder="Organisation">
                    <select name="role">
                        <option value="">Type d'acteur</option>
                        <option>Commerce</option>
                        <option>Association</option>
                        <option>Utilisateur</option>
                    </select>
                </div>
                <textarea name="message" rows="6" placeholder="Decris les modules que tu veux dans la prochaine etape"></textarea>
                <button class="btn btn-primary" type="submit">Envoyer le brief</button>
            </form>
        </div>
    </section>
</main>
<?php require __DIR__ . '/partials/footer.php'; ?>
