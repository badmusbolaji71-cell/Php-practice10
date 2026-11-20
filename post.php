
<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "social_media";
$connection = new mysqli($servername,$username,$password , $dbname);


?>
  
<?php
if($_SERVER['REQUEST_METHOD'] == 'POST'){
 
    if(isset($_POST['submit'])){
        $isvalid = true;
           $text =  $_POST['text'];
          $img = $_FILES['file']['name'];
          $tmp = $_FILES['file']['tmp_name'];
          move_uploaded_file($tmp,"image/" .$img);
        //  $img = $_SESSION['image'];
          if(empty($text) && empty($img)){
            $isvalid = false;
           $error = "Post can't be empty";
          }
          if(empty($img)){
            echo "";
          }
          
          if($isvalid){
$poster =   mysqli_query($connection, "SELECT * FROM user_details WHERE email='{$_SESSION['email']}'");
$poster_details = mysqli_fetch_assoc($poster);
$poster_id=$poster_details['id'];
    $insert = mysqli_query($connection,"INSERT into user_post(text,picture,uploader_id) VALUES('$text ','$img','$poster_id')");
    // header("location:index.php");

        header("location:home.php");
          }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Create Post • Vlast</title>

    <link rel="stylesheet" href="general_css.css">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Playfair+Display:wght@700&display=swap"
          rel="stylesheet">

    <style>

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

        /* ================= HEADER ================= */

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
            box-shadow:0 2px 12px rgba(0,0,0,.08);
            z-index:1000;
        }

        #vlast-logo{
            font-family:'Playfair Display',serif;
            font-size:32px;
            font-weight:700;
            color:#111;
            letter-spacing:-1px;
        }

        .header-icons{
            display:flex;
            align-items:center;
            gap:28px;
        }

        .header-icons a{
            color:#555;
            text-decoration:none;
            font-size:20px;
            transition:.2s;
        }

        .header-icons a:hover{
            color:#111;
            transform:translateY(-2px);
        }

        .header-email{
            font-size:13px;
            color:#666;
            max-width:180px;
            overflow:hidden;
            text-overflow:ellipsis;
            white-space:nowrap;
        }

        /* ================= MAIN ================= */

        .page{
            min-height:100vh;
            padding:105px 20px 50px;
            display:flex;
            justify-content:center;
        }

        .post-wrapper{
            width:100%;
            max-width:720px;
        }

        /* ================= TOP ================= */

        .page-title{
            text-align:center;
            margin-bottom:25px;
        }

        .page-title h1{
            font-size:28px;
            font-weight:700;
        }

        .page-title p{
            color:#888;
            font-size:13px;
            margin-top:5px;
        }

        /* ================= POST CARD ================= */

        .post-card{
            background:white;
            border-radius:20px;
            box-shadow:0 3px 15px rgba(0,0,0,.07);
            overflow:hidden;
        }

        /* ================= USER ================= */

        .user-section{
            display:flex;
            align-items:center;
            gap:14px;
            padding:22px;
            border-bottom:1px solid #eee;
        }

        .user-avatar{
            width:52px;
            height:52px;
            border-radius:50%;
            background:#e4e5e6;
            display:flex;
            align-items:center;
            justify-content:center;
            flex-shrink:0;
        }

        .user-avatar i{
            font-size:22px;
            color:#999;
        }

        .user-details h3{
            font-size:14px;
            font-weight:600;
        }

        .user-details p{
            font-size:11px;
            color:#999;
            margin-top:3px;
        }

        /* ================= FORM ================= */

        form{
            padding:25px;
        }

        .description-label{
            display:block;
            font-size:13px;
            font-weight:600;
            margin-bottom:10px;
        }

        #description{
            width:100%;
            min-height:150px;
            resize:vertical;
            border:1px solid #e2e2e2;
            border-radius:15px;
            padding:16px;
            outline:none;
            font-family:'Poppins',sans-serif;
            font-size:14px;
            line-height:1.6;
            transition:.2s;
        }

        #description:focus{
            border-color:#777;
            box-shadow:0 0 0 3px rgba(0,0,0,.04);
        }

        #description::placeholder{
            color:#aaa;
        }

        /* ================= IMAGE AREA ================= */

        .image-section{
            margin-top:20px;
        }

        .image-label{
            display:flex;
            align-items:center;
            justify-content:space-between;
            margin-bottom:10px;
        }

        .image-label strong{
            font-size:13px;
        }

        .image-label span{
            font-size:11px;
            color:#999;
        }

        .image-upload{
            width:100%;
            min-height:220px;
            border:2px dashed #d8d8d8;
            border-radius:16px;
            display:flex;
            flex-direction:column;
            align-items:center;
            justify-content:center;
            cursor:pointer;
            transition:.2s;
            background:#fafafa;
            overflow:hidden;
            position:relative;
        }

        .image-upload:hover{
            background:#f5f5f5;
            border-color:#999;
        }

        .image-upload i{
            font-size:42px;
            color:#aaa;
            margin-bottom:12px;
        }

        .image-upload strong{
            font-size:13px;
            color:#555;
        }

        .image-upload span{
            font-size:11px;
            color:#aaa;
            margin-top:5px;
        }

        #file{
            display:none;
        }

        /* IMAGE PREVIEW */

        #preview{
            width:100%;
            height:auto;
            max-height:500px;
            object-fit:contain;
            display:none;
        }

        .remove-image{
            position:absolute;
            top:12px;
            right:12px;
            width:35px;
            height:35px;
            border:none;
            border-radius:50%;
            background:rgba(0,0,0,.7);
            color:white;
            cursor:pointer;
            display:none;
            align-items:center;
            justify-content:center;
            z-index:5;
        }

        /* ================= ERROR ================= */

        .error{
            margin:0 25px;
            padding:12px 15px;
            border-radius:10px;
            background:#fff0f0;
            color:#d33;
            font-size:12px;
        }

        /* ================= POST BUTTON ================= */

        .submit-area{
            padding:0 25px 25px;
            display:flex;
            gap:12px;
        }

        .back-btn{
            flex:1;
            border:1px solid #ddd;
            background:white;
            color:#555;
            text-decoration:none;
            border-radius:12px;
            height:50px;
            display:flex;
            justify-content:center;
            align-items:center;
            gap:8px;
            font-size:13px;
            transition:.2s;
        }

        .back-btn:hover{
            background:#f5f5f5;
        }

        .post-btn{
            flex:2;
            height:50px;
            border:none;
            border-radius:12px;
            background:#111;
            color:white;
            font-family:'Poppins',sans-serif;
            font-weight:600;
            cursor:pointer;
            font-size:13px;
            transition:.2s;
        }

        .post-btn:hover{
            background:#333;
            transform:translateY(-1px);
        }

        .post-btn i{
            margin-right:7px;
        }

        /* ================= TIPS ================= */

        .tips{
            margin-top:18px;
            background:white;
            border-radius:16px;
            padding:17px 20px;
            box-shadow:0 2px 10px rgba(0,0,0,.04);
        }

        .tips h3{
            font-size:13px;
            margin-bottom:10px;
        }

        .tips p{
            color:#888;
            font-size:11px;
            line-height:1.7;
        }

        .tips i{
            margin-right:7px;
            color:#555;
        }

        /* ================= MOBILE ================= */

        @media(max-width:600px){

            header{
                height:62px;
                padding:0 18px;
            }

            #vlast-logo{
                font-size:27px;
            }

            .header-icons{
                gap:17px;
            }

            .header-icons a{
                font-size:17px;
            }

            .header-icons a:nth-child(2),
            .header-icons a:nth-child(4),
            .header-icons a:nth-child(6){
                display:none;
            }

            .header-email{
                display:none;
            }

            .page{
                padding:85px 10px 30px;
            }

            .page-title h1{
                font-size:23px;
            }

            .post-card{
                border-radius:15px;
            }

            form{
                padding:18px;
            }

            .user-section{
                padding:18px;
            }

            .submit-area{
                padding:0 18px 18px;
            }

            .image-upload{
                min-height:190px;
            }

            #description{
                min-height:130px;
            }

        }

        @media(max-width:400px){

            .submit-area{
                flex-direction:column;
            }

            .back-btn,
            .post-btn{
                flex:none;
                width:100%;
            }

        }

    </style>
