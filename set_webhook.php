<?php
$token = json_decode(file_get_contents('token.json'), true)['token'];
$webhookUrl = 'https://camera-4lg4.onrender.com/abo.php';
$api = "https://api.telegram.org/bot$token/setWebhook?url=" . urlencode($webhookUrl);
echo file_get_contents($api);
?>