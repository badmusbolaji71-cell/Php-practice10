
<?php
// include("user_sign_uo.php");
session_start();

$email = $password = "";

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "social_media";

$connection = new mysqli($servername,$username,$password , $dbname);
$void = $no ="";
if(isset($_POST["submit"])){

    $email = $_POST["email"];

    $_SESSION['email'] = $email;

    //  $_SESSION['uploader_id'] = $user['id'];
    // $_SESSION['id'] = user_details.id;

    $password = $_POST["password"];

    if(empty($email) || empty($password)){
        $void = "All must be filled";
    }

    $login = mysqli_query($connection,"SELECT * FROM admin_details where email = '$email' AND password = '$password'");

    $login_uem= mysqli_query($connection,"SELECT * FROM user_details where email = '$email' AND password = '$password'");

    if(mysqli_num_rows($login) > 0 ){

        $user = mysqli_fetch_assoc($login);

        $_SESSION['email'] = $email;

        $id = (int)$user['id'];

        header("location:admin_homepage.php");

        exit();

    }

    elseif( mysqli_num_rows($login_uem) > 0 ){

        $user = mysqli_fetch_assoc($login_uem);
        $_SESSION['id'] = $user['id'];
        $id = (int)$user['id'];

        header("location:home.php");

        exit();

    }

    else{

        $no = "No record found";

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

    <title>Vlast | Sign In</title>


    <!-- FONT AWESOME -->

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
    >


    <!-- GOOGLE FONT -->

    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Playfair+Display:wght@700&display=swap"
        rel="stylesheet"
    >


<style>

/* =====================================================
   RESET
===================================================== */

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}


/* =====================================================
   BODY
===================================================== */

body{

    min-height:100vh;

    font-family:'Poppins', sans-serif;

    background:#f3f5f7;

    display:flex;

    align-items:center;

    justify-content:center;

    padding:30px;

    color:#111;

}


/* =====================================================
   LOGIN WRAPPER
===================================================== */

.login-wrapper{

    width:100%;

    max-width:1000px;

    min-height:600px;

    background:white;

    border-radius:28px;

    overflow:hidden;

    display:grid;

    grid-template-columns:45% 55%;

    box-shadow:
        0 25px 70px rgba(0,0,0,.12);

}


/* =====================================================
   LEFT BRAND SECTION
===================================================== */

.brand-section{

    background:#111;

    color:white;

    padding:55px;

    position:relative;

    overflow:hidden;

    display:flex;

    flex-direction:column;

    justify-content:space-between;

}


/* Decorative circles */

.brand-section::before{

    content:"";

    position:absolute;

    width:380px;

    height:380px;

    border:1px solid rgba(255,255,255,.09);

    border-radius:50%;

    right:-200px;

    top:-150px;

}


.brand-section::after{

    content:"";

    position:absolute;

    width:300px;

    height:300px;

    border:1px solid rgba(255,255,255,.08);

    border-radius:50%;

    left:-180px;

    bottom:-150px;

}


/* =====================================================
   LOGO
===================================================== */

.logo{

    font-family:'Playfair Display', serif;

    font-size:40px;

    font-weight:700;

    letter-spacing:-2px;

    position:relative;

    z-index:2;

}


/* =====================================================
   BRAND TEXT
===================================================== */

.brand-content{

    position:relative;

    z-index:2;

}


.brand-content .small-text{

    font-size:11px;

    text-transform:uppercase;

    letter-spacing:2px;

    color:#999;

    margin-bottom:15px;

}


.brand-content h1{

    font-family:'Playfair Display', serif;

    font-size:42px;

    line-height:1.12;

    margin-bottom:20px;

}


.brand-content p{

    max-width:340px;

    color:#aaa;

    font-size:13px;

    line-height:1.8;

}


/* =====================================================
   BRAND FEATURES
===================================================== */

.brand-features{

    position:relative;

    z-index:2;

    display:flex;

    flex-direction:column;

    gap:13px;

}


.brand-feature{

    display:flex;

    align-items:center;

    gap:12px;

    color:#bbb;

    font-size:11px;

}


.feature-icon{

    width:34px;

    height:34px;

    border-radius:10px;

    background:#202020;

    display:flex;

    align-items:center;

    justify-content:center;

}


.feature-icon i{

    font-size:13px;

    color:white;

}


/* =====================================================
   RIGHT LOGIN SECTION
===================================================== */

.login-section{

    display:flex;

    align-items:center;

    justify-content:center;

    padding:55px;

    background:white;

}


.login-box{

    width:100%;

    max-width:390px;

}


/* =====================================================
   LOGIN HEADING
===================================================== */

.login-heading{

    margin-bottom:32px;

}


.login-heading h2{

    font-size:30px;

    font-weight:700;

    letter-spacing:-1px;

    margin-bottom:7px;

}


.login-heading p{

    color:#999;

    font-size:12px;

}


/* =====================================================
   FORM
===================================================== */

.form-group{

    margin-bottom:20px;

}


.form-group label{

    display:block;

    font-size:11px;

    font-weight:600;

    color:#444;

    margin-bottom:8px;

}


/* =====================================================
   INPUT
===================================================== */

.input-box{

    height:52px;

    width:100%;

    background:#f5f6f7;

    border:1px solid transparent;

    border-radius:13px;

    display:flex;

    align-items:center;

    padding:0 16px;

    gap:12px;

    transition:.25s ease;

}


.input-box:hover{

    background:#f1f2f3;

}


.input-box:focus-within{

    background:white;

    border-color:#111;

    box-shadow:
        0 0 0 3px rgba(17,17,17,.05);

}


.input-box > i{

    font-size:14px;

    color:#999;

}


.input-box input{

    width:100%;

    height:100%;

    border:none;

    outline:none;

    background:transparent;

    font-family:'Poppins', sans-serif;

    font-size:12px;

    color:#111;

}


.input-box input::placeholder{

    color:#aaa;

}


/* =====================================================
   PASSWORD EYE
===================================================== */

#togglePassword{

    cursor:pointer;

    padding:5px;

    transition:.2s;

}


