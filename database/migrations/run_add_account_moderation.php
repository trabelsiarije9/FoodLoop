<?php
declare(strict_types=1);

require dirname(__DIR__, 2) . '/config/bootstrap.php';

$pdo = App\Core\Database::getConnection();

$exists = (int) $pdo
    ->query("SELECT COUNT(*) AS TOTAL FROM USER_TABLES WHERE TABLE_NAME = 'ACCOUNT_MODERATION'")
    ->fetch()['TOTAL'];

if ($exists === 0) {
    $sql = file_get_contents(__DIR__ . '/2026-05-04_add_account_moderation.sql');
    if ($sql === false) {
        throw new RuntimeException('Migration SQL introuvable.');
    }
    $pdo->exec(rtrim(trim($sql), ';'));
}

echo "MIGRATION_OK\n";
