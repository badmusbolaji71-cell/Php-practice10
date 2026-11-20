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

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

/* LIKE AND UNLIKE */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_like'])) {

    $postId = (int)($_POST['post_id'] ?? 0);

    if ($postId > 0) {

        $checkPost = $connection->prepare(
            "SELECT id FROM user_post WHERE id = ? LIMIT 1"
        );

        $checkPost->bind_param("i", $postId);
        $checkPost->execute();
        $postExists = $checkPost->get_result()->num_rows > 0;
        $checkPost->close();

        if ($postExists) {

            $checkLike = $connection->prepare(
                "SELECT post_id FROM likes
                 WHERE user_id = ? AND post_id = ?
                 LIMIT 1"
            );

            $checkLike->bind_param("ii", $userId, $postId);
            $checkLike->execute();

            $alreadyLiked = $checkLike->get_result()->num_rows > 0;
            $checkLike->close();

            if ($alreadyLiked) {

                $deleteLike = $connection->prepare(
                    "DELETE FROM likes
                     WHERE user_id = ? AND post_id = ?"
                );

                $deleteLike->bind_param("ii", $userId, $postId);
                $deleteLike->execute();
                $deleteLike->close();

            } else {

                $insertLike = $connection->prepare(
                    "INSERT INTO likes (user_id, post_id)
                     VALUES (?, ?)"
                );

                $insertLike->bind_param("ii", $userId, $postId);
                $insertLike->execute();
                $insertLike->close();

            }
        }
    }

    $returnPost = (int)($_POST['return_post'] ?? 0);

    if ($returnPost > 0) {
        header("Location: user_profile.php?view=" . $returnPost . "#posts");
    } else {
        header("Location: user_profile.php#posts");
    }

    exit;
}


/* ADD COMMENT */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_comment'])) {

    $postId = (int)($_POST['post_id'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');

    if ($postId > 0 && $comment !== '' && strlen($comment) <= 2000) {

        $checkPost = $connection->prepare(
            "SELECT id FROM user_post WHERE id = ? LIMIT 1"
        );

        $checkPost->bind_param("i", $postId);
        $checkPost->execute();
        $exists = $checkPost->get_result()->num_rows > 0;
        $checkPost->close();

        if ($exists) {

            $addComment = $connection->prepare(
                "INSERT INTO post_comments (post_id, user_id, comment)
                 VALUES (?, ?, ?)"
            );

            $addComment->bind_param("iis", $postId, $userId, $comment);
            $addComment->execute();
            $addComment->close();
        }
    }

    header("Location: user_profile.php?view=" . $postId . "#posts");
    exit;
}


/* DELETE OWN POST */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_post'])) {

    
if(isset($_POST['delete_post'])){
    $deletepost = mysqli_query($connection,"DELETE from user_post where post_id = '$postId'");
     $deletepost->bind_param("i", $postId);
                $deletepost->execute();
                $deletepost->close();



                $deleteLikes = $connection->prepare(
                    "DELETE FROM likes WHERE post_id = ?"
                );

                $deleteLikes->bind_param("i", $postId);
                $deleteLikes->execute();
                $deleteLikes->close();

        }
    
    }




/* GET USER */
$userQuery = $connection->prepare(
    "SELECT * FROM user_details WHERE id = ? LIMIT 1"
);

$userQuery->bind_param("i", $userId);
$userQuery->execute();

$userResult = $userQuery->get_result();
$user = $userResult->fetch_assoc();

$userQuery->close();

if (!$user) {
    die("User profile not found.");
}


/* PROFILE STATISTICS */
$postCountQuery = $connection->prepare(
    "SELECT COUNT(*) AS total
     FROM user_post
     WHERE uploader_id = ?"
);

$postCountQuery->bind_param("i", $userId);
$postCountQuery->execute();

$postCount = $postCountQuery->get_result()->fetch_assoc()['total'];

$postCountQuery->close();


$followerQuery = $connection->prepare(
    "SELECT COUNT(*) AS total
     FROM follow
     WHERE following_id = ?"
);

$followerQuery->bind_param("i", $userId);
$followerQuery->execute();

$followerCount = $followerQuery->get_result()->fetch_assoc()['total'];

$followerQuery->close();


$followingQuery = $connection->prepare(
    "SELECT COUNT(*) AS total
     FROM follow
     WHERE follower_id = ?"
);

$followingQuery->bind_param("i", $userId);
$followingQuery->execute();

$followingCount = $followingQuery->get_result()->fetch_assoc()['total'];

$followingQuery->close();


/* GET POSTS AND REAL LIKE COUNTS */
$postsQuery = $connection->prepare(
    "SELECT
        p.id,
        p.text,
        p.picture,
        p.date,

        (
            SELECT COUNT(*)
            FROM likes l
            WHERE l.post_id = p.id
        ) AS likes_count,

        (
            SELECT COUNT(*)
            FROM likes l2
            WHERE l2.post_id = p.id
            AND l2.user_id = ?
        ) AS is_liked

     FROM user_post p

     WHERE p.uploader_id = ?

     ORDER BY p.date DESC"
);

