<?php
session_start();
require_once "config.php";
header('Content-Type: application/json');

if (!isset($_SESSION["id"])) {
    http_response_code(403);
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit;
}

$receiver_id = intval($_GET["receiver_id"] ?? 0);
$current_user_id = $_SESSION["id"];

if ($receiver_id <= 0) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Invalid receiver ID"]);
    exit;
}

$stmt = $conn->prepare("SELECT * FROM messages WHERE 
    (sender_id = ? AND receiver_id = ?) OR 
    (sender_id = ? AND receiver_id = ?)
    ORDER BY timestamp ASC");

if ($stmt) {
    $stmt->bind_param("iiii", $current_user_id, $receiver_id, $receiver_id, $current_user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $messages = [];
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }
    echo json_encode($messages);
    $stmt->close();
} else {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "DB prepare failed"]);
}
?>
