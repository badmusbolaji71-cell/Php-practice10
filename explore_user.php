
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

$myId = (int)$_SESSION['id'];

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}


/* FOLLOW / UNFOLLOW */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_follow'])) {

    $targetId = (int)($_POST['user_id'] ?? 0);

    if ($targetId > 0 && $targetId !== $myId) {

        $checkUser = $connection->prepare(
            "SELECT id FROM user_details WHERE id = ? LIMIT 1"
        );

        $checkUser->bind_param("i", $targetId);
        $checkUser->execute();

        $userExists = $checkUser->get_result()->num_rows > 0;

        $checkUser->close();

        if ($userExists) {

            $checkFollow = $connection->prepare(
                "SELECT follower_id
                 FROM follow
                 WHERE follower_id = ? AND following_id = ?
                 LIMIT 1"
            );

            $checkFollow->bind_param("ii", $myId, $targetId);
            $checkFollow->execute();

            $alreadyFollowing = $checkFollow->get_result()->num_rows > 0;

            $checkFollow->close();

            if ($alreadyFollowing) {

                $unfollow = $connection->prepare(
                    "DELETE FROM follow
                     WHERE follower_id = ? AND following_id = ?"
                );

                $unfollow->bind_param("ii", $myId, $targetId);
                $unfollow->execute();
                $unfollow->close();

            } else {

                $follow = $connection->prepare(
                    "INSERT IGNORE INTO follow (follower_id, following_id)
                     VALUES (?, ?)"
                );

                $follow->bind_param("ii", $myId, $targetId);
                $follow->execute();
                $follow->close();

            }
        }
    }

    header("Location: explore_user.php");
    exit;
}


/* GET USERS AND FOLLOWING STATUS */

$select = $connection->prepare(
    "SELECT
        u.id,
        u.first_name,
        u.last_name,
        u.email,
        u.bio,
        u.picturepr,
        u.coverpic,
        u.verified,

        CASE
            WHEN f.follower_id IS NULL THEN 0
            ELSE 1
        END AS is_following

     FROM user_details u

     LEFT JOIN follow f
        ON f.follower_id = ?
        AND f.following_id = u.id

     WHERE u.id != ?

     ORDER BY RAND()"
);

$select->bind_param("ii", $myId, $myId);
$select->execute();

$users = $select->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Vlast | Explore</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<link rel="preconnect" href="https://fonts.googleapis.com">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">

<script>
window.VLAST_USER_ID = <?php echo $myId; ?>;
</script>

<script src="theme.js"></script>

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
}

