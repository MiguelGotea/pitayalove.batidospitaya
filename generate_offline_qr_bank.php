<?php
/**
 * Script para generar banco de 100 QRs offline
 * Ejecutar una sola vez para crear el banco inicial
 * 
 * Cada QR tiene puntos del 1 al 100
 */

require_once 'core/database/conexion.php';

// Security salt for offline QRs
$secret_salt_offline = "PITAYA_LOVE_2026_OFFLINE_KEY";

// Create directory for QR images
$qr_dir = __DIR__ . '/offline_qrs/';
if (!is_dir($qr_dir)) {
    mkdir($qr_dir, 0777, true);
}

// Check if phpqrcode library exists
$phpqrcode_path = 'core/vendor/phpqrcode/qrlib.php';
if (!file_exists($phpqrcode_path)) {
    die("Error: phpqrcode library not found. Please install it first.\n" .
        "Download from: https://sourceforge.net/projects/phpqrcode/\n" .
        "Or install via composer: composer require phpqrcode/phpqrcode\n");
}

require_once $phpqrcode_path;

$base_url = "https://pitayalove.batidospitaya.com/";

// --- MIGRACIÓN: Asegurar que la columna codigo_local existe ---
try {
    // Intentar agregar la columna (ignorará si ya existe)
    $alterSql = "ALTER TABLE qr_offline_bank ADD COLUMN IF NOT EXISTS codigo_local INT DEFAULT NULL AFTER points";
    // Si la versión de MySQL es vieja y no soporta ADD COLUMN IF NOT EXISTS:
    try {
        ejecutarConsulta($alterSql);
    } catch (Exception $e) {
        // Fallback para versiones de MySQL que no soportan IF NOT EXISTS en ALTER
        ejecutarConsulta("ALTER TABLE qr_offline_bank ADD COLUMN codigo_local INT DEFAULT NULL AFTER points");
    }
    echo "✓ Estructura de base de datos verificada/actualizada.\n";
} catch (Exception $e) {
    // Si falla es probablemente porque ya existe
    echo "• Nota: La estructura de la tabla ya estaba actualizada o hubo un error manejado.\n";
}
// -------------------------------------------------------------

echo "Generando banco de 400 QRs offline (20 sucursales x 20 puntos)...\n\n";

$qr_count = 0;
for ($sucursal = 1; $sucursal <= 20; $sucursal++) {
    for ($points = 1; $points <= 20; $points++) {
        $qr_count++;

        // Use a persistent ID for each combination
        $qr_id = ($sucursal * 100) + $points;

        // Generate unique key
        $key_hash = substr(hash('sha256', $qr_id . $points . $sucursal . $secret_salt_offline), 0, 16);

        // Generate QR URL with sucursal (cl) and points (p)
        $qr_url = $base_url . "?k=" . $key_hash . "&p=" . $points . "&cl=" . $sucursal . "&t=offline";

        // Generate filename format: qr_offline_s01_p01.png
        $filename = sprintf("qr_offline_s%02d_p%02d.png", $sucursal, $points);
        $filepath = $qr_dir . $filename;

        // Generate QR code image
        QRcode::png($qr_url, $filepath, QR_ECLEVEL_L, 10, 2);

        // Insert into database (include sucursal/codigo_local)
        // First, check if column exists or handle it in validate_qr.php schema update
        $sql = "INSERT INTO qr_offline_bank (qr_id, key_hash, points, filename, codigo_local) VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE key_hash = VALUES(key_hash), points = VALUES(points), filename = VALUES(filename), codigo_local = VALUES(codigo_local)";
        $result = ejecutarConsulta($sql, [$qr_id, $key_hash, $points, $filename, $sucursal]);

        if ($result) {
            echo "  ✓ QR S$sucursal-P$points generado: $filename (Key: $key_hash)\n";
        } else {
            echo "  ✗ Error generando QR S$sucursal-P$points\n";
        }
    }
}

echo "\n¡Banco de QRs offline generado exitosamente!\n";
echo "Total de QRs: $qr_count (20 sucursales x 20 puntos)\n";
echo "Ubicación: $qr_dir\n";
?>