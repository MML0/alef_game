<?php 
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

include 'db.php'; // Include the database connection

// Function to get the top 5 users based on score and game duration (time taken to finish the game)
function getScoreBoard() {
    global $pdo;

    // Query to get the top 5 users by score, and in case of a tie, by game duration
    $stmt = $pdo->prepare("
        SELECT u.id, u.first_name, u.last_name, u.score, u.start_game_time, u.end_game_time,
            -- If the game is not finished, calculate game duration based on current time
            CASE
                WHEN u.end_game_time IS NULL THEN TIMESTAMPDIFF(SECOND, u.start_game_time, CURRENT_TIMESTAMP)
                ELSE TIMESTAMPDIFF(SECOND, u.start_game_time, u.end_game_time)
            END AS game_duration
        FROM users u
        WHERE u.game_status = 'completed' OR u.game_status = 'ongoing'  -- Include users who finished or are still playing
        ORDER BY u.score DESC, game_duration ASC
        LIMIT 5
    ");
    $stmt->execute();

    $scoreBoard = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return [
        'status' => 'success',
        'scoreboard' => $scoreBoard
    ];
}

// Get the scoreboard data
$response = getScoreBoard();

// Return the response
echo json_encode($response);
?>
