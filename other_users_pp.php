<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "social_media";

$connection = new mysqli($servername, $username, $password, $dbname);

if ($connection->connect_error) {
    die("Database connection failed: " . $connection->connect_error);
}

$connection->set_charset("utf8mb4");

$me = isset($_SESSION['id']) ? (int)$_SESSION['id'] : 0;
$profileId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($me <= 0) {
    die("Please log in first.");
}

if ($profileId <= 0) {
    die("Invalid profile.");
}

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

/* GET PROFILE */

$profileQuery = $connection->prepare(
    "SELECT * FROM user_details WHERE id = ? LIMIT 1"
);

$profileQuery->bind_param("i", $profileId);
$profileQuery->execute();

$profileResult = $profileQuery->get_result();

if ($profileResult->num_rows === 0) {
    die("User not found.");
}

$row = $profileResult->fetch_assoc();
$profileQuery->close();

$fullName = trim($row['first_name'] . " " . $row['last_name']);


/* FOLLOW AND UNFOLLOW */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['follow'])) {

    if ($me === $profileId) {
        die("You cannot follow yourself.");
    }

    try {

        $connection->begin_transaction();

        $check = $connection->prepare(
            "SELECT 1 FROM follow
             WHERE follower_id = ? AND following_id = ?
             LIMIT 1"
        );

        $check->bind_param("ii", $me, $profileId);
        $check->execute();

        $alreadyFollowing = $check->get_result()->num_rows > 0;
        $check->close();

        if ($alreadyFollowing) {

            $updateFollow = $connection->prepare(
                "DELETE FROM follow
                 WHERE follower_id = ? AND following_id = ?"
            );

        } else {

            $updateFollow = $connection->prepare(
                "INSERT INTO follow (follower_id, following_id)
                 VALUES (?, ?)"
            );

        }

        $updateFollow->bind_param("ii", $me, $profileId);
        $updateFollow->execute();
        $updateFollow->close();

        $connection->commit();

        header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $profileId);
        exit;

    } catch (mysqli_sql_exception $error) {

        $connection->rollback();

        die("Follow error: " . e($error->getMessage()));
    }
}


/* CHECK FOLLOW STATUS */

$followCheck = $connection->prepare(
    "SELECT 1 FROM follow
     WHERE follower_id = ? AND following_id = ?
     LIMIT 1"
);

$followCheck->bind_param("ii", $me, $profileId);
$followCheck->execute();

$isFollowing = $followCheck->get_result()->num_rows > 0;
$followCheck->close();


/* FOLLOWER COUNT */

$followerQuery = $connection->prepare(
    "SELECT COUNT(*) AS total
     FROM follow
     WHERE following_id = ?"
);

$followerQuery->bind_param("i", $profileId);
$followerQuery->execute();

$followerCount = $followerQuery->get_result()->fetch_assoc()['total'];
$followerQuery->close();


/* FOLLOWING COUNT */

$followingQuery = $connection->prepare(
    "SELECT COUNT(*) AS total
     FROM follow
     WHERE follower_id = ?"
);

$followingQuery->bind_param("i", $profileId);
$followingQuery->execute();

$followingCount = $followingQuery->get_result()->fetch_assoc()['total'];
$followingQuery->close();


/* GET USER POSTS */

$postsQuery = $connection->prepare(
    "SELECT * FROM user_post
     WHERE uploader_id = ?
     ORDER BY date DESC"
);

$postsQuery->bind_param("i", $profileId);
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

$postCount = count($imagePosts) + count($textPosts);
$postsQuery->close();
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

body {
    background: #f3f5f7;
    color: #222;
    font-family: 'Poppins', sans-serif;
}

/* HEADER */

header {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 70px;
    background: white;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 5%;
    box-shadow: 0 2px 15px rgba(0,0,0,.07);
    z-index: 1000;
}

.logo {
    font-family: 'Playfair Display', serif;
    font-size: 32px;
    color: #111;
    text-decoration: none;
}

.header-icons {
    display: flex;
    align-items: center;
    gap: 25px;
}

.header-icons a {
    color: #666;
    font-size: 19px;
    text-decoration: none;
}

.header-icons a:hover {
    color: #111;
}

/* PAGE */

.page {
    width: 100%;
    max-width: 1100px;
    margin: auto;
    padding: 100px 20px 60px;
}