body {
    background: var(--background, #f3f5f7);
    color: var(--text, #222);
    font-family: 'Poppins', sans-serif;
    min-height: 100vh;
    padding-bottom: 0;
    transition: background .2s, color .2s;
}

button, input {
    font-family: inherit;
}

/* HEADER */

header {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 70px;
    background: var(--surface, white);
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 5%;
    border-bottom: 1px solid var(--border, #eee);
    box-shadow: 0 2px 12px rgba(0,0,0,.05);
    z-index: 1000;
}

.logo {
    font-family: 'Playfair Display', serif;
    font-size: 32px;
    font-weight: 700;
    letter-spacing: -1px;
    color: var(--text, #111);
    text-decoration: none;
}

.header-right {
    display: flex;
    align-items: center;
    gap: 27px;
}

.header-right a {
    text-decoration: none;
    color: var(--muted, #777);
    font-size: 18px;
    transition: .2s;
}

.header-right a:hover,
.header-right a.active {
    color: var(--accent, #111);
    transform: translateY(-2px);
}

/* MAIN */

.explore-page {
    max-width: 1250px;
    margin: auto;
    padding: 105px 25px 60px;
}

/* INTRO */

.explore-top {
    text-align: center;
    margin-bottom: 35px;
}

.explore-top h1 {
    font-family: 'Playfair Display', serif;
    font-size: 38px;
    margin-bottom: 8px;
}

.explore-top p {
    color: var(--muted, #888);
    font-size: 13px;
}

/* SEARCH */

.search-wrapper {
    max-width: 650px;
    margin: 25px auto 0;
    position: relative;
}

.search-wrapper > i {
    position: absolute;
    left: 20px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--muted, #999);
    pointer-events: none;
}

.search-wrapper input {
    width: 100%;
    height: 55px;
    border: 1px solid var(--border, #eee);
    outline: none;
    background: var(--surface, white);
    color: var(--text, #222);
    border-radius: 16px;
    padding: 0 55px;
    font-size: 13px;
    box-shadow: 0 3px 15px rgba(0,0,0,.04);
}

.search-wrapper input:focus {
    border-color: var(--accent, #111);
}

.search-wrapper input::placeholder {
    color: var(--muted, #999);
}

.clear-search {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    width: 30px;
    height: 30px;
    border: none;
    background: var(--soft, #eee);
    color: var(--muted, #777);
    border-radius: 50%;
    cursor: pointer;
    display: none;
    align-items: center;
    justify-content: center;
}

/* SECTION HEADER */

.section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 18px;
    gap: 10px;
}

.section-header h2 {
    font-size: 18px;
}

.section-header span {
    color: var(--muted, #999);
    font-size: 11px;
}

/* USER GRID */

.user-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 20px;
}

/* USER CARD */

.user-card {
    background: var(--surface, white);
    border: 1px solid var(--border, #eee);
    border-radius: 18px;
    overflow: hidden;
    box-shadow: 0 3px 15px rgba(0,0,0,.04);
    transition: .25s;
    min-width: 0;
}

.user-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 25px rgba(0,0,0,.08);
}

/* COVER */

.user-cover {
    height: 145px;
    width: 100%;
    position: relative;
    background: linear-gradient(135deg, #171717, #555);
    overflow: hidden;
}

.user-cover img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

/* AVATAR */

.user-avatar {
    width: 88px;
    height: 88px;
    border-radius: 50%;
    background: var(--soft, #eee);
    border: 4px solid var(--surface, white);
    position: absolute;
    left: 50%;
    bottom: -38px;
    transform: translateX(-50%);
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
}

.user-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.user-avatar i {
    color: var(--muted, #999);
    font-size: 30px;
}

/* USER INFO */

.user-info {
    padding: 47px 17px 18px;
    text-align: center;
}

.user-info h3 {
    font-size: 14px;
    margin-bottom: 3px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.username {
    color: var(--muted, #999);
    font-size: 10px;
    margin-bottom: 10px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.user-bio {
    color: var(--muted, #888);
    font-size: 10px;
    line-height: 1.7;
    min-height: 34px;
    margin-bottom: 13px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    overflow-wrap: anywhere;
}

/* FOLLOW BUTTON */

.follow-form {
    width: 100%;
}

.follow-button {
    width: 100%;
    height: 39px;
    border: none;
    border-radius: 10px;
    background: var(--accent, #111);
    color: white;
    font-size: 11px;
    font-weight: 600;
    cursor: pointer;
    transition: .2s;
}

.follow-button:hover {
    opacity: .8;
    transform: translateY(-1px);
}

.follow-button.following {
    background: var(--soft, #eee);
    color: var(--text, #222);
    border: 1px solid var(--border, #ddd);
}

.follow-button.following:hover {
    background: var(--accent, #111);
    color: white;
}

/* VIEW PROFILE */

.profile-button {
    display: flex;
    width: 100%;
    height: 36px;
    margin-top: 8px;
    border: 1px solid var(--border, #eee);
    border-radius: 10px;
    color: var(--text, #555);
    text-decoration: none;
    font-size: 10px;
    align-items: center;
    justify-content: center;
    transition: .2s;
}

.profile-button:hover {
    background: var(--soft, #eee);
}

/* NO RESULTS */

.no-results {
    display: none;
    background: var(--surface, white);
    border: 1px solid var(--border, #eee);
    border-radius: 18px;
    padding: 50px 20px;
    text-align: center;
    grid-column: 1 / -1;
}

.no-results i {
    font-size: 35px;
    color: var(--muted, #aaa);
    margin-bottom: 15px;
}

.no-results h3 {
    font-size: 15px;
    margin-bottom: 5px;
}

.no-results p {
    color: var(--muted, #999);
    font-size: 11px;
}

/* MOBILE NAV */

.mobile-nav {
    display: none;
}

/* TABLET */

@media(max-width: 1050px) {
    .user-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}

@media(max-width: 800px) {
    .user-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

/* MOBILE FACEBOOK-STYLE LIST */

@media(max-width: 550px) {

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

    .header-right {
        gap: 20px;
    }

    .header-right a {
        font-size: 16px;
    }

    .header-right a:nth-child(2),
    .header-right a:nth-child(4),
    .header-right a:nth-child(6) {
        display: none;
    }

    .explore-page {
        padding: 85px 12px 90px;
    }

    .explore-top {
        margin-bottom: 27px;
    }

    .explore-top h1 {
        font-size: 29px;
    }

    .explore-top p {
        font-size: 11px;
    }

    .search-wrapper {
        margin-top: 20px;
    }

    .search-wrapper input {
        height: 49px;
        border-radius: 13px;
    }

    .section-header {
        margin-bottom: 13px;
    }

    .section-header h2 {
        font-size: 15px;
    }

    .user-grid {
        display: flex;
        flex-direction: column;
        gap: 11px;
    }

    /* HORIZONTAL PERSON ROW */

    .user-card {
        width: 100%;
        min-height: 118px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        padding: 12px;
        gap: 12px;
        box-shadow: 0 2px 9px rgba(0,0,0,.035);
    }

    .user-card:hover {
        transform: none;
    }

    .user-cover {
        width: 76px;
        height: 76px;
        min-width: 76px;
        border-radius: 50%;
        overflow: hidden;
        background: var(--soft, #eee);
    }

    .user-cover img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .user-avatar {
        display: none;
    }

    .user-info {
        padding: 0;
        flex: 1;
        min-width: 0;
        text-align: left;
    }

    .user-info h3 {
        font-size: 13px;
        margin-bottom: 2px;
    }

    .username {
        font-size: 9px;
        margin-bottom: 4px;
    }

    .user-bio {
        font-size: 9px;
        min-height: 0;
        margin-bottom: 7px;
        -webkit-line-clamp: 1;
    }

    .card-actions {
        display: flex;
        align-items: center;
        gap: 7px;
    }

    .follow-form {
        width: auto;
        flex: 1;
    }

    .follow-button {
        height: 33px;
        padding: 0 12px;
        font-size: 10px;
        border-radius: 8px;
    }

    .profile-button {
        width: auto;
        height: 33px;
        margin-top: 0;
        padding: 0 10px;
        white-space: nowrap;
        font-size: 9px;
        border-radius: 8px;
    }

    /* MOBILE BOTTOM NAV */

    .mobile-nav {
        position: fixed;
        display: flex;
        align-items: center;
        justify-content: space-around;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 65px;
        background: var(--surface, white);
        border-top: 1px solid var(--border, #eee);
        box-shadow: 0 -3px 15px rgba(0,0,0,.07);
        z-index: 1000;
        padding-bottom: env(safe-area-inset-bottom);
    }

    .mobile-nav a {
        color: var(--muted, #777);
        text-decoration: none;
        font-size: 18px;
        width: 42px;
        height: 42px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
    }

    .mobile-nav a.active {
        background: var(--accent, #111);
        color: white;
    }

    .mobile-add {
        width: 44px !important;
        height: 44px !important;
        border-radius: 13px !important;
        background: var(--accent, #111);
        color: white !important;
    }

}

/* VERY SMALL PHONES */

@media(max-width: 350px) {

    .user-card {
        gap: 8px;
        padding: 9px;
    }

    .user-cover {
        width: 63px;
        height: 63px;
        min-width: 63px;
    }

    .user-info h3 {
        font-size: 12px;
    }

    .profile-button {
        padding: 0 7px;
        font-size: 8px;
    }

}

</style>

</head>

<body>

<header>

    <a href="home.php" class="logo">Vlast</a>

    <div class="header-right">

        <a href="home.php" title="Home">
            <i class="fa-solid fa-house"></i>
        </a>

        <a href="messages.php" title="Messages">
            <i class="fa-solid fa-message"></i>
        </a>

        <a href="explore_user.php" class="active" title="Explore">
            <i class="fa-solid fa-compass"></i>
        </a>

        <a href="notification.php" title="Notifications">
            <i class="fa-solid fa-bell"></i>
        </a>

        <a href="user_profile.php" title="Profile">
            <i class="fa-solid fa-user"></i>
        </a>

        <a href="settings.php" title="Settings">
            <i class="fa-solid fa-gear"></i>
        </a>

    </div>

</header>


<main class="explore-page">

    <section class="explore-top">

        <h1>Explore People</h1>

        <p>Discover people, make connections and grow your Vlast community.</p>

        <div class="search-wrapper">

            <i class="fa-solid fa-magnifying-glass"></i>

            <input
                type="text"
                id="searchInput"
                placeholder="Search people by name or username..."
                autocomplete="off"
            >

            <button
                class="clear-search"
                id="clearSearch"
                type="button"
            >
                <i class="fa-solid fa-xmark"></i>
            </button>

        </div>

    </section>


    <div class="section-header">

        <h2>People you may know</h2>

        <span id="userCount">
            Discover & connect
        </span>

    </div>


    <section class="user-grid" id="userGrid">

        <?php while ($row = $users->fetch_assoc()): ?>

            <?php
                $fullName = trim(
                    ($row['first_name'] ?? '') . ' ' .
                    ($row['last_name'] ?? '')
                );

                $email = $row['email'] ?? '';
                $bio = $row['bio'] ?? '';

                $profilePicture = !empty($row['picturepr'])
                    ? "image/" . $row['picturepr']
                    : "";

                $coverPicture = !empty($row['coverpic'])
                    ? "image/" . $row['coverpic']
                    : "";

                $isFollowing = (int)$row['is_following'];
            ?>

            <article
                class="user-card"
                <?php if($row['verified'] == 1){?>
                  <?php echo h(strtolower($fullName)); ?>
                  <?php }elseif($row['verified'] == 0){?>
                <?php echo h(strtolower($fullName)); ?>
                <?php }?>
                data-username="<?php echo h(strtolower($email)); ?>"
                data-bio="<?php echo h(strtolower($bio)); ?>"
            >

                <div class="user-cover">

                    <?php if ($coverPicture): ?>

                        <img
                            src="<?php echo h($coverPicture); ?>"
                            alt="Cover photo"
                            loading="lazy"
                        >

                    <?php elseif ($profilePicture): ?>

                        <img
                            src="<?php echo h($profilePicture); ?>"
                            alt="Profile background"
                            loading="lazy"
                        >

                    <?php endif; ?>


                    <div class="user-avatar">

                        <?php if ($profilePicture): ?>

                            <img
                                src="<?php echo h($profilePicture); ?>"
                                alt="Profile picture"
                                loading="lazy"
                            >

                        <?php else: ?>

                            <i class="fa-solid fa-user"></i>

                        <?php endif; ?>

                    </div>

                </div>


                <div class="user-info">
                    <?php 
                    if($row['verified'] == 1){
                    ?>
                     <h3><?php echo h($fullName); ?> <i class = "fa-solid fa-circle-check " style = "color:blue; height:20px;"></i></h3>
                    <?php }elseif($row['verified'] == 0){?>

                    <h3><?php echo h($fullName); ?></h3>
                    <?php }?>

                    <div class="username">
                        <?php echo h($email); ?>
                    </div>

                    <p class="user-bio">
                        <?php echo !empty($bio) ? h($bio) : "No bio yet."; ?>
                    </p>


                    <div class="card-actions">

                        <form method="POST" class="follow-form">

                            <input
                                type="hidden"
                                name="user_id"
                                value="<?php echo (int)$row['id']; ?>"
                            >

                            <button
                                type="submit"
                                name="toggle_follow"
                                class="follow-button <?php echo $isFollowing ? 'following' : ''; ?>"
                            >

                                <?php if ($isFollowing): ?>

                                    <i class="fa-solid fa-check"></i>
                                    Following

                                <?php else: ?>

                                    <i class="fa-solid fa-user-plus"></i>
                                    Follow

                                <?php endif; ?>

                            </button>

                        </form>


                        <a
                            href="other_users_pp.php?id=<?php echo (int)$row['id']; ?>"
                            class="profile-button"
                        >
                            View Profile
                        </a>

                    </div>

                </div>

            </article>

        <?php endwhile; ?>


        <div class="no-results" id="noResults">

            <i class="fa-solid fa-user-slash"></i>

            <h3>No people found</h3>

            <p>Try searching with another name or username.</p>

        </div>

    </section>

</main>


<!-- MOBILE NAV -->

<nav class="mobile-nav">

    <a href="home.php" title="Home">
        <i class="fa-solid fa-house"></i>
    </a>

    <a href="explore_user.php" class="active" title="Explore">
        <i class="fa-solid fa-compass"></i>
    </a>

    <a href="post.php" class="mobile-add" title="Create post">
        <i class="fa-solid fa-plus"></i>
    </a>

    <a href="messages.php" title="Messages">
        <i class="fa-solid fa-message"></i>
    </a>

    <a href="user_profile.php" title="Profile">
        <i class="fa-solid fa-user"></i>
    </a>

</nav>


<script>

/* SEARCH */

const searchInput = document.getElementById("searchInput");

const clearSearch = document.getElementById("clearSearch");

const userCards = document.querySelectorAll(".user-card");

const noResults = document.getElementById("noResults");

const userCount = document.getElementById("userCount");


searchInput.addEventListener("input", function() {

    const value = searchInput.value.toLowerCase().trim();

    let found = 0;

    clearSearch.style.display = value ? "flex" : "none";

    userCards.forEach(function(card) {

        const name = card.dataset.name || "";

        const username = card.dataset.username || "";

        const bio = card.dataset.bio || "";

        const matches =
            name.includes(value) ||
            username.includes(value) ||
            bio.includes(value);

        card.style.display = matches ? "" : "none";

        if (matches) {
            found++;
        }

    });

    noResults.style.display =
        value && found === 0 ? "block" : "none";

    if (value) {

        userCount.textContent =
            found + (found === 1 ? " person found" : " people found");

    } else {

        userCount.textContent = "Discover & connect";

    }

});


/* CLEAR SEARCH */

clearSearch.addEventListener("click", function() {

    searchInput.value = "";

    searchInput.dispatchEvent(new Event("input"));

    searchInput.focus();

});

</script>

</body>
</html>

<?php
$select->close();
$connection->close();
?>
