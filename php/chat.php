<?php
session_start();
require_once "config.php";

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: login.php");
    exit;
}

$current_user_id = $_SESSION["id"];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ChatApp | Chat</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../css/chat.css">
</head>
<body>

<!-- Left Sidebar -->
<div class="left-sidebar">
    <a href="home.php"><div class="icon" data-tooltip="Home"><i class="fa-solid fa-house"></i></div></a>
    <a href="notifications.php"><div class="icon" data-tooltip="Notifications"><i class="fas fa-bell"></i></div></a>
    <a href="chat.php"><div class="icon" data-tooltip="All Chats"><i class="fas fa-comments"></i></div></a>
    <a href="groups.php"><div class="icon" data-tooltip="Groups"><i class="fas fa-users"></i></div></a>
    <a href="profile.php"><div class="icon" data-tooltip="Profile"><i class="fa-solid fa-user"></i></div></a>
    <a href="settings.php"><div class="icon" data-tooltip="Settings"><i class="fa-solid fa-gear"></i></div></a>
</div>

<!-- Sidebar -->
<div class="sidebar">
    <h2>Chats</h2>
    <div class="search-bar">
        <i class="fas fa-search"></i>
        <input type="text" placeholder="Search...">
    </div>
    <div class="chat-list">
        <ul>
            <?php
            $stmt = $conn->prepare("SELECT id, username FROM users WHERE id != ?");
            $stmt->bind_param("i", $current_user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                echo '<li onclick="openChat(' . $row['id'] . ', \'' . htmlspecialchars($row['username'], ENT_QUOTES) . '\')">
                        <i class="fas fa-user"></i> ' . htmlspecialchars($row['username']) . '
                      </li>';
            }
            $stmt->close();
            ?>
        </ul>
    </div>
</div>

<!-- Chat Section -->
<div class="chat-section">
    <div class="chat-header" id="chat-header">
        Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?>!
    </div>
    <div class="chat-container" id="chat-container"></div>
    <div class="chat-input">
        <input type="text" id="chat-input" placeholder="Type a message..." onkeydown="if(event.key==='Enter'){sendMessage();}">
        <button onclick="sendMessage()">Send</button>
    </div>
</div>

<script>
let selectedUserId = null;
let selectedUsername = null;

// Sanitize to prevent XSS
function escapeHTML(text) {
    const div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
}

// Open chat with a user
function openChat(userId, username) {
    selectedUserId = userId;
    selectedUsername = username;
    document.getElementById('chat-header').textContent = "Chat with " + username;
    fetchMessages();
}

// Send message
function sendMessage() {
    const messageBox = document.getElementById('chat-input');
    const message = messageBox.value.trim();

    if (!selectedUserId) {
        console.warn('No user selected for sending message.');
        alert('Please select a user to chat with.');
        return;
    }

    if (!message) {
        console.warn('Message is empty.');
        alert('Please type a message before sending.');
        return;
    }

    console.log(`Sending message to user ID ${selectedUserId}: "${message}"`);

    fetch('/sendMessage.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'receiver_id=' + encodeURIComponent(selectedUserId) + '&message=' + encodeURIComponent(message)
    })
    .then(res => {
        console.log(`Server responded with status: ${res.status}`);
        return res.json();
    })
    .then(data => {
        if (data.status === 'success') {
            console.log('✅ Message sent successfully.');
            messageBox.value = '';
            fetchMessages();
        } else {
            console.error('❌ Message failed to send:', data.message);
            alert('Error sending message: ' + data.message);
        }
    })
    .catch(err => {
        console.error('🚨 Network or server error occurred while sending message:', err);
        alert('A network error occurred. Please try again.');
    });
}



// Fetch and render messages
function fetchMessages() {
    if (!selectedUserId) return;

fetch('php/getMessage.php?receiver_id=' + selectedUserId)
        .then(res => res.json())
        .then(data => {
            const container = document.getElementById('chat-container');
            container.innerHTML = '';
            data.forEach(msg => {
                const div = document.createElement('div');
                div.classList.add('message');
                div.classList.add(msg.sender_id == <?php echo $current_user_id; ?> ? 'user-message' : 'bot-message');
                div.innerHTML = escapeHTML(msg.message);
                container.appendChild(div);
            });
            container.scrollTop = container.scrollHeight;
        });
}

// Auto refresh
setInterval(() => {
    if (selectedUserId) fetchMessages();
}, 2000);
</script>
<script>
  const currentUserId = <?php echo json_encode($_SESSION["id"]); ?>;
</script>
<script src="../js/chat.js"></script>


</body>
</html>
