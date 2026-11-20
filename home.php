
<?php
session_start();

$connection = new mysqli("localhost", "root", "", "social_media");

if ($connection->connect_error) {
    die("Database connection failed.");
}

$connection->set_charset("utf8mb4");

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

$currentUserId = (int)$_SESSION['id'];
$message = "";
$searchTerm = trim($_GET['search'] ?? '');

function h($value) {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function profileImage($filename) {
    return !empty($filename)
        ? 'image/' . rawurlencode(basename($filename))
        : '';
}

/* POST IMAGE PATH */
function postImage($filename) {
    return !empty($filename)
        ? 'image/' . rawurlencode(basename($filename))
        : '';
}

/* LIKE / UNLIKE */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_like'])) {

    $postId = (int)($_POST['post_id'] ?? 0);

    if ($postId > 0) {

        $ownerStmt = $connection->prepare(
            "SELECT uploader_id
             FROM user_post
             WHERE id = ?
             LIMIT 1"
        );

        $ownerStmt->bind_param("i", $postId);
        $ownerStmt->execute();

        $postOwner = $ownerStmt->get_result()->fetch_assoc();
        $ownerStmt->close();

        if ($postOwner) {

            $ownerId = (int)$postOwner['uploader_id'];

            $check = $connection->prepare(
                "SELECT 1
                 FROM likes
                 WHERE user_id = ? AND post_id = ?
                 LIMIT 1"
            );

            $check->bind_param("ii", $currentUserId, $postId);
            $check->execute();

            $alreadyLiked = $check->get_result()->num_rows > 0;
            $check->close();

            if ($alreadyLiked) {

                /* UNLIKE */
                $delete = $connection->prepare(
                    "DELETE FROM likes
                     WHERE user_id = ? AND post_id = ?
                     LIMIT 1"
                );

                $delete->bind_param("ii", $currentUserId, $postId);
                $delete->execute();
                $delete->close();

            } else {

                /* LIKE */
                $insert = $connection->prepare(
                    "INSERT IGNORE INTO likes (user_id, post_id)
                     VALUES (?, ?)"
                );

                $insert->bind_param("ii", $currentUserId, $postId);
                $insert->execute();

                $newLike = $insert->affected_rows > 0;
                $insert->close();

                /* NOTIFY POST OWNER */
                if ($newLike && $ownerId !== $currentUserId) {

                    $notice = $connection->prepare(
                        "INSERT INTO `notification`
                         (post_id, user_id, my_id, `date`)
                         VALUES (?, ?, ?, NOW())"
                    );

                    $notice->bind_param(
                        "iii",
                        $postId,
                        $currentUserId,
                        $ownerId
                    );

                    $notice->execute();
                    $notice->close();
                }
            }
        }
    }

    $redirectUrl = "home.php";

    if ($searchTerm !== '') {
        $redirectUrl .= '?search=' . urlencode($searchTerm);
    }

    header("Location: " . $redirectUrl . "#post-" . $postId);
    exit;
}

/* DELETE OWN POST */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_post'])) {

    $postId = (int)($_POST['post_id'] ?? 0);

    $deleteCheck = $connection->prepare(
        "SELECT picture
         FROM user_post
         WHERE id = ? AND uploader_id = ?
         LIMIT 1"
    );

    $deleteCheck->bind_param("ii", $postId, $currentUserId);
    $deleteCheck->execute();

    $ownedPost = $deleteCheck->get_result()->fetch_assoc();
    $deleteCheck->close();

    if ($ownedPost) {

        $connection->begin_transaction();

        try {

            $removeLikes = $connection->prepare(
                "DELETE FROM likes WHERE post_id = ?"
            );

            $removeLikes->bind_param("i", $postId);
            $removeLikes->execute();
            $removeLikes->close();

            $removePost = $connection->prepare(
                "DELETE FROM user_post
                 WHERE id = ? AND uploader_id = ?"
            );

            $removePost->bind_param("ii", $postId, $currentUserId);
            $removePost->execute();
            $removePost->close();

            $connection->commit();

            if (!empty($ownedPost['picture'])) {

                $imagePath = __DIR__ . '/image/' .
                    basename($ownedPost['picture']);

                if (is_file($imagePath)) {
                    unlink($imagePath);
                }
            }

        } catch (Throwable $e) {

            $connection->rollback();
            $message = "Post could not be deleted. Please try again.";
        }
    }

    header("Location: home.php");
    exit;
}

