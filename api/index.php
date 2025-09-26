<?php
// API 요청 처리
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');

    $ACCESS_TOKEN = 'kTc2TEfIECcCzeExHh4gLA';
    $REFRESH_TOKEN = 'Bci3OoS9ER5pe2BVqnYKpA';
    $CLIENT_ID = 'cW116Up5MxYlXrTlmzCjgA';
    $MALL_ID = 'udit1';
    $SHOP_NO = '1';

    $action = $_GET['action'];

    switch ($action) {
        case 'test':
            echo json_encode([
                'status' => 'PHP 작동 성공!',
                'server' => $_SERVER['HTTP_HOST'] ?? 'localhost',
                'time' => date('Y-m-d H:i:s'),
                'php_version' => phpversion()
            ]);
            exit;

        case 'refresh':
            $url = "https://{$MALL_ID}.cafe24api.com/api/v2/oauth/token";
            $data = http_build_query([
                'grant_type' => 'refresh_token',
                'client_id' => $CLIENT_ID,
                'refresh_token' => $REFRESH_TOKEN
            ]);

            $context = stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => "Content-Type: application/x-www-form-urlencoded\r\nAuthorization: Basic " . base64_encode($CLIENT_ID . ':'),
                    'content' => $data
                ]
            ]);

            $result = @file_get_contents($url, false, $context);

            if ($result) {
                echo $result;
            } else {
                $error = error_get_last();
                echo json_encode([
                    'error' => '토큰 갱신 실패',
                    'details' => $error ? $error['message'] : 'Unknown error'
                ]);
            }
            exit;

        case 'orders':
            $startDate = $_GET['start_date'] ?? date('Y-m-d');
            $endDate = $_GET['end_date'] ?? date('Y-m-d');

            $url = "https://{$MALL_ID}.cafe24api.com/api/v2/admin/orders/count?" . http_build_query([
                'shop_no' => $SHOP_NO,
                'start_date' => $startDate,
                'end_date' => $endDate
            ]);

            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'header' => "Authorization: Bearer {$ACCESS_TOKEN}\r\nContent-Type: application/json\r\nX-Cafe24-Api-Version: 2025-09-01"
                ]
            ]);

            $result = @file_get_contents($url, false, $context);
            echo $result ?: json_encode(['error' => 'API 호출 실패']);
            exit;

        default:
            echo json_encode(['error' => '지원하지 않는 액션']);
            exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>🚀 Cafe24 API 테스트</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        button { padding: 12px 20px; margin: 5px; background: #007cba; color: white; border: none; cursor: pointer; border-radius: 5px; font-size: 14px; }
        button:hover { background: #005a8a; }
        .result { margin: 15px 0; padding: 15px; background: #f8f9fa; border-left: 4px solid #007cba; white-space: pre-wrap; border-radius: 5px; }
        .success { border-left-color: #28a745; background: #e8f5e9; }
        .error { border-left-color: #dc3545; background: #f8e6e7; }
        input { padding: 8px; margin: 5px; border: 1px solid #ddd; border-radius: 3px; }
        h1 { color: #333; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 Cafe24 API 테스트</h1>

        <div style="margin-bottom: 20px;">
            <button onclick="testPHP()">🔧 PHP 작동 테스트</button>
            <button onclick="testRefresh()">🔄 토큰 갱신</button>
            <button onclick="testOrders()">📊 주문 수 조회</button>
        </div>

        <div>
            <label>주문 조회 날짜:</label>
            <input type="date" id="start-date" value="2025-09-26">
            <input type="date" id="end-date" value="2025-09-26">
        </div>

        <div id="result" class="result">버튼을 클릭하여 테스트를 시작하세요.</div>
    </div>

    <script>
        function showResult(data, isSuccess = true) {
            const resultDiv = document.getElementById('result');
            resultDiv.textContent = typeof data === 'object' ? JSON.stringify(data, null, 2) : data;
            resultDiv.className = 'result ' + (isSuccess ? 'success' : 'error');
        }

        async function makeRequest(action, params = '') {
            try {
                const url = `?action=${action}${params}`;
                const response = await fetch(url);

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }

                const data = await response.json();
                showResult(data, !data.error);
                return data;
            } catch (error) {
                showResult(`요청 실패: ${error.message}`, false);
                return null;
            }
        }

        async function testPHP() {
            showResult('PHP 테스트 중...', true);
            await makeRequest('test');
        }

        async function testRefresh() {
            showResult('토큰 갱신 중...', true);
            await makeRequest('refresh');
        }

        async function testOrders() {
            showResult('주문 수 조회 중...', true);
            const startDate = document.getElementById('start-date').value;
            const endDate = document.getElementById('end-date').value;
            await makeRequest('orders', `&start_date=${startDate}&end_date=${endDate}`);
        }

        // 페이지 로드시 자동 PHP 테스트
        window.onload = function() {
            console.log('Cafe24 API 테스트 페이지 로드됨');
        };
    </script>
</body>
</html>