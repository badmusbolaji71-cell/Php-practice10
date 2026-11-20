<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "social_media";

$connection = new mysqli($servername, $username, $password, $dbname);

if ($connection->connect_error) {
    die("Database connection failed.");
}

$connection->set_charset("utf8mb4");

$me = isset($_SESSION['id']) ? (int)$_SESSION['id'] : 0;

if ($me <= 0) {
    die("Please log in to access your messages.");
}

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function messageTime($date) {
    if (!$date) return "";
    return date("g:i A", strtotime($date));
}

/* GET ALL PEOPLE THE USER HAS CHATTED WITH */

$conversations = [];

$sql = "
    SELECT
        u.id,
        u.first_name,
        u.last_name,
        u.picturepr,
        u.verified,
        (
            SELECT m2.message
            FROM messages m2
            WHERE
                (m2.sender_id = ? AND m2.receiver_id = u.id)
                OR
                (m2.sender_id = u.id AND m2.receiver_id = ?)
            ORDER BY m2.sent_at DESC
            LIMIT 1
        ) AS last_message,
        (
            SELECT MAX(m3.sent_at)
            FROM messages m3
            WHERE
                (m3.sender_id = ? AND m3.receiver_id = u.id)
                OR
                (m3.sender_id = u.id AND m3.receiver_id = ?)
        ) AS last_time
    FROM user_details u
    INNER JOIN messages m
        ON (
            (m.sender_id = ? AND m.receiver_id = u.id)
            OR
            (m.sender_id = u.id AND m.receiver_id = ?)
        )
    WHERE u.id != ?
    GROUP BY u.id
    ORDER BY last_time DESC
";

$stmt = $connection->prepare($sql);
$stmt->bind_param("iiiiiii", $me, $me, $me, $me, $me, $me, $me);
$stmt->execute();

$result = $stmt->get_result();

while ($person = $result->fetch_assoc()) {
    $conversations[] = $person;
}

$stmt->close();

/* GET THE SELECTED PROFILE */

$profileId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($profileId <= 0 && !empty($conversations)) {
    $profileId = (int)$conversations[0]['id'];
}

$row = null;
$fullName = "";

if ($profileId > 0 && $profileId !== $me) {

    $profileQuery = $connection->prepare(
        "SELECT id, first_name, last_name, picturepr,verified
         FROM user_details
         WHERE id = ?
         LIMIT 1"
    );

    $profileQuery->bind_param("i", $profileId);
    $profileQuery->execute();

    $profileResult = $profileQuery->get_result();

    if ($profileResult->num_rows > 0) {
        $row = $profileResult->fetch_assoc();
        $fullName = trim($row['first_name'] . " " . $row['last_name']);
    }

    $profileQuery->close();
}

/* ADD SELECTED PERSON TO SIDEBAR IF NECESSARY */

if ($row) {

    $found = false;

    foreach ($conversations as $person) {
        if ((int)$person['id'] === $profileId) {
            $found = true;
            break;
        }
    }

    if (!$found) {
        $row['last_message'] = "Start a conversation";
        $row['last_time'] = null;
        $conversations[] = $row;
    }
}

/* SEND MESSAGE */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send'])) {

    $messageText = trim($_POST['message'] ?? '');
    $receiverId = (int)($_POST['receiver_id'] ?? 0);

    if ($messageText !== '' && $receiverId > 0 && $receiverId !== $me) {

        $checkUser = $connection->prepare(
            "SELECT id FROM user_details WHERE id = ? LIMIT 1"
        );

        $checkUser->bind_param("i", $receiverId);
        $checkUser->execute();

        $validUser = $checkUser->get_result()->num_rows > 0;
        $checkUser->close();

        if ($validUser) {

            $insert = $connection->prepare(
                "INSERT INTO messages (sender_id, receiver_id, message)
                 VALUES (?, ?, ?)"
            );

            $insert->bind_param("iis", $me, $receiverId, $messageText);
            $insert->execute();
            $insert->close();
        }
    }

    header("Location: messages.php?id=" . $receiverId);
    exit();
}

/* GET ALL MESSAGES BETWEEN THE TWO USERS */

$allMessages = [];

