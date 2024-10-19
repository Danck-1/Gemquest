
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Check-In</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://telegram.org/js/telegram-web-app.js"></script> <!-- Include Telegram Web Apps script -->
</head>
<body>
    <div class="container">
        <!-- Theme Toggle Button (Moon for light theme, Sun for dark theme) -->
        <button class="theme-toggle">
            <i class="fas fa-moon"></i> <!-- Moon icon initially -->
        </button>

        <!-- Header Section -->
<!-- Header Section -->
<header class="header">
    <img src="diamond_icon.png" alt="Diamond Icon" class="icon" id="diamond-icon">
    <span class="header-text">DAILY CHECK-IN</span>
    <button id="checkin-button">Check-In</button>

    <!-- Seven segmented bars -->
    <div class="progress-bar-container">
        <div class="progress-bar-segment"></div>
        <div class="progress-bar-segment"></div>
        <div class="progress-bar-segment"></div>
        <div class="progress-bar-segment"></div>
        <div class="progress-bar-segment"></div>
        <div class="progress-bar-segment"></div>
        <div class="progress-bar-segment"></div>
    </div>
</header>


        <!-- Large Diamond Icon Section with Welcome Message -->
        <div class="welcome-section">
            <div class="large-icon">
                <img src="diamond_icon.png" alt="Large Diamond Icon">
            </div>
            <div class="welcome-message">
                <h2>Welcome, <span id="username">Guest</span>!</h2>
            </div>
        </div>

        <!-- Gems Card Section -->
<!-- Gems Card Section -->
<div class="gems-card">
    <div class="gems-info">
        <span class="gems-count">100</span> <!-- This will be dynamically updated -->
        <span class="gems-label">GP</span>
    </div>
    <img src="diamond_icon.png" alt="Diamond Icon" class="gems-icon">
</div>



        <!-- Task Section -->
      
<div class="tasks-section">
    <h3>Tasks</h3>
    <!-- This is where the task cards will be dynamically inserted -->
  <div id="task-list">
    <!-- Referral Task -->



    <!-- Show More/Show Less Link -->
    <a href="#" class="more-tasks" id="toggle-tasks">Show more tasks</a>
</div> 

        <!-- Bottom Navigation Section -->
        <nav class="bottom-nav">
            <a href="ranks.php" class="nav-item">Ranks</a>
            <a href="#" class="nav-item">Home</a>
            <a href="frens.php" class="nav-item">Frens</a>
        </nav>

        <!-- Modal Popup for Profile -->
        <div class="modal" id="profile-modal">
            <div class="modal-content">
                <span class="close-button" id="close-modal">&times;</span>
                <h2>User Profile</h2>
                <p>Days in Game: <span id="modal-days-in-game">30</span></p>
                <p>Gem Points: <span id="modal-gem-points">1000</span></p>
            </div>
        </div>
    </div>
    <script src="js/app.js"></script>

</body>
</html>
