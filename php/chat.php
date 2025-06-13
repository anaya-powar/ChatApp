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
        <a href="home.php">
            <div class="icon" data-tooltip="Home"><i class="fa-solid fa-house"></i></div>
        </a>
        <a href="notifications.php">
            <div class="icon" data-tooltip="Notifications"><i class="fas fa-bell"></i></div>
        </a>
        <a href="chat.php">
            <div class="icon" data-tooltip="All Chats"><i class="fas fa-comments"></i></div>
        </a>
        <a href="groups.php">
            <div class="icon" data-tooltip="Groups"><i class="fas fa-users"></i></div>
        </a>
        <a href="profile.php">
            <div class="icon" data-tooltip="Profile"><i class="fa-solid fa-user"></i></div>
        </a>
        <a href="settings.php">
            <div class="icon" data-tooltip="Settings"><i class="fa-solid fa-gear"></i></div>
        </a>
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
            <input type="text" id="chat-input" placeholder="Type a message..."
                onkeydown="if(event.key==='Enter'){sendMessage();}">
            <button onclick="sendMessage()">Send</button>
        </div>
    </div>

    <script>
        let lastMessageId = 0;
        let selectedUserId = null;
        let selectedUsername = null;

        function sendMessage() {
            const inputBox = document.getElementById("chat-input");
            const message = inputBox.value.trim();

            if (message === "") {
                alert("Please enter a message before sending.");
                return;
            }

            if (!selectedUserId) {
                alert("Please select a user to chat with first.");
                return;
            }

            fetch("send_messages.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    receiver_id: selectedUserId,
                    message: message
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        inputBox.value = "";
                        // Let fetchMessages() handle displaying the message
                    } else {
                        alert("Failed to send message: " + (data.error || "Unknown error"));
                    }
                })
                .catch(error => {
                    console.error("Error sending message:", error);
                    alert("An error occurred while sending the message.");
                });
        }



        function fetchMessages() {
            if (!selectedUserId) return;

            fetch("fetch_messages.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({ receiver_id: selectedUserId })
            })
                .then(response => response.json())
                .then(data => {
                    const chatContainer = document.getElementById("chat-container");

                    data.messages.forEach(msg => {
                        if (msg.id > lastMessageId) {  // Assuming your messages have an ID
                            const msgDiv = document.createElement("div");
                            msgDiv.textContent = (msg.sender_id == <?php echo $current_user_id; ?> ? "You: " : selectedUsername + ": ") + msg.message;
                            msgDiv.classList.add("chat-message");
                            chatContainer.appendChild(msgDiv);
                            lastMessageId = msg.id;
                        }
                    });

                    chatContainer.scrollTop = chatContainer.scrollHeight;
                })
                .catch(error => {
                    console.error("Error fetching messages:", error);
                });
        }




        // Open chat with a user
        function openChat(userId, username) {
            selectedUserId = userId;
            selectedUsername = username;
            lastMessageId = 0; // Reset on new chat
            document.getElementById('chat-header').textContent = "Chat with " + username;

            fetchMessages();
            clearInterval(window.messagePolling);
            window.messagePolling = setInterval(fetchMessages, 1000);
        }


        window.addEventListener('beforeunload', () => {
            clearInterval(window.messagePolling);
        });


    </script>
</body>

</html>