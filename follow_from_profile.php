<?php
session_start();
$userId = $_GET['id'];
$me = $_SESSION['id'];
// $_SESSION['id']
echo $userId;
// echo $_SESSION['suc'];
echo "<br/>";
echo $me;