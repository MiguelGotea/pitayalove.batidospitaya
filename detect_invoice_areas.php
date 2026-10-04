<?php
header('Content-Type: application/json');

$response = ['success' => false, 'areas' => [], 'codigo_participar' => null, 'puntos' => null, 'codigo_sorteo_ia' => null, 'puntos_ia' => null, 'monto_total' => null, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['invoice_image'])) {
    $image = $_FILES['invoice_image'];

    // Google Vision API configuration
    // TODO: Replace with actual API key
    $api_key = 'AIzaSyC4e4r67sp9LTDsMQE4tQCw6orLvKtJQ5M';
    $api_url = 'https://vision.googleapis.com/v1/images:annotate?key=' . $api_key;

    // Read image content
    $image_content = base64_encode(file_get_contents($image['tmp_name']));

    // Prepare request for Google Vision API
    $request_body = [
        'requests' => [
            [
                'image' => ['content' => $image_content],
                'features' => [
                    ['type' => 'TEXT_DETECTION', 'maxResults' => 1]
                ]
            ]
        ]
    ];

    // Call Google Vision API
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request_body));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

    $result = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    if ($http_code !== 200) {
        $error_details = json_decode($result, true);
        $response['message'] = 'Error API (HTTP ' . $http_code . ')';
        $response['debug'] = [
            'http_code' => $http_code,
            'curl_error' => $curl_error,
            'api_response' => $error_details
        ];
        echo json_encode($response);
        exit;
    }

    $vision_response = json_decode($result, true);

    if (isset($vision_response['responses'][0]['textAnnotations'])) {
        $annotations = $vision_response['responses'][0]['textAnnotations'];
        $full_text = $annotations[0]['description'] ?? '';

        // Search for "Código de Sorteo: XXXXX" (formato real de factura)
        if (preg_match('/C[oó]digo\s+de\s+Sorteo\s*:\s*(\d+)/i', $full_text, $matches)) {
            $response['codigo_participar'] = $matches[1];

            // Find bounding box for this text
            foreach ($annotations as $annotation) {
                if (
                    stripos($annotation['description'], 'Código') !== false ||
                    stripos($annotation['description'], 'Sorteo') !== false ||
                    stripos($annotation['description'], $matches[1]) !== false
                ) {
                    if (isset($annotation['boundingPoly']['vertices'])) {
                        $vertices = $annotation['boundingPoly']['vertices'];
                        $response['areas'][] = [
                            'type' => 'codigo',
                            'label' => 'Código de Sorteo',
                            'x' => $vertices[0]['x'] ?? 0,
                            'y' => $vertices[0]['y'] ?? 0,
                            'width' => ($vertices[2]['x'] ?? 0) - ($vertices[0]['x'] ?? 0),
                            'height' => ($vertices[2]['y'] ?? 0) - ($vertices[0]['y'] ?? 0)
                        ];
                        break;
                    }
                }
            }
        }

        // Search for "Puntos: X"
        if (preg_match('/Puntos\s*:\s*(\d+)/i', $full_text, $matches)) {
            $response['puntos'] = intval($matches[1]);

            // Find bounding box for this text
            foreach ($annotations as $annotation) {
                if (stripos($annotation['description'], 'Puntos') !== false) {
                    if (isset($annotation['boundingPoly']['vertices'])) {
                        $vertices = $annotation['boundingPoly']['vertices'];
                        $response['areas'][] = [
                            'type' => 'puntos',
                            'label' => 'Puntos',
                            'x' => $vertices[0]['x'] ?? 0,
                            'y' => $vertices[0]['y'] ?? 0,
                            'width' => ($vertices[2]['x'] ?? 0) - ($vertices[0]['x'] ?? 0),
                            'height' => ($vertices[2]['y'] ?? 0) - ($vertices[0]['y'] ?? 0)
                        ];
                        break;
                    }
                }
            }
        }

        // Search for "Total a Pagar: XXX" (formato real de factura)
        if (preg_match('/Total\s+a\s+Pagar\s*:\s*C?\$?\s*([\d,]+\.?\d*)/i', $full_text, $matches)) {
            $response['monto_total'] = floatval(str_replace(',', '', $matches[1]));
        }

        // ============================================
        // Extraer Código de Sorteo (número)
        // ============================================
        $codigo_sorteo_ia = null;

        // Patrón 1: "CODIGO DE SORTEO: 12345" o "CODIGO: 12345"
        if (preg_match('/(?:CODIGO|SORTEO|PARTICIPAR)(?:\s+DE\s+SORTEO)?[\s:]*(\d{4,15})/i', $full_text, $matches)) {
            $codigo_sorteo_ia = (int) $matches[1];
        }

        // Patrón 2: "No. XXXXX" o "#XXXXX" (común en facturas)
        if ($codigo_sorteo_ia === null) {
            if (preg_match('/(?:No\.?|#)[\s]*(\d{4,15})/i', $full_text, $matches)) {
                $codigo_sorteo_ia = (int) $matches[1];
            }
        }

        // Patrón 3: Número aislado de 5-15 dígitos (más conservador)
        if ($codigo_sorteo_ia === null) {
            if (preg_match('/\b(\d{5,15})\b/', $full_text, $matches)) {
                $codigo_sorteo_ia = (int) $matches[1];
            }
        }

        $response['codigo_sorteo_ia'] = $codigo_sorteo_ia;

        // ============================================
        // Extraer Puntos
        // ============================================
        $puntos_ia = null;

        // Patrón 1: "PUNTOS: 10" o "PUNTOS 25"
        if (preg_match('/PUNTOS[\s:]*(\d+)/i', $full_text, $matches)) {
            $puntos_ia = (int) $matches[1];
        }

        // Patrón 2: "10 PUNTOS" o "25 PUNTOS"
        if ($puntos_ia === null) {
            if (preg_match('/(\d+)[\s]*PUNTOS/i', $full_text, $matches)) {
                $puntos_ia = (int) $matches[1];
            }
        }

        // Patrón 3: "PUNTOS GANADOS: 10"
        if ($puntos_ia === null) {
            if (preg_match('/PUNTOS\s+GANADOS[\s:]*(\d+)/i', $full_text, $matches)) {
                $puntos_ia = (int) $matches[1];
            }
        }

        $response['puntos_ia'] = $puntos_ia;

        $response['success'] = true;
        $response['raw_text'] = $full_text; // For debugging

    } else {
        $response['message'] = 'No se pudo detectar texto en la imagen';
    }
} else {
    $response['message'] = 'No se recibió imagen';
}

echo json_encode($response);
?>