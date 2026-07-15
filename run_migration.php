<?php
require_once __DIR__ . '/conexion.php';

$sql = file_get_contents(__DIR__ . '/docs/migraciones/002_admin_module.sql');
$queries = explode(';', $sql);

echo "<!DOCTYPE html><html lang='es'><head><title>Migración Admin</title>";
echo "<style>body{font-family:sans-serif;padding:40px;background:#f7faf8;color:#121a17}";
echo ".ok{color:#157145}.err{color:#ba1a1a}hr{margin:20px 0}</style></head><body>";
echo "<h1>Ejecutando migración 002_admin_module...</h1><hr>";

$count = 0;
foreach ($queries as $q) {
    $q = trim($q);
    if (empty($q) || str_starts_with($q, '--')) continue;

    try {
        if ($conn->query($q) === TRUE) {
            echo "<p class='ok'>✓ OK: " . htmlspecialchars(substr($q, 0, 80)) . "...</p>";
            $count++;
        } else {
            echo "<p class='err'>✗ Error: " . htmlspecialchars($conn->error) . "</p>";
        }
    } catch (\mysqli_sql_exception $e) {
        echo "<p class='err'>✗ " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}

echo "<hr><h2>Migración completada. $count consultas ejecutadas.</h2>";
echo "<p><a href='admin.php' style='color:#157145;font-weight:bold'>Ir al panel admin</a></p>";
echo "</body></html>";

$conn->close();
