<?php
error_reporting(0);
header('Content-Type: application/json');

$original_url = $_GET['url'] ?? $_POST['url'] ?? '';

if (empty($original_url)) {
    echo json_encode(['success' => false, 'message' => 'URL girilmedi']);
    exit;
}

$original_url = urldecode($original_url);
$short_url = false;

$is_gd_api = "https://is.gd/create.php?format=simple&url=" . urlencode($original_url);
$short_url = @file_get_contents($is_gd_api);

if (!$short_url || strpos($short_url, 'http') !== 0 || strpos($short_url, 'Error') !== false) {
    $da_gd_api = "https://da.gd/s?url=" . urlencode($original_url);
    $short_url = @file_get_contents($da_gd_api);
}

if (!$short_url || strpos($short_url, 'http') !== 0) {
    $short_url = $original_url;
}

$response = [
    'success' => true,
    'data' => [
        'orjinal url' => $original_url,
        'kısaltılmış url' => trim($short_url)
    ]
];

echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
exit;
