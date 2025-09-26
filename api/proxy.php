<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

$ACCESS_TOKEN = 'kTc2TEfIECcCzeExHh4gLA';
$REFRESH_TOKEN = 'Bci3OoS9ER5pe2BVqnYKpA';
$CLIENT_ID = 'cW116Up5MxYlXrTlmzCjgA';
$MALL_ID = 'udit1';
$SHOP_NO = '1';

function refreshToken($mallId, $clientId, $refreshToken) {
    $url = "https://{$mallId}.cafe24api.com/api/v2/oauth/token";
    $data = http_build_query([
        'grant_type' => 'refresh_token',
        'client_id' => $clientId,
        'refresh_token' => $refreshToken
    ]);
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => [
                'Content-Type: application/x-www-form-urlencoded',
                'Authorization: Basic ' . base64_encode($clientId . ':')
            ],
            'content' => $data
        ]
    ]);
    $result = @file_get_contents($url, false, $context);
    if ($result !== false) {
        $response = json_decode($result, true);
        return $response['access_token'] ?? null;
    }
    return null;
}

function callAPI($url, $token) {
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => [
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json',
                'X-Cafe24-Api-Version: 2025-09-01'
            ]
        ]
    ]);
    $result = @file_get_contents($url, false, $context);
    return $result !== false ? $result : null;
}

$action = $_GET['action'] ?? 'help';

switch ($action) {
    case 'refresh':
        $newToken = refreshToken($MALL_ID, $CLIENT_ID, $REFRESH_TOKEN);
        if ($newToken) {
            echo json_encode(['success' => true, 'message' => '토큰 갱신 성공']);
        } else {
            echo json_encode(['success' => false, 'message' => '토큰 갱신 실패']);
        }
        break;
    case 'orders':
        $startDate = $_GET['start_date'] ?? date('Y-m-d');
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        $url = "https://{$MALL_ID}.cafe24api.com/api/v2/admin/orders/count?" . http_build_query([
            'shop_no' => $SHOP_NO,
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
        $result = callAPI($url, $ACCESS_TOKEN);
        echo $result ?: json_encode(['error' => 'API 호출 실패']);
        break;
    default:
        echo json_encode(['message' => 'Cafe24 API 프록시', 'actions' => ['refresh', 'orders']]);
}
?>