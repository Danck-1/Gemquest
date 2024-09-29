<?php

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Log errors to server log
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error_log.txt'); // This logs errors to error_log.txt in the same directory

// Database connection credentials
$host = 'aws-0-us-east-1.pooler.supabase.com';
$port = 6543;
$dbname = 'postgres';
$user = 'postgres.fncvidwqvcmbivahaolo';
$password = 'fisayomi@123';
$dsn = "pgsql:host=$host;port=$port;dbname=$dbname";

// Telegram bot token (for verifying initData)
$bot_token = '7831758723:AAGE6llkvTnYJHRpXbx0drYb2SdBmnWocag';

// Connect to PostgreSQL
try {
    $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die(json_encode(['success' => false, 'error' => 'Database connection failed: ' . $e->getMessage()]));
}

// Retrieve and decode the incoming JSON data
$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    error_log("Failed to decode JSON data: " . file_get_contents('php://input'));
    die(json_encode(['success' => false, 'error' => 'Invalid JSON data']));
}

$initData = $data['initData'] ?? null;
$user = $data['user'] ?? null;

// Log the incoming data
error_log("Incoming data: " . print_r($data, true));

// Function to verify the Telegram initData hash
function verifyTelegramAuth($initData, $bot_token) {
    if (!$initData) {
        error_log("InitData is missing");
        return false;
    }
    
    // Parse the initData string
    parse_str($initData, $data_arr);
    
    // Check if hash exists
    if (!isset($data_arr['hash'])) {
        error_log("Hash is missing from initData");
        return false;
    }
    
    // Separate the hash from the rest of the data
    $hash = $data_arr['hash'];
    unset($data_arr['hash']);
    
    // Sort the data alphabetically and create a string representation
    ksort($data_arr);
    $data_check_string = http_build_query($data_arr, '', "\n");
    
    // Calculate the HMAC hash using SHA256 and the bot token as the secret key
    $secret_key = hash('sha256', $bot_token, true);
    $hmac = hash_hmac('sha256', $data_check_string, $secret_key);

    // Log the calculated HMAC and the provided hash
    error_log("Calculated HMAC: $hmac");
    error_log("Provided Hash: $hash");

    // Compare the calculated HMAC to the provided hash
    return hash_equals($hmac, $hash);
}

// Check if both user data and initData exist
if ($initData && $user && verifyTelegramAuth($initData, $bot_token)) {
    // User authentication successful
    $userId = $user['id'];
    $firstName = $user['first_name'] ?? '';
    $username = $user['username'] ?? '';

    try {
        // Insert user data into the database
        $stmt = $pdo->prepare("
            INSERT INTO telegram_users (user_id, first_name, username)
            VALUES (:user_id, :first_name, :username)
            ON CONFLICT (user_id) DO NOTHING
        ");
        $stmt->execute([
            'user_id' => $userId,
            'first_name' => $firstName,
            'username' => $username
        ]);

        // Respond with the username
        echo json_encode(['success' => true, 'username' => $username]);
    } catch (PDOException $e) {
        error_log("Database insert failed: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'Database insert failed: ' . $e->getMessage()]);
    }
} else {
    // Authentication failed
    error_log("Authentication failed or invalid data");
    echo json_encode(['success' => false, 'error' => 'Invalid data or failed authentication']);
}

?>
