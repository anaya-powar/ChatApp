let selectedUserId = null;
let selectedUsername = null;

// Open chat
function openChat(userId, username) {
    selectedUserId = userId;
    selectedUsername = username;
    document.getElementById('chat-header').textContent = "Chat with " + username;
    fetchMessages();
}

// Send message using AJAX
function sendMessage() {
    const messageBox = document.getElementById('chat-input');
    const message = messageBox.value.trim();
    if (!message || !selectedUserId) return;

    const xhr = new XMLHttpRequest();
    xhr.open("POST", "php/sendMessage.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onload = function () {
        if (xhr.status === 200) {
            messageBox.value = '';
            fetchMessages();
        }
    };
    xhr.send("receiver_id=" + selectedUserId + "&message=" + encodeURIComponent(message));
}

// Fetch messages and display chat bubbles
function fetchMessages() {
    if (!selectedUserId) return;

    const xhr = new XMLHttpRequest();
    xhr.open("GET", "php/getMessages.php?receiver_id=" + selectedUserId, true);
    xhr.onload = function () {
        if (xhr.status === 200) {
            const messages = JSON.parse(xhr.responseText);
            const container = document.getElementById('chat-container');
            container.innerHTML = '';
messages.forEach(msg => {
    const bubble = document.createElement('div');
    bubble.className = (msg.sender_id == currentUserId) ? 'bubble right' : 'bubble left';
    bubble.textContent = msg.message;
    container.appendChild(bubble);
});

            container.scrollTop = container.scrollHeight;
        }
    };
    xhr.send();
}

// Optional: Auto-refresh
setInterval(() => {
    if (selectedUserId) fetchMessages();
}, 2000);