<script>
    window.VLAST_USER_ID = <?php echo (int)$_SESSION['id']; ?>;
</script>
<script src="theme.js"></script>
</head>

<body>


<!-- ================= HEADER ================= -->

<header>

    <div id="vlast-logo">
        Vlast
    </div>

    <div class="header-icons">

        <a href="home.php" title="Home">
            <i class="fa-solid fa-house"></i>
        </a>

        <a href="" title="Messages">
            <i class="fa-solid fa-message"></i>
        </a>

        <a href="" title="Search">
            <i class="fa-solid fa-magnifying-glass"></i>
        </a>

        <a href="" title="Notifications">
            <i class="fa-solid fa-bell"></i>
        </a>

        <a href="user_edit.php" title="Profile">
            <i class="fa-solid fa-user"></i>
        </a>

        <a href="post.php" title="Create Post">
            <i class="fa-solid fa-plus"></i>
        </a>

    </div>

    <div class="header-email">
        <?php echo $_SESSION['email']?>
    </div>

</header>


<!-- ================= MAIN ================= -->

<main class="page">

    <div class="post-wrapper">


        <!-- TITLE -->

        <div class="page-title">

            <h1>Create a Post</h1>

            <p>Share something with the Vlast community</p>

        </div>


        <!-- POST CARD -->

        <section class="post-card">


            <!-- USER -->

            <div class="user-section">

                <div class="user-avatar">

                    <i class = "fa-solid fa-user"></i>

                </div>

                <div class="user-details">

                    <h3>
                        <?php echo $_SESSION['email']?>
                    </h3>

                    <p>
                        Posting publicly on Vlast
                    </p>

                </div>

            </div>


            <!-- FORM -->

            <form method="post"
                  enctype="multipart/form-data"
                  id="form">


                <!-- DESCRIPTION -->

                <label class="description-label">
                    What's on your mind?
                </label>

                <textarea
                    name="text"
                    id="description"
                    placeholder="Share your thoughts, ideas, updates or anything interesting..."
                ></textarea>


                <!-- IMAGE -->

                <div class="image-section">

                    <div class="image-label">

                        <strong>
                            Add a picture
                        </strong>

                        <span>
                            Optional
                        </span>

                    </div>


                    <label for="file" class="image-upload">

                        <i class="fa-regular fa-image"></i>

                        <strong>
                            Click to select a picture
                        </strong>

                        <span>
                            JPG, JPEG, PNG or GIF
                        </span>

                        <img id="preview" alt="Image preview">

                        <button
                            type="button"
                            class="remove-image"
                            id="removeImage"
                            onclick="removeSelectedImage(event)"
                        >
                            <i class="fa-solid fa-xmark"></i>
                        </button>

                    </label>

                    <input
                        type="file"
                        name="file"
                        id="file"
                        accept="image/*"
                    >

                </div>


            </form>


            <!-- ERROR -->

            <?php

            if(isset($error)){

                echo '<div class="error">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        '.$error.'
                      </div>';

            }

            ?>


            <!-- BUTTONS -->

            <div class="submit-area">

                <a href="home.php" class="back-btn">

                    <i class="fa-solid fa-arrow-left"></i>

                    Back

                </a>


                <button
                    type="submit"
                    name="submit"
                    form="form"
                    class="post-btn"
                >

                    <i class="fa-solid fa-paper-plane"></i>

                    Publish Post

                </button>

            </div>


        </section>


        <!-- TIPS -->

        <section class="tips">

            <h3>
                <i class="fa-solid fa-lightbulb"></i>
                Posting tips
            </h3>

            <p>
                Share something meaningful with the Vlast community.
                You can add text, a picture, or both to your post.
            </p>

        </section>


    </div>

