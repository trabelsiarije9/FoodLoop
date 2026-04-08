<?php
/** @var string $title */
/** @var string $description */
/** @var string $currentPage */
/** @var array $navigation */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($description, ENT_QUOTES, 'UTF-8'); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="page-orb orb-one"></div>
    <div class="page-orb orb-two"></div>
    <header class="site-header">
        <div class="container nav-shell">
            <a class="brand" href="index.php?page=home">FoodLoop</a>
            <nav class="nav-links">
                <?php foreach ($navigation as $item): ?>
                    <a href="index.php?page=<?php echo urlencode($item['page']); ?>" class="<?php echo $currentPage === $item['page'] ? 'is-active' : ''; ?>">
                        <?php echo htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8'); ?>
                    </a>
                <?php endforeach; ?>
            </nav>
            <a class="nav-cta" href="index.php?page=contact">Demarrer</a>
            <button class="menu-toggle" type="button" aria-label="Ouvrir le menu">Menu</button>
        </div>
    </header>
