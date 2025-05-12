<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once "config.php";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = trim($_POST["first_name"]);
    $last_name = trim($_POST["last_name"]);
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $dob = $_POST["dob"];
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    // Basic validation
    if (empty($first_name) || empty($last_name) || empty($username) || empty($email) || empty($dob) || empty($password) || empty($confirm_password)) {
        echo "<script>alert('Please fill in all fields.'); window.history.back();</script>";
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Invalid email format.'); window.history.back();</script>";
        exit;
    }

    if ($password !== $confirm_password) {
        echo "<script>alert('Passwords do not match.'); window.history.back();</script>";
        exit;
    }

    // Check for existing username or email
    $sql = "SELECT id FROM users WHERE username = ? OR email = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            echo "<script>alert('Username or email already taken.'); window.history.back();</script>";
            exit;
        }
        $stmt->close();
    } else {
        echo "Prepare failed (SELECT): " . $conn->error;
        exit;
    }

    // Insert user
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (first_name, last_name, username, email, dob, password) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("ssssss", $first_name, $last_name, $username, $email, $dob, $hashed_password);
        if ($stmt->execute()) {
            echo "<script>alert('Registration successful!'); window.location.href='../login.php';</script>";
            exit;
        } else {
            echo "Execute failed: " . $stmt->error;
            exit;
        }
        $stmt->close();
    } else {
        echo "Prepare failed (INSERT): " . $conn->error;
        exit;
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register - ChatApp</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/register.css">
</head>
<body class="bg-light register-page">
  <div class="container mt-5">
    <div class="row justify-content-center">
      <div class="col-md-6 col-lg-5">
        <div class="card shadow-lg">
          <div class="card-body p-4">
            <h3 class="mb-4 text-center">Register for ChatApp</h3>
            <form action="register.php" method="POST">
              <div class="row mb-3">
                <div class="col">
                  <label for="first_name" class="form-label">First Name</label>
                  <input type="text" name="first_name" class="form-control" required>
                </div>
                <div class="col">
                  <label for="last_name" class="form-label">Last Name</label>
                  <input type="text" name="last_name" class="form-control" required>
                </div>
              </div>

              <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" name="username" class="form-control" required>
              </div>

              <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" required>
              </div>

              <div class="mb-3">
                <label for="dob" class="form-label">Date of Birth</label>
                <input type="date" name="dob" class="form-control" required>
              </div>

              <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
              </div>

              <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control" required>
              </div>

              <button type="submit" class="btn btn-success w-100">Register</button>

              <p class="text-center mt-3">
                Already have an account? <a href="../login.php">Login here</a>
              </p>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
