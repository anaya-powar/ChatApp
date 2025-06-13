<?php
session_start();
require_once "config.php";
header('Content-Type: application/json');

if (!isset($_SESSION["id"])) {
    http_response_code(403);
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sender_id = $_SESSION["id"];
    $receiver_id = intval($_POST["receiver_id"] ?? 0);
    $message = trim($_POST["message"] ?? "");

    if ($receiver_id <= 0 || $message === "") {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Invalid input"]);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("iis", $sender_id, $receiver_id, $message);
        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Message sent"]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "DB execute failed"]);
        }
        $stmt->close();
    } else {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "DB prepare failed"]);
    }
} else {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Only POST allowed"]);
}
?>