/* CREATE POST */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_post'])) {

    $postText = trim($_POST['text'] ?? '');
    $picture = '';

    if (!empty($_FILES['file']['name'])) {

        if ($_FILES['file']['error'] === UPLOAD_ERR_OK) {

            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            $extension = strtolower(
                pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION)
            );

            if (in_array($extension, $allowed, true)) {

                $uploadDirectory = __DIR__ . '/image/';

                if (!is_dir($uploadDirectory)) {
                    mkdir($uploadDirectory, 0755, true);
                }

                $picture = uniqid('post_', true) . '.' . $extension;

                if (!move_uploaded_file(
                    $_FILES['file']['tmp_name'],
                    $uploadDirectory . $picture
                )) {
                    $message = "Image upload failed.";
                    $picture = '';
                }

            } else {
                $message = "Upload a JPG, PNG, GIF or WEBP image.";
            }

        } else {
            $message = "There was a problem uploading your image.";
        }
    }

    if ($message === '') {

        if ($postText === '' && $picture === '') {

            $message = "Post can't be empty.";

        } else {

            $stmt = $connection->prepare(
                "INSERT INTO user_post (text, picture, uploader_id)
                 VALUES (?, ?, ?)"
            );

            $stmt->bind_param("ssi", $postText, $picture, $currentUserId);

            if ($stmt->execute()) {

                $stmt->close();

                header("Location: home.php");
                exit;
            }

            $stmt->close();
            $message = "Could not publish your post.";
        }
    }
}

/* CURRENT USER */
$currentStmt = $connection->prepare(
    "SELECT id, first_name, last_name, email, picturepr,role
     FROM user_details
     WHERE id = ?
     LIMIT 1"
);

$currentStmt->bind_param("i", $currentUserId);
$currentStmt->execute();

$currentUser = $currentStmt->get_result()->fetch_assoc();
$currentStmt->close();

if (!$currentUser) {
    session_destroy();
    header("Location: login.php");
    exit;
}

/* USER SEARCH */
$searchUsers = null;

if ($searchTerm !== '') {

    $searchLike = '%' . $searchTerm . '%';

    $searchStmt = $connection->prepare(
        "SELECT id, first_name, last_name, picturepr, verified
         FROM user_details
         WHERE id != ?
         AND CONCAT(first_name, ' ', last_name) LIKE ?
         ORDER BY first_name, last_name
         LIMIT 20"
    );

    $searchStmt->bind_param("is", $currentUserId, $searchLike);
    $searchStmt->execute();

    $searchUsers = $searchStmt->get_result();
}

/* PEOPLE YOU MAY KNOW */
$suggestionsStmt = $connection->prepare(
    "SELECT id, first_name, last_name, picturepr, verified, memes_admin
     FROM user_details
     WHERE id != ?
     ORDER BY RAND()
     LIMIT 3"
);

$suggestionsStmt->bind_param("i", $currentUserId);
$suggestionsStmt->execute();

$suggestions = $suggestionsStmt->get_result();

/* STORIES */
$storyStmt = $connection->prepare(
    "SELECT id, first_name, picturepr
     FROM user_details
     WHERE id != ?
     ORDER BY RAND()
     LIMIT 10"
);

$storyStmt->bind_param("i", $currentUserId);
$storyStmt->execute();

$storyUsers = $storyStmt->get_result();

/* POSTS - RANDOM ORDER */
$postsStmt = $connection->prepare(
    "SELECT
        p.id,
        p.text,
        p.picture,
        p.date,

        u.id AS userID,
        u.first_name,
        u.last_name,
        u.picturepr,
        u.verified,
        u.memes_admin,

        (SELECT COUNT(*)
         FROM likes l
         WHERE l.post_id = p.id) AS like_count,

        (SELECT COUNT(*)
         FROM likes l
         WHERE l.post_id = p.id
         AND l.user_id = ?) AS is_liked

     FROM user_post p

     INNER JOIN user_details u
        ON u.id = p.uploader_id

     ORDER BY RAND()"
);

$postsStmt->bind_param("i", $currentUserId);
$postsStmt->execute();

$posts = $postsStmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Vlast | Home</title>

<link rel="stylesheet"
 href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<link rel="preconnect" href="https://fonts.googleapis.com">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Playfair+Display:wght@700&display=swap"
 rel="stylesheet">

<script>
window.VLAST_USER_ID = <?php echo (int)$currentUserId; ?>;
</script>

<script src="theme.js"></script>

<style>

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

