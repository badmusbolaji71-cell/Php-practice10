
<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "social_media";

$connection = new mysqli($servername, $username, $password, $dbname);

if ($connection->connect_error) {
    die("Database connection failed.");
}

$connection->set_charset("utf8mb4");

$userId = (int)$_SESSION['id'];

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function timeAgo($date) {
    if (empty($date)) return "";

    $time = strtotime($date);
    if (!$time) return "";

    $difference = time() - $time;

    if ($difference < 60) return "Just now";
    if ($difference < 3600) return floor($difference / 60) . "m ago";
    if ($difference < 86400) return floor($difference / 3600) . "h ago";
    if ($difference < 604800) return floor($difference / 86400) . "d ago";

    return date("M j", $time);
}

$notifications = [];
$error = "";

$currentName = "Your account";
$currentPicture = "";

$userStmt = $connection->prepare("
    SELECT first_name, last_name, picturepr
    FROM user_details
    WHERE id = ?
    LIMIT 1
");

if ($userStmt) {
    $userStmt->bind_param("i", $userId);
    $userStmt->execute();

    $userResult = $userStmt->get_result();

    if ($user = $userResult->fetch_assoc()) {
        $currentName = trim($user['first_name'] . " " . $user['last_name']);
        $currentPicture = $user['picturepr'] ?? "";
    }

    $userStmt->close();
}

$tableCheck = $connection->query("SHOW TABLES LIKE 'notification'");

if (!$tableCheck || $tableCheck->num_rows === 0) {

    $error = "Your notification table does not exist.";

} else {

    $columns = [];

    $columnResult = $connection->query("SHOW COLUMNS FROM notification");

    if ($columnResult) {
        while ($column = $columnResult->fetch_assoc()) {
            $columns[] = $column['Field'];
        }
    }

    if (
        !in_array('user_id', $columns) ||
        !in_array('my_id', $columns) ||
        !in_array('post_id', $columns)
    ) {

        $error = "Your notification table must contain user_id, my_id and post_id.";

    } else {

        $dateColumn = "";

        foreach (['created_at', 'date', 'time', 'notification_date'] as $possibleDate) {
            if (in_array($possibleDate, $columns)) {
                $dateColumn = $possibleDate;
                break;
            }
        }

        $idColumn = "";

        foreach (['id', 'notification_id'] as $possibleId) {
            if (in_array($possibleId, $columns)) {
                $idColumn = $possibleId;
                break;
            }
        }

        if ($dateColumn) {
            $order = "n.`$dateColumn` DESC";
        } elseif ($idColumn) {
            $order = "n.`$idColumn` DESC";
        } else {
            $order = "n.post_id DESC";
        }

        $sql = "
            SELECT
                n.*,
                u.first_name,
                u.last_name,
                u.picturepr,
                p.id AS related_post_id

            FROM notification n

            LEFT JOIN user_details u
                ON u.id = n.user_id

            LEFT JOIN user_post p
                ON p.id = n.post_id

            WHERE n.my_id = ?

            ORDER BY $order

            LIMIT 100
        ";

        $stmt = $connection->prepare($sql);

        if ($stmt) {

            $stmt->bind_param("i", $userId);
            $stmt->execute();

            $result = $stmt->get_result();

            while ($row = $result->fetch_assoc()) {
                $notifications[] = $row;
            }

            $stmt->close();

        } else {

            $error = "Unable to load notifications. Check your database table structure.";

        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Vlast | Notifications</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

:root {
    --background: #f3f5f7;
    --white: #ffffff;
    --text: #222222;
    --muted: #888888;
    --border: #eeeeee;
    --black: #171717;
}

body {
    background: var(--background);
    color: var(--text);
    font-family: 'Poppins', sans-serif;
    padding-top: 85px;
}

a {
    text-decoration: none;
    color: inherit;
}

/* HEADER */

.topbar {
    height: 70px;
    width: 100%;
    position: fixed;
    top: 0;
    left: 0;
    z-index: 1000;
    background: rgba(255,255,255,.96);
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 5%;
    backdrop-filter: blur(15px);
}

.logo {
    font-family: 'Playfair Display', serif;
    font-size: 30px;
    font-weight: 800;
    letter-spacing: -1px;
}

.search-box {
    width: 400px;
    height: 43px;
    background: #f3f4f5;
    border-radius: 30px;
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 0 16px;
    transition: .2s;
}

.search-box:focus-within {
    background: #fff;
    box-shadow: 0 0 0 2px #171717;
}

.search-box i {
    color: #888;
}

.search-box input {
    border: none;
    outline: none;
    background: transparent;
    width: 100%;
    font-size: 13px;
    font-family: inherit;
}

.header-links {
    display: flex;
    align-items: center;
    gap: 23px;
}

.header-links a {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    display: grid;
    place-items: center;
    font-size: 19px;
    color: #555;
}

.header-links a:hover,
.header-links a.active {
    background: #f0f0f0;
    color: #111;
}

/* PAGE LAYOUT */

.page {
    width: 94%;
    max-width: 1240px;
    margin: 25px auto;
    display: grid;
    grid-template-columns: 235px minmax(0,650px) 285px;
    gap: 30px;
    align-items: start;
}

.left-column,
.right-column {
    position: sticky;
    top: 95px;
}

/* CARDS */

.side-card,
.right-card,
.notification-panel {
    background: var(--white);
    border: 1px solid var(--border);
    border-radius: 20px;
    box-shadow: 0 8px 28px rgba(0,0,0,.04);
}

/* PROFILE */

.profile-mini {
    text-align: center;
    padding: 20px;
}

.profile-mini img {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    object-fit: cover;
    margin-bottom: 10px;
}

.profile-mini h3 {
    font-size: 14px;
}

.profile-mini p {
    font-size: 11px;
    color: var(--muted);
    margin-top: 4px;
}

/* SIDE MENU */

.side-menu {
    margin-top: 15px;
    padding: 10px;
}

.side-menu a {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 13px;
    margin: 3px 0;
    border-radius: 12px;
    color: #666;
    font-size: 13px;
    transition: .2s;
}

.side-menu a i {
    width: 20px;
    font-size: 17px;
}

.side-menu a:hover,
.side-menu a.active {
    background: #f1f1f1;
    color: #111;
    font-weight: 700;
}

.side-menu a.active {
    box-shadow: inset 3px 0 #171717;
}

.side-note {
    padding: 18px;
    margin-top: 15px;
}

.side-note h4 {
    font-size: 13px;
    margin-bottom: 8px;
}

.side-note p {
    color: var(--muted);
    font-size: 11px;
    line-height: 1.8;
}

/* MAIN */

.main-column {
    min-width: 0;
}

.heading {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.heading small {
    color: #999;
    font-size: 10px;
    letter-spacing: 2px;
    font-weight: 700;
    text-transform: uppercase;
}

.heading h1 {
    font-family: 'Playfair Display', serif;
    font-size: 35px;
    margin-top: 5px;
}

.heading p {
    font-size: 12px;
    color: var(--muted);
    margin-top: 5px;
}

.heading-icon {
    width: 48px;
    height: 48px;
    background: #171717;
    color: white;
    border-radius: 15px;
    display: grid;
    place-items: center;
    font-size: 19px;
}

/* NOTIFICATIONS */

.notification-panel {
    overflow: hidden;
}

.panel-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px;
    border-bottom: 1px solid var(--border);
}

.panel-header h3 {
    font-size: 14px;
}

.panel-header span {
    color: var(--muted);
    font-size: 11px;
}

.notification-list {
    padding: 5px 12px;
}

.notification-item {
    display: flex;
    align-items: center;
    gap: 13px;
    padding: 17px 10px;
    border-bottom: 1px solid #f0f0f0;
    transition: .2s;
}

.notification-item:last-child {
    border-bottom: none;
}

.notification-item:hover {
    background: #fafafa;
    border-radius: 12px;
}

.actor-avatar,
.fallback-avatar {
    width: 49px;
    height: 49px;
    border-radius: 50%;
    flex-shrink: 0;
}

.actor-avatar {
    object-fit: cover;
    border: 1px solid #eee;
}

.fallback-avatar {
    background: #eee;
    display: grid;
    place-items: center;
    color: #888;
}

.notification-content {
    flex: 1;
    min-width: 0;
}

.notification-content p {
    font-size: 12px;
    line-height: 1.7;
    color: #555;
}

.notification-content strong {
    color: #171717;
    font-weight: 700;
}

.notification-content time {
    display: block;
    color: #aaa;
    font-size: 10px;
    margin-top: 4px;
}

.notification-arrow {
    width: 34px;
    height: 34px;
    background: #f5f5f5;
    color: #888;
    border-radius: 50%;
    display: grid;
    place-items: center;
    flex-shrink: 0;
}

.notification-item:hover .notification-arrow {
    background: #171717;
    color: white;
}

/* EMPTY STATE */

.empty-state {
    text-align: center;
    padding: 65px 20px;
}

.empty-icon {
    width: 75px;
    height: 75px;
    background: #f2f2f2;
    color: #888;
    border-radius: 25px;
    display: grid;
    place-items: center;
    font-size: 27px;
    margin: 0 auto 18px;
}

.empty-state h3 {
    font-size: 16px;
    margin-bottom: 8px;
}

.empty-state p {
    font-size: 12px;
    color: var(--muted);
    line-height: 1.8;
}

.error-message {
    margin: 18px;
    padding: 15px;
    background: #fff0f0;
    color: #a33;
    border-radius: 12px;
    font-size: 12px;
    line-height: 1.7;
}

/* RIGHT SIDEBAR */

.right-card {
    padding: 19px;
    margin-bottom: 15px;
}

.right-card h3 {
    font-size: 13px;
    margin-bottom: 13px;
}

.right-card p {
    font-size: 11px;
    color: var(--muted);
    line-height: 1.8;
}

.quick-link {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 11px 0;
    border-bottom: 1px solid #f1f1f1;
    font-size: 12px;
    color: #666;
}

.quick-link:last-child {
    border: none;
}

.quick-link i {
    width: 20px;
    color: #222;
}

.copyright {
    color: #aaa;
    font-size: 10px;
    line-height: 1.8;
}

/* MOBILE NAV */

.mobile-bottom {
    display: none;
}

/* TABLET */

@media(max-width:1100px) {
    .page {
        grid-template-columns: 220px minmax(0,1fr);
        max-width: 930px;
    }

    .right-column {
        display: none;
    }
}

/* MOBILE */

@media(max-width:750px) {

    body {
        padding-top: 75px;
        padding-bottom: 80px;
    }

    .topbar {
        height: 62px;
        padding: 0 15px;
    }

    .logo {
        font-size: 27px;
    }

    .search-box {
        width: 42%;
        height: 38px;
        padding: 0 12px;
    }

    .header-links {
        gap: 3px;
    }

    .header-links a {
        display: none;
    }

    .page {
        display: block;
        width: 100%;
        padding: 0 13px;
        margin: 15px auto;
    }

    .left-column,
    .right-column {
        display: none;
    }

    .heading h1 {
        font-size: 30px;
    }

    .notification-panel {
        border-radius: 17px;
    }

    .notification-list {
        padding: 4px 8px;
    }

    .notification-item {
        gap: 10px;
        padding: 14px 7px;
    }

    .actor-avatar,
    .fallback-avatar {
        width: 43px;
        height: 43px;
    }

    .notification-content p {
        font-size: 11px;
    }

    .mobile-bottom {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        height: 65px;
        z-index: 1000;
        display: flex;
        align-items: center;
        justify-content: space-around;
        background: rgba(255,255,255,.97);
        border-top: 1px solid var(--border);
        backdrop-filter: blur(15px);
        padding-bottom: env(safe-area-inset-bottom);
    }

    .mobile-bottom a {
        width: 43px;
        height: 43px;
        display: grid;
        place-items: center;
        border-radius: 50%;
        color: #777;
        font-size: 19px;
    }

    .mobile-bottom a.active {
        background: #171717;
        color: white;
    }
}

@media(max-width:390px) {
    .search-box {
        width: 40%;
    }

    .heading h1 {
        font-size: 27px;
    }

    .notification-content p {
        font-size: 10.5px;
    }
}

</style>

</head>

<body>

<!-- HEADER -->

<header class="topbar">

    <a href="home.php" class="logo">Vlast</a>

    <div class="search-box">
        <i class="fa-solid fa-magnifying-glass"></i>

        <input
            type="search"
            id="notificationSearch"
            placeholder="Search notifications..."
            autocomplete="off"
        >
    </div>

    <nav class="header-links">

        <a href="home.php">
            <i class="fa-solid fa-house"></i>
        </a>

        <a href="messages.php">
            <i class="fa-regular fa-paper-plane"></i>
        </a>

        <a href="explore_user.php">
            <i class="fa-regular fa-compass"></i>
        </a>

        <a href="notification.php" class="active">
            <i class="fa-regular fa-bell"></i>
        </a>

        <a href="user_profile.php">
            <i class="fa-regular fa-user"></i>
        </a>

    </nav>

</header>


<div class="page">

    <!-- LEFT SIDEBAR -->

    <aside class="left-column">

        <div class="side-card profile-mini">

            <?php if (!empty($currentPicture)): ?>

                <img src="image/<?php echo e($currentPicture); ?>" alt="Profile">

            <?php else: ?>

                <div class="fallback-avatar" style="margin:0 auto 10px;">
                    <i class="fa-regular fa-user"></i>
                </div>

            <?php endif; ?>

            <h3><?php echo e($currentName); ?></h3>

            <p>Welcome back to Vlast</p>

        </div>

        <nav class="side-card side-menu">

            <a href="home.php">
                <i class="fa-solid fa-house"></i>
                Home
            </a>

            <a href="explore_user.php">
                <i class="fa-regular fa-compass"></i>
                Explore
            </a>

            <a href="messages.php">
                <i class="fa-regular fa-paper-plane"></i>
                Messages
            </a>

            <a href="notification.php" class="active">
                <i class="fa-regular fa-bell"></i>
                Notifications
            </a>

            <a href="user_profile.php">
                <i class="fa-regular fa-user"></i>
                Profile
            </a>

            <a href="settings.php">
                <i class="fa-solid fa-gear"></i>
                Settings
            </a>

        </nav>

        <div class="side-card side-note">

            <h4>
                <i class="fa-solid fa-wand-magic-sparkles"></i>
                Your Vlast space
            </h4>

            <p>
                Stay connected with the people and moments that matter to you.
            </p>

        </div>

    </aside>


    <!-- MAIN CONTENT -->

    <main class="main-column">

        <div class="heading">

            <div>
                <small>Stay in the loop</small>

                <h1>Notifications</h1>

                <p>
                    See what's happening around your Vlast account.
                </p>
            </div>

            <div class="heading-icon">
                <i class="fa-regular fa-bell"></i>
            </div>

        </div>


        <section class="notification-panel">

            <div class="panel-header">

                <h3>Recent activity</h3>

                <span>
                    <?php echo count($notifications); ?> notifications
                </span>

            </div>


            <?php if ($error !== ""): ?>

                <div class="error-message">
                    <?php echo e($error); ?>
                </div>


            <?php elseif (empty($notifications)): ?>

                <div class="empty-state">

                    <div class="empty-icon">
                        <i class="fa-regular fa-bell-slash"></i>
                    </div>

                    <h3>You're all caught up</h3>

                    <p>
                        When someone interacts with your posts or your account,
                        you'll see the update here.
                    </p>

                </div>


            <?php else: ?>

                <div class="notification-list">

                    <?php foreach ($notifications as $notification):

                        $actorName = trim(
                            ($notification['first_name'] ?? '') . " " .
                            ($notification['last_name'] ?? '')
                        );
                       

                        if ($actorName === "") {
                            $actorName = "Someone";
                        }

                        $actorPicture = $notification['picturepr'] ?? "";

                        $postId = (int)($notification['related_post_id'] ?? 0);

                        $target = $postId > 0
                            ? "home.php#post-" . $postId
                            : "notification.php";

                        $dateValue = "";

                        foreach (
                            ['created_at', 'date', 'time', 'notification_date']
                            as $dateField
                        ) {
                            if (!empty($notification[$dateField])) {
                                $dateValue = $notification[$dateField];
                                break;
                            }
                        }

                    ?>

                        <a
                            href="<?php echo e($target); ?>"
                            class="notification-item"
                        >

                            <?php if (!empty($actorPicture)): ?>

                                <img
                                    src="image/<?php echo e($actorPicture); ?>"
                                    class="actor-avatar"
                                    alt="User"
                                >

                            <?php else: ?>

                                <div class="fallback-avatar">
                                    <i class="fa-regular fa-user"></i>
                                </div>

                            <?php endif; ?>


                            <div class="notification-content">

                                <p>
                                    <strong>
                                        <?php echo e($actorName); ?>
                                         <a   href="other_users_pp.php?id=<?php echo (int)$notification['id']; ?>"></a>
                                    </strong>

                                    interacted with your post.
                                </p>

                                <time>
                                    <?php echo e(timeAgo($dateValue)); ?>
                                </time>

                            </div>


                            <span class="notification-arrow">
                                <i class="fa-solid fa-chevron-right"></i>
                            </span>

                        </a>

                    <?php endforeach; ?>

                </div>

            <?php endif; ?>


            <!-- SEARCH EMPTY STATE -->

            <div id="noSearchResults" class="empty-state" style="display:none;">

                <div class="empty-icon">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </div>

                <h3>No results found</h3>

                <p>
                    Try searching with another name or keyword.
                </p>

            </div>

        </section>

    </main>


    <!-- RIGHT SIDEBAR -->

    <aside class="right-column">

        <div class="right-card">

            <h3>Quick access</h3>

            <a href="home.php" class="quick-link">
                <i class="fa-solid fa-house"></i>
                Your home feed
            </a>

            <a href="explore_user.php" class="quick-link">
                <i class="fa-regular fa-compass"></i>
                Discover people
            </a>

            <a href="messages.php" class="quick-link">
                <i class="fa-regular fa-paper-plane"></i>
                Your messages
            </a>

            <a href="user_profile.php" class="quick-link">
                <i class="fa-regular fa-user"></i>
                Your profile
            </a>

        </div>

        <div class="right-card">

            <h3>About notifications</h3>

            <p>
                Notifications help you keep track of activity connected
                to your posts and your Vlast account.
            </p>

        </div>

        <div class="copyright">
            © <?php echo date('Y'); ?> Vlast.
            Made for real connections.
        </div>

    </aside>

</div>


<!-- MOBILE BOTTOM NAVIGATION -->

<nav class="mobile-bottom">

    <a href="home.php">
        <i class="fa-solid fa-house"></i>
    </a>

    <a href="explore_user.php">
        <i class="fa-regular fa-compass"></i>
    </a>

    <a href="messages.php">
        <i class="fa-regular fa-paper-plane"></i>
    </a>

    <a href="notification.php" class="active">
        <i class="fa-regular fa-bell"></i>
    </a>

    <a href="user_profile.php">
        <i class="fa-regular fa-user"></i>
    </a>

</nav>


<script>
window.VLAST_USER_ID = <?php echo $userId; ?>;
</script>

<!-- LIVE NOTIFICATION SEARCH -->

<script>

document.addEventListener("DOMContentLoaded", function () {

    const searchInput = document.getElementById("notificationSearch");

    const notificationItems = document.querySelectorAll(".notification-item");

    const noResults = document.getElementById("noSearchResults");

    const notificationList = document.querySelector(".notification-list");

    if (!searchInput) return;

    searchInput.addEventListener("input", function () {

        const searchValue = this.value.toLowerCase().trim();

        let visibleCount = 0;

        notificationItems.forEach(function (item) {

            const content = item.textContent.toLowerCase();

            if (content.includes(searchValue)) {

                item.style.display = "flex";
                visibleCount++;

            } else {

                item.style.display = "none";

            }

        });

        if (noResults) {

            noResults.style.display =
                searchValue !== "" && visibleCount === 0
                ? "block"
                : "none";

        }

        if (notificationList) {

            notificationList.style.display =
                searchValue !== "" && visibleCount === 0
                ? "none"
                : "block";

        }

    });

});

</script>

<script src="theme.js"></script>

</body>
</html>
