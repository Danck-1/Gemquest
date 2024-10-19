document.addEventListener('DOMContentLoaded', () => {
    // Modal functionality
    const diamondIcon = document.getElementById('diamond-icon');
    const modal = document.getElementById('profile-modal');
    const closeModal = document.getElementById('close-modal');

    if (diamondIcon && modal && closeModal) {
        diamondIcon.onclick = function () {
            modal.style.display = "block";
            // Fetch user details and populate modal with user info
            const userId = window.Telegram.WebApp.initDataUnsafe.user.id; // Get Telegram user ID
            const apiUrl = `https://gemguest.onrender.com/getUserDetails.php?telegram_id=${userId}`;

            fetch(apiUrl)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        console.error('Error fetching user details:', data.error);
                        document.getElementById('modal-days-in-game').innerText = 'Error';
                        document.getElementById('modal-gem-points').innerText = 'Error';
                    } else {
                        document.getElementById('modal-days-in-game').innerText = data.telegram_age || '30'; // Default to 30 if not set
                        document.getElementById('modal-gem-points').innerText = data.tokens || '1000'; // Default to 1000 if not set
                        document.getElementById('username').innerText = `${data.first_name} ${data.last_name}`;
                    }
                })
                .catch(error => {
                    console.error('Error fetching user details:', error);
                    document.getElementById('modal-days-in-game').innerText = 'Error';
                    document.getElementById('modal-gem-points').innerText = 'Error';
                });
        };

        closeModal.onclick = function () {
            modal.style.display = "none";
        };

        window.onclick = function (event) {
            if (event.target === modal) {
                modal.style.display = "none";
            }
        };
    }

    // Fetch and display tasks
    fetch('https://gemguest.onrender.com/getTasks.php')
        .then(response => response.json())
        .then(data => {
            if (data.tasks) {
                displayTasks(data.tasks);
            } else {
                console.error('No tasks found or error in response:', data);
            }
        })
        .catch(error => {
            console.error('Error fetching tasks:', error);
        });

    function displayTasks(tasks) {
        const taskList = document.getElementById('task-list');
        taskList.innerHTML = ''; // Clear any existing tasks

        tasks.forEach(task => {
            createTaskCard(taskList, task);
        });

        // Add referral link and copy button at the end of the task section
        addReferralLinkSection(); // Referral link logic is moved to a separate function
    }

    function createTaskCard(taskList, task) {
        const taskCard = document.createElement('div');
        taskCard.classList.add('task-card');

        const taskInfo = document.createElement('div');
        taskInfo.classList.add('task-info');

        // Task Title
        const taskTitle = document.createElement('p');
        taskTitle.classList.add('task-title');
        taskTitle.innerText = task.task_name;
        taskInfo.appendChild(taskTitle);

        // Task Reward
        const taskReward = document.createElement('p');
        taskReward.classList.add('task-reward');
        taskReward.innerText = `${task.reward_gp} GP`; // Fixed template string usage
        taskInfo.appendChild(taskReward);

        taskCard.appendChild(taskInfo);

        // Task Button
        const taskButton = document.createElement('button');
        taskButton.classList.add('task-button');
        taskButton.innerText = 'Open';
        taskButton.setAttribute('data-task-url', task.task_url);
        taskButton.setAttribute('data-task-reward', task.reward_gp);
        taskButton.setAttribute('data-task-id', task.task_id); // Store task ID for verification
        taskButton.addEventListener('click', handleTaskButtonClick);
        
        taskCard.appendChild(taskButton);

        // Append the task to the task list
        taskList.appendChild(taskCard);
    }

    // Task button functionality
    function handleTaskButtonClick(event) {
        const button = event.currentTarget;
        const taskUrl = button.getAttribute('data-task-url');
        const taskId = button.getAttribute('data-task-id');
        const rewardGp = button.getAttribute('data-task-reward');
        const telegramId = window.Telegram.WebApp.initDataUnsafe.user.id; // Assuming Telegram WebApp

        // Step 1: Open the task and change button to "Verify"
        if (button.innerText === 'Open') {
            window.open(taskUrl, '_blank'); // Open task URL in a new tab
            button.innerText = 'Verify'; // Change button text to "Verify"
        }
        
        // Step 2: On clicking "Verify", send verification request
        else if (button.innerText === 'Verify') {
            verifyTaskCompletion(taskId, rewardGp, telegramId, button);
        }
        
        // Step 3: Claim the reward once verified
        else if (button.innerText === 'Claim') {
            claimTaskReward(taskId, rewardGp, telegramId, button);
        }
    }

    function verifyTaskCompletion(taskId, rewardGp, telegramId, button) {
        // Send a POST request to your PHP backend to verify the task
        fetch('https://gemguest.onrender.com/verifyTask.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                task_id: taskId,
                telegram_id: telegramId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Task verified, change button to "Claim"
                button.innerText = 'Claim';
            } else {
                // Show error message
                alert(data.error || 'Task verification failed.');
            }
        })
        .catch(error => {
            console.error('Error verifying task:', error);
            alert('Error during task verification.');
        });
    }

    function claimTaskReward(taskId, rewardGp, telegramId, button) {
        // Send a POST request to your PHP backend to claim the reward
        fetch('https://gemguest.onrender.com/claimTaskReward.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                task_id: taskId,
                reward_gp: rewardGp,
                telegram_id: telegramId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update balance and disable the button after claiming
                document.getElementById('balance').innerText = `${data.newBalance} GP`; // Fixed template string usage
                button.innerText = 'Claimed';
                button.disabled = true; // Prevent further clicks
            } else {
                // Show error message
                alert(data.error || 'Task reward claim failed.');
            }
        })
        .catch(error => {
            console.error('Error claiming reward:', error);
            alert('Error during reward claim.');
        });
    }

    // Theme toggle functionality
    const themeToggleButton = document.querySelector('.theme-toggle');
    const body = document.body;

    if (themeToggleButton) {
        themeToggleButton.addEventListener('click', () => {
            const isDark = body.getAttribute('data-theme') === 'dark';
            body.setAttribute('data-theme', isDark ? '' : 'dark'); // Toggle theme
        });
    }

    // Fetch user details and update UI
    const usernameElement = document.getElementById('username');

    if (usernameElement && window.Telegram.WebApp) {
        const telegram = window.Telegram.WebApp;
        telegram.ready();

        const user = telegram.initDataUnsafe.user;
        if (user && user.id) {
            const welcomeMessage = `Welcome, ${user.first_name}!`;
            usernameElement.innerText = welcomeMessage;

            const apiUrl = `https://gemguest.onrender.com/getUserDetails.php?telegram_id=${user.id}`;
            fetch(apiUrl)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        usernameElement.innerText = data.error;
                    } else {
                        const gemsCountElement = document.querySelector('.gems-count');
                        if (gemsCountElement && data.tokens !== undefined) {
                            gemsCountElement.innerText = data.tokens; // Set the tokens as GP
                        }
                        const balanceElement = document.getElementById('balance');
                        if (balanceElement && data.tokens !== undefined) {
                            balanceElement.innerText = `${data.tokens} GP`; // Update the balance too
                        }
                    }
                })
                .catch(error => {
                    usernameElement.innerText = 'Failed to fetch user details: ' + error.message;
                    console.error('Error fetching user details:', error);
                });
        } else {
            usernameElement.innerText = 'No Telegram ID provided.';
        }
    }
    // Function to add referral link and copy button under the task section
    function addReferralLinkSection() {
        const taskList = document.getElementById('task-list');
        // Create container for referral link
        const referralContainer = document.createElement('div');
        referralContainer.classList.add('referral-section');
        // Referral Link Label
        const referralLabel = document.createElement('p');
        referralLabel.innerText = 'Your Referral Link:';
        referralContainer.appendChild(referralLabel);
        // Referral Link
        const referralLink = document.createElement('input');
        const telegramId = window.Telegram.WebApp.initDataUnsafe.user.id; // Get the user's Telegram ID
        referralLink.value = `https://gemguest.onrender.com/referral.php?ref=${telegramId}`; // Update with your referral logic
        referralLink.readOnly = true;
        referralContainer.appendChild(referralLink);
        // Copy Button
        const copyButton = document.createElement('button');
        copyButton.innerText = 'Copy';
        copyButton.addEventListener('click', () => {
            referralLink.select();
            document.execCommand('copy');
            alert('Referral link copied to clipboard!');
        });
        referralContainer.appendChild(copyButton);
        // Append the referral container to task list
        taskList.appendChild(referralContainer);
    }
});
