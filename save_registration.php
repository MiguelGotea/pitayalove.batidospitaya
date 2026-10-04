<?php
header('Content-Type: application/json');
require_once 'core/database/conexion.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre_completo'] ?? '';
    $contacto = $_POST['numero_contacto'] ?? '';
    $cedula = $_POST['numero_cedula'] ?? '';
    $numero_factura = $_POST['numero_factura'] ?? '';
    $correo = $_POST['correo_electronico'] ?? '';
    $monto = $_POST['monto_factura'] ?? 0;
    $puntos = $_POST['puntos_factura'] ?? 0;
    $validado_ia = $_POST['validado_ia'] ?? 0;
    $tipo_qr = $_POST['tipo_qr'] ?? 'online';

    // El codigo_local es opcional e entero
    $codigo_local = $_POST['codigo_local'] ?? '';
    if ($codigo_local === '' || $codigo_local === 'null') {
        $codigo_local = null;
    } else {
        $codigo_local = (int) $codigo_local;
    }

    // Capturar datos detectados por IA
    $codigo_sorteo_ia = $_POST['codigo_sorteo_ia'] ?? '';
    $puntos_ia = $_POST['puntos_ia'] ?? '';

    // Validar y convertir codigo_sorteo_ia con filter_var
    if ($codigo_sorteo_ia === '' || $codigo_sorteo_ia === 'null' || $codigo_sorteo_ia === null) {
        $codigo_sorteo_ia = null;
    } else {
        $codigo_sorteo_ia = filter_var($codigo_sorteo_ia, FILTER_VALIDATE_INT);
        if ($codigo_sorteo_ia === false || $codigo_sorteo_ia <= 0) {
            $codigo_sorteo_ia = null;
        }
    }

    // Validar y convertir puntos_ia con filter_var
    if ($puntos_ia === '' || $puntos_ia === 'null' || $puntos_ia === null) {
        $puntos_ia = null;
    } else {
        $puntos_ia = filter_var($puntos_ia, FILTER_VALIDATE_INT);
        if ($puntos_ia === false || $puntos_ia < 0 || $puntos_ia > 1000) {
            $puntos_ia = null;
        }
    }

    // Validation
    if (empty($nombre) || empty($contacto) || empty($numero_factura)) {
        $response['message'] = 'Nombre, contacto y número de factura son obligatorios.';
        echo json_encode($response);
        exit;
    }

    // Validate nombre completo (at least 2 words)
    $nombre_words = preg_split('/\s+/', trim($nombre));
    if (count($nombre_words) < 2) {
        $response['message'] = 'Por favor ingrese nombre y apellido completo.';
        echo json_encode($response);
        exit;
    }

    // Capitalize each word
    $nombre = implode(' ', array_map('ucfirst', array_map('strtolower', $nombre_words)));

    // Validate numero contacto (exactly 8 digits)
    if (!preg_match('/^[0-9]{8}$/', $contacto)) {
        $response['message'] = 'El número de contacto debe tener exactamente 8 dígitos.';
        echo json_encode($response);
        exit;
    }

    // Validate numero factura (required and numeric)
    if (!preg_match('/^[0-9]+$/', $numero_factura)) {
        $response['message'] = 'El número de factura debe ser numérico.';
        echo json_encode($response);
        exit;
    }

    // Check if numero_factura + codigo_local already exists (NULL-safe check)
    if ($codigo_local === null) {
        $checkSql = "SELECT COUNT(*) as count FROM pitaya_love_registros WHERE numero_factura = ? AND codigo_local IS NULL";
        $checkParams = [$numero_factura];
    } else {
        $checkSql = "SELECT COUNT(*) as count FROM pitaya_love_registros WHERE numero_factura = ? AND codigo_local = ?";
        $checkParams = [$numero_factura, $codigo_local];
    }

    $checkStmt = ejecutarConsulta($checkSql, $checkParams);
    if ($checkStmt) {
        $checkResult = $checkStmt->fetchAll(PDO::FETCH_ASSOC);
        if ($checkResult && $checkResult[0]['count'] > 0) {
            $response['message'] = 'Este número de factura ya ha sido registrado para esta sucursal.';
            echo json_encode($response);
            exit;
        }
    }

    // Validate correo if provided (must contain @ and end with .com)
    if (!empty($correo) && !preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.com$/', $correo)) {
        $response['message'] = 'El correo debe contener @ y terminar en .com';
        echo json_encode($response);
        exit;
    }

    // Validate monto (required and numeric)
    if (empty($monto) || !is_numeric($monto) || $monto <= 0) {
        $response['message'] = 'El monto de factura es obligatorio y debe ser mayor a 0.';
        echo json_encode($response);
        exit;
    }

    // Validate photo is uploaded
    if (!isset($_FILES['foto_factura']) || $_FILES['foto_factura']['error'] !== UPLOAD_ERR_OK) {
        $response['message'] = 'La foto de la factura es obligatoria.';
        echo json_encode($response);
        exit;
    }

    $foto_nombre = null;
    if (isset($_FILES['foto_factura']) && $_FILES['foto_factura']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_extension = pathinfo($_FILES['foto_factura']['name'], PATHINFO_EXTENSION);
        $foto_nombre = 'factura_' . time() . '_' . uniqid() . '.' . $file_extension;
        $target_path = $upload_dir . $foto_nombre;

        if (!move_uploaded_file($_FILES['foto_factura']['tmp_name'], $target_path)) {
            $response['message'] = 'Error al subir la imagen.';
            echo json_encode($response);
            exit;
        }
    }

    $sql = "INSERT INTO pitaya_love_registros 
            (nombre_completo, numero_contacto, numero_cedula, numero_factura, 
             correo_electronico, monto_factura, puntos_factura, tipo_qr, 
             foto_factura, validado_ia, codigo_sorteo_ia, puntos_ia, codigo_local, fecha_registro) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $params = [
        $nombre,
        $contacto,
        $cedula,
        $numero_factura,
        $correo,
        $monto,
        $puntos,
        $tipo_qr,
        $foto_nombre,
        $validado_ia,
        $codigo_sorteo_ia,
        $puntos_ia,
        $codigo_local,
        date('Y-m-d H:i:s') // Guardar hora local de Nicaragua
    ];

    if (ejecutarConsulta($sql, $params)) {
        $response['success'] = true;
        $response['message'] = 'Registro guardado exitosamente.';
    } else {
        $response['message'] = 'Error al guardar en la base de datos.';
    }
} else {
    $response['message'] = 'Método no permitido.';
}

echo json_encode($response);
?>