if ($row) {

    $messageQuery = $connection->prepare(
        "SELECT sender_id, receiver_id, message, sent_at
         FROM messages
         WHERE
            (sender_id = ? AND receiver_id = ?)
            OR
            (sender_id = ? AND receiver_id = ?)
         ORDER BY sent_at desc"
    );

    $messageQuery->bind_param(
        "iiii",
        $me,
        $profileId,
        $profileId,
        $me
    );

    $messageQuery->execute();

    $messageResult = $messageQuery->get_result();

    while ($message = $messageResult->fetch_assoc()) {
        $allMessages[] = $message;
    }

    $messageQuery->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Vlast | Messages</title>

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<link rel="preconnect" href="https://fonts.googleapis.com">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">

<script>
    window.VLAST_USER_ID = <?php echo (int)$me; ?>;
</script>

<script src="theme.js"></script>

<style>

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Poppins', sans-serif;
    background: #f1f3f6;
    color: #222;
}

button, input {
    font-family: inherit;
}

a {
    text-decoration: none;
    color: inherit;
}

/* HEADER */

header {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 70px;
    background: #fff;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 5%;
    box-shadow: 0 2px 15px rgba(0,0,0,.06);
    z-index: 1000;
}

.logo {
    font-family: 'Playfair Display', serif;
    font-size: 32px;
    color: #111;
}

.header-icons {
    display: flex;
    align-items: center;
    gap: 25px;
}

.header-icons a {
    color: #777;
    font-size: 19px;
    transition: .2s;
}

.header-icons a:hover,
.header-icons a.active {
    color: #111;
}

/* MAIN */

.page {
    max-width: 1300px;
    height: 100vh;
    margin: auto;
    padding: 90px 25px 20px;
}

.messages-container {
    height: 100%;
    background: #fff;
    border-radius: 22px;
    overflow: hidden;
    display: flex;
    box-shadow: 0 5px 30px rgba(0,0,0,.06);
}

/* SIDEBAR */

.conversations {
    width: 350px;
    min-width: 300px;
    display: flex;
    flex-direction: column;
    border-right: 1px solid #eee;
    background: #fff;
}

.messages-header {
    padding: 25px 22px 18px;
    border-bottom: 1px solid #eee;
}

.messages-header-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 18px;
}

.messages-header h2 {
    font-size: 23px;
    font-weight: 700;
}

.new-message {
    width: 39px;
    height: 39px;
    border: none;
    border-radius: 50%;
    background: #111;
    color: white;
    cursor: pointer;
}

.search-box {
    height: 44px;
    background: #f2f3f5;
    border-radius: 13px;
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 0 14px;
}

.search-box i {
    color: #999;
    font-size: 14px;
}

.search-box input {
    width: 100%;
    border: none;
    outline: none;
    background: transparent;
    font-size: 12px;
}

.chat-list {
    flex: 1;
    overflow-y: auto;
    overscroll-behavior: contain;
}

.chat-list::-webkit-scrollbar,
.chat-messages::-webkit-scrollbar {
    width: 5px;
}

.chat-list::-webkit-scrollbar-thumb,
.chat-messages::-webkit-scrollbar-thumb {
    background: #d8dce1;
    border-radius: 10px;
}

/* CHAT ITEM */

.chat-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 15px 19px;
    cursor: pointer;
    border-bottom: 1px solid #f5f5f5;
    transition: .2s;
}

.chat-item:hover {
    background: #f8f8f8;
}

.chat-item.active {
    background: #f0f1f4;
}

.avatar {
    width: 50px;
    height: 50px;
    min-width: 50px;
    border-radius: 50%;
    background: #e4e5e8;
    overflow: hidden;
    position: relative;
}

.avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.chat-info {
    flex: 1;
    min-width: 0;
}

.chat-top {
    display: flex;
    justify-content: space-between;
    gap: 8px;
}

.chat-name {
    font-size: 13px;
    font-weight: 600;
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
}

.chat-time {
    font-size: 9px;
    color: #999;
    white-space: nowrap;
}

.last-message {
    font-size: 10px;
    color: #999;
    margin-top: 5px;
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
}