$postsQuery->bind_param("ii", $userId, $userId);
$postsQuery->execute();

$postsResult = $postsQuery->get_result();

$imagePosts = [];
$textPosts = [];

while ($post = $postsResult->fetch_assoc()) {

    if (!empty($post['picture'])) {
        $imagePosts[] = $post;
    } elseif (!empty(trim($post['text'] ?? ''))) {
        $textPosts[] = $post;
    }
}

$postsQuery->close();







$fullName = trim($user['first_name'] . " " . $user['last_name']);

$profileImage = !empty($user['picturepr'])
    ? "image/" . $user['picturepr']
    : "";

$coverImage = !empty($user['coverpic'])
    ? "image/" . $user['coverpic']
    : "";

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Vlast | Profile</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<link rel="preconnect" href="https://fonts.googleapis.com">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">

<style>

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

:root {
    --background: #f3f5f7;
    --surface: #ffffff;
    --accent: #171717;
    --text: #222222;
    --muted: #888888;
    --border: #eeeeee;
    --soft: #f1f2f4;
    --sidebar: #ffffff;
}

body {
    background: var(--background, #f3f5f7);
    color: var(--text, #222);
    font-family: 'Poppins', sans-serif;
    padding-bottom: 0;
    transition: background .2s, color .2s;
}

button, a {
    font-family: inherit;
}

/* HEADER */

header {
    height: 70px;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    z-index: 1000;
    background: var(--surface, white);
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 5%;
    box-shadow: 0 2px 15px rgba(0,0,0,.06);
    border-bottom: 1px solid var(--border, #eee);
}

.logo {
    font-family: 'Playfair Display', serif;
    font-size: 32px;
    color: var(--text, #111);
    text-decoration: none;
    letter-spacing: -1px;
}

.header-icons {
    display: flex;
    align-items: center;
    gap: 27px;
}

.header-icons a {
    color: var(--muted, #777);
    text-decoration: none;
    font-size: 18px;
    transition: .2s;
}

.header-icons a:hover,
.header-icons a.active {
    color: var(--accent, #111);
    transform: translateY(-2px);
}

/* PAGE */

.page {
    width: 100%;
    max-width: 1100px;
    margin: auto;
    padding: 100px 20px 60px;
}

/* PROFILE CARD */

.profile-card {
    background: var(--surface, white);
    border-radius: 22px;
    overflow: hidden;
    box-shadow: 0 3px 25px rgba(0,0,0,.05);
}

/* COVER */

.cover {
    width: 100%;
    height: 350px;
    position: relative;
    overflow: hidden;
    background: linear-gradient(135deg, #171717, #555, #999);
}

.cover img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.cover::after {
    content: "";
    position: absolute;
    left: 0;
    right: 0;
    bottom: 0;
    height: 130px;
    background: linear-gradient(transparent, rgba(0,0,0,.35));
    pointer-events: none;
}

.cover-empty {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    color: rgba(255,255,255,.7);
}

.cover-empty i {
    font-size: 48px;
    margin-bottom: 10px;
}

/* PROFILE CONTENT */

.profile-content {
    padding: 0 35px 40px;
    position: relative;
}

/* PROFILE PICTURE */

.profile-picture-wrapper {
    width: 150px;
    height: 150px;
    margin-top: -75px;
    position: relative;
    z-index: 5;
}

.profile-picture {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    background: var(--soft, #eee);
    border: 6px solid var(--surface, white);
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 5px 20px rgba(0,0,0,.12);
}

.profile-picture img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.profile-picture i {
    font-size: 48px;
    color: var(--muted, #999);
}

/* USER INFORMATION */

.profile-info {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 20px;
    margin-top: 15px;
}

.profile-name h1 {
    font-size: 27px;
    font-weight: 700;
    line-height: 1.3;
    word-break: break-word;
}

.username {
    color: var(--muted, #999);
    font-size: 12px;
    margin-top: 5px;
    overflow-wrap: anywhere;
}

.bio {
    margin-top: 15px;
    max-width: 650px;
    color: var(--muted, #777);
    font-size: 13px;
    line-height: 1.8;
    overflow-wrap: anywhere;
}

/* EDIT BUTTON */

.edit-btn {
    background: var(--accent, #111);
    color: white;
    padding: 12px 19px;
    border-radius: 11px;
    text-decoration: none;
    font-size: 12px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
    white-space: nowrap;
    transition: .2s;
}

.edit-btn:hover {
    opacity: .8;
    transform: translateY(-2px);
}

/* STATS */

.stats {
    display: flex;
    gap: 40px;
    margin-top: 27px;
    padding: 22px 0;
    border-top: 1px solid var(--border, #eee);
    border-bottom: 1px solid var(--border, #eee);
}

.stat {
    text-align: center;
    min-width: 65px;
}

.stat strong {
    display: block;
    font-size: 18px;
    font-weight: 700;
}

.stat span {
    font-size: 10px;
    color: var(--muted, #999);
    text-transform: uppercase;
    letter-spacing: .5px;
}

/* PROFILE TABS */

.profile-tabs {
    display: flex;
    border-bottom: 1px solid var(--border, #eee);
    margin-top: 10px;
}

.profile-tabs a {
    flex: 1;
    text-align: center;
    padding: 17px 8px;
    text-decoration: none;
    color: var(--muted, #888);
    font-size: 12px;
    font-weight: 500;
    transition: .2s;
}

.profile-tabs a.active {
    color: var(--accent, #111);
    border-bottom: 2px solid var(--accent, #111);
    font-weight: 700;
}

/* POST SECTION */

.post-section {
    margin-top: 30px;
    scroll-margin-top: 90px;
}

.section-heading {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 18px;
}

.section-heading h2 {
    font-size: 17px;
    font-weight: 700;
}

.section-heading span {
    color: var(--muted, #999);
    font-size: 11px;
}

/* IMAGE POSTS GRID */

.posts {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 9px;
}

.post-card {
    border: none;
    padding: 0;
    background: var(--soft, #eee);
    position: relative;
    aspect-ratio: 1 / 1;
    overflow: hidden;
    cursor: pointer;
    border-radius: 10px;
    text-align: left;
}

.post-card img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform .4s;
}

.post-card:hover img {
    transform: scale(1.06);
}

.post-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(transparent 55%, rgba(0,0,0,.65));
    display: flex;
    align-items: flex-end;
    padding: 12px;
    opacity: 0;
    transition: .25s;
}

.post-card:hover .post-overlay {
    opacity: 1;
}

.post-overlay-content {
    width: 100%;
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 11px;
    gap: 8px;
}

.post-overlay-content span {
    display: flex;
    align-items: center;
    gap: 5px;
}

/* EMPTY POSTS */

.empty-state {
    grid-column: 1 / -1;
    padding: 50px 15px;
    text-align: center;
    background: var(--soft, #f1f2f4);
    border-radius: 15px;
    color: var(--muted, #999);
}

.empty-state i {
    font-size: 35px;
    margin-bottom: 12px;
}

.empty-state p {
    font-size: 12px;
}

/* TEXT POSTS */

.text-posts {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 15px;
}

.text-post-card {
    background: var(--soft, #f1f2f4);
    border: 1px solid var(--border, #eee);
    border-radius: 15px;
    padding: 20px;
    cursor: pointer;
    transition: .25s;
    min-height: 155px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.text-post-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(0,0,0,.06);
}

.text-post-content {
    font-family: 'Playfair Display', serif;
    font-size: 17px;
    line-height: 1.7;
    color: var(--text, #222);
    overflow-wrap: anywhere;
    display: -webkit-box;
    -webkit-line-clamp: 4;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.text-post-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
    margin-top: 20px;
    color: var(--muted, #888);
    font-size: 10px;
}

.text-post-footer span {
    display: flex;
    gap: 6px;
    align-items: center;
}

/* POST MODAL */

.post-modal {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.78);
    z-index: 3000;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 25px;
    backdrop-filter: blur(5px);
}

.post-modal.show {
    display: flex;
}

.modal-box {
    background: var(--surface, white);
    width: 100%;
    max-width: 900px;
    max-height: 90vh;
    border-radius: 17px;
    overflow: hidden;
    display: grid;
    grid-template-columns: 1.2fr .8fr;
    position: relative;
    animation: modalIn .25s ease;
}

@keyframes modalIn {
    from {
        opacity: 0;
        transform: scale(.96);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

.modal-image {
    background: #111;
    min-height: 400px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-image img {
    width: 100%;
    height: 100%;
    max-height: 90vh;
    object-fit: contain;
}

.modal-image.hide {
    display: none;
}

.modal-details {
    display: flex;
    flex-direction: column;
    min-width: 0;
    padding: 25px;
    overflow-y: auto;
}

.modal-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-bottom: 17px;
    border-bottom: 1px solid var(--border, #eee);
}

.modal-author {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 0;
}

.modal-author img,
.modal-avatar {
    width: 39px;
    height: 39px;
    border-radius: 50%;
    object-fit: cover;
    background: var(--soft, #eee);
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-author strong {
    font-size: 12px;
    display: block;
}

.modal-author small {
    font-size: 10px;
    color: var(--muted, #999);
}

.modal-close {
    width: 35px;
    height: 35px;
    border: none;
    border-radius: 50%;
    background: var(--soft, #eee);
    color: var(--text, #222);
    cursor: pointer;
    flex-shrink: 0;
}

.modal-caption {
    padding: 23px 0;
    font-size: 13px;
    line-height: 1.9;
    white-space: pre-wrap;
    overflow-wrap: anywhere;
    flex: 1;
    color: var(--text, #222);
}

.modal-engagement {
    border-top: 1px solid var(--border, #eee);
    padding-top: 17px;
}

.like-form {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}

.like-button {
    border: none;
    border-radius: 10px;
    padding: 11px 16px;
    background: var(--soft, #eee);
    color: var(--text, #222);
    cursor: pointer;
    font-size: 12px;
    font-weight: 600;
    transition: .2s;
}

.like-button:hover {
    background: var(--accent, #111);
    color: white;
}

.like-button.liked {
    color: #e63946;
    background: rgba(230,57,70,.1);
}

.like-count {
    font-size: 12px;
    color: var(--muted, #888);
}

.modal-share {
    width: 100%;
    border: 1px solid var(--border, #eee);
    background: transparent;
    color: var(--text, #222);
    padding: 11px;
    border-radius: 10px;
    margin-top: 12px;
    cursor: pointer;
    font-size: 11px;
}

.modal-share:hover {
    background: var(--soft, #eee);
}

/* POST MENU AND COMMENTS */

.modal-actions {
    display: flex;
    align-items: center;
    gap: 8px;
}

.post-menu-wrap {
    position: relative;
}

.post-menu-button {
    width: 35px;
    height: 35px;
    border: 0;
    border-radius: 50%;
    background: var(--soft, #eee);
    color: var(--text, #222);
    cursor: pointer;
}

.post-menu-dropdown {
    display: none;
    position: absolute;
    top: 42px;
    right: 0;
    min-width: 155px;
    padding: 6px;
    border: 1px solid var(--border, #eee);
    border-radius: 10px;
    background: var(--surface, #fff);
    box-shadow: 0 8px 25px rgba(0,0,0,.15);
    z-index: 20;
}

.post-menu-dropdown.show {
    display: block;
}

.delete-post-button {
    width: 100%;
    border: 0;
    border-radius: 7px;
    padding: 10px;
    background: transparent;
    color: #e63946;
    text-align: left;
    cursor: pointer;
    font-size: 12px;
}

.delete-post-button:hover {
    background: rgba(230,57,70,.1);
}

.comments-area {
    margin-top: 18px;
    border-top: 1px solid var(--border, #eee);
    padding-top: 15px;
}

.comments-heading {
    font-size: 12px;
    font-weight: 700;
    margin-bottom: 12px;
}

.comments-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
    max-height: 220px;
    overflow-y: auto;
}

.comment-item {
    display: flex;
    align-items: flex-start;
    gap: 9px;
}

.comment-avatar {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    object-fit: cover;
    background: var(--soft, #eee);
    flex-shrink: 0;
}

.comment-avatar-placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--muted, #888);
}

.comment-content {
    min-width: 0;
    flex: 1;
    background: var(--soft, #f1f2f4);
    border-radius: 10px;
    padding: 8px 11px;
    overflow-wrap: anywhere;
}

.comment-content strong {
    display: block;
    font-size: 10px;
    margin-bottom: 3px;
}

.comment-content p {
    font-size: 11px;
    line-height: 1.6;
    white-space: pre-wrap;
}

.no-comments {
    color: var(--muted, #888);
    font-size: 11px;
    padding: 8px 0;
}

.comment-form {
    display: flex;
    gap: 8px;
    margin-top: 12px;
}

.comment-form input {
    flex: 1;
    min-width: 0;
    border: 1px solid var(--border, #eee);
    background: var(--background, #f3f5f7);
    color: var(--text, #222);
    border-radius: 9px;
    padding: 11px;
    outline: none;
    font: inherit;
    font-size: 11px;
}

.comment-form button {
    border: 0;
    border-radius: 9px;
    padding: 0 13px;
    background: var(--accent, #171717);
    color: #fff;
    cursor: pointer;
    font-size: 11px;
    font-weight: 600;
}

/* MOBILE NAV */

.mobile-nav {
    display: none;
}

/* TABLET */

@media(max-width: 800px) {

    .page {
        padding-left: 12px;
        padding-right: 12px;
    }

    .cover {
        height: 290px;
    }

    .profile-content {
        padding-left: 25px;
        padding-right: 25px;
    }

    .modal-box {
        grid-template-columns: 1fr .9fr;
    }

}

/* MOBILE */

@media(max-width: 600px) {

    body {
        padding-bottom: 75px;
    }

    header {
        height: 62px;
        padding: 0 17px;
    }

    .logo {
        font-size: 27px;
    }

    .header-icons {
        gap: 19px;
    }

    .header-icons a {
        font-size: 16px;
    }

    .header-icons a:nth-child(2),
    .header-icons a:nth-child(4) {
        display: none;
    }

    .page {
        padding: 75px 7px 25px;
    }

    .profile-card {
        border-radius: 15px;
    }

    .cover {
        height: 210px;
    }

    .profile-content {
        padding: 0 14px 25px;
    }

    .profile-picture-wrapper {
        width: 112px;
        height: 112px;
        margin-top: -56px;
    }

    .profile-picture {
        width: 112px;
        height: 112px;
        border-width: 5px;
    }

    .profile-picture i {
        font-size: 35px;
    }

    .profile-info {
        flex-direction: column;
        gap: 15px;
        margin-top: 13px;
    }

    .profile-name {
        width: 100%;
    }

    .profile-name h1 {
        font-size: 22px;
    }

    .bio {
        font-size: 12px;
        margin-top: 12px;
    }

    .edit-btn {
        width: 100%;
        justify-content: center;
        padding: 12px;
    }

    .stats {
        justify-content: space-around;
        gap: 8px;
        margin-top: 20px;
        padding: 17px 0;
    }

    .stat strong {
        font-size: 16px;
    }

    .stat span {
        font-size: 9px;
    }

    .profile-tabs a {
        padding: 15px 4px;
        font-size: 10px;
    }

    .profile-tabs a i {
        margin-right: 3px;
    }

    .post-section {
        margin-top: 24px;
    }

    .section-heading {
        margin-bottom: 13px;
    }

    .section-heading h2 {
        font-size: 15px;
    }

    .section-heading span {
        font-size: 10px;
    }

    .posts {
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 4px;
    }

    .post-card {
        border-radius: 5px;
    }

    .post-overlay {
        opacity: 1;
        padding: 6px;
        background: linear-gradient(transparent 65%, rgba(0,0,0,.55));
    }

    .post-overlay-content {
        font-size: 9px;
    }

    .text-posts {
        grid-template-columns: 1fr;
        gap: 10px;
    }

    .text-post-card {
        min-height: 125px;
        padding: 16px;
    }

    .text-post-content {
        font-size: 15px;
    }

    /* MODAL MOBILE */

    .post-modal {
        padding: 0;
        align-items: flex-end;
    }

    .modal-box {
        width: 100%;
        max-width: none;
        max-height: 94vh;
        height: auto;
        border-radius: 20px 20px 0 0;
        display: flex;
        flex-direction: column;
        overflow-y: auto;
    }

    .modal-image {
        min-height: 0;
        height: 42vh;
        flex-shrink: 0;
    }

    .modal-image img {
        max-height: 42vh;
        object-fit: contain;
    }

    .modal-image.hide {
        display: none;
    }

    .modal-details {
        padding: 17px;
        overflow: visible;
        min-height: 240px;
    }

    .modal-caption {
        padding: 18px 0;
        font-size: 12px;
        min-height: 70px;
    }

    .modal-top {
        padding-bottom: 13px;
    }

    /* BOTTOM NAV */

    .mobile-nav {
        display: flex;
        position: fixed;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 67px;
        background: var(--surface, white);
        border-top: 1px solid var(--border, #eee);
        box-shadow: 0 -4px 18px rgba(0,0,0,.06);
        align-items: center;
        justify-content: space-around;
        z-index: 1500;
        padding-bottom: env(safe-area-inset-bottom);
    }

    .mobile-nav a {
        width: 43px;
        height: 43px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 13px;
        color: var(--muted, #888);
        text-decoration: none;
        font-size: 18px;
        transition: .2s;
    }

    .mobile-nav a.active {
        background: var(--accent, #111);
        color: white;
    }

    .mobile-nav a.add {
        background: var(--accent, #111);
        color: white;
        width: 47px;
        height: 47px;
        border-radius: 15px;
        font-size: 19px;
    }

}

/* SMALL PHONES */

@media(max-width: 360px) {

    .cover {
        height: 180px;
    }

    .profile-content {
        padding-left: 11px;
        padding-right: 11px;
    }

    .profile-picture-wrapper {
        width: 100px;
        height: 100px;
        margin-top: -50px;
    }

    .profile-picture {
        width: 100px;
        height: 100px;
    }

    .header-icons {
        gap: 14px;
    }

    .text-post-content {
        font-size: 14px;
    }

}

</style>

<script>
window.VLAST_USER_ID = <?php echo (int)$userId; ?>;
</script>

<script src="theme.js"></script>

</head>

<body>

<header>

    <a href="home.php" class="logo">Vlast</a>

    <div class="header-icons">

        <a href="home.php" title="Home">
            <i class="fa-solid fa-house"></i>
        </a>

        <a href="messages.php" title="Messages">
            <i class="fa-solid fa-message"></i>
        </a>

        <a href="explore_user.php" title="Explore">
            <i class="fa-solid fa-magnifying-glass"></i>
        </a>

        <a href="notification.php" title="Notifications">
            <i class="fa-solid fa-bell"></i>
        </a>

        <a href="user_profile.php" class="active" title="Profile">
            <i class="fa-solid fa-user"></i>
        </a>

        <a href="settings.php" title="Settings">
            <i class="fa-solid fa-gear"></i>
        </a>

    </div>

</header>


<main class="page">

<section class="profile-card">

    <!-- COVER -->

    <div class="cover">

        <?php if ($coverImage): ?>

            <img src="<?php echo h($coverImage); ?>" alt="Cover photo">

        <?php else: ?>

            <div class="cover-empty">
                <i class="fa-regular fa-image"></i>
                <span>No cover photo</span>
            </div>

        <?php endif; ?>

    </div>


    <div class="profile-content">

        <!-- PROFILE PICTURE -->

        <div class="profile-picture-wrapper">

            <div class="profile-picture">

                <?php if ($profileImage): ?>

                    <img src="<?php echo h($profileImage); ?>" alt="Profile picture">

                <?php else: ?>

                    <i class="fa-solid fa-user"></i>

                <?php endif; ?>

            </div>

        </div>


        <!-- PROFILE INFORMATION -->
<?php 
if($user['verified'] == 1){
?>
  <div class="profile-info">

            <div class="profile-name">


                <h1><?php echo h($fullName); ?></h1><i class = "fa-solid fa-circle-check " style = "color:blue; height:20px;"></i>

                <div class="username">
                    <i class="fa-solid fa-link"></i>
                    vlast/<?php echo h($user['email']); ?>
                </div>

                <p class="bio">
                    <?php
                    echo !empty($user['bio'])
                        ? nl2br(h($user['bio']))
                        : "No bio yet.";
                    ?>
                </p>

            </div>

            <a href="user_edit.php" class="edit-btn">
                <i class="fa-solid fa-pen"></i>
                Edit Profile
            </a>

        </div>
<?php }elseif($user['verified'] == 0){?>
        <div class="profile-info">

            <div class="profile-name">


                <h1><?php echo h($fullName); ?></h1>

                <div class="username">
                    <i class="fa-solid fa-link"></i>
                    vlast/<?php echo h($user['email']); ?>
                </div>

                <p class="bio">
                    <?php
                    echo !empty($user['bio'])
                        ? nl2br(h($user['bio']))
                        : "No bio yet.";
                    ?>
                </p>

            </div>

            <a href="user_edit.php" class="edit-btn">
                <i class="fa-solid fa-pen"></i>
                Edit Profile
            </a>

        </div>
<?php }?>

        <!-- STATISTICS -->

        <div class="stats">

            <div class="stat">
                <strong><?php echo number_format($postCount); ?></strong>
                <span>Posts</span>
            </div>

            <div class="stat">
                <strong><?php echo number_format($followerCount); ?></strong>
                <span>Followers</span>
            </div>

            <div class="stat">
                <strong><?php echo number_format($followingCount); ?></strong>
                <span>Following</span>
            </div>

        </div>


        <!-- PROFILE TABS -->

        <div class="profile-tabs">

            <a href="#posts" class="active">
                <i class="fa-solid fa-grip"></i>
                Posts
            </a>

            <a href="#photos">
                <i class="fa-solid fa-images"></i>
                Photos
            </a>

            <a href="#texts">
                <i class="fa-solid fa-quote-left"></i>
                Texts
            </a>

        </div>


        <!-- IMAGE POSTS -->

        <section class="post-section" id="posts">

            <div class="section-heading">

                <h2>
                    <i class="fa-solid fa-images"></i>
                    Your Posts
                </h2>

                <span><?php echo count($imagePosts); ?> posts</span>

            </div>

            <div class="posts" id="photos">

                <?php if (count($imagePosts) > 0): ?>

                    <?php foreach ($imagePosts as $post): ?>

                        <button
                            type="button"
                            class="post-card open-post"

                            data-id="<?php echo (int)$post['id']; ?>"

                            data-image="<?php echo h('image/' . $post['picture']); ?>"

                            data-text="<?php echo h($post['text']); ?>"

                            data-likes="<?php echo (int)$post['likes_count']; ?>"

                            data-liked="<?php echo (int)$post['is_liked']; ?>"

                            data-date="<?php echo h($post['date']); ?>"
                            data-comments="<?php echo h(json_encode($commentsByPost[(int)$post['id']] ?? [], JSON_UNESCAPED_UNICODE)); ?>"
                        >

                            <img
                                src="<?php echo h('image/' . $post['picture']); ?>"
                                alt="Post image"
                                loading="lazy"
                            >

                            <div class="post-overlay">

                                <div class="post-overlay-content">

                                    <span>
                                        <i class="fa-solid fa-heart"></i>
                                        <?php echo number_format($post['likes_count']); ?>
                                    </span>

                                    <span>
                                        <i class="fa-solid fa-expand"></i>
                                        View
                                    </span>

                                </div>

                            </div>

                        </button>

                    <?php endforeach; ?>

                <?php else: ?>

                    <div class="empty-state">

                        <i class="fa-regular fa-images"></i>

                        <p>You haven't shared any image posts yet.</p>

                    </div>

                <?php endif; ?>

            </div>

        </section>


        <!-- TEXT-ONLY POSTS -->

        <section class="post-section" id="texts">

            <div class="section-heading">

                <h2>
                    <i class="fa-solid fa-quote-left"></i>
                    Your Texts
                </h2>

                <span><?php echo count($textPosts); ?> texts</span>

            </div>

            <div class="text-posts">

                <?php if (count($textPosts) > 0): ?>

                    <?php foreach ($textPosts as $post): ?>

                        <div
                            class="text-post-card open-post"

                            role="button"
                            tabindex="0"

                            data-id="<?php echo (int)$post['id']; ?>"

                            data-image=""

                            data-text="<?php echo h($post['text']); ?>"

                            data-likes="<?php echo (int)$post['likes_count']; ?>"

                            data-liked="<?php echo (int)$post['is_liked']; ?>"

                            data-date="<?php echo h($post['date']); ?>"
                            data-comments="<?php echo h(json_encode($commentsByPost[(int)$post['id']] ?? [], JSON_UNESCAPED_UNICODE)); ?>"
                        >

                            <div class="text-post-content">
                                <?php echo nl2br(h($post['text'])); ?>
                            </div>

                            <div class="text-post-footer">

                                <span>
                                    <i class="fa-solid fa-heart"></i>
                                    <?php echo number_format($post['likes_count']); ?> likes
                                </span>

                                <span>
                                    View post
                                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                </span>

                            </div>

                        </div>

                    <?php endforeach; ?>

                <?php else: ?>

                    <div class="empty-state">

                        <i class="fa-regular fa-file-lines"></i>

                        <p>You haven't shared any text posts yet.</p>

                    </div>

                <?php endif; ?>

            </div>

        </section>

    </div>

</section>

</main>


<!-- POST VIEWER MODAL -->

<div class="post-modal" id="postModal">

    <div class="modal-box">

        <div class="modal-image" id="modalImageBox">

            <img src="" id="modalImage" alt="Full post">

        </div>


        <div class="modal-details">

            <div class="modal-top">

                <div class="modal-author">

                    <?php if ($profileImage): ?>

                        <img src="<?php echo h($profileImage); ?>" alt="">

                    <?php else: ?>

                        <div class="modal-avatar">
                            <i class="fa-solid fa-user"></i>
                        </div>

                    <?php endif; ?>

                    <div>
                        <strong><?php echo h($fullName); ?></strong>
                        <small id="modalDate"></small>
                    </div>

                </div>

                <div class="modal-actions">
                    <div class="post-menu-wrap">
                        <button type="button" class="post-menu-button" id="postMenuButton" title="Post options">
                            <i class="fa-solid fa-ellipsis"></i>
                        </button>

                        <div class="post-menu-dropdown" id="postMenuDropdown">

                            <form method="POST" id="deletePostForm">

                                <input type="hidden" name="post_id" id="deletePostId">

                                <button type="submit" name="delete_post" class="delete-post-button">
                                    <i class="fa-regular fa-trash-can"></i> Delete post
                                </button>

                            </form>

                        </div>
                    </div>

                    <button type="button" class="modal-close" id="closeModal">
                        <i class="fa-solid fa-xmark"></i>
                    </button>

                </div>

            </div>


            <div class="modal-caption" id="modalText"></div>


            <div class="modal-engagement">

                <form method="POST" class="like-form" id="modalLikeForm">

                    <input type="hidden" name="post_id" id="likePostId">

                    <input type="hidden" name="return_post" id="returnPostId">

                    <button type="submit" name="toggle_like" class="like-button" id="modalLikeButton">

                        <i class="fa-solid fa-heart"></i>
                        <span id="likeButtonText">Like</span>

                    </button>

                    <span class="like-count" id="modalLikeCount"></span>

                </form>


                <button type="button" class="modal-share" id="sharePost">
                    <i class="fa-solid fa-share-nodes"></i>
                    Share Post
                </button>


                <div class="comments-area">

                    <div class="comments-heading">
                        Comments (<span id="commentCount">0</span>)
                    </div>

                    <div class="comments-list" id="commentsList">
                        <p class="no-comments">No comments yet. Be the first to comment.</p>
                    </div>

                    <form method="POST" class="comment-form">

                        <input type="hidden" name="post_id" id="commentPostId">

                        <input type="text" name="comment" maxlength="2000" placeholder="Write a comment..." required>

                        <button type="submit" name="add_comment">Send</button>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>


<!-- MOBILE NAVIGATION -->

<nav class="mobile-nav">

    <a href="home.php" title="Home">
        <i class="fa-solid fa-house"></i>
    </a>

    <a href="explore_user.php" title="Explore">
        <i class="fa-solid fa-magnifying-glass"></i>
    </a>

    <a href="post.php" class="add" title="Create post">
        <i class="fa-solid fa-plus"></i>
    </a>

    <a href="notification.php" title="Notifications">
        <i class="fa-solid fa-bell"></i>
    </a>

    <a href="user_profile.php" class="active" title="Profile">
        <i class="fa-solid fa-user"></i>
    </a>

</nav>


<script>

/* POST MODAL */

const postModal = document.getElementById("postModal");

const modalImage = document.getElementById("modalImage");

const modalImageBox = document.getElementById("modalImageBox");

const modalText = document.getElementById("modalText");

const modalDate = document.getElementById("modalDate");

const modalLikeCount = document.getElementById("modalLikeCount");

const modalLikeButton = document.getElementById("modalLikeButton");

const likeButtonText = document.getElementById("likeButtonText");

const likePostId = document.getElementById("likePostId");

const returnPostId = document.getElementById("returnPostId");

let currentPostId = 0;

const commentsList = document.getElementById("commentsList");
const commentCount = document.getElementById("commentCount");
const commentPostId = document.getElementById("commentPostId");
const deletePostId = document.getElementById("deletePostId");
const postMenuButton = document.getElementById("postMenuButton");
const postMenuDropdown = document.getElementById("postMenuDropdown");


/* OPEN POST */

function openPost(post) {

    const image = post.dataset.image || "";

    const text = post.dataset.text || "";

    const id = post.dataset.id;

    const likes = parseInt(post.dataset.likes || "0");

    const liked = parseInt(post.dataset.liked || "0");

    currentPostId = id;

    if (image) {

        modalImageBox.classList.remove("hide");

        modalImage.src = image;

    } else {

        modalImageBox.classList.add("hide");

        modalImage.src = "";

    }

    modalText.textContent = text;

    modalDate.textContent = post.dataset.date || "";

    modalLikeCount.textContent = likes + (likes === 1 ? " like" : " likes");

    likePostId.value = id;

    returnPostId.value = id;
    commentPostId.value = id;
   

    let comments = [];

    try {
        comments = JSON.parse(post.dataset.comments || "[]");
    } catch (error) {
        comments = [];
    }

    commentCount.textContent = comments.length;
    commentsList.innerHTML = "";

    if (comments.length === 0) {

        commentsList.innerHTML = '<p class="no-comments">No comments yet. Be the first to comment.</p>';

    } else {

        comments.forEach(comment => {

            const item = document.createElement("div");
            item.className = "comment-item";

            if (comment.picture) {

                const avatar = document.createElement("img");
                avatar.className = "comment-avatar";
                avatar.src = "image/" + comment.picture;
                avatar.alt = "";
                item.appendChild(avatar);

            } else {

                const avatar = document.createElement("div");
                avatar.className = "comment-avatar comment-avatar-placeholder";
                avatar.innerHTML = '<i class="fa-solid fa-user"></i>';
                item.appendChild(avatar);

            }

            const content = document.createElement("div");
            content.className = "comment-content";

            const author = document.createElement("strong");
            author.textContent = comment.name || "Vlast user";

            const message = document.createElement("p");
            message.textContent = comment.comment || "";

            content.appendChild(author);
            content.appendChild(message);

            item.appendChild(content);
            commentsList.appendChild(item);

        });

    }

    postMenuDropdown.classList.remove("show");

    if (liked) {

        modalLikeButton.classList.add("liked");

        likeButtonText.textContent = "";

    } else {

        modalLikeButton.classList.remove("liked");

        likeButtonText.textContent = "";

    }

    postModal.classList.add("show");

    document.body.style.overflow = "hidden";

}


/* OPEN BUTTONS AND TEXT CARDS */

document.querySelectorAll(".open-post").forEach(post => {

    post.addEventListener("click", () => {
        openPost(post);
    });

    post.addEventListener("keydown", event => {

        if (event.key === "Enter" || event.key === " ") {

            event.preventDefault();

            openPost(post);

        }

    });

});


/* CLOSE MODAL */

function closePostModal() {

    postModal.classList.remove("show");

    document.body.style.overflow = "";

}

document.getElementById("closeModal").addEventListener("click", closePostModal);

postMenuButton.addEventListener("click", event => {
    event.stopPropagation();
    postMenuDropdown.classList.toggle("show");
});

document.addEventListener("click", event => {

    if (!event.target.closest(".post-menu-wrap")) {
        postMenuDropdown.classList.remove("show");
    }

});

postModal.addEventListener("click", event => {

    if (event.target === postModal) {
        closePostModal();
    }

});

document.addEventListener("keydown", event => {

    if (event.key === "Escape") {
        closePostModal();
    }

});


/* SHARE POST */

document.getElementById("sharePost").addEventListener("click", async () => {

    const url = window.location.origin +
        window.location.pathname +
        "?view=" + currentPostId;

    try {

        if (navigator.share) {

            await navigator.share({
                title: "Vlast post",
                text: "Check out this post on Vlast",
                url: url
            });

        } else if (navigator.clipboard && window.isSecureContext) {

            await navigator.clipboard.writeText(url);
            alert("Post link copied!");

        } else {

            const temp = document.createElement("textarea");
            temp.value = url;
            document.body.appendChild(temp);
            temp.select();
            document.execCommand("copy");
            temp.remove();
            alert("Post link copied!");

        }

    } catch (error) {

        if (error.name !== "AbortError") {
            alert("Unable to share this post.");
        }

    }

});


/* REOPEN POST AFTER LIKING OR UNLIKING */

const urlParams = new URLSearchParams(window.location.search);

const requestedPost = urlParams.get("view");

if (requestedPost) {

    const postToOpen = document.querySelector(
        '.open-post[data-id="' + requestedPost + '"]'
    );

    if (postToOpen) {
        openPost(postToOpen);
    }

}


/* PROFILE TAB ACTIVE STATE */

document.querySelectorAll(".profile-tabs a").forEach(tab => {

    tab.addEventListener("click", () => {

        document.querySelectorAll(".profile-tabs a").forEach(item => {
            item.classList.remove("active");
        });

        tab.classList.add("active");

    });

});

</script>

</body>
</html>

<?php
$connection->close();
?>