/* PROFILE */

.profile-card {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 3px 20px rgba(0,0,0,.05);
}

.cover {
    width: 100%;
    height: 340px;
    background: linear-gradient(135deg,#111,#555);
    overflow: hidden;
}

.cover img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.cover-empty {
    height: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
    flex-direction: column;
    color: #ddd;
    gap: 10px;
}

.cover-empty i {
    font-size: 45px;
}

.profile-content {
    padding: 0 35px 35px;
}

.profile-picture-wrapper {
    width: 150px;
    height: 150px;
    margin-top: -75px;
    position: relative;
}

.profile-picture {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    overflow: hidden;
    border: 6px solid white;
    background: #eee;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 15px rgba(0,0,0,.12);
}

.profile-picture img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.profile-picture i {
    font-size: 45px;
    color: #999;
}

/* PROFILE INFORMATION */

.profile-info {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 20px;
    margin-top: 15px;
}

.profile-name h1 {
    font-size: 27px;
    font-weight: 700;
}

.username {
    color: #999;
    font-size: 12px;
    margin-top: 4px;
}

.bio {
    margin-top: 13px;
    color: #555;
    font-size: 13px;
    line-height: 1.8;
    max-width: 650px;
}

/* FOLLOW BUTTONS */

.profile-actions {
    display: flex;
    gap: 10px;
    align-items: center;
}

.profile-actions form {
    margin: 0;
}

.follow-btn,
.message-btn {
    border: none;
    padding: 12px 20px;
    border-radius: 9px;
    font-family: 'Poppins', sans-serif;
    font-size: 12px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
    justify-content: center;
    text-decoration: none;
    cursor: pointer;
    white-space: nowrap;
}

.follow-btn {
    background: #111;
    color: white;
}

.follow-btn.unfollow {
    background: #eee;
    color: #111;
    border: 1px solid #ddd;
}

.message-btn {
    background: white;
    color: #111;
    border: 1px solid #ddd;
}

/* PROFILE STATS */

.stats {
    display: flex;
    gap: 40px;
    margin-top: 25px;
    padding: 20px 0;
    border-top: 1px solid #eee;
}

.stat strong {
    display: block;
    font-size: 18px;
}

.stat span {
    font-size: 10px;
    color: #999;
    text-transform: uppercase;
}

/* TABS */

.profile-tabs {
    display: flex;
    border-top: 1px solid #eee;
    border-bottom: 1px solid #eee;
}

.profile-tabs a {
    flex: 1;
    text-align: center;
    padding: 17px 10px;
    text-decoration: none;
    color: #888;
    font-size: 12px;
    font-weight: 600;
}

.profile-tabs a.active {
    color: #111;
    border-bottom: 2px solid #111;
}

/* SECTION HEADINGS */

.section-heading {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 35px 0 18px;
}

.section-heading h2 {
    font-size: 19px;
    font-weight: 700;
}

.section-heading span {
    font-size: 12px;
    color: #999;
}

/* IMAGE POSTS */

.posts-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 15px;
}

.post-card {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 3px 15px rgba(0,0,0,.05);
    cursor: pointer;
    transition: .25s;
    min-width: 0;
}

.post-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 25px rgba(0,0,0,.09);
}

.post-image {
    width: 100%;
    aspect-ratio: 1 / 1;
    overflow: hidden;
    background: #eee;
}

.post-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: .3s;
}

.post-card:hover .post-image img {
    transform: scale(1.04);
}

.post-details {
    padding: 14px;
}

.post-author {
    display: flex;
    align-items: center;
    gap: 9px;
    margin-bottom: 12px;
}

.post-author img,
.avatar-empty {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    object-fit: cover;
    background: #eee;
    flex-shrink: 0;
}

.avatar-empty {
    display: flex;
    align-items: center;
    justify-content: center;
    color: #999;
}

.post-author strong {
    font-size: 11px;
    display: block;
}

.post-author small {
    display: block;
    color: #999;
    font-size: 9px;
}

.post-caption {
    color: #555;
    font-size: 12px;
    line-height: 1.7;
    overflow-wrap: anywhere;
    white-space: pre-wrap;
}

.post-engagement {
    display: flex;
    justify-content: space-between;
    border-top: 1px solid #eee;
    padding-top: 12px;
    margin-top: 13px;
    color: #777;
    font-size: 11px;
}