#togglePassword:hover{

    color:#111;

}


/* =====================================================
   LOGIN BUTTON
===================================================== */

.login-btn{

    width:100%;

    height:52px;

    border:none;

    border-radius:13px;

    background:#111;

    color:white;

    font-family:'Poppins', sans-serif;

    font-size:12px;

    font-weight:600;

    cursor:pointer;

    margin-top:5px;

    transition:.25s ease;

}


.login-btn:hover{

    background:#292929;

    transform:translateY(-2px);

    box-shadow:
        0 10px 25px rgba(0,0,0,.16);

}


.login-btn:active{

    transform:translateY(0);

}


.login-btn i{

    margin-left:8px;

}


/* =====================================================
   DIVIDER
===================================================== */

.divider{

    display:flex;

    align-items:center;

    gap:12px;

    margin:27px 0;

    color:#aaa;

    font-size:9px;

    letter-spacing:1px;

}


.divider::before,
.divider::after{

    content:"";

    flex:1;

    height:1px;

    background:#eee;

}


/* =====================================================
   SIGN UP
===================================================== */

.signup-text{

    text-align:center;

    font-size:11px;

    color:#999;

}


.signup-text a{

    color:#111;

    font-weight:600;

    text-decoration:none;

    margin-left:3px;

}


.signup-text a:hover{

    text-decoration:underline;

}


/* =====================================================
   FOOTER
===================================================== */

.footer{

    text-align:center;

    margin-top:28px;

    font-size:9px;

    color:#bbb;

}


/* =====================================================
   DESKTOP DECORATION
===================================================== */

.login-decoration{

    position:absolute;

    width:8px;

    height:8px;

    background:white;

    border-radius:50%;

    opacity:.5;

}


.dot-one{

    top:35%;

    left:15%;

}


.dot-two{

    top:62%;

    right:17%;

}


.dot-three{

    bottom:25%;

    left:25%;

}


/* =====================================================
   TABLET
===================================================== */

@media(max-width:850px){

    body{

        padding:20px;

    }


    .login-wrapper{

        max-width:520px;

        grid-template-columns:1fr;

    }


    .brand-section{

        min-height:310px;

        padding:35px;

    }


    .brand-content h1{

        font-size:32px;

    }


    .brand-features{

        display:none;

    }


    .login-section{

        padding:40px 35px;

    }

}


/* =====================================================
   MOBILE
===================================================== */

@media(max-width:550px){

    body{

        padding:0;

        background:white;

    }


    .login-wrapper{

        min-height:100vh;

        border-radius:0;

        box-shadow:none;

    }


    .brand-section{

        min-height:270px;

        padding:30px 25px;

    }


    .logo{

        font-size:32px;

    }


    .brand-content .small-text{

        font-size:9px;

        margin-bottom:10px;

    }


    .brand-content h1{

        font-size:28px;

        margin-bottom:12px;

    }


    .brand-content p{

        font-size:11px;

        line-height:1.7;

    }


    .login-section{

        padding:35px 25px;

        align-items:flex-start;

    }


    .login-heading{

        margin-bottom:27px;

    }


    .login-heading h2{

        font-size:26px;

    }

}


