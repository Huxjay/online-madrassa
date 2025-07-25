<?php
include('../includes/db.php');

$user_id = $_SESSION['user_id'] ?? null;
$user_role = $_SESSION['user_role'] ?? null;

if (!$user_id || !$user_role) {
    echo "<p style='color: red;'>Access Denied. Please login.</p>";
    return;

}
?>

<!-- Chat Box Wrapper -->
<div class="chat-glass-wrapper">
  <div class="chat-glass-header">💬 Live Madrassa Chat</div>
  <div class="chat-glass-messages" id="chatMessages"></div>

  <?php if ($user_role === 'parent'): ?>
    <div class="chat-glass-input">
      <input type="text" id="chatInput" placeholder="Type your question..." />
      <button id="sendChatBtn">Send</button>
    </div>
  <?php else: ?>
    <div style="text-align:center; padding:10px; color:#555;">
      You can respond to parents' questions.
    </div>
  <?php endif; ?>
</div>

<!-- Style -->
<style>
  .chat-glass-wrapper {
    max-width: 700px;
    margin: 20px auto;
    backdrop-filter: blur(12px);
    background: rgba(255, 255, 255, 0.2);
    border-radius: 16px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
    padding: 16px;
    display: flex;
    flex-direction: column;
    height: 75vh;
    border: 1px solid rgba(255,255,255,0.3);
  }

  .chat-glass-header {
    font-size: 20px;
    font-weight: bold;
    text-align: center;
    padding-bottom: 10px;
    color: #333;
  }

  .chat-glass-messages {
    flex: 1;
    overflow-y: auto;
    padding: 10px;
    background: rgba(255, 255, 255, 0.15);
    border-radius: 10px;
    margin-bottom: 12px;
  }

  .chat-msg {
    margin: 8px 0;
    padding: 10px 14px;
    border-radius: 12px;
    max-width: 80%;
    animation: fadeIn 0.3s ease-in-out;
    background: rgba(255,255,255,0.6);
    color: #000;
  }

  .chat-msg.you {
    background: #007bff;
    color: white;
    margin-left: auto;
    text-align: right;
  }

  .chat-msg small {
    display: block;
    font-size: 11px;
    color: #333;
    opacity: 0.7;
  }

  .chat-glass-input {
    display: flex;
    gap: 8px;
  }

  .chat-glass-input input {
    flex: 1;
    padding: 10px;
    border-radius: 8px;
    border: none;
    font-size: 14px;
    outline: none;
  }

  .chat-glass-input button {
    background: #28a745;
    color: white;
    border: none;
    padding: 10px 16px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 14px;
    transition: background 0.3s ease;
  }

  .chat-glass-input button:hover {
    background: #218838;
  }

  @keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
  }
</style>

<!-- JS -->
<script>
  const chatBox = document.getElementById('chatMessages'); // Make sure your HTML has id="chatMessages"
  const chatInput = document.getElementById('chatInput');   // input field
  const sendBtn = document.getElementById('sendChatBtn');   // send button

  // Fetch messages from the server
  function fetchMessages() {
    fetch('../includes/fetch_chat_messages.php')
      .then(res => res.json())
      .then(data => {
        if (data.status === 'success' && Array.isArray(data.messages)) {
          chatBox.innerHTML = '';

          data.messages.forEach(msg => {
            const msgDiv = document.createElement('div');
            msgDiv.className = 'chat-msg ' + (msg.role === 'parent' ? 'you' : 'other');
            msgDiv.innerHTML = `
              <p><strong>${msg.sender}</strong> <span>${msg.time}</span></p>
              <div class="bubble">${msg.message}</div>
            `;
            chatBox.appendChild(msgDiv);
          });

          // Auto-scroll to bottom
          chatBox.scrollTop = chatBox.scrollHeight;
        } else {
          console.error("Invalid messages format or failed status");
        }
      })
      .catch(err => console.error("Fetch error:", err));
  }

  // Send a message
  if (sendBtn) {
    sendBtn.addEventListener('click', () => {
      const message = chatInput.value.trim();
      if (!message) return alert('Message cannot be empty');

      fetch('../includes/send_chat_message.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ message: message })
      })
      .then(res => res.json())
      .then(data => {
        if (data.status === 'success') {
          chatInput.value = '';
          fetchMessages(); // refresh messages
        } else {
          alert(data.message || 'Failed to send message');
        }
      })
      .catch(err => {
        console.error("Send error:", err);
        alert('Error sending message');
      });
    });
  }

  // Initial fetch and auto refresh
  fetchMessages();
  setInterval(fetchMessages, 5000);
</script>