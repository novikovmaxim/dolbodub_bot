<?php
/**
 * Telegram Bot access token и URL.
 */
$access_token = '213612388:AAGycym12GKlPVyugKUaxGfTjBaBfawnbI4';
$api = 'https://api.telegram.org/bot' . $access_token;

/**
 * Задаём основные переменные.
 */
$output = json_decode(file_get_contents('php://input'), TRUE);
$chat_id = $output['message']['chat']['id'];
$first_name = $output['message']['chat']['first_name'];
$message = $output['message']['text'];

/**
 * Emoji для лучшего визуального оформления.
 */
$emoji = array(
  'preload' => json_decode('"\uD83D\uDE03"'), // Улыбочка.
  'weather' => array(
    'clear' => json_decode('"\u2600"'), // Солнце.
    'clouds' => json_decode('"\u2601"'), // Облака.
    'rain' => json_decode('"\u2614"'), // Дождь.
    'snow' => json_decode('"\u2744"'), // Снег.
  ),
);

/**
 * Получаем команды от пользователя.
 */
switch(strtolower_ru($message)) {
  // API погоды предоставлено OpenWeatherMap.
  // @see http://openweathermap.org
//  case ('/pogoda' || 'погода'):
  case ('/pogoda'):
  case ('погода'):
    // Отправляем приветственный текст.
    $preload_text = 'Одну секунду, ' . $first_name . ' ' . $emoji['preload'] . ' Я уточняю для вас погоду в Москве...';
    sendMessage($chat_id, $preload_text);
    // App ID для OpenWeatherMap.
    $appid = 'da17ea4e8bdeda3c5b4b0ccbeea04fa7';
    // ID для города/района/местности (есть все города РФ).
    $id = '524901'; // Для примера: Москва.
    // Получаем JSON-ответ от OpenWeatherMap.
    $pogoda = json_decode(file_get_contents('http://api.openweathermap.org/data/2.5/weather?appid=' . $appid . '&id=' . $id . '&units=metric&lang=ru'), TRUE);
    // Определяем тип погоды из ответа и выводим соответствующий Emoji.
    if ($pogoda['weather'][0]['main'] === 'Clear') { $weather_type = $emoji['weather']['clear'] . ' ' . $pogoda['weather'][0]['description']; }
    elseif ($pogoda['weather'][0]['main'] === 'Clouds') { $weather_type = $emoji['weather']['clouds'] . ' ' . $pogoda['weather'][0]['description']; }
    elseif ($pogoda['weather'][0]['main'] === 'Rain') { $weather_type = $emoji['weather']['rain'] . ' ' . $pogoda['weather'][0]['description']; }
    elseif ($pogoda['weather'][0]['main'] === 'Snow') { $weather_type = $emoji['weather']['snow'] . ' ' . $pogoda['weather'][0]['description']; }
    else $weather_type = $pogoda['weather'][0]['description'];
    // Температура воздуха.
    if ($pogoda['main']['temp'] > 0) { $temperature = '+' . sprintf("%d", $pogoda['main']['temp']); }
    else { $temperature = sprintf("%d", $pogoda['main']['temp']); }
    // Направление ветра.
    if ($pogoda['wind']['deg'] >= 0 && $pogoda['wind']['deg'] <= 11.25) { $wind_direction = 'северный'; }
    elseif ($pogoda['wind']['deg'] > 11.25 && $pogoda['wind']['deg'] <= 78.75) { $wind_direction = 'северо-восточный, '; }
    elseif ($pogoda['wind']['deg'] > 78.75 && $pogoda['wind']['deg'] <= 101.25) { $wind_direction = 'восточный, '; }
    elseif ($pogoda['wind']['deg'] > 101.25 && $pogoda['wind']['deg'] <= 168.75) { $wind_direction = 'юго-восточный, '; }
    elseif ($pogoda['wind']['deg'] > 168.75 && $pogoda['wind']['deg'] <= 191.25) { $wind_direction = 'южный, '; }
    elseif ($pogoda['wind']['deg'] > 191.25 && $pogoda['wind']['deg'] <= 258.75) { $wind_direction = 'юго-западный, '; }
    elseif ($pogoda['wind']['deg'] > 258.75 && $pogoda['wind']['deg'] <= 281.25) { $wind_direction = 'западный, '; }
    elseif ($pogoda['wind']['deg'] > 281.25 && $pogoda['wind']['deg'] <= 348.75) { $wind_direction = 'северо-западный, '; }
    else { $wind_direction = ' '; }
    // Формирование ответа.
    $weather_text = 'Сейчас ' . $weather_type . '. Температура воздуха: ' . $temperature . '°C. Ветер ' . $wind_direction . sprintf("%u", $pogoda['wind']['speed']) . ' м/сек.';
    // Отправка ответа пользователю Telegram.
    sendMessage($chat_id, $weather_text);
    break;
  case ('привет'):
  case ('/hello'):
    sendMessage($chat_id, 'Привет, '. $first_name . '! ' . $emoji['preload'] );
    break;
  case ('/start'):
    break;
  default:
    sendMessage($chat_id, 'Неизвестная команда!' );
    // sendMessage($chat_id, 'Неизвестная команда!!!' );
    break;
}

/**
 * Функция отправки сообщения sendMessage().
 */
function sendMessage($chat_id, $message) {
  file_get_contents($GLOBALS['api'] . '/sendMessage?chat_id=' . $chat_id . '&text=' . urlencode($message));
}

function strtolower_ru($text) {
 $alfavitlover = array('ё','й','ц','у','к','е','н','г', 'ш','щ','з','х','ъ','ф','ы','в', 'а','п','р','о','л','д','ж','э', 'я','ч','с','м','и','т','ь','б','ю');
 $alfavitupper = array('Ё','Й','Ц','У','К','Е','Н','Г', 'Ш','Щ','З','Х','Ъ','Ф','Ы','В', 'А','П','Р','О','Л','Д','Ж','Э', 'Я','Ч','С','М','И','Т','Ь','Б','Ю');
 return str_replace($alfavitupper,$alfavitlover,strtolower($text));
}