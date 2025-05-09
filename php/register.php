<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once "config.php";

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve and sanitize form inputs
    $first_name = trim($_POST["first_name"]);
    $last_name = trim($_POST["last_name"]);
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $dob = $_POST["dob"];
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    // Validate inputs
    if (empty($first_name) || empty($last_name) || empty($username) || empty($email) || empty($dob) || empty($password) || empty($confirm_password)) {
        echo "Please fill in all fields.";
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Invalid email format.";
        exit;
    }

    if ($password !== $confirm_password) {
        echo "Passwords do not match.";
        exit;
    }

    // Check if username or email already exists
    $sql = "SELECT id FROM users WHERE username = ? OR email = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            echo "Username or email already taken.";
            exit;
        }
        $stmt->close();
    } else {
        echo "Something went wrong. Please try again later.";
        exit;
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert new user into the database
    $sql = "INSERT INTO users (first_name, last_name, username, email, dob, password) VALUES (?, ?, ?, ?, ?, ?)";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ssssss", $first_name, $last_name, $username, $email, $dob, $hashed_password);
        if ($stmt->execute()) {
            echo "<script>alert('Registration successful!'); 
            window.location.href='../login.html';
            </script>";
            exit;
        }
        
        } else {
            echo "Something went wrong. Please try again later.";
            exit;
        }
        $stmt->close() ;
    } else {
        echo "Something went wrong. Please try again later.";
        exit;
    }

    $conn->close() ;



?>
