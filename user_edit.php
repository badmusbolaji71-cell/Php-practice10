
<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "social_media";

$connection = new mysqli($servername, $username, $password, $dbname);

?>


<?php

if(isset($_POST['update'])){

    /*
    ============================================================
    GET EXISTING USER DATA
    ============================================================
    */

    $current_select = mysqli_query(
        $connection,
        "SELECT * FROM user_details WHERE id = '{$_SESSION['id']}'"
    );

    $current_user = mysqli_fetch_array($current_select);


    /*
    ============================================================
    PROFILE PICTURE
    KEEP OLD VALUE IF NO NEW IMAGE IS SELECTED
    ============================================================
    */

    $picturepr = $current_user['picturepr'];

    if(
        isset($_FILES['picturerr']) &&
        !empty($_FILES['picturerr']['name'])
    ){

        $picturepr = $_FILES['picturerr']['name'];

        $tmp = $_FILES['picturerr']['tmp_name'];

        move_uploaded_file(
            $tmp,
            "image/" . $picturepr
        );
    }


    /*
    ============================================================
    COVER PICTURE
    KEEP OLD VALUE IF NO NEW IMAGE IS SELECTED
    ============================================================
    */

    $coverpic = $current_user['coverpic'];

    if(
        isset($_FILES['coverpic']) &&
        !empty($_FILES['coverpic']['name'])
    ){

        $coverpic = $_FILES['coverpic']['name'];

        $tmp_cover = $_FILES['coverpic']['tmp_name'];

        move_uploaded_file(
            $tmp_cover,
            "image/" . $coverpic
        );
    }


    /*
    ============================================================
    FORM VALUES
    ============================================================
    */

    $bio = $_POST['bio'];

    $email = $_POST['email'];

    $first_name = $_POST['first_name'];

    $last_name = $_POST['last_name'];


    /*
    ============================================================
    VALIDATION
    ============================================================
    */

    $isvalid = true;

    if(
        empty($first_name) ||
        empty($last_name) ||
        empty($email)
    ){

        $error = "Neccessary part should be fllled";

        $isvalid = false;
    }


    /*
    ============================================================
    YOUR UPDATE SYNTAX
    ============================================================
    */

    if($isvalid){

        $update = mysqli_prepare(
            $connection,

            "UPDATE user_details
            SET
            first_name = ?,
            last_name = ?,
            email = ?,
            picturepr = ?,
            coverpic = ?,
            bio = ?
            WHERE id = '{$_SESSION['id']}'"
            
        );
        


        mysqli_stmt_bind_param(
            $update,
            "ssssss",
            $first_name,
            $last_name,
            $email,
            $picturepr,
            $coverpic,
            $bio
        );


        if(mysqli_stmt_execute($update)){

            /*
            If the email was changed,
            update the session too.
            */

            $_SESSION['id'] = $id;

        

            

        }else{

            echo "Error: " . mysqli_error($connection);
        }
            header("Location: user_profile.php");

    }

}

?>


<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Vlast | Edit Profile</title>


    <!-- FONT AWESOME -->

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
    >


    <!-- GOOGLE FONTS -->

    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
    >

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Playfair+Display:wght@700&display=swap"
        rel="stylesheet"
    >


<style>

/* ============================================================
   RESET
============================================================ */

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}


body{

    background:#f3f5f7;

    font-family:'Poppins',sans-serif;

    color:#222;

    min-height:100vh;

}


/* ============================================================
   HEADER
============================================================ */

header{

    position:fixed;

    top:0;

    left:0;

    width:100%;

    height:70px;

    background:white;

    display:flex;

    align-items:center;

    justify-content:space-between;

    padding:0 5%;

    box-shadow:0 2px 15px rgba(0,0,0,.07);

    z-index:1000;

}


.logo{

    font-family:'Playfair Display',serif;

    font-size:32px;

    font-weight:700;

    color:#111;

    letter-spacing:-1px;

    text-decoration:none;

}


.header-right{

    display:flex;

    align-items:center;

    gap:12px;

}


.header-right a{

    width:40px;

    height:40px;

    border-radius:50%;

    display:flex;

    align-items:center;

    justify-content:center;

    color:#666;

    text-decoration:none;

    transition:.2s;

}


.header-right a:hover{

    background:#f1f1f1;

    color:#111;

}


.header-right a.active{

    background:#111;

    color:white;

}


/* ============================================================
   MAIN PAGE
============================================================ */

