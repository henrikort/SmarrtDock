<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file'])) {
    $jwtToken = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VySW5mb3JtYXRpb24iOnsiaWQiOiI1ZDEyODhmYS1lOWJmLTQ3NWQtOTUyMi02YWRjMzY1MGJkYjEiLCJlbWFpbCI6ImhlbnJpa29ydDEzQGdtYWlsLmNvbSIsImVtYWlsX3ZlcmlmaWVkIjp0cnVlLCJwaW5fcG9saWN5Ijp7InJlZ2lvbnMiOlt7ImRlc2lyZWRSZXBsaWNhdGlvbkNvdW50IjoxLCJpZCI6IkZSQTEifSx7ImRlc2lyZWRSZXBsaWNhdGlvbkNvdW50IjoxLCJpZCI6Ik5ZQzEifV0sInZlcnNpb24iOjF9LCJtZmFfZW5hYmxlZCI6ZmFsc2UsInN0YXR1cyI6IkFDVElWRSJ9LCJhdXRoZW50aWNhdGlvblR5cGUiOiJzY29wZWRLZXkiLCJzY29wZWRLZXlLZXkiOiI2ODA2NmYzZmIwNGRkODVhZTgyMSIsInNjb3BlZEtleVNlY3JldCI6ImU2MmU0YjE4N2JjYjBlN2MxNzkzZGVhMmY5YjJjZTg1M2JjNzQ3ZThhYjcwYmFiYmQwYzIzOTQxMGI2MjhiMzkiLCJleHAiOjE3NzE1MTI0MTl9.DdI2uWnqugwiech0g0MSHDPhDywCThuPQr7UfxQyVlc";

    $file = $_FILES['file'];
    $filePath = $file['tmp_name'];
    $fileName = $file['name'];
    
    // Capturar o tipo de documento enviado
    $documentType = isset($_POST['document_type']) ? $_POST['document_type'] : "Desconhecido";

    if (!is_uploaded_file($filePath)) {
        echo json_encode(["success" => false, "error" => "Erro ao processar o arquivo"]);
        exit();
    }

    $pinataHeaders = [
        "Authorization: Bearer $jwtToken"
    ];

    $postData = [
        "file" => new CURLFile($filePath, $file['type'], $fileName),
        "pinataMetadata" => json_encode([
            "name" => $fileName,
            "keyvalues" => [
                "tipo_documento" => $documentType, // Salvar tipo de documento no Pinata
                "unique_id" => uniqid(),
                "timestamp" => time()
            ]
        ]),
        "pinataOptions" => json_encode([
            "cidVersion" => 1
        ])
    ];

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => "https://api.pinata.cloud/pinning/pinFileToIPFS",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => $pinataHeaders,
        CURLOPT_POSTFIELDS => $postData
    ]);

    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    if (curl_errno($curl)) {
        echo json_encode(["success" => false, "error" => "Erro cURL: " . curl_error($curl)]);
        exit();
    }

    curl_close($curl);

    if ($response) {
        $data = json_decode($response, true);
        if (isset($data['IpfsHash'])) {
            $ipfsUrl = "https://gateway.pinata.cloud/ipfs/" . $data['IpfsHash'];
            echo json_encode(["success" => true, "url" => $ipfsUrl, "name" => $fileName, "document_type" => $documentType]);
        } else {
            echo json_encode(["success" => false, "error" => "Falha ao obter hash do IPFS. Código HTTP: " . $httpCode, "resposta" => $response]);
        }
    } else {
        echo json_encode(["success" => false, "error" => "Falha ao conectar ao Pinata. Código HTTP: " . $httpCode]);
    }
} else {
    echo json_encode(["success" => false, "error" => "Nenhum arquivo enviado"]);
}
?>
