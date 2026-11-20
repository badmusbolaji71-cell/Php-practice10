<?php
session_start();

$name = $lname = $email = $confirm_password = $password = "";
$age = "";
$passworderror = $fillall = $confirm_error = $emailerror = $ager = "";
$terms_error = "";

$servername = "localhost";
$username = "root";
$db_password = "";
$dbname = "social_media";

$connection = new mysqli($servername, $username, $db_password, $dbname);

if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

if (isset($_POST["submit"])) {

    $isvalid = true;

    $email = trim($_POST["email"] ?? "");
    $name = trim($_POST["name"] ?? "");
    $lname = trim($_POST["lname"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";
    $age = $_POST["age"] ?? "";

    $_SESSION['first_name'] = $name;

    if (
        empty($email) ||
        empty($name) ||
        empty($lname) ||
        empty($password) ||
        empty($confirm_password) ||
        $age === "" ||
        !isset($_POST['check'])
    ) {
        $fillall = "Please fill in all the required fields.";
        $isvalid = false;
    }

    if (empty($email)) {
        $emailerror = "Please enter your email.";
        $isvalid = false;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) && !empty($email)) {
        $emailerror = "Please enter a valid email address.";
        $isvalid = false;
    }

    if ($age === "" || !ctype_digit((string)$age) || (int)$age < 18 || (int)$age > 120) {
        $ager = "You must be 18 years or older.";
        $isvalid = false;
    } else {
        $age = (int)$age;
    }

    if (empty($password)) {
        $passworderror = "Please enter a password.";
        $isvalid = false;
    }

    if (empty($confirm_password)) {
        $confirm_error = "Please confirm your password.";
        $isvalid = false;
    } elseif ($confirm_password !== $password) {
        $confirm_error = "Passwords do not match.";
        $isvalid = false;
    }

    if (!isset($_POST['check'])) {
        $terms_error = "You must accept the terms and conditions.";
        $isvalid = false;
    }

    if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $stmt = $connection->prepare(
            "SELECT id FROM user_details WHERE email = ?"
        );

        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $emailerror = "This email is already registered. Please use another email.";
            $isvalid = false;
        }

        $stmt->close();
    }

    if ($isvalid) {

        $img = "";

        $stmt = $connection->prepare(
            "INSERT INTO user_details
            (picturepr, first_name, last_name, email, password, age)
            VALUES (?, ?, ?, ?, ?, ?)"
        );

        $stmt->bind_param(
            "sssssi",
            $img,
            $name,
            $lname,
            $email,
            $password,
            $age
        );

        if ($stmt->execute()) {

            $_SESSION['name'] = $name;

            header("location:login.php");
            exit();

        } else {
            $fillall = "Registration failed. Please try again.";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Vlast | Sign Up</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<link rel="preconnect" href="https://fonts.googleapis.com">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

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

.signup-wrapper{
    width:100%;
    max-width:1000px;
    min-height:650px;
    background:white;
    border-radius:28px;
    overflow:hidden;
    display:grid;
    grid-template-columns:45% 55%;
    box-shadow:0 25px 70px rgba(0,0,0,.12);
}

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

.logo{
    font-family:'Playfair Display',serif;
    font-size:40px;
    font-weight:700;
    letter-spacing:-2px;
    position:relative;
    z-index:2;
}

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
    font-family:'Playfair Display',serif;
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

.signup-section{
    display:flex;
    align-items:center;
    justify-content:center;
    padding:45px 55px;
    background:white;
}

.signup-box{
    width:100%;
    max-width:390px;
}

.signup-heading{
    margin-bottom:25px;
}

.signup-heading h2{
    font-size:30px;
    font-weight:700;
    letter-spacing:-1px;
    margin-bottom:7px;
}

.signup-heading p{
    color:#999;
    font-size:12px;
}

.general-error{
    display:flex;
    align-items:center;
    gap:9px;
    background:#fff3f3;
    border:1px solid #ffd6d6;
    color:#d93025;
    padding:10px 13px;
    border-radius:11px;
    font-size:10px;
    margin-bottom:18px;
}

.form-group{
    margin-bottom:14px;
}

.form-group label{
    display:block;
    font-size:11px;
    font-weight:600;
    color:#444;
    margin-bottom:7px;
}

.input-box{
    height:48px;
    width:100%;
    background:#f5f6f7;
    border:1px solid transparent;
    border-radius:13px;
    display:flex;
    align-items:center;
    padding:0 15px;
    gap:11px;
    transition:.25s ease;
}

.input-box:hover{
    background:#f1f2f3;
}

.input-box:focus-within{
    background:white;
    border-color:#111;
    box-shadow:0 0 0 3px rgba(17,17,17,.05);
}

.input-box.error{
    background:#fff8f8;
    border-color:#e63946;
}

.input-box > i{
    font-size:13px;
    color:#999;
    cursor:pointer;
}

.input-box input{
    width:100%;
    height:100%;
    border:none;
    outline:none;
    background:transparent;
    font-family:'Poppins',sans-serif;
    font-size:12px;
    color:#111;
}

.input-box input::placeholder{
    color:#aaa;
}

.field-error{
    display:flex;
    align-items:center;
    gap:5px;
    color:#e63946;
    font-size:9px;
    margin-top:5px;
}

.signup-btn{
    width:100%;
    height:52px;
    border:none;
    border-radius:13px;
    background:#111;
    color:white;
    font-family:'Poppins',sans-serif;
    font-size:12px;
    font-weight:600;
    cursor:pointer;
    margin-top:5px;
    transition:.25s ease;
}

.signup-btn:hover{
    background:#292929;
    transform:translateY(-2px);
    box-shadow:0 10px 25px rgba(0,0,0,.16);
}

.divider{
    display:flex;
    align-items:center;
    gap:12px;
    margin:20px 0;
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

.login-text{
    text-align:center;
    font-size:11px;
    color:#999;
}

.login-text a{
    color:#111;
    font-weight:600;
    text-decoration:none;
    margin-left:3px;
}

.login-text a:hover{
    text-decoration:underline;
}

.footer{
    text-align:center;
    margin-top:20px;
    font-size:9px;
    color:#bbb;
}

.signup-decoration{
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

.terms{
    display:flex;
    align-items:center;
    gap:7px;
    flex-wrap:wrap;
    font-size:10px;
    color:#555;
}

.terms input{
    accent-color:#111;
    width:14px;
    height:14px;
    cursor:pointer;
}

.terms a{
    color:#111;
    font-weight:600;
}

@media(max-width:850px){

    body{
        padding:20px;
    }

    .signup-wrapper{
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

    .signup-section{
        padding:40px 35px;
    }
}

@media(max-width:550px){

    body{
        padding:0;
        background:white;
    }

    .signup-wrapper{
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

    .signup-section{
        padding:35px 25px;
        align-items:flex-start;
    }

    .signup-heading{
        margin-bottom:27px;
    }

    .signup-heading h2{
        font-size:26px;
    }

    .form-group{
        margin-bottom:15px;
    }
}

@media(max-width:360px){

    .brand-section{
        min-height:240px;
        padding:25px 20px;
    }

    .brand-content h1{
        font-size:25px;
    }

    .signup-section{
        padding:30px 20px;
    }

    .input-box,
    .signup-btn{
        height:48px;
    }
}

</style>

</head>

<body>

<div class="signup-wrapper">

    <section class="brand-section">

        <div class="logo">
            Vlast
        </div>

        <div class="brand-content">

            <div class="small-text">
                Join Vlast
            </div>

            <h1>
                Your world.<br>
                Your people.
            </h1>

            <p>
                Create your account, connect with people
                and discover a community built around
                the things that matter to you.
            </p>

        </div>

        <div class="brand-features">

            <div class="brand-feature">
                <div class="feature-icon">
                    <i class="fa-solid fa-user-plus"></i>
                </div>
                <span>Create your Vlast account</span>
            </div>

            <div class="brand-feature">
                <div class="feature-icon">
                    <i class="fa-solid fa-users"></i>
                </div>
                <span>Connect with your community</span>
            </div>

            <div class="brand-feature">
                <div class="feature-icon">
                    <i class="fa-solid fa-compass"></i>
                </div>
                <span>Discover new people and ideas</span>
            </div>

        </div>

        <span class="signup-decoration dot-one"></span>
        <span class="signup-decoration dot-two"></span>
        <span class="signup-decoration dot-three"></span>

    </section>

    <section class="signup-section">

        <div class="signup-box">

            <div class="signup-heading">

                <h2>Create account</h2>

                <p>
                    Fill in your details to join Vlast.
                </p>

            </div>

            <?php if(!empty($fillall)){ ?>

                <div class="general-error">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span><?php echo htmlspecialchars($fillall); ?></span>
                </div>

            <?php } ?>

            <form method="post" id="form" enctype="multipart/form-data">

                <div class="form-group">

                    <label for="name">First name</label>

                    <div class="input-box">
                        <i class="fa-regular fa-user"></i>

                        <input
                            type="text"
                            name="name"
                            id="name"
                            placeholder="Enter your first name"
                            value="<?php echo htmlspecialchars($name); ?>"
                            required
                        >
                    </div>

                </div>

                <div class="form-group">

                    <label for="lname">Last name</label>

                    <div class="input-box">
                        <i class="fa-regular fa-user"></i>

                        <input
                            type="text"
                            name="lname"
                            id="lname"
                            placeholder="Enter your last name"
                            value="<?php echo htmlspecialchars($lname); ?>"
                            required
                        >
                    </div>

                </div>

                <div class="form-group">

                    <label for="email">Email address</label>

                    <div class="input-box">
                        <i class="fa-regular fa-envelope"></i>

                        <input
                            type="email"
                            name="email"
                            id="email"
                            placeholder="Enter your email"
                            value="<?php echo htmlspecialchars($email); ?>"
                            required
                        >
                    </div>

                    <?php if(!empty($emailerror)){ ?>
                        <div class="field-error">
                            <?php echo htmlspecialchars($emailerror); ?>
                        </div>
                    <?php } ?>

                </div>

                <div class="form-group">

                    <label for="password">Password</label>

                    <div class="input-box">

                        <i class="fa-solid fa-lock"></i>

                        <input
                            type="password"
                            name="password"
                            id="password"
                            placeholder="Create a password"
                            required
                        >

                        <i class="fa-regular fa-eye" id="togglePassword"></i>

                    </div>

                    <?php if(!empty($passworderror)){ ?>
                        <div class="field-error">
                            <?php echo htmlspecialchars($passworderror); ?>
                        </div>
                    <?php } ?>

                </div>

                <div class="form-group">

                    <label for="confirm_password">Confirm password</label>

                    <div class="input-box">

                        <i class="fa-solid fa-lock"></i>

                        <input
                            type="password"
                            name="confirm_password"
                            id="confirm_password"
                            placeholder="Confirm your password"
                            required
                        >

                        <i class="fa-regular fa-eye" id="toggleConfirmPassword"></i>

                    </div>

                    <?php if(!empty($confirm_error)){ ?>
                        <div class="field-error">
                            <?php echo htmlspecialchars($confirm_error); ?>
                        </div>
                    <?php } ?>

                </div>

                <div class="form-group">

                    <label for="age">Age</label>

                    <div class="input-box">

                        <i class="fa-solid fa-calendar"></i>

                        <input
                            type="number"
                            name="age"
                            id="age"
                            placeholder="Enter your age"
                            min="18"
                            max="120"
                            value="<?php echo htmlspecialchars((string)$age); ?>"
                            required
                        >

                    </div>

                    <?php if(!empty($ager)){ ?>
                        <div class="field-error">
                            <?php echo htmlspecialchars($ager); ?>
                        </div>
                    <?php } ?>

                </div>

                <div class="form-group">

                    <div class="terms">

                        <input
                            type="checkbox"
                            name="check"
                            id="check"
                            value="1"
                            required
                            <?php echo isset($_POST['check']) ? 'checked' : ''; ?>
                        >

                        <label for="check">
                            I agree with terms and policies
                            <a href="terms.php">Terms and Policies</a>
                        </label>

                    </div>

                    <?php if(!empty($terms_error)){ ?>
                        <div class="field-error">
                            <?php echo htmlspecialchars($terms_error); ?>
                        </div>
                    <?php } ?>

                </div>

                <input
                    type="submit"
                    value="Create account"
                    name="submit"
                    class="signup-btn"
                >

            </form>

            <div class="divider">
                OR
            </div>

            <div class="login-text">

                Already have an account?

                <a href="login.php">Sign in</a>

            </div>

            <div class="footer">
                Vlast © <?php echo date("Y"); ?>
            </div>

        </div>

    </section>

</div>

<script>

const passwordInput = document.getElementById("password");
const togglePassword = document.getElementById("togglePassword");

togglePassword.addEventListener("click", function(){

    if(passwordInput.type === "password"){

        passwordInput.type = "text";

        togglePassword.classList.remove("fa-eye");
        togglePassword.classList.add("fa-eye-slash");

    } else {

        passwordInput.type = "password";

        togglePassword.classList.remove("fa-eye-slash");
        togglePassword.classList.add("fa-eye");

    }

});

const confirmPasswordInput = document.getElementById("confirm_password");
const toggleConfirmPassword = document.getElementById("toggleConfirmPassword");

toggleConfirmPassword.addEventListener("click", function(){

    if(confirmPasswordInput.type === "password"){

        confirmPasswordInput.type = "text";

        toggleConfirmPassword.classList.remove("fa-eye");
        toggleConfirmPassword.classList.add("fa-eye-slash");

    } else {

        confirmPasswordInput.type = "password";

        toggleConfirmPassword.classList.remove("fa-eye-slash");
        toggleConfirmPassword.classList.add("fa-eye");

    }

});

</script>

</body>
</html>