:root {
    --black: #111;
    --muted: #888;
    --background: #f3f5f7;
    --border: #ededed;

    --vlast-accent: var(--accent, #111);
    --vlast-text: var(--text, #222);
    --vlast-surface: var(--surface, #fff);
    --vlast-soft: var(--soft, #f1f2f4);
    --vlast-muted: var(--muted, #888);
    --vlast-border: var(--border, #ededed);
}

body {
    background: var(--background, #f3f5f7);
    color: var(--vlast-text);
    font-family: 'Poppins', sans-serif;
    padding-top: 78px;
    padding-bottom: 80px;
}

a {
    color: inherit;
    text-decoration: none;
}

button, input, textarea {
    font: inherit;
}

button {
    cursor: pointer;
}

/* HEADER */

header {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 70px;
    padding: 0 4%;
    background: var(--vlast-surface);
    box-shadow: 0 2px 12px rgba(0,0,0,.06);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    z-index: 1000;
}

.logo {
    font-family: 'Playfair Display', serif;
    font-size: 31px;
    font-weight: 700;
    letter-spacing: -1px;
}

/* PAGE LAYOUT */

.page {
    width: min(1400px, 96%);
    margin: 12px auto 40px;
    display: grid;
    grid-template-columns: 235px minmax(0, 650px) 285px;
    gap: 22px;
    align-items: start;
}

.left-sidebar,
.right-sidebar {
    position: sticky;
    top: 84px;
    align-self: start;
    max-height: calc(100vh - 95px);
    overflow-y: auto;
    scrollbar-width: thin;
    scrollbar-color: #ddd transparent;
}

.sidebar-card,
.card {
    background: var(--vlast-surface);
    border-radius: 17px;
    padding: 18px;
    margin-bottom: 17px;
    box-shadow: 0 3px 15px rgba(0,0,0,.035);
    }

.mini-profile {
    text-align: center;
}

.mini-profile img {
    width: 76px;
    height: 76px;
    object-fit: cover;
    border-radius: 50%;
    background: #eee;
    margin-bottom: 10px;
}

.mini-profile h3 {
    font-size: 14px;
    overflow-wrap: anywhere;
}

.mini-profile p {
    font-size: 10px;
    color: var(--vlast-muted);
    margin-top: 4px;
    overflow-wrap: anywhere;
}

.primary-link {
    display: flex;
    justify-content: center;
    align-items: center;
    background: var(--vlast-accent);
    color: #fff;
    padding: 10px;
    border-radius: 9px;
    font-size: 11px;
    font-weight: 600;
    margin-top: 14px;
}

/* SIDEBAR MENU */

.side-menu {
    display: grid;
    gap: 5px;
}

.side-menu a {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 10px;
    border-radius: 10px;
    font-size: 12px;
    color: var(--vlast-muted);
}

.side-menu a:hover,
.side-menu a.active {
    color: var(--vlast-text);
    background: var(--vlast-soft);
}

.side-menu i {
    width: 20px;
    text-align: center;
    font-size: 16px;
}

/* FEED */

.feed {
    min-width: 0;
}

/* STORIES */

.stories {
    display: flex;
    gap: 14px;
    overflow-x: auto;
    background: var(--vlast-surface);
    border-radius: 17px;
    padding: 17px;
    margin-bottom: 17px;
    scrollbar-width: thin;
}

.story {
    min-width: 65px;
    text-align: center;
}

.story-avatar {
    width: 57px;
    height: 57px;
    border-radius: 50%;
    padding: 3px;
    border: 2px solid var(--vlast-accent);
    margin: 0 auto 7px;
    overflow: hidden;
}

.story-avatar img {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
}

.story span {
    display: block;
    font-size: 9px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* CREATE POST */

.create-post {
    position: relative;
    background: var(--vlast-surface);
    border-radius: 18px;
    padding: 18px;
    margin-bottom: 20px;
    box-shadow: 0 5px 25px rgba(0,0,0,.045);
    border: 1px solid var(--vlast-border);
}

.create-post::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: var(--vlast-accent);
    border-radius: 18px 18px 0 0;
}

.create-top {
    display: flex;
    align-items: center;
    gap: 12px;
}

.create-avatar {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background: var(--vlast-soft);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.create-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.create-avatar i {
    color: var(--vlast-muted);
}

.create-input {
    flex: 1;
    background: var(--vlast-soft);
    border-radius: 25px;
    padding: 13px 18px;
    color: var(--vlast-muted);
    font-size: 13px;
    cursor: pointer;
    border: 1px solid transparent;
    transition: .25s ease;
}

.create-input:hover {
    border-color: var(--vlast-border);
    transform: scale(1.01);
}

.create-actions {
    display: flex;
    margin-top: 15px;
    border-top: 1px solid var(--vlast-border);
    padding-top: 12px;
    justify-content: space-around;
    gap: 5px;
}

.create-actions button {
    border: none;
    background: transparent;
    cursor: pointer;
    padding: 8px 15px;
    border-radius: 10px;
    color: var(--vlast-muted);
    font-size: 12px;
    transition: .2s ease;
}

.create-actions button:hover {
    background: var(--vlast-soft);
    color: var(--vlast-accent);
}

/* POSTS */

.post {
    background: var(--vlast-surface);
    border-radius: 17px;
    padding: 18px;
    margin-bottom: 17px;
    box-shadow: 0 3px 15px rgba(0,0,0,.035);
    min-width: 0;
    scroll-margin-top: 90px;
}

.post-header {
    display: flex;
    align-items: center;
    gap: 11px;
    margin-bottom: 14px;
}

.post-header > img {
    width: 43px;
    height: 43px;
    object-fit: cover;
    border-radius: 50%;
    background: #eee;
}

.post-user {
    flex: 1;
    min-width: 0;
}

.post-user strong {
    display: block;
    font-size: 12px;
}

.post-user small {
    display: block;
    font-size: 9px;
    color: var(--vlast-muted);
    margin-top: 4px;
}

.post-text {
    font-size: 12px;
    line-height: 1.8;
    overflow-wrap: anywhere;
    white-space: pre-wrap;
    margin-bottom: 13px;
}

.post-image {
    display: block;
    width: 100%;
    max-height: 650px;
    object-fit: contain;
    background: var(--vlast-soft);
    border-radius: 12px;
    margin-bottom: 13px;
}

/* POST ACTIONS */

.post-actions {
    display: flex;
    align-items: center;
    gap: 20px;
    border-top: 1px solid var(--vlast-border);
    padding-top: 13px;
    flex-wrap: wrap;
}

.post-actions form {
    margin: 0;
    padding: 0;
}

.post-actions button {
    display: flex;
    align-items: center;
    gap: 7px;
    border: 0;
    background: transparent;
    color: var(--vlast-muted);
    cursor: pointer;
    font-size: 11px;
    transition: .2s;
}

.post-actions button:hover {
    color: var(--vlast-accent);
}

.post-actions .like-button {
    color: var(--vlast-muted);
}

.post-actions .like-button.liked {
    color: #e63946;
}

.post-actions .like-button.liked i {
    font-weight: 900;
}

.post-actions .like-button:hover {
    transform: scale(1.06);
}

.post-actions .like-button span {
    font-size: 11px;
}
</style>
</head>
<body>
<style>
/* HEADER CONTENT */
.header-left {
    display: flex;
    align-items: center;
    gap: 18px;
}

.header-search {
    width: min(340px, 40vw);
    position: relative;
}

.header-search input {
    width: 100%;
    padding: 11px 16px 11px 40px;
    border: 1px solid var(--vlast-border);
    border-radius: 25px;
    outline: none;
    background: var(--vlast-soft);
    color: var(--vlast-text);
    font-size: 12px;
}

.header-search i {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--vlast-muted);
}

.mobile-menu-button {
    display: none;
    border: 0;
    background: transparent;
    font-size: 20px;
    color: var(--vlast-text);
}

.verified {
    color: #1689e8;
    font-size: 12px;
    margin-left: 3px;
}

.admin-badge {
    display: inline-block;
    background: #fff1c7;
    color: #a16a00;
    font-size: 8px;
    padding: 3px 6px;
    border-radius: 5px;
    margin-left: 4px;
}

.post-menu {
    border: 0;
    background: transparent;
    color: var(--vlast-muted);
    padding: 8px;
}

.delete-form button {
    color: #e63946;
    font-size: 11px;
    border: 0;
    background: transparent;
    padding: 8px;
}

.comment-section {
    display: none;
    border-top: 1px solid var(--vlast-border);
    margin-top: 15px;
    padding-top: 15px;
}

.comment-section.show {
    display: block;
}

.comment {
    display: flex;
    gap: 9px;
    margin-bottom: 13px;
}

.comment img {
    width: 31px;
    height: 31px;
    border-radius: 50%;
    object-fit: cover;
    background: var(--vlast-soft);
}

.comment-content {
    flex: 1;
    min-width: 0;
    background: var(--vlast-soft);
    padding: 9px 12px;
    border-radius: 12px;
}

.comment-content strong {
    font-size: 10px;
    display: block;
    margin-bottom: 4px;
}

.comment-content p {
    font-size: 11px;
    line-height: 1.6;
    overflow-wrap: anywhere;
    white-space: pre-wrap;
}

.comment-content small {
    display: block;
    color: var(--vlast-muted);
    font-size: 8px;
    margin-top: 5px;
}

.comment-form {
    display: flex;
    gap: 8px;
    margin-top: 13px;
}

.comment-form input {
    flex: 1;
    min-width: 0;
    border: 1px solid var(--vlast-border);
    background: var(--vlast-soft);
    border-radius: 20px;
    padding: 10px 14px;
    outline: none;
    color: var(--vlast-text);
    font-size: 11px;
}

.comment-form button {
    border: 0;
    background: var(--vlast-accent);
    color: white;
    padding: 0 15px;
    border-radius: 20px;
    font-size: 11px;
}

.search-results {
    background: var(--vlast-surface);
    padding: 17px;
    border-radius: 15px;
    margin-bottom: 17px;
}

.search-results h3 {
    font-size: 13px;
    margin-bottom: 13px;
}

.search-user {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 9px 0;
    border-bottom: 1px solid var(--vlast-border);
}

.search-user:last-child {
    border-bottom: 0;
}

.search-user img {
    width: 39px;
    height: 39px;
    border-radius: 50%;
    object-fit: cover;
}

.search-user strong {
    font-size: 11px;
}

.search-user small {
    display: block;
    font-size: 9px;
    color: var(--vlast-muted);
}

.search-user .view-profile {
    margin-left: auto;
    color: var(--vlast-accent);
    font-size: 10px;
}

.suggestion {
    display: flex;
    align-items: center;
    gap: 9px;
    margin: 13px 0;
}

.suggestion img {
    width: 37px;
    height: 37px;
    border-radius: 50%;
    object-fit: cover;
}

.suggestion-info {
    flex: 1;
    min-width: 0;
}

.suggestion-info strong {
    display: block;
    font-size: 10px;
    overflow-wrap: anywhere;
}

.suggestion-info small {
    font-size: 8px;
    color: var(--vlast-muted);
}

.suggestion a {
    font-size: 9px;
    font-weight: 600;
    color: var(--vlast-accent);
}

.sidebar-heading {
    font-size: 13px;
    margin-bottom: 14px;
}

.about-vlast {
    font-size: 10px;
    line-height: 1.8;
    color: var(--vlast-muted);
}

.bottom-nav {
    position: fixed;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 62px;
    background: var(--vlast-surface);
    border-top: 1px solid var(--vlast-border);
    display: flex;
    align-items: center;
    justify-content: space-around;
    z-index: 900;
}

.bottom-nav a {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    color: var(--vlast-muted);
    font-size: 9px;
}

.bottom-nav a i {
    font-size: 18px;
}

.bottom-nav a.active {
    color: var(--vlast-accent);
}

.mobile-drawer {
    display: none;
}

.empty-feed {
    background: var(--vlast-surface);
    padding: 35px 15px;
    text-align: center;
    border-radius: 15px;
    color: var(--vlast-muted);
    font-size: 12px;
}

.empty-feed i {
    font-size: 30px;
    margin-bottom: 12px;
}

@media(max-width:1100px) {
    .page {
        grid-template-columns: 220px minmax(0, 1fr);
    }

    .right-sidebar {
        display: none;
    }
}

@media(max-width:750px) {
    body {
        padding-top: 70px;
        padding-bottom: 75px;
    }

    header {
        height: 62px;
        padding: 0 4%;
    }

    .logo {
        font-size: 26px;
    }

    .mobile-menu-button {
        display: block;
    }

    .header-search {
        width: min(230px, 52vw);
    }

    .page {
        display: block;
        width: 96%;
        margin-top: 10px;
    }

    .left-sidebar {
        display: none;
    }

    .feed {
        width: 100%;
    }

    .post {
        padding: 14px;
    }

    .stories {
        padding: 13px;
    }

    .mobile-drawer {
        position: fixed;
        top: 62px;
        left: 0;
        width: 270px;
        max-width: 85%;
        height: calc(100vh - 62px);
        background: var(--vlast-surface);
        z-index: 1200;
        padding: 20px;
        box-shadow: 5px 0 20px rgba(0,0,0,.12);
        transform: translateX(-110%);
        transition: .25s ease;
        display: block;
        overflow-y: auto;
    }

    .mobile-drawer.open {
        transform: translateX(0);
    }

    .drawer-overlay {
        display: none;
        position: fixed;
        inset: 62px 0 0;
        background: rgba(0,0,0,.4);
        z-index: 1100;
    }

    .drawer-overlay.show {
        display: block;
    }
}
</style>

<header>
    <div class="header-left">
        <button type="button" class="mobile-menu-button" id="menuToggle">
            <i class="fa-solid fa-bars"></i>
        </button>

        <a href="home.php" class="logo">Vlast</a>
    </div>

    <form class="header-search" method="GET" action="home.php">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input
            type="search"
            name="search"
            placeholder="Search people..."
            value="<?php echo h($searchTerm); ?>"
        >
    </form>
</header>

<div class="drawer-overlay" id="drawerOverlay"></div>

<aside class="mobile-drawer" id="mobileDrawer">
    <div class="mini-profile">
        <?php if (!empty($currentUser['picturepr'])): ?>
            <img src="image/<?php echo ($currentUser['picturepr']); ?>" alt="Profile picture">
        <?php else: ?>
            <i class="fa-solid fa-circle-user" style="font-size:65px;color:#aaa;"></i>
        <?php endif; ?>

        <h3>
            <?php echo h($currentUser['first_name'] . ' ' . $currentUser['last_name']); ?>
        </h3>
        <p><?php echo h($currentUser['email']); ?></p>

        <a class="primary-link" href="user_profile.php?id=<?php echo $currentUserId; ?>">
            View Profile
        </a>
    </div>

    <br>

    <nav class="side-menu">
        <a href="home.php" class="active"><i class="fa-solid fa-house"></i> Home</a>
        <a href="user_profile.php?id=<?php echo $currentUserId; ?>"><i class="fa-solid fa-user"></i> My Profile</a>
        <a href="notification.php"><i class="fa-solid fa-bell"></i> Notifications</a>
        <a href="messages.php"><i class="fa-solid fa-message"></i> Messages</a>
        <a href="explore_user.php">
            <i class="fa-regular fa-compass"></i> Explore Users
        </a>
        <a href="post.php">
            <i class="fa-solid fa-box"></i> Post
        </a>
        <a href="memes.php">
            <i class="fa-solid fa-fire"></i> Memes Spark
        </a>
        <a href="settings.php"><i class="fa-solid fa-gear"></i> Settings</a>

<?php
if($currentUser['role'] == 'admin'){
?>
        <a href="admin_homepage.php">
            <i class="fa-solid fa-user-group"></i> My ADMIN
        </a>
<?php
}
?>
        <a href="login.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
    </nav>
</aside>

<div class="page">

    <!-- LEFT SIDEBAR -->
    <aside class="left-sidebar">

        <div class="sidebar-card mini-profile">

            <?php if (!empty($currentUser['picturepr'])): ?>
                <img src="image/<?php echo($currentUser['picturepr']); ?>" alt="Profile picture">
            <?php else: ?>
                <i class="fa-solid fa-circle-user" style="font-size:75px;color:#aaa;"></i>
            <?php endif; ?>

            <h3>
                <?php echo h($currentUser['first_name'] . ' ' . $currentUser['last_name']); ?>
            </h3>

            <p><?php echo h($currentUser['email']); ?></p>

            <a class="primary-link" href="user_profile.php?id=<?php echo $currentUserId; ?>">
                View My Profile
            </a>
        </div>

        <div class="sidebar-card">
            <nav class="side-menu">
                <a href="home.php" class="active">
                    <i class="fa-solid fa-house"></i> Home
                </a>

                <a href="user_profile.php?id=<?php echo $currentUserId; ?>">
                    <i class="fa-solid fa-user"></i> My Profile
                </a>

                <a href="notification.php">
                    <i class="fa-solid fa-bell"></i> Notifications
                </a>

                <a href="messages.php">
                    <i class="fa-solid fa-message"></i> Messages
                </a>
                <a href="explore_user.php">
                    <i class="fa-regular fa-compass"></i> Explore Users
                </a>
                <a href="post.php">
                    <i class="fa-solid fa-box"></i> Post
                </a>
                <a href="memes.php">
                    <i class="fa-solid fa-fire"></i> Memes Spark
                </a>
                <a href="settings.php">
                    <i class="fa-solid fa-gear"></i> Settings
                </a>

<?php
if($currentUser['role'] == 'admin'){
?>
                <a href="admin_homepage.php">
                    <i class="fa-solid fa-user-group"></i> My ADMIN
                </a>
<?php
}
?>
                <a href="login.php">
                    <i class="fa-solid fa-right-from-bracket"></i> Logout
                </a>
            </nav>
        </div>

    </aside>

    <!-- MAIN FEED -->
    <main class="feed">

        <?php if ($searchTerm !== ''): ?>

            <div class="search-results">
                <h3>
                    Search results for "<?php echo h($searchTerm); ?>"
                </h3>

                <?php if ($searchUsers && $searchUsers->num_rows > 0): ?>

                    <?php while ($person = $searchUsers->fetch_assoc()): ?>

                        <div class="search-user">

                            <?php if (!empty($person['picturepr'])): ?>
                                <img src="image/<?php echo ($person['picturepr']); ?>" alt="">
                            <?php else: ?>
                                <i class="fa-solid fa-circle-user" style="font-size:38px;color:#aaa;"></i>
                            <?php endif; ?>

                            <div>
                                <strong>
                                    <?php echo h($person['first_name'] . ' ' . $person['last_name']); ?>

                                    <?php if (!empty($person['verified'])): ?>
                                        <i class="fa-solid fa-circle-check verified"></i>
                                    <?php endif; ?>
                                </strong>
                                <small>Vlast member</small>
                            </div>

                            <a class="view-profile" href="other_users_pp.php?id=<?php echo (int)$person['id']; ?>">
                                View
                            </a>

                        </div>

                    <?php endwhile; ?>

                <?php else: ?>

                    <p style="font-size:11px;color:var(--vlast-muted);">
                        No users found matching your search.
                    </p>

                <?php endif; ?>
            </div>

        <?php endif; ?>

        <!-- STORIES -->
        <section class="stories">

            <div class="story">
                <div class="story-avatar">
                    <?php if (!empty($currentUser['picturepr'])): ?>
                        <img src="image/<?php echo ($currentUser['picturepr']); ?>" alt="">
                    <?php else: ?>
                        <i class="fa-solid fa-user" style="display:flex;align-items:center;justify-content:center;height:100%;"></i>
                    <?php endif; ?>
                </div>
                <span>Your story</span>
            </div>

            <?php while ($story = $storyUsers->fetch_assoc()): ?>

                <a class="story" href="other_users_pp.php?id=<?php echo (int)$story['id']; ?>">
                    <div class="story-avatar">
                        <?php if (!empty($story['picturepr'])): ?>
                            <img src="<?php echo h(profileImage($story['picturepr'])); ?>" alt="">
                        <?php else: ?>
                            <i class="fa-solid fa-user" style="display:flex;align-items:center;justify-content:center;height:100%;"></i>
                        <?php endif; ?>
                    </div>

                    <span><?php echo h($story['first_name']); ?></span>
                </a>

            <?php endwhile; ?>

        </section>

        <!-- CREATE POST -->
        <section class="create-post">

            <form method="POST" enctype="multipart/form-data">

                <div class="create-top">

                    <div class="create-avatar">
                        <?php if (!empty($currentUser['picturepr'])): ?>
                            <img src="<?php echo h(profileImage($currentUser['picturepr'])); ?>" alt="">
                        <?php else: ?>
                            <i class="fa-solid fa-user"></i>
                        <?php endif; ?>
                    </div>

                    <textarea
                        class="create-input"
                        name="text"
                        rows="2"
                        placeholder="What's happening on Vlast?"
                        style="resize:vertical;border:0;outline:0;"
                    ></textarea>

                </div>

                <div id="selectedImageName" style="font-size:10px;color:var(--vlast-muted);margin-top:8px;"></div>

                <div class="create-actions">

                    <label style="cursor:pointer;color:#299b65;font-size:12px;padding:8px;">
                        <i class="fa-solid fa-image"></i> Photo
                        <input
                            type="file"
                            name="file"
                            id="postImageInput"
                            accept="image/*"
                            hidden
                        >
                    </label>

                    <button type="submit" name="create_post">
                        <i class="fa-solid fa-paper-plane"></i> Publish
                    </button>

                </div>

            </form>

        </section>

        <?php if ($message !== ''): ?>
            <div class="card" style="font-size:11px;color:#d33;">
                <?php echo h($message); ?>
            </div>
        <?php endif; ?>

        <!-- POSTS -->
        <?php if ($posts->num_rows > 0): ?>

            <?php while ($post = $posts->fetch_assoc()): ?>

                            <?php
                    $postId = (int)$post['id'];
                    $postOwnerId = (int)$post['userID'];
                    $isLiked = (int)$post['is_liked'] > 0;
                ?>

                <article class="post" id="post-<?php echo $postId; ?>">

                    <div class="post-header">

                        <?php if (!empty($post['picturepr'])): ?>
                            <img src="image/<?php echo ($post['picturepr']); ?>" alt="">
                        <?php else: ?>
                            <i class="fa-solid fa-circle-user" style="font-size:43px;color:#aaa;"></i>
                        <?php endif; ?>

                        <div class="post-user">

                            <strong>
                               <?php 

                            //    $_GET['id'] = $profile;
                               ?>

                                    <a href="other_users_pp.php?id=<?php echo $postOwnerId?>"><?php echo h($post['first_name'] . ' ' . $post['last_name']); ?></a>

                                <?php if (!empty($post['verified'])): ?>
                                    <i class="fa-solid fa-circle-check verified"></i>
                                <?php elseif (empty($post['verified'])): ?>
                                <?php endif; ?>

                                <?php if (!empty($post['memes_admin'])): ?>
                                    <span class="admin-badge">ADMIN</span>
                                <?php endif; ?>
                            </strong>

                            <small>
                                <?php echo h(date('M j, Y • g:i A', strtotime($post['date']))); ?>
                            </small>

                        </div>

                        <?php if ($postOwnerId === $currentUserId): ?>
                            <form method="POST" class="delete-form"
                                  onsubmit="return confirm('Are you sure you want to delete this post?');">

                                <input type="hidden" name="post_id" value="<?php echo $postId; ?>">

                                <button type="submit" name="delete_post" title="Delete post">
                                    <i class="fa-solid fa-trash"></i>
                                </button>

                            </form>
                        <?php endif; ?>

                    </div>

                    <?php if (!empty($post['text'])): ?>
                        <div class="post-text"><?php echo nl2br(h($post['text'])); ?></div>
                    <?php endif; ?>

                    <?php if (!empty($post['picture'])): ?>
                        <img
                            class="post-image"
                            src="image/<?php echo ($post['picture']); ?>"
                            alt="couldn't load"
                            loading="lazy"
                        >
                    <?php endif; ?>

                    <div class="post-actions">

                        <!-- LIKE / UNLIKE -->
                        <form method="POST">

                            <input type="hidden" name="post_id" value="<?php echo $postId; ?>">

                            <button
                                type="submit"
                                name="toggle_like"
                                class="like-button <?php echo $isLiked ? 'liked' : ''; ?>"
                            >
                                <i class="<?php echo $isLiked ? 'fa-solid' : 'fa-regular'; ?> fa-heart"></i>

                                <span><?php echo (int)$post['like_count']; ?> Like</span>
                            </button>

                        </form>

                        <button type="button" class="comment-toggle">
                            <i class="fa-regular fa-comment"></i>
                            <span>0 Comment</span>
                        </button>

                        <button
                            type="button"
                            class="share-button"
                            data-share-url="<?php echo h('http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/home.php#post-' . $postId); ?>"
                            data-share-text="<?php echo h(mb_substr($post['text'] ?? '', 0, 150)); ?>"
                        >
                            <i class="fa-solid fa-share-nodes"></i>
                            <span>Share</span>
                        </button>

                    </div>

                    <!-- COMMENTS -->
                    <div class="comment-section">
                        <p style="font-size:10px;color:var(--vlast-muted);">
                            No comments yet. Be the first to comment.
                        </p>

                        <form method="POST" class="comment-form">
                            <input
                                type="text"
                                name="comment_text"
                                placeholder="Write a comment..."
                                maxlength="2000"
                                required
                            >
                            <button type="submit" name="add_comment">
                                Send
                            </button>
                        </form>
                    </div>

                </article>

            <?php endwhile; ?>

        <?php else: ?>

            <div class="empty-feed">
                <i class="fa-regular fa-newspaper"></i>
                <p>There are no posts to show right now.</p>
            </div>

        <?php endif; ?>

    </main>

    <!-- RIGHT SIDEBAR -->
    <aside class="right-sidebar">

        <div class="sidebar-card">

            <h3 class="sidebar-heading">
                People you may know
            </h3>

            <a href="explore_user.php" style="width:3px;">see all</a>

            <?php if ($suggestions->num_rows > 0): ?>

                <?php while ($person = $suggestions->fetch_assoc()): ?>

                    <div class="suggestion">

                        <?php if (!empty($person['picturepr'])): ?>
                            <img
                                src="image/<?php echo ($person['picturepr']); ?>"
                                alt="Profile picture"
                            >
                        <?php else: ?>
                            <i class="fa-solid fa-circle-user"
                               style="font-size:37px;color:#aaa;"></i>
                        <?php endif; ?>

                        <div class="suggestion-info">

                            <strong>
                                <?php echo h($person['first_name'] . ' ' . $person['last_name']); ?>

                                <?php if (!empty($person['verified'])): ?>
                                    <i class="fa-solid fa-circle-check verified"></i>
                                <?php endif; ?>
                            </strong>

                            <small>Vlast member</small>

                        </div>

                        <a href="other_users_pp.php?id=<?php echo (int)$person['id']; ?>">
                            View
                        </a>

                    </div>

                <?php endwhile; ?>

            <?php else: ?>

                <p style="font-size:10px;color:var(--vlast-muted);">
                    No suggestions available.
                </p>

            <?php endif; ?>

        </div>

        <div class="sidebar-card">

            <h3 class="sidebar-heading">About Vlast</h3>

            <p class="about-vlast">
                Vlast is a place to connect, share your thoughts,
                discover people and stay connected with your community.
            </p>

            <p class="about-vlast" style="margin-top:12px;">
                © <?php echo date('Y'); ?> Vlast. All rights reserved.
            </p>

        </div>

    </aside>

</div>

<!-- BOTTOM NAVIGATION -->
<nav class="bottom-nav">

    <a href="home.php" class="active">
        <i class="fa-solid fa-house"></i>
        <span>Home</span>
    </a>

    <a href="notification.php">
        <i class="fa-regular fa-bell"></i>
        <span>Notifications</span>
    </a>

    <a href="messages.php">
        <i class="fa-regular fa-message"></i>
        <span>Messages</span>
    </a>

    <a href="user_profile.php?id=<?php echo $currentUserId; ?>">
        <i class="fa-regular fa-user"></i>
        <span>Profile</span>
    </a>

    <a href="settings.php">
        <i class="fa-solid fa-gear"></i>
        <span>Settings</span>
    </a>

</nav>

<script>

/* MOBILE MENU */

const menuToggle = document.getElementById('menuToggle');
const mobileDrawer = document.getElementById('mobileDrawer');
const drawerOverlay = document.getElementById('drawerOverlay');

if (menuToggle && mobileDrawer && drawerOverlay) {

    menuToggle.addEventListener('click', function () {

        mobileDrawer.classList.toggle('open');
        drawerOverlay.classList.toggle('show');

    });

    drawerOverlay.addEventListener('click', function () {

        mobileDrawer.classList.remove('open');
        drawerOverlay.classList.remove('show');

    });

}


/* SHOW AND HIDE COMMENTS */

document.querySelectorAll('.comment-toggle').forEach(function(button) {

    button.addEventListener('click', function() {

        const post = button.closest('.post');

        if (!post) return;

        const commentSection = post.querySelector('.comment-section');

        if (commentSection) {

            commentSection.classList.toggle('show');

            if (commentSection.classList.contains('show')) {

                const commentInput = commentSection.querySelector('input[name="comment_text"]');

                if (commentInput) {
                    commentInput.focus();
                }

            }

        }

    });

});


/* SHARE POST */

document.querySelectorAll('.share-button').forEach(function(button) {

    button.addEventListener('click', async function() {

        const shareUrl = button.dataset.shareUrl;
        const shareText = button.dataset.shareText || 'Check out this post on Vlast';

        try {

            if (navigator.share) {

                await navigator.share({
                    title: 'Vlast',
                    text: shareText,
                    url: shareUrl
                });

            } else if (navigator.clipboard && window.isSecureContext) {

                await navigator.clipboard.writeText(shareUrl);

                alert('Post link copied successfully!');

            } else {

                const temporaryInput = document.createElement('textarea');

                temporaryInput.value = shareUrl;

                document.body.appendChild(temporaryInput);

                temporaryInput.select();

                document.execCommand('copy');

                temporaryInput.remove();

                alert('Post link copied successfully!');

            }

        } catch (error) {

            if (error.name !== 'AbortError') {
                alert('Unable to share this post. Please try again.');
            }

        }

    });

});


/* SHOW SELECTED IMAGE NAME */

const postImageInput = document.getElementById('postImageInput');
const selectedImageName = document.getElementById('selectedImageName');

if (postImageInput && selectedImageName) {

    postImageInput.addEventListener('change', function() {

        if (this.files.length > 0) {

            selectedImageName.textContent =
                'Selected image: ' + this.files[0].name;

        } else {

            selectedImageName.textContent = '';

        }

    });

}


/* PREVENT EMPTY COMMENT SUBMISSION */

document.querySelectorAll('.comment-form').forEach(function(form) {

    form.addEventListener('submit', function(event) {

        const input = form.querySelector('input[name="comment_text"]');

        if (!input || input.value.trim() === '') {

            event.preventDefault();

        }

    });

});

</script>

</body>
</html>

<?php

/* CLOSE DATABASE RESULTS AND CONNECTION */

if (isset($searchStmt)) {
    $searchStmt->close();
}

if (isset($suggestionsStmt)) {
    $suggestionsStmt->close();
}

if (isset($storyStmt)) {
    $storyStmt->close();
}

if (isset($postsStmt)) {
    $postsStmt->close();
}

$connection->close();

?>