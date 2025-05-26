<?php
session_start();
include '../php/db.php';

if (!isset($_SESSION['user_id'])) {
  die("Access denied");
}

$user_id = $_SESSION['user_id'];
$chat_with = $_GET['chat_with'] ?? null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Direct Messages</title>
  <link rel="stylesheet" href="../css/styles.css">
  <style>
    .dm-container { display: flex; gap: 20px; padding: 20px; }
    .dm-sidebar { width: 250px; border-right: 1px solid #ccc; padding-right: 10px; }
    .dm-chat { flex: 1; }
    .chat-thread { max-height: 400px; overflow-y: auto; margin-bottom: 15px; }
    .chat-bubble { margin-bottom: 10px; }
    .chat-bubble.you { text-align: right; }
    textarea { width: 100%; }
  </style>
</head>
<body>
  <div class="dm-container">
    <!-- Sidebar -->
    <div class="dm-sidebar">
      <h3>Chats</h3>
      <ul>
        <?php
        $stmt = $conn->prepare("SELECT id, name FROM users WHERE id != ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $users = $stmt->get_result();
        while ($u = $users->fetch_assoc()):
        ?>
          <li>
            <a href="dm.php?chat_with=<?= $u['id'] ?>">
              <?= htmlspecialchars($u['name']) ?>
            </a>
          </li>
        <?php endwhile; ?>
      </ul>
    </div>

    <!-- Chat Area -->
    <div class="dm-chat">
      <h3>Conversation</h3>
      <?php if ($chat_with): ?>
        <div class="chat-thread" id="chat-thread"></div>

        <form method="POST" action="send_dm.php">
          <input type="hidden" name="receiver_id" value="<?= $chat_with ?>">
          <textarea name="message" rows="3" required placeholder="Type a message..."></textarea>
          <button type="submit">Send</button>
        </form>
      <?php else: ?>
        <p>Select a user to start chatting.</p>
      <?php endif; ?>
    </div>
  </div>

<script>
const currentUser = <?= json_encode($_SESSION['user_id']) ?>;
const chatWith = <?= json_encode($chat_with) ?>;
const chatThread = document.getElementById('chat-thread');

async function fetchMessages() {
  if (!chatWith || !chatThread) return;

  try {
    const res = await fetch(`../php/get_messages.php?chat_with=${chatWith}`);
    const messages = await res.json();

    chatThread.innerHTML = messages.map(msg => {
      const mine = msg.sender_id == currentUser;
      return `
        <div style="margin-bottom: 10px; text-align: ${mine ? 'right' : 'left'};">
          <strong>${msg.sender_name}</strong><br>
          ${msg.message.replace(/\n/g, "<br>")}<br>
          <small>${new Date(msg.timestamp).toLocaleString()}</small>
        </div>
      `;
    }).join('');

    chatThread.scrollTop = chatThread.scrollHeight;
  } catch (err) {
    console.error("Error fetching messages:", err);
  }
}

setInterval(fetchMessages, 5000);
fetchMessages();
</script>
</body>
</html>
