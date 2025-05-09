<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.html");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Home - ChatApp</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f7f9fc;
    }
    .card {
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .welcome {
      font-size: 1.8rem;
      font-weight: 600;
    }
  </style>
</head>
<body>
  <div class="container py-5">
    <div class="mb-4">
      <p class="welcome">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?> 👋</p>
      <p class="text-muted">Here’s your dashboard overview.</p>
    </div>

    <div class="row g-4">
      <!-- Stats Cards -->
      <div class="col-md-4">
        <div class="card p-3">
          <h5>Total Messages</h5>
          <p class="text-primary fs-4">124</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card p-3">
          <h5>Active Chats</h5>
          <p class="text-success fs-4">5</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card p-3">
          <h5>Last Login</h5>
          <p class="text-muted fs-5">08-May-2025</p>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="mt-5">
      <h4>Quick Actions</h4>
      <div class="d-flex flex-wrap gap-3 mt-3">
        <a href="chat.html" class="btn btn-primary">Go to Chat</a>
        <a href="profile.php" class="btn btn-outline-secondary">My Profile</a>
        <a href="settings.php" class="btn btn-outline-dark">Settings</a>
        <a href="logout.php" class="btn btn-danger">Logout</a>
      </div>
    </div>
  </div>

</body>
</html>
