<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "social_media";

$connection = new mysqli($servername, $username, $password, $dbname);

if ($connection->connect_error) {
    die("Database connection failed.");
}

$me = (int)$_SESSION['id'];

$stmt = $connection->prepare(
    "SELECT first_name, last_name, picturepr FROM user_details WHERE id = ? LIMIT 1"
);

$stmt->bind_param("i", $me);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

$fullName = $user
    ? trim($user['first_name'] . " " . $user['last_name'])
    : "My Account";

$picture = $user['picturepr'] ?? "";
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Vlast | Settings</title>

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">

<style>

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Poppins', sans-serif;
    background: var(--vlast-bg, #f3f5f7);
    color: var(--vlast-text, #222);
    min-height: 100vh;
}

a {
    text-decoration: none;
    color: inherit;
}

button {
    font-family: inherit;
    cursor: pointer;
}

header {
    height: 70px;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    padding: 0 5%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: var(--vlast-surface, white);
    border-bottom: 1px solid var(--vlast-border, #eee);
    z-index: 1000;
}

.logo {
    font-family: 'Playfair Display', serif;
    font-size: 32px;
    color: var(--vlast-text, #111);
}

.header-icons {
    display: flex;
    align-items: center;
    gap: 25px;
}

.header-icons a {
    color: var(--vlast-muted, #777);
    font-size: 19px;
}

.header-icons a.active,
.header-icons a:hover {
    color: var(--vlast-accent, #111);
}

.page {
    max-width: 1150px;
    margin: auto;
    padding: 105px 25px 40px;
}

.page-heading {
    margin-bottom: 25px;
}

.page-heading h1 {
    font-size: 27px;
    font-weight: 700;
}

.page-heading p {
    color: var(--vlast-muted, #888);
    font-size: 12px;
    margin-top: 5px;
}

.settings-layout {
    display: grid;
    grid-template-columns: 270px minmax(0, 1fr);
    gap: 22px;
    align-items: start;
}

.settings-sidebar,
.settings-content {
    background: var(--vlast-surface, white);
    border: 1px solid var(--vlast-border, #eee);
    border-radius: 18px;
    box-shadow: 0 4px 20px rgba(0,0,0,.035);
}

.settings-sidebar {
    padding: 20px;
}

.user-card {
    display: flex;
    align-items: center;
    gap: 12px;
    padding-bottom: 20px;
    border-bottom: 1px solid var(--vlast-border, #eee);
    margin-bottom: 15px;
}

.user-picture {
    width: 48px;
    height: 48px;
    min-width: 48px;
    border-radius: 50%;
    overflow: hidden;
    background: var(--vlast-soft, #eee);
}

.user-picture img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.user-card h3 {
    font-size: 13px;
}

.user-card p {
    font-size: 10px;
    color: var(--vlast-muted, #999);
    margin-top: 3px;
}

.settings-nav {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.settings-nav a {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    border-radius: 11px;
    font-size: 12px;
    color: var(--vlast-muted, #777);
    transition: .2s;
}

.settings-nav a:hover,
.settings-nav a.active {
    background: var(--vlast-soft, #f2f3f5);
    color: var(--vlast-accent, #111);
}

.settings-nav i {
    width: 18px;
    text-align: center;
}

.settings-content {
    padding: 28px;
}

.section-title {
    font-size: 18px;
    margin-bottom: 6px;
}

.section-description {
    color: var(--vlast-muted, #888);
    font-size: 11px;
    line-height: 1.7;
    margin-bottom: 25px;
}

.setting-block {
    padding: 22px 0;
    border-top: 1px solid var(--vlast-border, #eee);
}

.setting-block:first-of-type {
    border-top: none;
}

.setting-block h3 {
    font-size: 13px;
    margin-bottom: 5px;
}

.setting-block > p {
    font-size: 10px;
    color: var(--vlast-muted, #999);
    margin-bottom: 17px;
    line-height: 1.7;
}

.theme-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 13px;
}

.theme-option {
    border: 2px solid var(--vlast-border, #eee);
    background: var(--vlast-surface, white);
    border-radius: 13px;
    padding: 10px;
    text-align: left;
    transition: .2s;
    color: var(--vlast-text, #222);
}

.theme-option:hover {
    transform: translateY(-3px);
    border-color: var(--vlast-accent, #111);
}

.theme-option.selected {
    border-color: var(--vlast-accent, #111);
    box-shadow: 0 0 0 2px var(--vlast-soft, #eee);
}

.theme-preview {
    height: 65px;
    border-radius: 8px;
    display: flex;
    overflow: hidden;
    margin-bottom: 10px;
}

.preview-sidebar {
    width: 25%;
    background: var(--preview-sidebar);
}

.preview-main {
    flex: 1;
    padding: 9px;
    background: var(--preview-bg);
}

.preview-card {
    height: 19px;
    border-radius: 5px;
    background: var(--preview-card);
    margin-bottom: 6px;
}

.preview-accent {
    height: 8px;
    width: 55%;
    border-radius: 5px;
    background: var(--preview-accent);
}

.theme-option strong {
    display: block;
    font-size: 11px;
}

.theme-option small {
    display: block;
    font-size: 9px;
    color: var(--vlast-muted, #999);
    margin-top: 3px;
}

.mode-options {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.mode-option {
    flex: 1;
    min-width: 120px;
    border: 1px solid var(--vlast-border, #eee);
    border-radius: 12px;
    padding: 15px;
    background: var(--vlast-surface, white);
    color: var(--vlast-text, #222);
    text-align: left;
}

.mode-option.selected {
    border: 2px solid var(--vlast-accent, #111);
    background: var(--vlast-soft, #f5f5f5);
}

.mode-option i {
    font-size: 19px;
    margin-bottom: 10px;
}

.mode-option strong {
    display: block;
    font-size: 12px;
}

.mode-option small {
    display: block;
    color: var(--vlast-muted, #999);
    font-size: 9px;
    margin-top: 4px;
}

.preview-box {
    background: var(--vlast-soft, #f2f3f5);
    border-radius: 13px;
    padding: 18px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
}

.preview-box p {
    font-size: 11px;
    color: var(--vlast-muted, #888);
}

.preview-button {
    background: var(--vlast-accent, #111);
    color: white;
    padding: 10px 17px;
    border: none;
    border-radius: 9px;
    font-size: 10px;
}

.settings-actions {
    display: flex;
    gap: 10px;
    margin-top: 25px;
    flex-wrap: wrap;
}

.save-button,
.reset-button {
    padding: 12px 20px;
    border: none;
    border-radius: 10px;
    font-size: 11px;
    font-weight: 600;
}

.save-button {
    background: var(--vlast-accent, #111);
    color: white;
}

.reset-button {
    background: var(--vlast-soft, #eee);
    color: var(--vlast-text, #222);
}

.save-message {
    display: none;
    color: #239b55;
    font-size: 11px;
    margin-top: 13px;
}

/* CHAT WALLPAPER SETTINGS */

.chat-wallpaper-preview {
    width: 100%;
    height: 190px;
    border-radius: 15px;
    background: linear-gradient(135deg, #dce9f5, #f1e8f8);
    background-position: center;
    background-size: cover;
    border: 1px solid var(--vlast-border, #eee);
    margin: 15px 0;
}

.wallpaper-options {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 13px;
}

.wallpaper-choice {
    min-height: 105px;
    border: 2px solid var(--vlast-border, #eee);
    border-radius: 13px;
    padding: 8px;
    cursor: pointer;
    display: flex;
    align-items: flex-end;
    justify-content: flex-start;
    transition: .2s;
}

.wallpaper-choice span {
    background: rgba(255,255,255,.85);
    color: #222;
    padding: 5px 8px;
    border-radius: 7px;
    font-size: 10px;
    font-weight: 600;
}

.wallpaper-choice:hover,
.wallpaper-choice.selected {
    border-color: var(--vlast-accent, #111);
    transform: translateY(-2px);
}

.wallpaper-upload {
    display: inline-flex;
    align-items: center;
    gap: 9px;
    background: var(--vlast-soft, #eee);
    color: var(--vlast-text, #222);
    padding: 12px 16px;
    border-radius: 10px;
    cursor: pointer;
    font-size: 11px;
    font-weight: 600;
}

.wallpaper-upload input {
    display: none;
}

.wallpaper-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 15px;
}

.wallpaper-message {
    display: none;
    margin-top: 12px;
    color: #239b55;
    font-size: 11px;
    line-height: 1.6;
}

@media(max-width:550px) {
    .wallpaper-options {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media(max-width:800px) {
    .settings-layout {
        grid-template-columns: 1fr;
    }

    .settings-sidebar {
        padding: 15px;
    }

    .settings-nav {
        flex-direction: row;
        overflow-x: auto;
    }

    .settings-nav a {
        white-space: nowrap;
    }
}

@media(max-width:550px) {
    header {
        height: 62px;
        padding: 0 17px;
    }

    .logo {
        font-size: 27px;
    }

    .header-icons {
        gap: 15px;
    }

    .header-icons a {
        font-size: 16px;
    }

    .page {
        padding: 85px 13px 25px;
    }

    .settings-content {
        padding: 19px 15px;
    }

    .theme-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 9px;
    }

    .settings-sidebar {
        overflow: hidden;
    }

    .settings-nav a {
        padding: 10px;
    }

    .settings-nav a span {
        font-size: 10px;
    }

    .page-heading h1 {
        font-size: 23px;
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

        <a href="home.php" title="Home">
            <i class="fa-solid fa-house"></i>
        </a>

        <a href="messages.php" title="Messages">
            <i class="fa-solid fa-message"></i>
        </a>

        <a href="explore_user.php" title="Explore">
            <i class="fa-solid fa-magnifying-glass"></i>
        </a>

        <a href="notifications.php" title="Notifications">
            <i class="fa-solid fa-bell"></i>
        </a>

        <a href="user_profile.php" title="Profile">
            <i class="fa-solid fa-user"></i>
        </a>

    </div>

</header>

<main class="page">

    <div class="page-heading">
        <h1>Settings</h1>
        <p>Make Vlast look the way you want.</p>
    </div>

    <div class="settings-layout">

        <aside class="settings-sidebar">

            <div class="user-card">

                <div class="user-picture">
                    <?php if ($picture): ?>
                        <img src="image/<?php echo htmlspecialchars($picture); ?>" alt="">
                    <?php endif; ?>
                </div>

                <div>
                    <h3><?php echo htmlspecialchars($fullName); ?></h3>
                    <p>Personal settings</p>
                </div>

            </div>

            <nav class="settings-nav">

                <a href="#appearance" class="active">
                    <i class="fa-solid fa-palette"></i>
                    <span>Appearance</span>
                </a>

                <a href="#theme-colors">
                    <i class="fa-solid fa-swatchbook"></i>
                    <span>Theme colours</span>
                </a>

                <a href="#display-mode">
                    <i class="fa-solid fa-circle-half-stroke"></i>
                    <span>Display mode</span>
                </a>

                <a href="#chat-background">
                    <i class="fa-solid fa-image"></i>
                    <span>Chat background</span>
                </a>

                <a href="#preview">
                    <i class="fa-solid fa-eye"></i>
                    <span>Preview</span>
                </a>

            </nav>

        </aside>

        <section class="settings-content" id="appearance">

            <h2 class="section-title">Appearance</h2>

            <p class="section-description">
                Personalise the colours and appearance of your Vlast experience.
                
            </p>

            <div class="setting-block" id="theme-colors">

                <h3>Choose your theme</h3>

                <p>
                    Select a colour combination for your Vlast appearance
                </p>

                <div class="theme-grid" id="themeGrid"></div>

            </div>

            <div class="setting-block" id="display-mode">

                <h3>Display mode</h3>

                <p>
                    Choose whether Vlast should appear light or dark.
                </p>

                <div class="mode-options">

                    <button class="mode-option" data-mode="light" type="button">
                        <i class="fa-solid fa-sun"></i>
                        <strong>Light mode</strong>
                        <small>Bright and clean appearance</small>
                    </button>

                    <button class="mode-option" data-mode="dark" type="button">
                        <i class="fa-solid fa-moon"></i>
                        <strong>Dark mode</strong>
                        <small>Dark background and softer surfaces</small>
                    </button>

                </div>

            </div>

            <div class="setting-block" id="chat-background">

                <h3>Chat background</h3>

                <p>
                    Choose a default wallpaper for all your conversations.
                    You can also set a different wallpaper for an individual chat
                    from the Messages page.
                </p>

                <div class="chat-wallpaper-preview" id="wallpaperPreview"></div>

                <div class="wallpaper-options" id="wallpaperOptions">

                    <button type="button" class="wallpaper-choice"
                        data-wallpaper="blue"
                        style="background:linear-gradient(135deg,#dce9f5,#a7c9e8)">
                        <span>Ocean</span>
                    </button>

                    <button type="button" class="wallpaper-choice"
                        data-wallpaper="purple"
                        style="background:linear-gradient(135deg,#e8ddf8,#b9a2e9)">
                        <span>Lavender</span>
                    </button>

                    <button type="button" class="wallpaper-choice"
                        data-wallpaper="pink"
                        style="background:linear-gradient(135deg,#ffe1eb,#f5a9c4)">
                        <span>Rose</span>
                    </button>

                    <button type="button" class="wallpaper-choice"
                        data-wallpaper="green"
                        style="background:linear-gradient(135deg,#d8f0df,#91cda5)">
                        <span>Nature</span>
                    </button>

                    <button type="button" class="wallpaper-choice"
                        data-wallpaper="dark"
                        style="background:linear-gradient(135deg,#202638,#414c68)">
                        <span>Midnight</span>
                    </button>

                    <button type="button" class="wallpaper-choice"
                        data-wallpaper="sunset"
                        style="background:linear-gradient(135deg,#ffdfbd,#ed987d)">
                        <span>Sunset</span>
                    </button>

                </div>

                <div class="wallpaper-actions">

                    <label class="wallpaper-upload">
                        <i class="fa-solid fa-image"></i>
                        Upload your own image
                        <input type="file" id="wallpaperUpload" accept="image/*">
                    </label>

                    <button type="button" class="reset-button" id="resetWallpaper">
                        Reset wallpaper
                    </button>

                </div>

                <p class="wallpaper-message" id="wallpaperMessage" aria-live="polite"></p>

            </div>

            <div class="setting-block" id="preview">

                <h3>Theme preview</h3>

                <p>
                    This preview changes when you select a theme or display mode.
                </p>

                <div class="preview-box">

                    <div>
                        <strong>Welcome to Vlast</strong>
                        <p>Your social space, your style.</p>
                    </div>

                   
                </div>

            </div>

            <div class="settings-actions">

                <button class="save-button" id="saveTheme" type="button">
                    <i class="fa-solid fa-check"></i>
                    Save appearance
                </button>

                <button class="reset-button" id="resetTheme" type="button">
                    Reset settings
                </button>

            </div>

            <p class="save-message" id="saveMessage">
                <i class="fa-solid fa-circle-check"></i>
                Your appearance has been saved.
            </p>

        </section>

    </div>

</main>

<script>

const themeGrid = document.getElementById("themeGrid");
const saveMessage = document.getElementById("saveMessage");

const themes = window.VlastTheme.themes;

let selectedTheme = window.VlastTheme.getSettings().theme;
let selectedMode = window.VlastTheme.getSettings().mode;

function renderThemes() {

    themeGrid.innerHTML = "";

    Object.keys(themes).forEach(function(key) {

        const theme = themes[key];

        const button = document.createElement("button");

        button.type = "button";

        button.className =
            "theme-option " + (selectedTheme === key ? "selected" : "");

        button.innerHTML = `
            <div class="theme-preview">
                <div class="preview-sidebar"
                    style="--preview-sidebar:${theme.sidebar}"></div>

                <div class="preview-main"
                    style="--preview-bg:${theme.background}">

                    <div class="preview-card"
                        style="--preview-card:${theme.surface}"></div>

                    <div class="preview-accent"
                        style="--preview-accent:${theme.accent}"></div>

                </div>
            </div>

            <strong>${theme.name}</strong>
            <small>${theme.description}</small>
        `;

        button.addEventListener("click", function() {

            selectedTheme = key;

            window.VlastTheme.apply(selectedTheme, selectedMode);

            renderThemes();
            updateModeButtons();

        });

        themeGrid.appendChild(button);

    });

}

function updateModeButtons() {

    document.querySelectorAll(".mode-option").forEach(function(button) {

        button.classList.toggle(
            "selected",
            button.dataset.mode === selectedMode
        );

    });

}

document.querySelectorAll(".mode-option").forEach(function(button) {

    button.addEventListener("click", function() {

        selectedMode = this.dataset.mode;

        window.VlastTheme.apply(selectedTheme, selectedMode);

        updateModeButtons();
        renderThemes();

    });

});

document.getElementById("saveTheme").addEventListener("click", function() {

    window.VlastTheme.save(selectedTheme, selectedMode);

    saveMessage.style.display = "block";

    setTimeout(function() {
        saveMessage.style.display = "none";
    }, 3000);

});

document.getElementById("resetTheme").addEventListener("click", function() {

    selectedTheme = "classic";
    selectedMode = "light";

    window.VlastTheme.save(selectedTheme, selectedMode);

    renderThemes();
    updateModeButtons();

    saveMessage.textContent = "Appearance settings have been reset.";
    saveMessage.style.display = "block";

    setTimeout(function() {
        saveMessage.style.display = "none";
        saveMessage.innerHTML =
            '<i class="fa-solid fa-circle-check"></i> Your appearance has been saved.';
    }, 3000);

});


/* CHAT WALLPAPER SETTINGS */

const wallpaperStorageKey =
    "vlastChatBackground_user_" + window.VLAST_USER_ID;

const wallpaperPresets = {
    blue: "linear-gradient(135deg, #dce9f5, #a7c9e8)",
    purple: "linear-gradient(135deg, #e8ddf8, #b9a2e9)",
    pink: "linear-gradient(135deg, #ffe1eb, #f5a9c4)",
    green: "linear-gradient(135deg, #d8f0df, #91cda5)",
    dark: "linear-gradient(135deg, #202638, #414c68)",
    sunset: "linear-gradient(135deg, #ffdfbd, #ed987d)"
};

let wallpaperSettings = {
    global: null,
    chats: {}
};

try {

    const savedWallpaperSettings =
        localStorage.getItem(wallpaperStorageKey);

    if (savedWallpaperSettings) {

        const parsedSettings = JSON.parse(savedWallpaperSettings);

        if (parsedSettings && typeof parsedSettings === "object") {

            wallpaperSettings = {
                global: parsedSettings.global || null,
                chats: parsedSettings.chats || {}
            };

        }

    }

} catch (error) {

    wallpaperSettings = {
        global: null,
        chats: {}
    };

}

const wallpaperPreview =
    document.getElementById("wallpaperPreview");

const wallpaperMessage =
    document.getElementById("wallpaperMessage");

function saveWallpaperSettings() {

    try {

        localStorage.setItem(
            wallpaperStorageKey,
            JSON.stringify(wallpaperSettings)
        );

        return true;

    } catch (error) {

        wallpaperMessage.textContent =
            "Your browser could not save this image. Try a smaller image.";

        wallpaperMessage.style.display = "block";

        return false;

    }

}

function showWallpaperPreview(wallpaper) {

    if (!wallpaper) {

        wallpaperPreview.style.backgroundImage =
            "linear-gradient(135deg, #dce9f5, #f1e8f8)";

        return;

    }

    wallpaperPreview.style.backgroundImage = wallpaper.value;

}

function updateWallpaperChoices() {

    document.querySelectorAll(".wallpaper-choice").forEach(function(button) {

        button.classList.toggle(
            "selected",
            wallpaperSettings.global &&
            wallpaperSettings.global.type === "preset" &&
            wallpaperSettings.global.name === button.dataset.wallpaper
        );

    });

}

function selectGlobalWallpaper(wallpaper) {

    wallpaperSettings.global = wallpaper;

    if (saveWallpaperSettings()) {

        showWallpaperPreview(wallpaper);
        updateWallpaperChoices();

        wallpaperMessage.textContent =
            "Your default chat wallpaper has been saved.";

        wallpaperMessage.style.display = "block";

    }

}

document.querySelectorAll(".wallpaper-choice").forEach(function(button) {

    button.addEventListener("click", function() {

        const name = this.dataset.wallpaper;

        selectGlobalWallpaper({

            type: "preset",
            name: name,
            value: wallpaperPresets[name]

        });

    });

});

document.getElementById("wallpaperUpload").addEventListener("change", function() {

    const file = this.files && this.files[0];

    if (!file) return;

    if (!file.type.startsWith("image/")) {

        wallpaperMessage.textContent =
            "Please choose a valid image file.";

        wallpaperMessage.style.display = "block";

        this.value = "";

        return;

    }

    const reader = new FileReader();

    reader.onload = function(event) {

        const image = new Image();

        image.onload = function() {

            const canvas = document.createElement("canvas");

            const maxSize = 1200;

            const scale = Math.min(
                1,
                maxSize / Math.max(image.width, image.height)
            );

            canvas.width = Math.max(
                1,
                Math.round(image.width * scale)
            );

            canvas.height = Math.max(
                1,
                Math.round(image.height * scale)
            );

            const context = canvas.getContext("2d");

            context.drawImage(
                image,
                0,
                0,
                canvas.width,
                canvas.height
            );

            const compressedImage =
                canvas.toDataURL("image/jpeg", 0.72);

            selectGlobalWallpaper({

                type: "image",
                value: 'url("' + compressedImage + '")'

            });

        };

        image.onerror = function() {

            wallpaperMessage.textContent =
                "The selected image could not be opened.";

            wallpaperMessage.style.display = "block";

        };

        image.src = event.target.result;

    };

    reader.onerror = function() {

        wallpaperMessage.textContent =
            "Could not read that image. Please try another one.";

        wallpaperMessage.style.display = "block";

    };

    reader.readAsDataURL(file);

});

document.getElementById("resetWallpaper").addEventListener("click", function() {

    wallpaperSettings.global = null;

    if (saveWallpaperSettings()) {

        showWallpaperPreview(null);
        updateWallpaperChoices();

        wallpaperMessage.textContent =
            "Your default chat wallpaper has been reset.";

        wallpaperMessage.style.display = "block";

    }

    document.getElementById("wallpaperUpload").value = "";

});

showWallpaperPreview(wallpaperSettings.global);
updateWallpaperChoices();

renderThemes();
updateModeButtons();

</script>

</body>
</html>