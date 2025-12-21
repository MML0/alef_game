<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

include 'db.php'; // Include the database connection

function insertUserAnswer($user_token, $answer, $question_id) {
    global $pdo;
    
    // Get the user from the token
    $stmt = $pdo->prepare("SELECT * FROM users WHERE token = ?");
    $stmt->execute([$user_token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        // Check if the user already answered the question
        $checkAnswerStmt = $pdo->prepare("SELECT * FROM answers WHERE user_id = ? AND question_id = ?");
        $checkAnswerStmt->execute([$user['id'], $question_id]);
        $previousAnswer = $checkAnswerStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($previousAnswer) {
            return ['status' => 'error', 'message' => 'You already answered this question'];
        }

        // Get the correct answer for the question
        $stmt = $pdo->prepare("SELECT * FROM questions WHERE id = ?");
        $stmt->execute([$question_id]);
        $question = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($question) {
            // Check time
            $max_time_seconds = 600; // 10 minutes (600 seconds)
            $stmt = $pdo->prepare("SELECT TIMESTAMPDIFF(SECOND, start_game_time, CURRENT_TIMESTAMP) AS elapsed_time FROM users WHERE id = ?");
            $stmt->execute([$user['id']]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                $elapsed_time = $result['elapsed_time'];
                $remaining_time = $max_time_seconds - $elapsed_time;
                if ($remaining_time < 0) {
                    return ['status' => 'error', 'message' => 'Time is up'];
                }
            }
            
            // Check if the user's answer is correct
            $is_correct = ($answer == $question['correct_answer']) ? 1 : 0; // 1 for correct, 0 for incorrect

            // Insert the user's answer into the database
            $insertStmt = $pdo->prepare("INSERT INTO answers (user_id, question_id, answer, is_correct) 
                                        VALUES (?, ?, ?, ?)");
            $insertStmt->execute([$user['id'], $question_id, $answer, $is_correct]);

            // Increment question number
            $updateScoreStmt = $pdo->prepare("UPDATE users SET current_question = current_question + 1 WHERE id = ?");
            $updateScoreStmt->execute([$user['id']]);

            // If the answer is correct, increment the user's score
            if ($is_correct) {
                $updateScoreStmt = $pdo->prepare("UPDATE users SET score = score + 1 WHERE id = ?");
                $updateScoreStmt->execute([$user['id']]);
            }
            
            // Return success response
            return [
                'status' => 'success',
                'correct' => $is_correct,
                'correct_answer_num' => $question['correct_answer'],
                'message' => $is_correct ? 'Correct answer!' : 'Incorrect answer.',
                'score' => $user['score'],
                'remaining_time' => $remaining_time
            ];
        } else {
            return ['status' => 'error', 'message' => 'Question not found'];
        }
    } else {
        return ['status' => 'error', 'message' => 'User not found'];
    }
}

// Example usage
$data = json_decode(file_get_contents("php://input"));
$user_token = $data->token;
$answer = $data->answer;
$question_id = $data->question_id;

// Call the function to insert the user's answer and return the result
$result = insertUserAnswer($user_token, $answer, $question_id);
echo json_encode($result);
?>
