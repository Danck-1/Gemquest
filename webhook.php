<?php
// Get the incoming Telegram update as JSON
$update = file_get_contents('php://input');

// Decode the JSON update to an associative array
$updateArray = json_decode($update, true);

// Extract necessary information
$chatId = $updateArray['message']['chat']['id'];
$message = $updateArray['message']['text'];

// Set the bot token (Replace with your own)
$botToken = "YOUR_BOT_TOKEN_HERE";

// Respond based on the incoming message
if ($message === "/start") {
    $responseMessage = "Welcome! I am your bot.";
} else {
    $responseMessage = "You said: " . $message;
}

// Send a message back using Telegram's sendMessage API
$sendMessageUrl = "https://api.telegram.org/bot$botToken/sendMessage?chat_id=$chatId&text=" . urlencode($responseMessage);

// Send the message
file_get_contents($sendMessageUrl);
?>
