<?php
session_start();
require_once "config.php";

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve and sanitize form inputs
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    // Validate inputs
    if (empty($email) || empty($password)) {
        echo "Please enter both email and password.";
        exit;
    }

    // Prepare a select statement
    $sql = "SELECT id, username, password FROM users WHERE email = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        // Check if email exists
        if ($stmt->num_rows == 1) {
            // Bind result variables
            $stmt->bind_result($id, $username, $hashed_password);
            if ($stmt->fetch()) {
                if (password_verify($password, $hashed_password)) {
                    // Password is correct, start a new session
                    session_start();
                    $_SESSION["loggedin"] = true;
                    $_SESSION["id"] = $id;
                    $_SESSION["username"] = $username;

                    // Redirect to welcome page
                    echo "<script>alert('Login successful!'); window.location.href='chat.html';</script>";
                    exit;

                } else {
                    // Display an error message if password is not valid
                    echo "Invalid password.";
                    exit;
                }
            }
        } else {
            // Display an error message if email doesn't exist
            echo "No account found with that email.";
            exit;
        }
        $stmt->close();
    } else {
        echo "Something went wrong. Please try again later.";
        exit;
    }
    

    $conn->close();
}


?>