.no-conversations {
    text-align: center;
    padding: 35px 20px;
    color: #999;
    font-size: 12px;
}

/* CHAT AREA */

.chat-area {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    background: #f7f8fa;
}

.chat-header {
    height: 76px;
    min-height: 76px;
    padding: 0 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #fff;
    border-bottom: 1px solid #eee;
}

.chat-user {
    display: flex;
    align-items: center;
    gap: 12px;
}

.chat-user .avatar {
    width: 44px;
    height: 44px;
    min-width: 44px;
}

.chat-user-info h3 {
    font-size: 14px;
    font-weight: 600;
}

.chat-user-info span {
    display: block;
    font-size: 10px;
    color: #999;
    margin-top: 3px;
}

.chat-actions {
    display: flex;
    gap: 9px;
}

.chat-actions button {
    width: 38px;
    height: 38px;
    border: none;
    border-radius: 50%;
    background: #f2f3f5;
    color: #555;
    cursor: pointer;
}

.chat-actions button:hover {
    background: #e5e6e8;
}

.back-button {
    display: none;
    border: none;
    background: transparent;
    font-size: 18px;
    cursor: pointer;
    color: #555;
}

/* CHAT BACKGROUND */

.chat-messages {
    flex: 1;
    min-height: 0;
    overflow-y: auto;
    padding: 25px;
    display: flex;
    flex-direction: column;
    gap: 16px;
    background-color: #f1f3f7;
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    overscroll-behavior: contain;
}

/* MESSAGE BUBBLES */

.message {
    max-width: 70%;
    display: flex;
    flex-direction: column;
}

.message.sent {
    align-self: flex-end;
    align-items: flex-end;
}

.message.received {
    align-self: flex-start;
    align-items: flex-start;
}

.message-bubble {
    padding: 12px 16px;
    border-radius: 17px;
    font-size: 12px;
    line-height: 1.7;
    overflow-wrap: anywhere;
    box-shadow: 0 2px 6px rgba(0,0,0,.04);
}

.sent .message-bubble {
    background: #171717;
    color: white;
    border-bottom-right-radius: 5px;
}

.received .message-bubble {
    background: white;
    color: #333;
    border-bottom-left-radius: 5px;
}

.message-meta {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 9px;
    color: #999;
    margin-top: 5px;
    padding: 0 3px;
}

.message.sent .message-meta {
    justify-content: flex-end;
}

.sent-status {
    color: #777;
    font-size: 9px;
}

.empty-messages {
    margin: auto;
    text-align: center;
    color: #999;
    font-size: 12px;
    line-height: 2;
}

.empty-messages i {
    display: block;
    font-size: 35px;
    margin-bottom: 12px;
    color: #bbb;
}

/* MESSAGE INPUT */

.message-form {
    background: white;
    border-top: 1px solid #eee;
}

.message-input-area {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 15px 20px;
}

.message-extra {
    width: 39px;
    height: 39px;
    min-width: 39px;
    border: none;
    background: #f1f2f4;
    border-radius: 50%;
    color: #666;
    cursor: pointer;
}

.message-input {
    flex: 1;
    min-width: 0;
    height: 44px;
    border-radius: 25px;
    background: #f1f2f4;
    padding: 0 16px;
    display: flex;
    align-items: center;
}

.message-input input {
    width: 100%;
    border: none;
    outline: none;
    background: transparent;
    font-size: 12px;
}

.send-button {
    width: 44px;
    height: 44px;
    min-width: 44px;
    border: none;
    border-radius: 50%;
    background: #111;
    color: white;
    cursor: pointer;
    transition: .2s;
}

.send-button:hover {
    transform: scale(1.06);
    background: #333;
}

.send-button:disabled {
    opacity: .5;
    cursor: not-allowed;
}

/* EMPTY SELECTED CHAT */

.no-chat-selected {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    color: #999;
    padding: 30px;
}

.no-chat-selected i {
    font-size: 50px;
    margin-bottom: 15px;
    color: #ccc;
}

/* MOBILE NAV */

.mobile-nav {
    display: none;
}

/* TABLET */