</main>


<script>

    /* ================= IMAGE PREVIEW ================= */

    const fileInput = document.getElementById("file");
    const preview = document.getElementById("preview");
    const removeImage = document.getElementById("removeImage");
    const uploadBox = document.querySelector(".image-upload");

    fileInput.addEventListener("change", function(){

        const file = this.files[0];

        if(file){

            const reader = new FileReader();

            reader.onload = function(event){

                preview.src = event.target.result;

                preview.style.display = "block";

                removeImage.style.display = "flex";

                uploadBox.querySelector("i").style.display = "none";

                uploadBox.querySelector("strong").style.display = "none";

                uploadBox.querySelector("span").style.display = "none";

            };

            reader.readAsDataURL(file);

        }

    });


    /* ================= REMOVE IMAGE ================= */

    function removeSelectedImage(event){

        event.preventDefault();

        event.stopPropagation();

        fileInput.value = "";

        preview.src = "";

        preview.style.display = "none";

        removeImage.style.display = "none";

        uploadBox.querySelector("i").style.display = "block";

        uploadBox.querySelector("strong").style.display = "block";

        uploadBox.querySelector("span").style.display = "block";

    }


    /* ================= FORM CHECK ================= */

    document.getElementById("form").addEventListener("submit", function(event){

        const text = document.getElementById("description").value.trim();

        const image = fileInput.files.length;

        if(text === "" && image === 0){

            event.preventDefault();

            alert("Post can't be empty.");

        }

    });

</script>

</body>
</html>
