<?php 
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

include 'db.php'; // Include the database connection

function getUserRank($user_token) {
    global $pdo;

    // Check if the user exists with the provided token
    $stmt = $pdo->prepare("SELECT * FROM users WHERE token = ?");
    $stmt->execute([$user_token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {

        // Get all users with score + calculated game duration
        $stmt = $pdo->prepare("
            SELECT 
                u.id, 
                u.first_name, 
                u.last_name, 
                u.score, 
                u.start_game_time, 
                u.end_game_time,
                CASE
                    WHEN u.end_game_time IS NULL 
                        THEN TIMESTAMPDIFF(MICROSECOND, u.start_game_time, NOW(3))
                    ELSE 
                        TIMESTAMPDIFF(MICROSECOND, u.start_game_time, u.end_game_time)
                END AS game_duration_microseconds
            FROM users u
            WHERE u.game_status IN ('completed', 'ongoing')
        ");
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Sort by score DESC, then by game duration ASC
        usort($users, function($a, $b) {
            if ($a['score'] === $b['score']) {
                return $a['game_duration_microseconds'] <=> $b['game_duration_microseconds'];
            }
            return $b['score'] <=> $a['score'];
        });

        // Find the user's rank
        $rank = 0;
        foreach ($users as $index => $userData) {
            if ($userData['id'] == $user['id']) {
                $rank = $index + 1;
                $currentUser = $userData; // store full row including game_duration_microseconds
                break;
            }
        }

        // Convert microseconds → milliseconds
        $game_duration_ms = $currentUser['game_duration_microseconds'] / 1000;

        return [
            'status' => 'success',
            'user_rank' => $rank,
            'score' => $currentUser['score'],
            'game_duration_ms' => $game_duration_ms
        ];

    } else {
        return ['status' => 'error', 'message' => 'User not found'];
    }
}

// Retrieve the user token from the request body (POST)
$data = json_decode(file_get_contents("php://input"), true);
$user_token = $data['token'] ?? null;

if (!$user_token) {
    echo json_encode(['status' => 'error', 'message' => 'Token is required']);
    exit();
}

$response = getUserRank($user_token);
echo json_encode($response);
?>