@media(max-width:850px) {
    .conversations {
        width: 290px;
        min-width: 260px;
    }

    .chat-messages {
        padding: 20px 15px;
    }
}

/* MOBILE */

@media(max-width:650px) {

    body {
        padding-bottom: 65px;
    }

    header {
        height: 62px;
        padding: 0 17px;
    }

    .logo {
        font-size: 27px;
    }

    .header-icons {
        gap: 17px;
    }

    .header-icons a {
        font-size: 17px;
    }

    .page {
        height: calc(100dvh - 62px);
        padding: 62px 0 0;
    }

    .messages-container {
        height: 100%;
        border-radius: 0;
    }

    .conversations {
        width: 100%;
        min-width: 0;
        border: none;
    }

    .chat-area {
        display: none;
        width: 100%;
    }

    .messages-container.show-chat .conversations {
        display: none;
    }

    .messages-container.show-chat .chat-area {
        display: flex;
    }

    .back-button {
        display: block;
    }

    .chat-header {
        height: 65px;
        min-height: 65px;
        padding: 0 12px;
        gap: 10px;
    }

    .chat-user {
        flex: 1;
        min-width: 0;
    }

    .chat-user-info {
        min-width: 0;
    }

    .chat-user-info h3 {
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
    }

    .chat-actions button {
        width: 33px;
        height: 33px;
    }

    .chat-actions button:nth-child(1),
    .chat-actions button:nth-child(2) {
        display: none;
    }

    .chat-messages {
        padding: 18px 12px;
    }

    .message {
        max-width: 85%;
    }

    .message-input-area {
        padding: 10px;
        gap: 7px;
    }

    .message-extra {
        width: 34px;
        height: 34px;
        min-width: 34px;
    }

    .message-input {
        height: 41px;
    }

    .send-button {
        width: 41px;
        height: 41px;
        min-width: 41px;
    }

    .mobile-nav {
        position: fixed;
        display: flex;
        align-items: center;
        justify-content: space-around;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 63px;
        background: white;
        border-top: 1px solid #eee;
        z-index: 1000;
    }

    .mobile-nav a {
        width: 42px;
        height: 42px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        color: #777;
        font-size: 18px;
    }

    .mobile-nav a.active,
    .mobile-nav a.add {
        background: #111;
        color: white;
    }
}

@media(max-width:360px) {
    .messages-header {
        padding: 20px 15px 15px;
    }

    .chat-item {
        padding: 13px 15px;
    }

    .chat-actions {
        gap: 4px;
    }
}

</style>

</head>

<body>

<header>

    <a href="home.php" class="logo">Vlast</a>

    <div class="header-icons">

        <a href="home.php" title="Home">
            <i class="fa-solid fa-house"></i>
        </a>

        <a href="messages.php" class="active" title="Messages">
            <i class="fa-solid fa-message"></i>
        </a>

        <a href="explore_user.php" title="Explore">
            <i class="fa-solid fa-magnifying-glass"></i>
        </a>

        <a href="notification.php" title="Notifications">
            <i class="fa-solid fa-bell"></i>
        </a>

        <a href="user_profile.php" title="Profile">
            <i class="fa-solid fa-user"></i>
        </a>

    </div>

</header>

<main class="page">

