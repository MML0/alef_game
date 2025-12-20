<?php
include 'db.php'; // Include the database connection

// Check if the form has been submitted
if (isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action == 'start_game') {
        // Start the game (set game status to 'ongoing')
        startGame();
    } elseif ($action == 'end_game') {
        // End the game (set game status to 'ended')
        endGame();
    } elseif ($action == 'wait_game') {
        // Set game status to 'not_started'
        waitGame();
    }
}

function startGame() {
    global $pdo;
    // Set game status to 'ongoing'
    $stmt = $pdo->prepare("UPDATE game_stats SET game_status = 'ongoing' WHERE game_status = 'not_started'");
    $stmt->execute();
    $stmt = $pdo->prepare("UPDATE game_stats SET game_status = 'ongoing' WHERE game_status = 'ended'");
    $stmt->execute();
    echo "Game started!<br>";
}

function endGame() {
    global $pdo;
    // Set game status to 'ended'
    $stmt = $pdo->prepare("UPDATE game_stats SET game_status = 'ended' WHERE game_status = 'ongoing'");
    $stmt->execute();
    echo "Game ended!<br>";
}


function waitGame() {
    global $pdo;
    // Set game status to 'not_started' (game waiting)
    $stmt = $pdo->prepare("UPDATE game_stats SET game_status = 'not_started' WHERE game_status = 'ongoing' OR game_status = 'ended'");
    $stmt->execute();
    echo "Game status set to 'not_started'. Waiting for game start...<br>";
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 50px;
        }
        .btn {
            padding: 10px 20px;
            margin: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        .btn:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>

<h1>Admin Panel</h1>

<!-- Start Game Button -->
<form method="POST">
    <button type="submit" name="action" value="start_game" class="btn">Start Game</button>
</form>

<!-- End Game Button -->
<form method="POST">
    <button type="submit" name="action" value="end_game" class="btn">End Game</button>
</form>
<!-- Wait Game Button (sets the game to 'not_started') -->
<form method="POST">
    <button type="submit" name="action" value="wait_game" class="btn" id="waitGameBtn">Wait for Game</button>
</form>

<!-- Migrate Database Button -->
<button class="btn" onclick="migrateDatabase()">Migrate Database</button>

<script>
// JavaScript function to handle migration process
function migrateDatabase() {
    // Prompt for password input
    const password = prompt("Please enter the admin password:");

    // Check if the password is correct
    if (1) {
        // If password is correct, make a GET request to the migration PHP script
        const url = `http://0.0.0.0:3000/backend/migration.php?fresh=yes&pass=${password}`;
        
        // Create a new GET request using the Fetch API
        fetch(url)
            .then(response => response.text())
            .then(data => {
                // Show the response from the PHP script
                alert('Migration result: ' + data);
            })
            .catch(error => {
                // Handle any errors that occur during the request
                alert('An error occurred: ' + error);
            });
    } else {
        // If password is incorrect, show an alert
        alert("Incorrect password!");
    }
}
</script>

</body>
</html>
