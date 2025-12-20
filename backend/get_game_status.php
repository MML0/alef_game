<?php
include 'db.php';  // Include the database connection
header("Access-Control-Allow-Origin: *");  
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

$stmt = $pdo->query("SELECT game_status FROM game_stats LIMIT 1");
$gameStatus = $stmt->fetch(PDO::FETCH_ASSOC);

if ($gameStatus) {
    echo json_encode([
        'status' => 'success',
        'game_status' => $gameStatus['game_status']
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Unable to retrieve game status'
    ]);
}
?>