<section class="messages-container" id="messagesContainer">

    <aside class="conversations">

        <div class="messages-header">

            <div class="messages-header-top">

                <h2>Messages</h2>

                <button class="new-message" type="button" title="New message">
                    <i class="fa-solid fa-pen"></i>
                </button>

            </div>

            <div class="search-box">

                <i class="fa-solid fa-magnifying-glass"></i>

                <input
                    type="text"
                    id="searchChats"
                    placeholder="Search conversations..."
                    autocomplete="off"
                >

            </div>

        </div>

        <div class="chat-list" id="chatList">

            <?php if (empty($conversations)): ?>

                <div class="no-conversations">
                    <i class="fa-regular fa-comments"></i>
                    <p>No conversations yet.</p>
                </div>

            <?php else: ?>

                <?php foreach ($conversations as $person): ?>

                    <?php
                    $personId = (int)$person['id'];
                    $personName = trim($person['first_name'] . " " . $person['last_name']);
                    $lastMessage = $person['last_message'] ?? "Start a conversation";
                    $isActive = $personId === $profileId;
                    ?>

                    <a
                        href="messages.php?id=<?php echo $personId; ?>"
                        class="chat-item <?php echo $isActive ? 'active' : ''; ?>"
                       
                      <?php echo e(strtolower($personName)); ?>
                        
                    
                        
                    >

                        <div class="avatar">

                            <img
                                src="image/<?php echo e($person['picturepr']); ?>"
                                alt=""
                                onerror="this.style.display='none'"
                            >

                        </div>

                        <div class="chat-info">

                            <div class="chat-top">

                                <span class="chat-name">
                                    <?php if($person['verified'] == 1){?>
                                      <?php echo e($personName); ?> <i class = "fa-solid fa-circle-check " style = "color:blue; height:20px;"></i>
                                      <?php }elseif($person['verified'] == 0){?>
                                    <?php echo e($personName); ?>
                                    <?php }?>

                                </span>

                                <span class="chat-time">
                                    <?php echo e(messageTime($person['last_time'])); ?>
                                </span>

                            </div>

                            <p class="last-message">
                                <?php echo e($lastMessage); ?>
                            </p>

                        </div>

                    </a>

                <?php endforeach; ?>

            <?php endif; ?>

            <div class="no-conversations" id="noSearchResult" style="display:none;">
                No matching conversations found.
            </div>

        </div>

    </aside>

    <section class="chat-area">

        <?php if ($row): ?>

            <div class="chat-header">

                <button class="back-button" id="backButton" type="button">
                    <i class="fa-solid fa-arrow-left"></i>
                </button>

                <div class="chat-user">

                    <div class="avatar">

                        <img
                            src="image/<?php echo e($row['picturepr']); ?>"
                            alt=""
                            onerror="this.style.display='none'"
                        >

                    </div>

                    <div class="chat-user-info">

                        <h3>
                            <?php if($row['verified'] == 1){?>
                             <a href="other_users_pp.php?id=<?php echo $profileId; ?>">
                                <?php echo e($fullName); ?> <i class = "fa-solid fa-circle-check " style = "color:blue; height:20px;"></i>
                            </a>
                            <?php }elseif($row['verified'] == 0){?>
                            <a href="other_users_pp.php?id=<?php echo $profileId; ?>">
                                <?php echo e($fullName); ?>
                            </a>
                            <?php }?>
                        </h3>

                        <span>Vlast conversation</span>

                    </div>

                </div>

                <div class="chat-actions">

                    <button type="button" title="Call">
                        <i class="fa-solid fa-phone"></i>
                    </button>

                    <button type="button" title="Video call">
                        <i class="fa-solid fa-video"></i>
                    </button>

                    <button type="button" title="More">
                        <i class="fa-solid fa-ellipsis"></i>
                    </button>

                </div>

            </div>

            <div class="chat-messages" id="chatMessages">

                <?php if (empty($allMessages)): ?>

                    <div class="empty-messages">

                        <i class="fa-regular fa-comments"></i>

                        <p>
                            This is the beginning of your conversation with
                            <?php echo e($fullName); ?>.
                        </p>

                        <p>Send a message to get started.</p>

                    </div>

                <?php else: ?>

                    <?php foreach ($allMessages as $mes): ?>

                        <?php
                        $isSent = (int)$mes['sender_id'] === $me;
                        ?>

                        <div class="message <?php echo $isSent ? 'sent' : 'received'; ?>">

                            <div class="message-bubble">
                                <?php echo nl2br(e($mes['message'])); ?>
                                  <div class="message-meta">
                                  <span>
                                    <?php echo e(messageTime($mes['sent_at'])); ?>
                                        <?php if ($isSent): ?>

                                    <span class="sent-status">
                                        <i class="fa-solid fa-check"></i> Sent
                                    </span>

                                <?php endif; ?>
                                </span>
                            </div>

                          

                              

                            

                            </div>

                        </div>

                    <?php endforeach; ?>

                <?php endif; ?>

            </div>

            <form action="messages.php?id=<?php echo $profileId; ?>" method="POST" class="message-form">

                <input
                    type="hidden"
                    name="receiver_id"
                    value="<?php echo $profileId; ?>"
                >

                <div class="message-input-area">

                    <button
                        type="button"
                        class="message-extra"
                        title="Attachment"
                    >
                        <i class="fa-solid fa-plus"></i>
                    </button>

                    <div class="message-input">

                        <input
                            type="text"
                            name="message"
                            id="messageInput"
                            placeholder="Write a message..."
                            autocomplete="off"
                            required
                        >

                    </div>

                    <button
                        type="submit"
                        name="send"
                        class="send-button"
                        id="sendButton"
                        title="Send message"
                    >
                        <i class="fa-solid fa-paper-plane"></i>
                    </button>

                </div>

            </form>

        <?php else: ?>

            <div class="no-chat-selected">

                <div>
                    <i class="fa-regular fa-comments"></i>
                    <h3>Your messages</h3>
                    <p>Select a conversation from the left to start chatting.</p>
                </div>

            </div>

        <?php endif; ?>

    </section>

