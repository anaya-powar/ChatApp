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


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login - ChatApp</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/login.css">
</head>


<body class="bg-light login-page">
  <div class="container mt-5">
    <div class="row justify-content-center">
      <div class="col-md-6 col-lg-5">
        <div class="card shadow-lg">
          <div class="card-body p-4">
            <h3 class="mb-4 text-center">Login to ChatApp</h3>
            <form action="login.php" method="POST">
              <div class="mb-3">
                <label for="email" class="form-label">Email address</label>
                <input type="email" name="email" class="form-control" required>
              </div>
              <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
              </div>
              <button type="submit" class="btn btn-primary w-100">Login</button>
              <p class="text-center mt-3">
                Don't have an account? <a href="register.html">Register here</a>
              </p>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</body> 
</html>
