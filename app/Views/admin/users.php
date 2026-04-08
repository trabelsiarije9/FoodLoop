<?php require __DIR__ . '/../partials/app_header.php'; ?>
<main class="section">
    <div class="container dashboard-grid">
        <section class="panel-card">
            <h2><?php echo $editUser !== null ? 'Modifier un utilisateur' : 'Ajouter un utilisateur'; ?></h2>
            <form class="stack-form" method="post" action="<?php echo e(route('admin/users')); ?>">
                <input type="hidden" name="_csrf" value="<?php echo e(csrf_token()); ?>">
                <input type="hidden" name="id" value="<?php echo e((string) ($editUser['id'] ?? 0)); ?>">
                <input type="text" name="first_name" placeholder="Prenom" value="<?php echo e($editUser['first_name'] ?? ''); ?>" required>
                <input type="text" name="last_name" placeholder="Nom" value="<?php echo e($editUser['last_name'] ?? ''); ?>" required>
                <input type="email" name="email" placeholder="Email" value="<?php echo e($editUser['email'] ?? ''); ?>" required>
                <input type="text" name="phone" placeholder="Telephone" value="<?php echo e($editUser['phone'] ?? ''); ?>">
                <input type="password" name="password" placeholder="Mot de passe">
                <select name="role_code">
                    <?php foreach ($roles as $role): ?>
                        <option value="<?php echo e($role['code']); ?>" <?php echo (($editUser['role_code'] ?? '') === $role['code']) ? 'selected' : ''; ?>><?php echo e($role['name']); ?></option>
                    <?php endforeach; ?>
                </select>
                <label class="inline-check"><input type="checkbox" name="is_active" <?php echo !isset($editUser['is_active']) || (int) $editUser['is_active'] === 1 ? 'checked' : ''; ?>> Compte actif</label>
                <button class="btn btn-primary" type="submit"><?php echo $editUser !== null ? 'Mettre a jour' : 'Creer'; ?></button>
                <?php if ($editUser !== null): ?>
                    <a class="btn btn-secondary" href="<?php echo e(route('admin/users')); ?>">Annuler</a>
                <?php endif; ?>
            </form>
        </section>

        <section class="panel-card">
            <h2>Liste des utilisateurs</h2>
            <div class="table-wrap">
                <table class="data-table">
                    <thead><tr><th>Nom</th><th>Email</th><th>Role</th><th>Etat</th><th>Action</th></tr></thead>
                    <tbody>
                    <?php foreach ($users as $account): ?>
                        <tr>
                            <td><?php echo e($account['first_name'] . ' ' . $account['last_name']); ?></td>
                            <td><?php echo e($account['email']); ?></td>
                            <td><?php echo e($account['role_name']); ?></td>
                            <td><?php echo (int) $account['is_active'] === 1 ? 'Actif' : 'Inactif'; ?></td>
                            <td>
                                <a class="btn btn-secondary" href="<?php echo e(route('admin/users', ['edit' => (string) $account['id']])); ?>">Modifier</a>
                                <form method="post" action="<?php echo e(route('admin/users/delete')); ?>">
                                    <input type="hidden" name="_csrf" value="<?php echo e(csrf_token()); ?>">
                                    <input type="hidden" name="id" value="<?php echo e((string) $account['id']); ?>">
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
