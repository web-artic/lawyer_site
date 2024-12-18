<?php

// VK API Token и ID пользователя или группы
define('VK_TOKEN', '2d33ed172d33ed172d33ed177c2e12107c22d332d33ed174a2fe6cba23a75221cbf2063'); 
define('USER_ID', 'leo_dai'); // Укажите ID пользователя или группы

// Укажите точное время отправки
$sendTime = "2024-10-28 21:52:59.999"; // Формат: ГГГГ-ММ-ДД ЧЧ:ММ:СС.МММ

// Проверка времени
$currentMillis = round(microtime(true) * 1000); // Текущее время в миллисекундах
$sendMillis = strtotime($sendTime) * 1000; // Время отправки в миллисекундах

if ($currentMillis >= $sendMillis) {
    sendMessage("Привет! Это автоматическое сообщение."); // Текст сообщения
} else {
    echo "Ожидание до времени отправки...";
}

// Функция для отправки сообщения через VK API
function sendMessage($message) {
    $url = "https://api.vk.com/method/messages.send";
    $params = array(
        'user_id' => USER_ID,
        'message' => $message,
        'random_id' => mt_rand(),
        'access_token' => VK_TOKEN,
        'v' => '5.131'
    );

    $query = http_build_query($params);
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => 'Content-type: application/x-www-form-urlencoded',
            'content' => $query
        ]
    ]);

    $response = file_get_contents($url, false, $context);
    $responseData = json_decode($response, true);

    if (isset($responseData['response'])) {
        echo "Сообщение отправлено успешно!";
    } else {
        echo "Ошибка отправки сообщения: " . json_encode($responseData);
    }
}
?>

<div>
  <script src="https://unpkg.com/@vkid/sdk@<3.0.0/dist-sdk/umd/index.js"></script>
  <script type="text/javascript">
    if ('VKIDSDK' in window) {
      const VKID = window.VKIDSDK;

      VKID.Config.init({
        app: 52559211,
        redirectUrl: 'http://localhost/lawyers_site',
        responseMode: VKID.ConfigResponseMode.Callback,
        source: VKID.ConfigSource.LOWCODE,
      });

      const floatingOneTap = new VKID.FloatingOneTap();

      floatingOneTap.render({
        appName: 'ddf',
        showAlternativeLogin: true
      })
      .on(VKID.WidgetEvents.ERROR, vkidOnError)
      .on(VKID.FloatingOneTapInternalEvents.LOGIN_SUCCESS, function (payload) {
        const code = payload.code;
        const deviceId = payload.device_id;

        VKID.Auth.exchangeCode(code, deviceId)
          .then(vkidOnSuccess)
          .catch(vkidOnError);
      });
    
      function vkidOnSuccess(data) {
        floatingOneTap.close();
        
        // Обработка полученного результата
      }
    
      function vkidOnError(error) {
        // Обработка ошибки
      }
    }
  </script>
</div>