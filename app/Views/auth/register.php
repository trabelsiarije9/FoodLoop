<?php require __DIR__ . '/../partials/app_header.php'; ?>
<main>
    <section class="page-hero compact">
        <div class="container narrow reveal">
            <span class="eyebrow">Inscription</span>
            <h1>Cree ton compte FoodLoop.</h1>
            <p>Choisis ton role et active ton acces a la plateforme en quelques champs.</p>
        </div>
    </section>

    <section class="section">
        <div class="container form-shell reveal">
            <form class="contact-form-modern auth-form" method="post" action="<?php echo e(route('register')); ?>">
                <input type="hidden" name="_csrf" value="<?php echo e(csrf_token()); ?>">
                <div class="field-row">
                    <div class="field-group">
                        <label>Prenom</label>
                        <input type="text" name="first_name" value="<?php echo e(old('first_name')); ?>" required>
                    </div>
                    <div class="field-group">
                        <label>Nom</label>
                        <input type="text" name="last_name" value="<?php echo e(old('last_name')); ?>" required>
                    </div>
                </div>
                <div class="field-row">
                    <div class="field-group">
                        <label>Email</label>
                        <input type="email" name="email" value="<?php echo e(old('email')); ?>" required>
                    </div>
                    <div class="field-group">
                        <label>Telephone</label>
                        <input type="text" name="phone" value="<?php echo e(old('phone')); ?>">
                    </div>
                </div>
                <div class="field-row">
                    <div class="field-group">
                        <label>Mot de passe</label>
                        <input type="password" name="password" required>
                    </div>
                    <div class="field-group">
                        <label>Confirmation</label>
                        <input type="password" name="password_confirmation" required>
                    </div>
                </div>
                <div class="field-group">
                    <label>Role</label>
                    <select name="role_code" id="role_code">
                        <option value="regular_user" <?php echo old('role_code', 'regular_user') === 'regular_user' ? 'selected' : ''; ?>>User simple</option>
                        <option value="association_admin" <?php echo old('role_code') === 'association_admin' ? 'selected' : ''; ?>>Association</option>
                        <option value="business_owner" <?php echo old('role_code') === 'business_owner' ? 'selected' : ''; ?>>Business Owner</option>
                    </select>
                </div>

                <div class="role-form-card" data-role-section="regular_user">
                    <h3>Compte user simple</h3>
                    <p class="form-note">Ce profil est pour une personne qui veut consulter et reserver des lots.</p>
                </div>

                <div class="role-form-card" data-role-section="association_admin">
                    <h3>Compte association</h3>
                    <p class="form-note">Renseigne les informations de ton association.</p>
                    <div class="field-row">
                        <div class="field-group">
                            <label>Nom de l association</label>
                            <input type="text" name="association_organization_name" value="<?php echo e(old('organization_name')); ?>" data-sync-target="organization_name" data-role-required="association_admin">
                        </div>
                        <div class="field-group">
                            <label>Code association</label>
                            <input type="text" name="association_code" value="<?php echo e(old('association_code')); ?>" data-role-required="association_admin">
                        </div>
                    </div>
                    <div class="field-row">
                        <div class="field-group">
                            <label>Adresse</label>
                            <input type="text" name="association_address_line" value="<?php echo e(old('address_line')); ?>" data-sync-target="address_line">
                        </div>
                        <div class="field-group">
                            <label>Ville</label>
                            <input type="text" name="association_city" value="<?php echo e(old('city')); ?>" data-sync-target="city">
                        </div>
                    </div>
                    <div class="field-group">
                        <label>Gouvernorat</label>
                        <input type="text" name="association_governorate" value="<?php echo e(old('governorate')); ?>" data-sync-target="governorate">
                    </div>
                    <div class="field-group">
                        <label>Description association</label>
                        <textarea name="association_organization_description" rows="4" data-sync-target="organization_description"><?php echo e(old('organization_description')); ?></textarea>
                    </div>
                </div>

                <div class="role-form-card" data-role-section="business_owner">
                    <h3>Compte business owner</h3>
                    <p class="form-note">Renseigne les informations de ton commerce ou entreprise.</p>
                    <div class="field-row">
                        <div class="field-group">
                            <label>Nom de l entreprise</label>
                            <input type="text" name="business_organization_name" value="<?php echo e(old('organization_name')); ?>" data-sync-target="organization_name" data-role-required="business_owner">
                        </div>
                        <div class="field-group">
                            <label>Licence business</label>
                            <input type="text" name="business_license" value="<?php echo e(old('business_license')); ?>" data-role-required="business_owner">
                        </div>
                    </div>
                    <div class="field-row">
                        <div class="field-group">
                            <label>Adresse</label>
                            <input type="text" name="business_address_line" value="<?php echo e(old('address_line')); ?>" data-sync-target="address_line">
                        </div>
                        <div class="field-group">
                            <label>Ville</label>
                            <input type="text" name="business_city" value="<?php echo e(old('city')); ?>" data-sync-target="city">
                        </div>
                    </div>
                    <div class="field-group">
                        <label>Gouvernorat</label>
                        <input type="text" name="business_governorate" value="<?php echo e(old('governorate')); ?>" data-sync-target="governorate">
                    </div>
                    <div class="field-group">
                        <label>Description entreprise</label>
                        <textarea name="business_organization_description" rows="4" data-sync-target="organization_description"><?php echo e(old('organization_description')); ?></textarea>
                    </div>
                </div>

                <input type="hidden" name="organization_name" value="<?php echo e(old('organization_name')); ?>">
                <input type="hidden" name="address_line" value="<?php echo e(old('address_line')); ?>">
                <input type="hidden" name="city" value="<?php echo e(old('city')); ?>">
                <input type="hidden" name="governorate" value="<?php echo e(old('governorate')); ?>">
                <input type="hidden" name="organization_description" value="<?php echo e(old('organization_description')); ?>">
                <button class="btn btn-primary" type="submit">Creer mon compte</button>
            </form>
        </div>
    </section>
</main>
<?php require __DIR__ . '/../partials/app_footer.php'; ?>
