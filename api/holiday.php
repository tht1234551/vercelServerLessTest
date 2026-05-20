<?php

header('Content-Type: application/json; charset=utf-8');


$url = 'https://apis.data.go.kr/B090041/openapi/service/SpcdeInfoService/getRestDeInfo';
$serviceKey = getenv('HOLIDAY_SERVICE_KEY');

if ($serviceKey === false || trim($serviceKey) === '') {
    http_response_code(500);

    echo json_encode([
        'error'   => 'service_key_missing',
        'message' => 'HOLIDAY_SERVICE_KEY 환경변수가 설정되지 않았습니다.',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$params = array_merge(
    $_GET,
    [
        'serviceKey' => $serviceKey,
        'solYear' => $_GET['solYear'],
        'solMonth' => $_GET['solMonth'],
        'numOfRows' => $_GET['numOfRows'],
        'pageNo' => $_GET['pageNo']
    ]
);

$requestUrl = $url . '?' . http_build_query($params);

$ch = curl_init($requestUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_TIMEOUT        => 10,
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr  = curl_error($ch);
unset($ch);

if ($response === false) {
    http_response_code(502);
    echo json_encode(['error' => 'request_failed', 'message' => $curlErr], JSON_UNESCAPED_UNICODE);
    exit;
}

libxml_use_internal_errors(true);
$xml = simplexml_load_string($response);
if ($xml === false) {
    http_response_code(502);
    echo json_encode(['error' => 'xml_parse_failed', 'message' => $response], JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode($xml, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);