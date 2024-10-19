<?php
// Display errors for debugging (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include Composer's autoloader
require 'vendor/autoload.php';
use Telegram\Bot\Api;

// Load environment variables
$botToken = getenv('TELEGRAM_BOT_TOKEN');
$telegram = new Api($botToken);

// Database connection details
$host = 'aws-0-us-east-1.pooler.supabase.com';
$dbname = 'postgres';
$user = 'postgres.fncvidwqvcmbivahaolo';
$password = 'fisayomi@123';
$port = '6543';

try {
    // Create a new PDO instance for the PostgreSQL connection
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    error_log('Connected to the database successfully');
} catch (PDOException $e) {
    // Handle connection errors
    error_log("Database connection failed: " . $e->getMessage());
    exit('Database connection failed');
}

// Get the webhook update
try {
    $update = $telegram->getWebhookUpdate();
    error_log('Incoming update: ' . print_r($update, true));
} catch (Exception $e) {
    error_log('Failed to get webhook update: ' . $e->getMessage());
    exit('Webhook update failed');
}

// Check if the update contains a message
if (isset($update['message'])) {
    $message = $update['message'];

    // Check if 'text' and 'chat' fields exist
    if (isset($message['text']) && isset($message['chat'])) {
        $text = $message['text'];
        $chat_id = $message['chat']['id'];
        $user = $message['from'];

        $telegram_id = $user['id'];
        $first_name = $user['first_name'] ?? ''; // Use null coalescing operator
        $last_name = $user['last_name'] ?? '';
        $username = $user['username'] ?? '';
        $photo_url = ''; // You would need to fetch this via another API call if required
        $date_joined = date('Y-m-d H:i:s');

        // Handle the /start command
        if (strpos($text, '/start') === 0) {
            // Check if the /start command contains a referral ID (e.g., /start 123456789)
            $parts = explode(' ', $text);
            $referrerId = isset($parts[1]) ? $parts[1] : null;

            try {
                // Check if the user already exists in the database
                error_log("Checking if user with telegram_id = $telegram_id exists in the database.");
                $stmt = $pdo->prepare('SELECT * FROM users WHERE telegram_id = :telegram_id');
                $stmt->execute(['telegram_id' => $telegram_id]);
                $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$existingUser) {
                    // Insert new user into the database
                    error_log('Inserting new user into the database.');
                    $stmt = $pdo->prepare('
                        INSERT INTO users (telegram_id, first_name, last_name, username, photo_url, date_joined, tokens)
                        VALUES (:telegram_id, :first_name, :last_name, :username, :photo_url, :date_joined, 0)
                    ');

                    // Bind values to avoid potential SQL issues
                    $stmt->bindValue(':telegram_id', $telegram_id, PDO::PARAM_INT);
                    $stmt->bindValue(':first_name', $first_name, PDO::PARAM_STR);
                    $stmt->bindValue(':last_name', $last_name, PDO::PARAM_STR);
                    $stmt->bindValue(':username', $username, PDO::PARAM_STR);
                    $stmt->bindValue(':photo_url', $photo_url, PDO::PARAM_STR);
                    $stmt->bindValue(':date_joined', $date_joined, PDO::PARAM_STR);
                    $stmt->execute();

                    error_log('User inserted successfully');
                } else {
                    error_log('User already exists in the database');
                }

                // Handle referral if referrerId is provided
                if ($referrerId) {
                    error_log("New user was referred by Telegram ID: $referrerId");

                    // Check if the referrer exists in the database
                    $stmt = $pdo->prepare('SELECT * FROM users WHERE telegram_id = :telegram_id');
                    $stmt->execute(['telegram_id' => $referrerId]);
                    $referrer = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($referrer) {
                        // Store the referral information
                        $stmt = $pdo->prepare('
                            INSERT INTO referrals (referrer_id, referred_id, referred_at)
                            VALUES (:referrer_id, :referred_id, NOW())
                        ');
                        $stmt->bindValue(':referrer_id', $referrerId, PDO::PARAM_INT);
                        $stmt->bindValue(':referred_id', $telegram_id, PDO::PARAM_INT);
                        $stmt->execute();

                        error_log("Referral successfully recorded: $referrerId referred $telegram_id");

                        // Optionally, reward the referrer
                        // For example, increase the referrer's tokens
                        $stmt = $pdo->prepare('
                            UPDATE users SET tokens = tokens + 1 WHERE telegram_id = :telegram_id
                        ');
                        $stmt->bindValue(':telegram_id', $referrerId, PDO::PARAM_INT);
                        $stmt->execute();

                        // Notify the referrer
                        $telegram->sendMessage([
                            'chat_id' => $referrerId,
                            'text' => "Thank you! You've referred a new user and earned 1 token."
                        ]);
                    } else {
                        error_log("Referrer with ID $referrerId not found.");
                    }
                }

                // Send welcome message with mini app URL
                $miniAppUrl = 'https://t.me/BOXERCOIN_BOT/box'; // Link to your mini app
                $urlWithId = $miniAppUrl . '?telegram_id=' . $telegram_id; // Include Telegram ID

                $telegram->sendMessage([
                    'chat_id' => $chat_id,
                    'text' => "Welcome! Click the link to access your details: $urlWithId"
                ]);
            } catch (PDOException $e) {
                error_log('Database query error: ' . $e->getMessage());
            } catch (Exception $e) {
                error_log('Error sending message: ' . $e->getMessage());
            }
        }
    } else {
        error_log('Message does not contain expected fields');
    }
} else {
    error_log('No message in the update');
}