.post-engagement span {
    display: flex;
    align-items: center;
    gap: 6px;
}

/* TEXT POSTS */

.text-section {
    margin-top: 45px;
}

.text-posts {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px;
}

.text-card {
    background: white;
    padding: 23px;
    border-radius: 16px;
    border: 1px solid #eee;
    box-shadow: 0 4px 15px rgba(0,0,0,.035);
    cursor: pointer;
    transition: .25s;
    position: relative;
    overflow: hidden;
}

.text-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 9px 25px rgba(0,0,0,.08);
}

.text-card::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    width: 5px;
    height: 100%;
    background: #222;
}

.text-card:nth-child(3n+2)::before {
    background: #b28a56;
}

.text-card:nth-child(3n+3)::before {
    background: #6b8298;
}

.text-card-content {
    font-family: 'Playfair Display', serif;
    font-size: 19px;
    line-height: 1.7;
    color: #292929;
    overflow-wrap: anywhere;
    white-space: pre-wrap;
}

.text-card-bottom {
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-top: 1px solid #eee;
    padding-top: 14px;
    margin-top: 20px;
    color: #999;
    font-size: 10px;
}

.text-card-bottom span {
    display: flex;
    align-items: center;
    gap: 6px;
}

/* EMPTY STATE */

.empty-state {
    grid-column: 1 / -1;
    background: white;
    border: 1px dashed #ddd;
    border-radius: 15px;
    padding: 45px 15px;
    text-align: center;
    color: #999;
}

.empty-state i {
    font-size: 35px;
    margin-bottom: 12px;
}

.empty-state h3 {
    font-size: 15px;
    color: #444;
    margin-bottom: 5px;
}

.empty-state p {
    font-size: 11px;
}

/* FULL POST VIEWER */

.post-modal {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.82);
    z-index: 2000;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.post-modal.show {
    display: flex;
}

.modal-content {
    width: 100%;
    max-width: 850px;
    max-height: 90vh;
    background: white;
    border-radius: 17px;
    overflow: hidden;
    display: grid;
    grid-template-columns: 1.2fr .8fr;
    position: relative;
}

