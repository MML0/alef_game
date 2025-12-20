<?php
header("Access-Control-Allow-Origin: *");  
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

include 'db.php'; // Include the database connection

function loginUser($phone_number) {
    global $pdo;
    
    // Check if the user exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE phone_number = ?");
    $stmt->execute([$phone_number]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        // Generate a unique token
        $token = bin2hex(random_bytes(16));
        
        // Update the token in the database
        $update_stmt = $pdo->prepare("UPDATE users SET token = ? WHERE id = ?");
        $update_stmt->execute([$token, $user['id']]);
        // If the game has not started, set it to ongoing
        if ($user['game_status'] == 'not_started') {
            $update_game_stmt = $pdo->prepare("UPDATE users SET game_status = 'ongoing' WHERE id = ?");
            $update_game_stmt->execute([$user['id']]);
        }
        
        // Get the current question from the user's data
        $current_question = $user['current_question'];

        // Get the question text and answers from the questions table
        $question_stmt = $pdo->prepare("SELECT * FROM questions WHERE id = ?");
        $question_stmt->execute([$current_question]);
        $question = $question_stmt->fetch(PDO::FETCH_ASSOC);
        
        return [
            'status' => 'success',
            'user_id' => $user['id'],
            'first_name' => $user['first_name'],  
            'last_name' => $user['last_name'], 
            'token' => $token,
            'current_question' => $current_question,
            'question_text' => $question['question_text'],
            'answers' => [
                $question['answer_1'],
                $question['answer_2'],
                $question['answer_3'],
                $question['answer_4']
            ]
        ];
    } else {
        return ['status' => 'error', 'message' => 'User not found'];
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inputData = json_decode(file_get_contents('php://input'), true);

    if (isset($inputData['phone_number']) && !empty($inputData['phone_number'])) {
        $phone_number = $inputData['phone_number'];

        if (preg_match('/^09\d{9}$/', $phone_number)) {
            echo json_encode(loginUser($phone_number));
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid phone number']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Phone number is required']);
    }
}
?>
