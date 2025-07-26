<?php
include('../includes/db.php');
$user_id = $_SESSION['user_id'] ?? null;
$user_role = $_SESSION['user_role'] ?? null;
$user_name = $_SESSION['user_name'] ?? '';
if (!$user_id || !$user_role) {
echo "<p style='color: red;'>Access Denied. Please login.</p>";
return;
}
?>
<!-- Chat Box -->
<div class="chat-glass-wrapper">
<div class="chat-glass-header">💬 Live Madrassa Chat</div>
<div id="chatMessages" class="chat-glass-messages"></div>
<?php if (in_array($user_role, ['parent', 'teacher', 'admin'])): ?>
<div id="replyContext" style="font-size: 13px; margin-bottom: 5px; display: none;"></div>
<div class="chat-glass-input">
<input type="text" id="chatInput" placeholder="Type your message..." />
<button id="sendChatBtn">Send</button>
</div>
<?php endif; ?>
</div>
<!-- Styles -->
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
position: relative;
}
.chat-msg.you {
background: #007bff;
color: white;
margin-left: auto;
text-align: right;
}
.chat-msg .reply-snippet {
background: rgba(0,0,0,0.05);
border-left: 3px solid #888;
padding: 4px 8px;
font-size: 13px;
margin-bottom: 5px;
border-radius: 4px;
}
.reply-icon {
position: absolute;
top: 4px;
left: -20px;
cursor: pointer;
font-size: 16px;
color: #555;
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
#replyContext {
background: #eee;
padding: 6px 10px;
border-left: 4px solid #007bff;
border-radius: 4px;
color: #333;
}
#replyContext span {
font-weight: bold;
}
@keyframes fadeIn {
from { opacity: 0; transform: translateY(10px); }
to { opacity: 1; transform: translateY(0); }
}
</style>
<!-- Script -->
<script>
const chatBox = document.getElementById('chatMessages');
const chatInput = document.getElementById('chatInput');
const sendBtn = document.getElementById('sendChatBtn');
const replyContext = document.getElementById('replyContext');
let replyToId = null;
let replyToText = null;
function fetchMessages() {
fetch('../includes/fetch_chat_messages.php')
.then(res => res.json())
.then(data => {
if (data.status === 'success') {
chatBox.innerHTML = '';
data.messages.forEach(msg => {
const msgDiv = document.createElement('div');
msgDiv.className = 'chat-msg ' + (msg.role === '<?= $user_role ?>' ? 'you' : 'other');
msgDiv.dataset.id = msg.id;
let replyHTML = '';
if (msg.reply_to_text) {
replyHTML = `<div class="reply-snippet">↩️ <em>${msg.reply_to_text}</em></div>`;
}
msgDiv.innerHTML = `
<div class="reply-icon" onclick="replyTo(${msg.id}, '${msg.sender}',
\`${msg.message.replace(/`/g, '\\`')}\`)">↩️</div>
${replyHTML}
<p><strong>${msg.sender} (${msg.role})</strong> <span
style="font-size:10px;">${msg.time}</span></p>
<div class="bubble">${msg.message}</div>
`;
chatBox.appendChild(msgDiv);
});
chatBox.scrollTop = chatBox.scrollHeight;
}
});
}
function replyTo(id, sender, text) {
replyToId = id;
replyToText = text;
replyContext.innerHTML = `Replying to <span>${sender}</span>: "${text}" <a href="#"
onclick="cancelReply(); return false;">❌ Cancel</a>`;
replyContext.style.display = 'block';
}
function cancelReply() {
replyToId = null;
replyToText = null;
replyContext.innerHTML = '';
replyContext.style.display = 'none';
}
if (sendBtn) {
sendBtn.onclick = () => {
const message = chatInput.value.trim();
if (!message) return alert('Message cannot be empty');
fetch('../includes/send_chat_message.php', {
method: 'POST',
headers: { 'Content-Type': 'application/json' },
body: JSON.stringify({ message: message, reply_to_id: replyToId })
})
.then(res => res.json())
.then(data => {
if (data.status === 'success') {
chatInput.value = '';
cancelReply();
fetchMessages();
} else {
alert(data.message || 'Failed to send message');
}
})
.catch(err => {
alert('Error sending message');
console.error(err);
});
};
}
fetchMessages();
setInterval(fetchMessages, 5000);
</script>