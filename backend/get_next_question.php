<?php 
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

include 'db.php'; // Include the database connection

function getNextQuestion($user_token) {
    global $pdo;
    
    // Check if the user exists with the provided token
    $stmt = $pdo->prepare("SELECT * FROM users WHERE token = ?");
    $stmt->execute([$user_token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        // Get the game status from the game_stats table
        $game_status_stmt = $pdo->query("SELECT * FROM game_stats LIMIT 1");
        $game_status = $game_status_stmt->fetch(PDO::FETCH_ASSOC);

        if ($game_status) {
            // Check if the game is completed
            if ($game_status['game_status'] == 'ended') {
                return [
                    'status' => 'error',
                    'message' => 'game ended'
                ];
            }

            // If the game has not started
            if ($game_status['game_status'] == 'not_started') {
                return [
                    'status' => 'error',
                    'message' => 'game not started'
                ];
            }

            // If the game is ongoing, get the next question
            if ($game_status['game_status'] == 'ongoing') {
                // Get the current question number
                $current_question = $user['current_question'];
                if ($user['game_status'] == 'not_started') {
                    // Set the game status to 'ongoing' and set the start_game_time timestamp
                    $update_game_status_stmt = $pdo->prepare("UPDATE users SET game_status = 'ongoing', start_game_time = CURRENT_TIMESTAMP(3) WHERE id = ?");
                    $update_game_status_stmt->execute([$user['id']]);
                }

                // Get the next question from the database
                $next_question_stmt = $pdo->prepare("SELECT * FROM questions WHERE id = ?");
                $next_question_stmt->execute([$current_question]);
                $next_question = $next_question_stmt->fetch(PDO::FETCH_ASSOC);

                if ($next_question) {
                    // Set the max time (10 minutes + 5 seconds = 605 seconds)
                    $max_time_seconds = 600; // 10 minutes in seconds
                    // Query to fetch the current time and start time from MySQL and calculate the remaining time
                    $stmt = $pdo->prepare("
                        SELECT TIMESTAMPDIFF(SECOND, start_game_time, CURRENT_TIMESTAMP) AS elapsed_time
                        FROM users WHERE id = ?
                    ");
                    $stmt->execute([$user['id']]);
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($result) {
                        $elapsed_time = $result['elapsed_time'];
                        $remaining_time = $max_time_seconds - $elapsed_time;

                        // Ensure remaining time does not go below zero
                        if ($remaining_time < 0) {
                            $remaining_time = 0;
                        }
                    }
                    // Ensure remaining time does not go below zero
                    if ($remaining_time < 0) {
                        $remaining_time = 0;
                    }
                    return [
                        'status' => 'success',
                        'current_question' => $current_question ,
                        'question_text' => $next_question['question_text'],
                        'answers' => [
                            $next_question['answer_1'],
                            $next_question['answer_2'],
                            $next_question['answer_3'],
                            $next_question['answer_4']
                        ],
                        'remaining_seconds' => $remaining_time,
                        'score' => $user['score'],
                    ];
                } else {
                    if ($user['game_status'] == 'ongoing') {
                        // Set the game status to 'ongoing' and set the end_game_time timestamp
                        $update_game_status_stmt = $pdo->prepare("UPDATE users SET game_status = 'ongoing', end_game_time = CURRENT_TIMESTAMP(3) WHERE id = ?");
                        $update_game_status_stmt->execute([$user['id']]);
                    }
                    // If there are no more questions
                    $update_game_status_stmt = $pdo->prepare("UPDATE users SET game_status = 'completed' WHERE id = ?");
                    $update_game_status_stmt->execute([$user['id']]);


                    return [
                        'status' => 'error',
                        'message' => 'all questions answered'
                    ];
                }
            }
        } else {
            return ['status' => 'error', 'message' => 'Game status not found'];
        }
    } else {
        return ['status' => 'error', 'message' => 'User not found'];
    }
}

// Retrieve the user token from the request body (POST)
$data = json_decode(file_get_contents("php://input"), true);
$user_token = $data['token'] ?? null;

// If there's no token, return an error
if (!$user_token) {
    echo json_encode(['status' => 'error', 'message' => 'Token is required']);
    exit();
}

// Call the function to get the next question
$response = getNextQuestion($user_token);

// Return the response
echo json_encode($response);
?>
