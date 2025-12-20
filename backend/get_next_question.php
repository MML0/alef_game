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
        $game_status_stmt = $pdo->prepare("SELECT * FROM game_stats WHERE user_id = ?");
        $game_status_stmt->execute([$user['id']]);
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

                // Get the next question from the database
                $next_question_stmt = $pdo->prepare("SELECT * FROM questions WHERE id = ?");
                $next_question_stmt->execute([$current_question + 1]);
                $next_question = $next_question_stmt->fetch(PDO::FETCH_ASSOC);

                if ($next_question) {
                    // Update the user's current question in the database
                    $update_stmt = $pdo->prepare("UPDATE users SET current_question = ? WHERE id = ?");
                    $update_stmt->execute([$current_question + 1, $user['id']]);

                    return [
                        'status' => 'success',
                        'current_question' => $current_question + 1,
                        'question_text' => $next_question['question_text'],
                        'answers' => [
                            $next_question['answer_1'],
                            $next_question['answer_2'],
                            $next_question['answer_3'],
                            $next_question['answer_4']
                        ]
                    ];
                } else {
                    // If there are no more questions
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
