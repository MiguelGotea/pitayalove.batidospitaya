<?php
header('Content-Type: application/json');
require_once 'core/database/conexion.php';

$response = ['valid' => false, 'message' => ''];

// Security salts
$secret_salt_online = "PITAYA_LOVE_2026_SECRET_KEY_XYZ";
$secret_salt_offline = "PITAYA_LOVE_2026_OFFLINE_KEY";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    $key = $input['key'] ?? '';
    $type = $input['type'] ?? '';

    if ($type === 'online') {
        // Validate online QR (sin membresía)
        $nf = $input['nf'] ?? '';
        $m = $input['m'] ?? '';
        $p = $input['p'] ?? '';
        $cl = $input['cl'] ?? '';

        // Generate expected key (sin membresía)
        // Note: cl is treated as empty string if not provided, for hash consistency
        $expected_key = substr(hash('sha256', $nf . $m . $p . $cl . $secret_salt_online), 0, 16);

        if ($key === $expected_key) {
            $response['valid'] = true;
            $response['message'] = 'QR online válido';
        } else {
            $response['message'] = 'Llave de seguridad inválida';
        }

    } else if ($type === 'offline') {
        // Validate offline QR against bank
        // First, create table if it doesn't exist
        $createTableSql = "CREATE TABLE IF NOT EXISTS qr_offline_bank (
            id INT AUTO_INCREMENT PRIMARY KEY,
            qr_id INT NOT NULL UNIQUE,
            key_hash VARCHAR(16) NOT NULL UNIQUE,
            points INT NOT NULL,
            codigo_local INT DEFAULT NULL,
            filename VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_key_hash (key_hash)
        )";
        ejecutarConsulta($createTableSql);

        $sql = "SELECT * FROM qr_offline_bank WHERE key_hash = ?";
        $stmt = ejecutarConsulta($sql, [$key]);

        if ($stmt) {
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (count($result) > 0) {
                $response['valid'] = true;
                $response['message'] = 'QR offline válido';
            } else {
                $response['message'] = 'QR offline no encontrado en el banco';
            }
        } else {
            $response['message'] = 'Error al validar QR offline';
        }
    } else {
        $response['message'] = 'Tipo de QR no especificado';
    }
}

echo json_encode($response);
?>