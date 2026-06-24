<?php

function getTokenFromJson() {

    $tokenFile = 'token.json';

    if (file_exists($tokenFile)) {

        $json = file_get_contents($tokenFile);

        $data = json_decode($json, true);

        return isset($data['token']) ? $data['token'] : null;

    }

    return null;

}



$botToken = getTokenFromJson();

if (!$botToken) {

    header('Content-Type: application/json');

    echo json_encode(['success' => false, 'error' => 'Token bulunamadı']);

    exit;

}



if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header('Content-Type: application/json');

    echo json_encode(['success' => false, 'error' => 'Sadece POST istekleri kabul edilir']);

    exit;

}



if (isset($_FILES['file']) && isset($_POST['chat_id']) && isset($_POST['type'])) {

    $chat_id = $_POST['chat_id'];

    $type = $_POST['type'];

    $file = $_FILES['file'];

    

    if ($file['error'] !== UPLOAD_ERR_OK) {

        header('Content-Type: application/json');

        echo json_encode(['success' => false, 'error' => 'Dosya yükleme hatası']);

        exit;

    }

    

    $url = "https://api.telegram.org/bot{$botToken}/send" . ucfirst($type);

    

    $postData = [

        'chat_id' => $chat_id,

        $type => new CURLFile($file['tmp_name'], $file['type'], $file['name'])

    ];

    

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);

    curl_setopt($ch, CURLOPT_POST, true);

    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    

    $response = curl_exec($ch);

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    

    header('Content-Type: application/json');

    if ($httpCode === 200) {

        echo json_encode(['success' => true, 'response' => json_decode($response, true)]);

    } else {

        echo json_encode(['success' => false, 'error' => 'Telegram API hatası', 'response' => $response]);

    }

    exit;

}



$input = file_get_contents('php://input');

$data = json_decode($input, true);



if (!$data || !isset($data['chat_id']) || !isset($data['text'])) {

    header('Content-Type: application/json');

    echo json_encode(['success' => false, 'error' => 'Geçersiz veri']);

    exit;

}



$url = "https://api.telegram.org/bot{$botToken}/sendMessage";



$postData = [

    'chat_id' => $data['chat_id'],

    'text' => $data['text']

];



$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $url);

curl_setopt($ch, CURLOPT_POST, true);

curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

curl_setopt($ch, CURLOPT_HTTPHEADER, [

    'Content-Type: application/json'

]);



$response = curl_exec($ch);

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

curl_close($ch);



header('Content-Type: application/json');

if ($httpCode === 200) {

    $result = json_decode($response, true);

    if ($result['ok']) {

        echo json_encode(['success' => true, 'response' => $result]);

    } else {

        echo json_encode(['success' => false, 'error' => $result['description']]);

    }

} else {

    echo json_encode(['success' => false, 'error' => 'HTTP hatası: ' . $httpCode]);

}

?>
