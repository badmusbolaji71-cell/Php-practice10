<?php
session_start();

$connection = new mysqli("localhost", "root", "", "social_media");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin actions</title>
</head>
<body>
    <button>
        <i fa-solid fa-square-plus></i>
        <a href=""><?php
$se = "SELECT count(*) as total from user_post";
$result = mysqli_query($connection,$se);
$row = mysqli_fetch_assoc($result);

echo $row['total'];

?>Total Posts</a>
    </button>
            <i fa-solid fa-users></i>
        <a href="total_users.php"><?php
$ses = "SELECT count(*) as total from user_details";
$results = mysqli_query($connection,$ses);
$rows = mysqli_fetch_assoc($results);

echo $rows['total'];

?>Total Users</a>
    <button>

    </button>
</body>
</html>
