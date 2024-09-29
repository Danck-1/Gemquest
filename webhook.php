<?php

// Load the composer autoload if you're using dependencies
require __DIR__ . '/vendor/autoload.php';

// You can access the raw POST data received from Telegram
$update = json_decode(file_get_contents('php://input'), TRUE);

if (isset($update['message'])) {
    $chat_id = $update['message']['chat']['id'];
    $message_text = $update['message']['text'];

    // Sample response message to user
    $response_text = "You said: " . $message_text;

    // Use Telegram Bot API to send a message back
    $url = "https://api.telegram.org/bot7831758723:AAGE6llkvTnYJHRpXbx0drYb2SdBmnWocag/sendMessage?chat_id=" . $chat_id . "&text=" . urlencode($response_text);

    file_get_contents($url);
}
?>
