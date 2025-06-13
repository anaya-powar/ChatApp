<?php
session_start();
require_once "config.php";

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    http_response_code(403);
    echo json_encode(["error" => "Not logged in"]);
    exit;
}

$current_user_id = $_SESSION["id"];

// Read JSON body
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data["receiver_id"]) || !isset($data["message"])) {
    http_response_code(400);
    echo json_encode(["error" => "Missing required fields"]);
    exit;
}

$receiver_id = intval($data["receiver_id"]);
$message = trim($data["message"]);

if ($message === "") {
    http_response_code(400);
    echo json_encode(["error" => "Empty message"]);
    exit;
}

$stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $current_user_id, $receiver_id, $message);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    http_response_code(500);
    echo json_encode(["error" => "Failed to send message"]);
}
$stmt->close();
?>