.page{

    width:100%;

    max-width:1050px;

    margin:auto;

    padding:100px 20px 50px;

}


/* ============================================================
   EDIT CARD
============================================================ */

.edit-card{

    background:white;

    border-radius:22px;

    overflow:hidden;

    box-shadow:0 4px 25px rgba(0,0,0,.07);

    border:1px solid #e9e9e9;

}


/* ============================================================
   COVER
============================================================ */

.cover{

    width:100%;

    height:330px;

    position:relative;

    background:#202020;

    overflow:hidden;

}


.cover img{

    width:100%;

    height:100%;

    object-fit:cover;

    display:block;

}


.cover-empty{

    width:100%;

    height:100%;

    background:
    linear-gradient(
        135deg,
        #111,
        #292929,
        #666
    );

}


.cover::after{

    content:"";

    position:absolute;

    inset:0;

    background:linear-gradient(
        to bottom,
        transparent 45%,
        rgba(0,0,0,.5)
    );

    pointer-events:none;

}


/* ============================================================
   COVER CAMERA
============================================================ */

.cover-camera{

    position:absolute;

    right:22px;

    bottom:20px;

    width:48px;

    height:48px;

    border-radius:50%;

    background:rgba(0,0,0,.65);

    color:white;

    border:2px solid rgba(255,255,255,.8);

    display:flex;

    align-items:center;

    justify-content:center;

    cursor:pointer;

    z-index:5;

    transition:.2s;

}


.cover-camera:hover{

    background:#111;

    transform:scale(1.08);

}


.cover-camera input{

    position:absolute;

    inset:0;

    width:100%;

    height:100%;

    opacity:0;

    cursor:pointer;

}


/* ============================================================
   COVER TEXT
============================================================ */

.cover-text{

    position:absolute;

    left:30px;

    bottom:22px;

    color:white;

    z-index:3;

}


.cover-text h2{

    font-size:18px;

    font-weight:600;

}


.cover-text p{

    font-size:10px;

    opacity:.8;

}


/* ============================================================
   PROFILE AREA
============================================================ */

.profile-content{

    padding:0 35px 35px;

}


/* ============================================================
   PROFILE IMAGE
============================================================ */

.profile-picture-wrapper{

    position:relative;

    width:150px;

    height:150px;

    margin-top:-75px;

}


.profile-picture{

    width:150px;

    height:150px;

    border-radius:50%;

    background:#ddd;

    border:6px solid white;

    overflow:hidden;

    display:flex;

    align-items:center;

    justify-content:center;

    box-shadow:0 5px 20px rgba(0,0,0,.15);

    color:#999;

}


.profile-picture img{

    width:100%;

    height:100%;

    object-fit:cover;

}


.profile-picture i{

    font-size:45px;

}


/* ============================================================
   PROFILE CAMERA
============================================================ */

.profile-camera{

    position:absolute;

    right:3px;

    bottom:5px;

    width:42px;

    height:42px;

    border-radius:50%;

    background:#111;

    color:white;

    border:3px solid white;

    display:flex;

    align-items:center;

    justify-content:center;

    cursor:pointer;

    z-index:10;

    transition:.2s;

}


.profile-camera:hover{

    transform:scale(1.08);

    background:#333;

}


.profile-camera input{

    position:absolute;

    inset:0;

    width:100%;

    height:100%;

    opacity:0;

    cursor:pointer;

}


/* ============================================================
   FORM
============================================================ */

.form-area{

    margin-top:20px;

}


.form-label{

    display:block;

    font-size:11px;

    font-weight:600;

    color:#777;

    margin-bottom:7px;

    text-transform:uppercase;

    letter-spacing:.5px;

}


.name-row{

    display:grid;

    grid-template-columns:1fr 1fr;

    gap:15px;

}


.input-group{

    margin-bottom:20px;

}


.input-group input{

    width:100%;

    height:48px;

    border:1px solid #ddd;

    border-radius:11px;

    background:#f8f9fa;

    padding:0 15px;

    outline:none;

    font-family:inherit;

    font-size:13px;

    transition:.2s;

}


.input-group input:focus{

    border-color:#111;

    background:white;

    box-shadow:0 0 0 3px rgba(0,0,0,.04);

}


/* ============================================================
   BIO
============================================================ */