</section>

</main>

<nav class="mobile-nav">

    <a href="home.php">
        <i class="fa-solid fa-house"></i>
    </a>

    <a href="messages.php" class="active">
        <i class="fa-solid fa-message"></i>
    </a>

    <a href="post.php" class="add">
        <i class="fa-solid fa-plus"></i>
    </a>

    <a href="notifications.php">
        <i class="fa-solid fa-bell"></i>
    </a>

    <a href="user_profile.php">
        <i class="fa-solid fa-user"></i>
    </a>

</nav>

<script>

/* SEARCH CONVERSATIONS */

const searchInput = document.getElementById("searchChats");
const chatItems = document.querySelectorAll(".chat-item");
const noSearchResult = document.getElementById("noSearchResult");

searchInput.addEventListener("input", function () {

    const search = this.value.toLowerCase().trim();

    let visibleCount = 0;

    chatItems.forEach(function (item) {

        const name = item.dataset.name || "";

        if (name.includes(search)) {
            item.style.display = "flex";
            visibleCount++;
        } else {
            item.style.display = "none";
        }

    });

    noSearchResult.style.display =
        visibleCount === 0 && chatItems.length > 0 ? "block" : "none";

});

/* MOBILE BACK BUTTON */

const container = document.getElementById("messagesContainer");
const backButton = document.getElementById("backButton");

if (backButton) {

    backButton.addEventListener("click", function () {
        container.classList.remove("show-chat");
    });

}

/* OPEN CHAT VIEW ON MOBILE */

if (window.innerWidth <= 650 && <?php echo $row ? 'true' : 'false'; ?>) {
    container.classList.add("show-chat");
}

/* SCROLL TO THE LATEST MESSAGE */

const messages = document.getElementById("chatMessages");

if (messages) {
    messages.scrollTop = messages.scrollHeight;
}

/* FOCUS MESSAGE INPUT */

const messageInput = document.getElementById("messageInput");

if (messageInput && window.innerWidth > 650) {
    messageInput.focus();
}

/* APPLY CHAT WALLPAPER */

(function () {

    const userId = window.VLAST_USER_ID;
    const partnerId = <?php echo (int)$profileId; ?>;

    const chatMessages = document.getElementById("chatMessages");

    if (!userId || !chatMessages) {
        return;
    }

    const storageKey =
        "vlastChatBackground_user_" + String(userId);

    try {

        const savedSettings = localStorage.getItem(storageKey);

        if (!savedSettings) {
            return;
        }

        const settings = JSON.parse(savedSettings);

        const individualWallpaper =
            settings.chats &&
            settings.chats[String(partnerId)]
                ? settings.chats[String(partnerId)]
                : null;

        const wallpaper = individualWallpaper || settings.global;

        if (wallpaper && wallpaper.value) {

            chatMessages.style.backgroundImage = wallpaper.value;
            chatMessages.style.backgroundSize = "cover";
            chatMessages.style.backgroundPosition = "center";
            chatMessages.style.backgroundRepeat = "no-repeat";

        }

    } catch (error) {

        console.error("Could not load chat wallpaper:", error);

    }

})();

</script>

</body>
</html>