.modal-image-area {
    background: #111;
    min-height: 350px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-image-area img {
    width: 100%;
    max-height: 80vh;
    object-fit: contain;
}

.modal-info {
    padding: 25px;
    overflow-y: auto;
}

.modal-close {
    position: absolute;
    top: 12px;
    right: 12px;
    width: 35px;
    height: 35px;
    border: none;
    background: white;
    color: #111;
    border-radius: 50%;
    font-size: 17px;
    cursor: pointer;
    z-index: 3;
}

.modal-info .post-author {
    padding-bottom: 15px;
    border-bottom: 1px solid #eee;
}

.modal-full-text {
    font-size: 14px;
    line-height: 1.9;
    white-space: pre-wrap;
    overflow-wrap: anywhere;
    margin-top: 18px;
    color: #444;
}

.modal-engagement {
    display: flex;
    justify-content: space-around;
    border-top: 1px solid #eee;
    border-bottom: 1px solid #eee;
    padding: 16px 0;
    margin-top: 25px;
    color: #777;
    font-size: 12px;
}

.modal-engagement span {
    display: flex;
    gap: 7px;
    align-items: center;
}

/* MOBILE NAV */

.mobile-nav {
    display: none;
}

/* TABLET */

@media(max-width:750px) {

    .page {
        padding: 90px 12px 40px;
    }

    .cover {
        height: 280px;
    }

    .profile-content {
        padding: 0 22px 25px;
    }

    .posts-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

/* MOBILE */

@media(max-width:550px) {

    body {
        padding-bottom: 70px;
    }

    header {
        height: 62px;
        padding: 0 16px;
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

    .header-icons a:nth-child(2),
    .header-icons a:nth-child(4) {
        display: none;
    }

    .page {
        padding: 75px 8px 25px;
    }

    .profile-card {
        border-radius: 14px;
    }

    .cover {
        height: 210px;
    }

    .profile-content {
        padding: 0 15px 22px;
    }

    .profile-picture-wrapper {
        width: 115px;
        height: 115px;
        margin-top: -58px;
    }

    .profile-picture {
        width: 115px;
        height: 115px;
        border-width: 5px;
    }

    .profile-info {
        flex-direction: column;
        gap: 15px;
    }

    .profile-name h1 {
        font-size: 22px;
    }

    .profile-actions {
        width: 100%;
    }

    .profile-actions form {
        flex: 1;
    }

    .follow-btn,
    .message-btn {
        width: 100%;
        padding: 12px 10px;
    }

    .stats {
        justify-content: space-around;
        gap: 10px;
    }

    .profile-tabs a {
        font-size: 10px;
        padding: 14px 5px;
    }

    .posts-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 9px;
    }

    .post-details {
        padding: 10px;
    }

    .post-caption {
        font-size: 11px;
    }

    .text-posts {
        grid-template-columns: 1fr;
        gap: 12px;
    }

    .text-card {
        padding: 19px;
    }

    .text-card-content {
        font-size: 17px;
    }

    .post-engagement {
        font-size: 9px;
    }

    .modal-content {
        display: flex;
        flex-direction: column;
        max-height: 90vh;
        overflow-y: auto;
    }

    .modal-image-area {
        min-height: 220px;
        max-height: 45vh;
    }

    .modal-image-area img {
        max-height: 45vh;
    }

    .modal-info {
        padding: 18px;
        overflow: visible;
    }

    .mobile-nav {
        position: fixed;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 63px;
        background: white;
        border-top: 1px solid #eee;
        display: flex;
        justify-content: space-around;
        align-items: center;
        z-index: 1000;
    }

    .mobile-nav a {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #777;
        text-decoration: none;
    }

    .mobile-nav .add {
        background: #111;
        color: white;
    }
}

</style>
<script>
    window.VLAST_USER_ID = <?php echo (int)$_SESSION['id']; ?>;
</script>
<script src="theme.js"></script>
</head>

<body>

<header>

    <a href="home.php" class="logo">Vlast</a>

    <div class="header-icons">

        <a href="home.php">
            <i class="fa-solid fa-house"></i>
        </a>

        <a href="messages.php">
            <i class="fa-solid fa-message"></i>
        </a>

        <a href="explore_user.php">
            <i class="fa-solid fa-magnifying-glass"></i>
        </a>

        <a href="notification.php">
            <i class="fa-solid fa-bell"></i>
        </a>

        <a href="user_profile.php">
            <i class="fa-solid fa-user"></i>
        </a>

    </div>

</header>

<main class="page">

<section class="profile-card">

    <div class="cover">

        <?php if (!empty($row['coverpic'])): ?>

            <img src="image/<?php echo e($row['coverpic']); ?>" alt="Cover photo">

        <?php else: ?>

            <div class="cover-empty">
                <i class="fa-regular fa-image"></i>
                <span>No cover photo</span>
            </div>

        <?php endif; ?>

    </div>

    <div class="profile-content">

        <div class="profile-picture-wrapper">

            <div class="profile-picture">

                <?php if (!empty($row['picturepr'])): ?>

                    <img src="image/<?php echo e($row['picturepr']); ?>" alt="Profile picture">

                <?php else: ?>

                    <i class="fa-solid fa-user"></i>

                <?php endif; ?>

            </div>

        </div>

        <div class="profile-info">
            <?php 
            if($row['verified'] == 1){

            
            ?>
      <div class="profile-name">

                <h1><?php echo e($fullName); ?> <i class = "fa-solid fa-circle-check " style = "color:blue; height:20px;"></i></h1>

                <div class="username">
                    <i class="fa-solid fa-link"></i>
                    vlast/<?php echo e($row['email']); ?>
                </div>

                <p class="bio">
                    <?php
                    echo !empty($row['bio'])
                        ? nl2br(e($row['bio']))
                        : "No bio yet.";
                    ?>
                </p>

            </div>
        

    <?php }elseif($row['verified'] == 0){?>
            <div class="profile-name">

                <h1><?php echo e($fullName); ?></h1>

                <div class="username">
                    <i class="fa-solid fa-link"></i>
                    vlast/<?php echo e($row['email']); ?>
                </div>

                <p class="bio">
                    <?php
                    echo !empty($row['bio'])
                        ? nl2br(e($row['bio']))
                        : "No bio yet.";
                    ?>
                </p>
<?php }?>
            </div>

            <div class="profile-actions">

                <?php if ($me !== $profileId): ?>

                    <form method="POST" action="<?php echo e($_SERVER['PHP_SELF']); ?>?id=<?php echo $profileId; ?>">

                        <button
                            type="submit"
                            name="follow"
                            value="1"
                            class="follow-btn <?php echo $isFollowing ? 'unfollow' : ''; ?>"
                        >

                            <i class="fa-solid <?php echo $isFollowing ? 'fa-user-minus' : 'fa-user-plus'; ?>"></i>

                            <?php echo $isFollowing ? 'Unfollow' : 'Follow'; ?>

                        </button>

                    </form>

                    <?php if ($isFollowing): ?>

                        <a href="messages.php?id=<?php echo $profileId; ?>" class="message-btn">
                            <i class="fa-solid fa-message"></i>
                            Message
                        </a>

                    <?php endif; ?>

                <?php else: ?>

                    <a href="edit_profile.php" class="message-btn">
                        <i class="fa-solid fa-pen"></i>
                        Edit Profile
                    </a>

                <?php endif; ?>

            </div>

        </div>

        <div class="stats">

            <div class="stat">
                <strong><?php echo $postCount; ?></strong>
                <span>Posts</span>
            </div>

            <div class="stat">
                <strong><?php echo (int)$followerCount; ?></strong>
                <span>Followers</span>
            </div>

            <div class="stat">
                <strong><?php echo (int)$followingCount; ?></strong>
                <span>Following</span>
            </div>

        </div>

        <div class="profile-tabs">

            <a href="#posts-section" class="active">
                <i class="fa-solid fa-images"></i>
                Posts
            </a>

            <a href="#texts-section">
                <i class="fa-solid fa-align-left"></i>
                Texts
            </a>

        </div>

    </div>

</section>

<!-- IMAGE POSTS -->

<section id="posts-section">

    <div class="section-heading">

        <h2>
            <i class="fa-solid fa-images"></i>
            Posts
        </h2>

        <span><?php echo count($imagePosts); ?> image posts</span>

    </div>

    <div class="posts-grid">

        <?php if (count($imagePosts) > 0): ?>

            <?php foreach ($imagePosts as $post): ?>

                <article
                    class="post-card open-post"
                    data-image="<?php echo e('image/' . $post['picture']); ?>"
                    data-text="<?php echo e($post['text'] ?? ''); ?>"
                >

                    <div class="post-image">

                        <img
                            src="image/<?php echo e($post['picture']); ?>"
                            alt="Post image"
                            loading="lazy"
                        >

                    </div>

                    <div class="post-details">

                        <div class="post-author">

                            <?php if (!empty($row['picturepr'])): ?>

                                <img src="image/<?php echo e($row['picturepr']); ?>" alt="">

                            <?php else: ?>

                                <div class="avatar-empty">
                                    <i class="fa-solid fa-user"></i>
                                </div>

                            <?php endif; ?>

                            <div>
                                <strong><?php echo e($fullName); ?></strong>
                                <small><?php echo e($post['date'] ?? ''); ?></small>
                            </div>

                        </div>

                        <?php if (!empty(trim($post['text'] ?? ''))): ?>

                            <p class="post-caption">
                                <?php echo e($post['text']); ?>
                            </p>

                        <?php else: ?>

                            <p class="post-caption" style="color:#aaa;">
                                Photo post
                            </p>

                        <?php endif; ?>

                        <div class="post-engagement">

                            <span><i class="fa-regular fa-heart"></i> Like</span>

                            <span><i class="fa-regular fa-comment"></i> Comment</span>

                            <span><i class="fa-solid fa-share"></i> Share</span>

                        </div>

                    </div>

                </article>

            <?php endforeach; ?>

        <?php else: ?>

            <div class="empty-state">

                <i class="fa-regular fa-images"></i>

                <h3>No image posts yet</h3>

                <p>Image posts from this user will appear here.</p>

            </div>

        <?php endif; ?>

    </div>

</section>

<!-- TEXT ONLY POSTS -->

<section class="text-section" id="texts-section">

    <div class="section-heading">

        <h2>
            <i class="fa-solid fa-pen-nib"></i>
            Texts
        </h2>

        <span><?php echo count($textPosts); ?> text posts</span>

    </div>

    <div class="text-posts">

        <?php if (count($textPosts) > 0): ?>

            <?php foreach ($textPosts as $post): ?>

                <article
                    class="text-card open-post"
                    data-image=""
                    data-text="<?php echo e($post['text']); ?>"
                >

                    <div class="post-author">

                        <?php if (!empty($row['picturepr'])): ?>

                            <img src="image/<?php echo e($row['picturepr']); ?>" alt="">

                        <?php else: ?>

                            <div class="avatar-empty">
                                <i class="fa-solid fa-user"></i>
                            </div>

                        <?php endif; ?>

                        <div>
                            <strong><?php echo e($fullName); ?></strong>
                            <small><?php echo e($post['date'] ?? ''); ?></small>
                        </div>

                    </div>

                    <div class="text-card-content">
                        <?php echo e($post['text']); ?>
                    </div>

                    <div class="text-card-bottom">

                        <span>
                            <i class="fa-regular fa-heart"></i>
                            Like
                        </span>

                        <span>
                            <i class="fa-regular fa-comment"></i>
                            Comment
                        </span>

                        <span>
                            <i class="fa-solid fa-share"></i>
                            Share
                        </span>

                    </div>

                </article>

            <?php endforeach; ?>

        <?php else: ?>

            <div class="empty-state">

                <i class="fa-solid fa-pen-nib"></i>

                <h3>No text posts yet</h3>

                <p>Text-only posts from this user will appear here.</p>

            </div>

        <?php endif; ?>

    </div>

</section>

</main>

<!-- FULL POST VIEWER -->

<div class="post-modal" id="postModal">

    <div class="modal-content">

        <button class="modal-close" id="closePost">
            <i class="fa-solid fa-xmark"></i>
        </button>

        <div class="modal-image-area" id="modalImageArea">

            <img src="" id="modalImage" alt="Full post">

        </div>

        <div class="modal-info">

            <div class="post-author">

                <?php if (!empty($row['picturepr'])): ?>

                    <img src="image/<?php echo e($row['picturepr']); ?>" alt="">

                <?php else: ?>

                    <div class="avatar-empty">
                        <i class="fa-solid fa-user"></i>
                    </div>

                <?php endif; ?>

                <div>
                    <strong><?php echo e($fullName); ?></strong>
                    <small>Vlast post</small>
                </div>

            </div>

            <div class="modal-full-text" id="modalText"></div>

            <div class="modal-engagement">

                <span>
                    <i class="fa-regular fa-heart"></i>
                    Like
                </span>

                <span>
                    <i class="fa-regular fa-comment"></i>
                    Comment
                </span>

                <span>
                    <i class="fa-solid fa-share"></i>
                    Share
                </span>

            </div>

        </div>

    </div>

</div>

<nav class="mobile-nav">

    <a href="home.php">
        <i class="fa-solid fa-house"></i>
    </a>

    <a href="#">
        <i class="fa-solid fa-magnifying-glass"></i>
    </a>

    <a href="post.php" class="add">
        <i class="fa-solid fa-plus"></i>
    </a>

    <a href="#">
        <i class="fa-solid fa-bell"></i>
    </a>

    <a href="user_profile.php">
        <i class="fa-solid fa-user"></i>
    </a>

</nav>

<script>

/* OPEN FULL POST */

const postModal = document.getElementById("postModal");
const modalImage = document.getElementById("modalImage");
const modalImageArea = document.getElementById("modalImageArea");
const modalText = document.getElementById("modalText");
const closePost = document.getElementById("closePost");

document.querySelectorAll(".open-post").forEach(post => {

    post.addEventListener("click", function () {

        const image = this.dataset.image;
        const text = this.dataset.text;

        modalText.textContent = text || "";

        if (image) {
            modalImage.src = image;
            modalImageArea.style.display = "flex";
        } else {
            modalImage.src = "";
            modalImageArea.style.display = "none";
        }

        postModal.classList.add("show");
        document.body.style.overflow = "hidden";

    });

});

/* CLOSE POST */

function closePostViewer() {
    postModal.classList.remove("show");
    document.body.style.overflow = "";
    modalImage.src = "";
}

closePost.addEventListener("click", closePostViewer);

postModal.addEventListener("click", function(event) {

    if (event.target === postModal) {
        closePostViewer();
    }

});

document.addEventListener("keydown", function(event) {

    if (event.key === "Escape") {
        closePostViewer();
    }

});

</script>

</body>
</html>