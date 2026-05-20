<?php

// header('Content-Type: application/json; charset=utf-8');

$url = 'https://apis.data.go.kr/B090041/openapi/service/SpcdeInfoService/getHoliDeInfo';
$params = [
    'serviceKey' => getenv('HOLIDAY_SERVICE_KEY') ?: '당신의_인증키',
    'solYear'    => $_GET['solYear'] ?? '2025',
    'numOfRows'  => '100',
];

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
curl_close($ch);

if ($response === false) {
    http_response_code(502);
    echo json_encode(['error' => 'request_failed', 'message' => $curlErr], JSON_UNESCAPED_UNICODE);
    exit;
}

libxml_use_internal_errors(true);
$xml = simplexml_load_string($response);
if ($xml === false) {
    http_response_code(502);
    echo json_encode(['error' => 'xml_parse_failed', 'raw' => $response], JSON_UNESCAPED_UNICODE);
    exit;
}

$holidays = [];
foreach ($xml->xpath('//item') as $item) {
    $holidays[] = [
        'name' => (string) $item->dateName,
        'date' => (string) $item->locdate,
    ];
}

echo json_encode([
    'year'     => $params['solYear'],
    'count'    => count($holidays),
    'holidays' => $holidays,
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);