.bio-input{

    width:100%;

    min-height:120px;

    resize:vertical;

    border:1px solid #ddd;

    border-radius:11px;

    background:#f8f9fa;

    padding:14px 15px;

    outline:none;

    font-family:inherit;

    font-size:13px;

    line-height:1.6;

}


.bio-input:focus{

    border-color:#111;

    background:white;

    box-shadow:0 0 0 3px rgba(0,0,0,.04);

}


/* ============================================================
   EMAIL
============================================================ */

.email-box{

    margin-top:2px;

}


.email-box input{

    width:100%;

    height:48px;

    border:1px solid #ddd;

    border-radius:11px;

    background:#f8f9fa;

    padding:0 15px;

    outline:none;

    font-family:inherit;

    font-size:13px;

}


.email-box input:focus{

    border-color:#111;

    background:white;

}


/* ============================================================
   PHOTO NOTE
============================================================ */

.photo-note{

    margin-top:8px;

    font-size:10px;

    color:#999;

    display:flex;

    gap:7px;

    align-items:flex-start;

}


.photo-note i{

    margin-top:2px;

}


/* ============================================================
   ERROR
============================================================ */

.error{

    margin-bottom:20px;

    background:#fff0f0;

    color:#c62828;

    border:1px solid #ffd1d1;

    border-radius:10px;

    padding:12px 15px;

    font-size:11px;

}


/* ============================================================
   BUTTONS
============================================================ */

.form-buttons{

    display:flex;

    gap:12px;

    margin-top:28px;

    padding-top:22px;

    border-top:1px solid #eee;

}


.form-buttons button,
.form-buttons a{

    height:46px;

    padding:0 22px;

    border-radius:10px;

    border:1px solid #ddd;

    font-family:inherit;

    font-size:12px;

    font-weight:600;

    display:flex;

    align-items:center;

    justify-content:center;

    gap:8px;

    cursor:pointer;

    text-decoration:none;

    transition:.2s;

}


.cancel-btn{

    background:white;

    color:#555;

}


.cancel-btn:hover{

    background:#f3f3f3;

}


.update-btn{

    background:#111;

    border-color:#111 !important;

    color:white;

}


.update-btn:hover{

    background:#333;

    transform:translateY(-1px);

}


/* ============================================================
   MOBILE NAV
============================================================ */

.mobile-nav{

    display:none;

}


/* ============================================================
   TABLET
============================================================ */

@media(max-width:700px){

    .page{

        padding-left:12px;

        padding-right:12px;

    }


    .header{

        padding:0 18px;

    }


    .header-right{

        gap:4px;

    }


    .header-right a{

        width:36px;

        height:36px;

    }

}


/* ============================================================
   MOBILE
============================================================ */

@media(max-width:550px){

    body{

        padding-bottom:65px;

    }


    header{

        height:62px;

        padding:0 15px;

    }


    .logo{

        font-size:27px;

    }


    .header-right a:nth-child(2),
    .header-right a:nth-child(4){

        display:none;

    }


    .page{

        padding:74px 8px 20px;

    }


    .edit-card{

        border-radius:15px;

    }


    .cover{

        height:200px;

    }


    .cover-camera{

        width:42px;

        height:42px;

        right:14px;

        bottom:14px;

    }


    .cover-text{

        left:18px;

        bottom:16px;

    }


    .cover-text h2{

        font-size:15px;

    }


    .cover-text p{

        font-size:9px;

    }


    .profile-content{

        padding:0 16px 25px;

    }


    .profile-picture-wrapper{

        width:110px;

        height:110px;

        margin-top:-55px;

    }


    .profile-picture{

        width:110px;

        height:110px;

        border-width:5px;

    }


    .profile-picture i{

        font-size:35px;

    }


    .profile-camera{

        width:35px;

        height:35px;

        right:0;

        bottom:0;

        border-width:2px;

    }


    .name-row{

        grid-template-columns:1fr;

        gap:0;

    }


    .form-area{

        margin-top:18px;

    }


    .input-group{

        margin-bottom:16px;

    }


    .form-buttons{

        display:grid;

        grid-template-columns:1fr;

    }


    .form-buttons button,
    .form-buttons a{

        width:100%;

    }


    /* MOBILE NAV */

    .mobile-nav{

        position:fixed;

        display:flex;

        bottom:0;

        left:0;

        width:100%;

        height:63px;

        background:white;

        border-top:1px solid #eee;

        box-shadow:0 -3px 15px rgba(0,0,0,.08);

        align-items:center;

        justify-content:space-around;

        z-index:1000;

    }


    .mobile-nav a{

        width:42px;

        height:42px;

        border-radius:50%;

        display:flex;

        align-items:center;

        justify-content:center;

        color:#777;

        text-decoration:none;

    }


    .mobile-nav a.active{

        background:#111;

        color:white;

    }


    .mobile-nav .add{

        background:#111;

        color:white;

    }

}


