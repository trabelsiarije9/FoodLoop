<?php
/** @var string $title */
/** @var string $description */
/** @var string $currentPage */
/** @var array $navigation */
/** @var array|null $user */
/** @var array $flashMessages */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($title); ?></title>
    <meta name="description" content="<?php echo e($description); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo e(asset('assets/css/style.css')); ?>">
</head>
<body>
    <div class="page-orb orb-one"></div>
    <div class="page-orb orb-two"></div>
    <header class="site-header">
        <div class="container nav-shell">
            <a class="brand" href="<?php echo e(route('home')); ?>">FoodLoop</a>
            <nav class="nav-links">
                <?php foreach ($navigation as $item): ?>
                    <a href="<?php echo e(route($item['page'])); ?>" class="<?php echo $currentPage === $item['page'] ? 'is-active' : ''; ?>">
                        <?php echo e($item['label']); ?>
                    </a>
                <?php endforeach; ?>
            </nav>
            <div class="nav-meta">
                <?php if ($user !== null): ?>
                    <span class="nav-user"><?php echo e($user['first_name'] . ' ' . $user['last_name']); ?></span>
                    <a class="nav-cta" href="<?php echo e(route('logout')); ?>">Se deconnecter</a>
                <?php else: ?>
                    <a class="nav-cta" href="<?php echo e(route('register')); ?>">Commencer</a>
                <?php endif; ?>
            </div>
            <button class="menu-toggle" type="button" aria-label="Ouvrir le menu">Menu</button>
        </div>
    </header>
    <div class="container flash-stack">
        <?php foreach ($flashMessages as $type => $messages): ?>
            <?php foreach ($messages as $message): ?>
                <div class="flash flash-<?php echo e($type); ?>"><?php echo e($message); ?></div>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </div>
