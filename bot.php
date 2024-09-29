<?php

// Database connection credentials
$host = 'aws-0-us-east-1.pooler.supabase.com';
$port = 6543;
$dbname = 'postgres';
$user = 'postgres.fncvidwqvcmbivahaolo';
$password = 'fisayomi@123host';
$dsn = "pgsql:host=$host;port=$port;dbname=$dbname;user=$user;password=$password";

// Telegram bot token (for verifying initData)
$bot_token = '7831758723:AAGE6llkvTnYJHRpXbx0drYb2SdBmnWocag';

// Connect to PostgreSQL
try {
    $pdo = new PDO($dsn);
} catch (PDOException $e) {
    die(json_encode(['success' => false, 'error' => 'Database connection failed']));
}

// Retrieve and decode the incoming JSON data
$data = json_decode(file_get_contents('php://input'), true);
$initData = $data['initData'];
$user = $data['user'];

// Function to verify the Telegram initData hash
function verifyTelegramAuth($initData, $bot_token) {
    // Parse the initData string
    parse_str($initData, $data_arr);
    
    // Separate the hash and data
    $hash = $data_arr['hash'];
    unset($data_arr['hash']);
    
    // Sort the data and create a string representation
    ksort($data_arr);
    $data_check_string = http_build_query($data_arr, '', "\n");
    
    // Calculate the HMAC hash using SHA256
    $secret_key = hash('sha256', $bot_token, true);
    $hmac = hash_hmac('sha256', $data_check_string, $secret_key);

    // Compare the calculated HMAC to the provided hash
    return $hmac === $hash;
}

// Verify the user's data
if (verifyTelegramAuth($initData, $bot_token)) {
    // User authentication successful
    $username = $user['username'] ?? $user['first_name'];

    // Insert user data into the database
    $stmt = $pdo->prepare("INSERT INTO telegram_users (user_id, first_name, username) VALUES (?, ?, ?) ON CONFLICT (user_id) DO NOTHING");
    $stmt->execute([$user['id'], $user['first_name'], $user['username']]);

    // Respond with the username
    echo json_encode(['success' => true, 'username' => $username]);
} else {
    // Authentication failed
    echo json_encode(['success' => false, 'error' => 'Invalid data']);
}
?>