/* ============================================================
   VERY SMALL SCREEN
============================================================ */

@media(max-width:360px){

    .cover{

        height:175px;

    }


    .profile-picture-wrapper{

        width:95px;

        height:95px;

        margin-top:-48px;

    }


    .profile-picture{

        width:95px;

        height:95px;

    }


    .profile-content{

        padding-left:12px;

        padding-right:12px;

    }

}

</style>
<script>
    window.VLAST_USER_ID = <?php echo (int)$_SESSION['id']; ?>;
</script>
<script src="theme.js"></script>
</head>


<body>


<?php

/*
============================================================
GET CURRENT USER INFORMATION
============================================================
*/

$select = mysqli_query(
    $connection,
    "SELECT * FROM user_details WHERE id = '{$_SESSION['id']}'"
);

$i = 1;


if(mysqli_num_rows($select) > 0){

    while($row = mysqli_fetch_array($select)){

?>


<!-- ========================================================
     HEADER
========================================================= -->

<header>


    <a href="home.php" class="logo">

        Vlast

    </a>


    <div class="header-right">


        <a
            href="home.php"
            title="Home"
        >

            <i class="fa-solid fa-house"></i>

        </a>


        <a
            href="#"
            title="Messages"
        >

            <i class="fa-solid fa-message"></i>

        </a>


        <a
            href="#"
            title="Search"
        >

            <i class="fa-solid fa-magnifying-glass"></i>

        </a>


        <a
            href="#"
            title="Notifications"
        >

            <i class="fa-solid fa-bell"></i>

        </a>


        <a
            href="user_profile.php"
            class="active"
            title="Profile"
        >

            <i class="fa-solid fa-user"></i>

        </a>


    </div>


</header>



<!-- ========================================================
     PAGE
========================================================= -->

<main class="page">


<form
    class="edit-card"
    action=""
    method="POST"
    enctype="multipart/form-data"
>


    <!-- ====================================================
         COVER PHOTO
    ===================================================== -->

    <div class="cover">


        <?php

        if(!empty($row['coverpic'])){

        ?>

            <img
                src="image/<?php echo htmlspecialchars($row['coverpic']); ?>"
                id="coverPreview"
                alt="Cover photo"
            >

        <?php

        }else{

        ?>

            <div
                class="cover-empty"
                id="coverPreview"
            ></div>

        <?php

        }

        ?>


        <div class="cover-text">

            <h2>Edit your profile</h2>

            <p>Make your Vlast profile yours.</p>

        </div>


        <!-- COVER PHOTO INPUT -->

        <label class="cover-camera">

            <i class="fa-solid fa-camera"></i>

            <input
                type="file"
                name="coverpic"
                id="coverInput"
                accept="image/*"
            >

        </label>


    </div>



    <!-- ====================================================
         PROFILE CONTENT
    ===================================================== -->

    <div class="profile-content">


        <!-- =================================================
             PROFILE PICTURE
        ================================================== -->

        <div class="profile-picture-wrapper">


            <div class="profile-picture">


                <?php

                if(!empty($row['picturepr'])){

                ?>

                    <img
                        src="image/<?php echo htmlspecialchars($row['picturepr']); ?>"
                        id="profilePreview"
                        alt="Profile picture"
                    >

                <?php

                }else{

                ?>

                    <i
                        class="fa-solid fa-user"
                        id="profileIcon"
                    ></i>


                    <img
                        id="profilePreview"
                        alt="Profile picture"
                        style="display:none;"
                    >

                <?php

                }

                ?>


            </div>


            <!-- PROFILE PHOTO INPUT -->

            <label class="profile-camera">

                <i class="fa-solid fa-camera"></i>

                <input
                    type="file"
                    name="picturerr"
                    id="profileInput"
                    accept="image/*"
                >

            </label>


        </div>



        <!-- =================================================
             FORM AREA
        ================================================== -->

        <div class="form-area">


            <?php

            if(isset($error)){

            ?>

                <div class="error">

                    <i class="fa-solid fa-circle-exclamation"></i>

                    <?php echo $error; ?>

                </div>

            <?php

            }

            ?>


            <!-- NAME -->

            <div class="name-row">


                <div class="input-group">

                    <label class="form-label">
                        First Name
                    </label>

                    <input
                        type="text"
                        name="first_name"
                        value="<?php echo htmlspecialchars($row['first_name']); ?>"
                        placeholder="First name"
                        required
                    >

                </div>


                <div class="input-group">

                    <label class="form-label">
                        Last Name
                    </label>

                    <input
                        type="text"
                        name="last_name"
                        value="<?php echo htmlspecialchars($row['last_name']); ?>"
                        placeholder="Last name"
                        required
                    >

                </div>


            </div>



            <!-- EMAIL -->

            <div class="input-group email-box">

                <label class="form-label">
                    Email Address
                </label>

                <input
                    type="email"
                    name="email"
                    value="<?php echo htmlspecialchars($row['email']); ?>"
                    required
                >

            </div>



            <!-- BIO -->

            <div class="input-group">

                <label class="form-label">
                    Bio
                </label>

                <textarea
                    name="bio"
                    class="bio-input"
                    placeholder="Tell people something about yourself..."
                ><?php echo htmlspecialchars($row['bio']); ?></textarea>

            </div>



            <!-- PHOTO NOTE -->

            <div class="photo-note">

                <i class="fa-solid fa-circle-info"></i>

                <span>
                    Select a new photo only if you want to change it.
                    If you don't select anything, your current photo
                    will remain unchanged.
                </span>

            </div>



            <!-- BUTTONS -->

            <div class="form-buttons">


                <a
                    href="user_profile.php"
                    class="cancel-btn"
                >

                    <i class="fa-solid fa-arrow-left"></i>

                    Back to Profile

                </a>


                <button
                    type="submit"
                    name="update"
                    class="update-btn"
                >

                    <i class="fa-solid fa-check"></i>

                    Update Profile

                </button>


            </div>


        </div>


    </div>


</form>


</main>



<!-- ========================================================
     MOBILE NAVIGATION
========================================================= -->

<nav class="mobile-nav">


    <a href="home.php">

        <i class="fa-solid fa-house"></i>

    </a>


    <a href="#">

        <i class="fa-solid fa-magnifying-glass"></i>

    </a>


    <a
        href="post.php"
        class="add"
    >

        <i class="fa-solid fa-plus"></i>

    </a>


    <a href="#">

        <i class="fa-solid fa-bell"></i>

    </a>


    <a
        href="user_profile.php"
        class="active"
    >

        <i class="fa-solid fa-user"></i>

    </a>


</nav>



<!-- ========================================================
     JAVASCRIPT
========================================================= -->

<script>


/* ============================================================
   PROFILE PHOTO PREVIEW
============================================================ */

const profileInput =
    document.getElementById("profileInput");

const profilePreview =
    document.getElementById("profilePreview");

const profileIcon =
    document.getElementById("profileIcon");


if(profileInput){

    profileInput.addEventListener(
        "change",
        function(){

            const file = this.files[0];

            if(!file){

                return;

            }


            const reader =
                new FileReader();


            reader.onload =
                function(event){

                    profilePreview.src =
                        event.target.result;

                    profilePreview.style.display =
                        "block";


                    if(profileIcon){

                        profileIcon.style.display =
                            "none";

                    }

                };


            reader.readAsDataURL(file);

        }
    );

}



/* ============================================================
   COVER PHOTO PREVIEW
============================================================ */

const coverInput =
    document.getElementById("coverInput");

const coverPreview =
    document.getElementById("coverPreview");


if(coverInput){

    coverInput.addEventListener(
        "change",
        function(){

            const file = this.files[0];

            if(!file){

                return;

            }


            const reader =
                new FileReader();


            reader.onload =
                function(event){

                    /*
                    If an existing cover image exists
                    */

                    if(
                        coverPreview.tagName === "IMG"
                    ){

                        coverPreview.src =
                            event.target.result;

                    }


                    /*
                    If there was no existing cover image
                    */

                    else{

                        coverPreview.style.backgroundImage =
                            "url('" +
                            event.target.result +
                            "')";

                        coverPreview.style.backgroundSize =
                            "cover";

                        coverPreview.style.backgroundPosition =
                            "center";

                    }

                };


            reader.readAsDataURL(file);

        }
    );

}

</script>


<?php

    }

}

?>


</body>

</html>
