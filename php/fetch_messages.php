<?php
header('Content-Type: application/json');
session_start();
require_once "config.php";

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    http_response_code(403);
    echo json_encode(["error" => "Not logged in"]);
    exit;
}

$current_user_id = $_SESSION["id"];

$input = json_decode(file_get_contents("php://input"), true);

if (!isset($input['receiver_id'])) {
    http_response_code(400);
    echo json_encode(["error" => "Missing receiver_id"]);
    exit;
}

$receiver_id = intval($input['receiver_id']);

// Fetch messages between current user and selected receiver
$stmt = $conn->prepare("
    SELECT id, sender_id, message, timestamp 
    FROM messages 
    WHERE 
        (sender_id = ? AND receiver_id = ?) 
        OR 
        (sender_id = ? AND receiver_id = ?)
    ORDER BY timestamp ASC
");

$stmt->bind_param("iiii", $current_user_id, $receiver_id, $receiver_id, $current_user_id);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}

echo json_encode(["messages" => $messages]);
?>
