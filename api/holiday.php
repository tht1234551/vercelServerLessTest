<?php
header('Content-Type: application/json; charset=utf-8');

/*
 * 공공데이터포털 - 특일 정보 (SpcdeInfoService)
 *   포털 URL : https://www.data.go.kr/data/15012690/openapi.do
 *   Base URL : https://apis.data.go.kr/B090041/openapi/service/SpcdeInfoService
 *
 * [지원 오퍼레이션]
 *   1. /getAnniversaryInfo  - 기념일 정보 조회 (연/월별)
 *   2. /getRestDeInfo       - 공휴일 정보 조회 (대체공휴일 포함, 관보 공포 후 반영)
 *   3. /getHoliDeInfo       - 국경일 및 공휴일 정보 조회
 *   4. /get24DivisionsInfo  - 24절기 정보 조회
 *   5. /getSundryDayInfo    - 잡절 정보 조회
 *   * 모든 오퍼레이션 일일 트래픽: 10000회
 *
 * [공통 요청 파라미터]
 *   - serviceKey (필수, 서버에서 주입)
 *   - solYear, solMonth (조회 연/월)
 *   - numOfRows, pageNo  (페이지네이션)
 *   - _type=json|xml     (응답 포맷)
 */

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

$clientParams = $_GET;
unset($clientParams['serviceKey']);

$params = array_merge($clientParams, ['serviceKey' => $serviceKey]);

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