/* =====================================================
   SMALL PHONES
===================================================== */

@media(max-width:360px){

    .brand-section{

        min-height:240px;

        padding:25px 20px;

    }


    .brand-content h1{

        font-size:25px;

    }


    .login-section{

        padding:30px 20px;

    }


    .input-box,
    .login-btn{

        height:48px;

    }

}

</style>

</head>


<body>


<!-- =====================================================
     LOGIN WRAPPER
===================================================== -->

<div class="login-wrapper">


    <!-- =================================================
         BRAND SIDE
    ================================================== -->

    <section class="brand-section">


        <div class="logo">

            Vlast

        </div>


        <div class="brand-content">

            <div class="small-text">

                Welcome to Vlast

            </div>


            <h1>

                Your world.<br>

                Your people.

            </h1>


            <p>

                Connect, share and discover what matters
                to you. Sign in to continue your journey
                on Vlast.

            </p>

        </div>


        <div class="brand-features">


            <div class="brand-feature">

                <div class="feature-icon">

                    <i class="fa-solid fa-users"></i>

                </div>

                <span>

                    Connect with your community

                </span>

            </div>


            <div class="brand-feature">

                <div class="feature-icon">

                    <i class="fa-solid fa-compass"></i>

                </div>

                <span>

                    Discover new people and ideas

                </span>

            </div>


            <div class="brand-feature">

                <div class="feature-icon">

                    <i class="fa-solid fa-share-nodes"></i>

                </div>

                <span>

                    Share moments that matter

                </span>

            </div>


        </div>


        <!-- Decorative dots -->

        <span class="login-decoration dot-one"></span>

        <span class="login-decoration dot-two"></span>

        <span class="login-decoration dot-three"></span>


    </section>



    <!-- =================================================
         LOGIN SIDE
    ================================================== -->

    <section class="login-section">


        <div class="login-box">


            <div class="login-heading">

                <h2>

                    Sign in

                </h2>


                <p>

                    Enter your details to access your account.

                </p>

            </div>



            <!-- =================================================
                 LOGIN FORM
            ================================================== -->

            <form method="post" id="form">


                <!-- EMAIL -->

                <div class="form-group">

                    <label for="email">

                        Email address

                    </label>


                    <div class="input-box">

                        <i class="fa-regular fa-envelope"></i>


                        <input
                            type="email"
                            name="email"
                            id="email"
                            placeholder="Enter your email"
                            value="<?php echo htmlspecialchars($email); ?>"
                        >

                    </div>

                </div>



                <!-- PASSWORD -->

                <div class="form-group">

                    <label for="password">

                        Password

                    </label>


                    <div class="input-box">

                        <i class="fa-solid fa-lock"></i>


                        <input
                            type="password"
                            name="password"
                            id="password"
                            placeholder="Enter your password"
                        >


                        <i
                            class="fa-regular fa-eye"
                            id="togglePassword"
                        ></i>

                    </div>

                </div>



                <!-- SUBMIT -->

                <input
                    type="submit"
                    value="Sign in"
                    name="submit"
                    class="login-btn"
                >


            </form>

        <p><?php echo $void?></p>
        <p><?php echo $no?></p>

            <!-- DIVIDER -->

            <div class="divider">

                OR

            </div>



            <!-- SIGN UP -->

            <div class="signup-text">

                Don't have an account?

                <a href="user_sign_up.php">

                    Create account

                </a>

            </div>


            <div class="footer">

                Vlast © <?php echo date("Y"); ?>

            </div>


        </div>


    </section>


</div>



<script>

/* =====================================================
   SHOW / HIDE PASSWORD
===================================================== */

const passwordInput =
    document.getElementById("password");

const togglePassword =
    document.getElementById("togglePassword");


togglePassword.addEventListener("click", function(){

    if(passwordInput.type === "password"){

        passwordInput.type = "text";

        togglePassword.classList.remove(
            "fa-eye"
        );

        togglePassword.classList.add(
            "fa-eye-slash"
        );

    }

    else{

        passwordInput.type = "password";

        togglePassword.classList.remove(
            "fa-eye-slash"
        );

        togglePassword.classList.add(
            "fa-eye"
        );

    }

});

</script>


</body>

</html>
