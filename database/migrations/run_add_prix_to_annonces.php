<?php
declare(strict_types=1);

require dirname(__DIR__, 2) . '/config/bootstrap.php';

$pdo = App\Core\Database::getConnection();

$hasColumn = (int) $pdo
    ->query("SELECT COUNT(*) AS TOTAL FROM USER_TAB_COLUMNS WHERE TABLE_NAME = 'ANNONCES' AND COLUMN_NAME = 'PRIX'")
    ->fetch()['TOTAL'];

if ($hasColumn === 0) {
    $pdo->exec("ALTER TABLE ANNONCES ADD (PRIX NUMBER(10,2) DEFAULT 0 NOT NULL)");
}

$updates = [
    1 => 2.90,
    2 => 4.60,
    3 => 3.40,
    4 => 2.30,
    5 => 1.80,
];

$stmt = $pdo->prepare('UPDATE ANNONCES SET PRIX = :prix WHERE ID_ANNONCE = :id AND NVL(PRIX, 0) = 0');
foreach ($updates as $id => $price) {
    $stmt->execute([
        'prix' => $price,
        'id' => $id,
    ]);
}

echo "MIGRATION_OK\n";
