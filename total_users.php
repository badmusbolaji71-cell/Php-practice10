<?php
// include("user_sign_uo.php");
session_start();

$email = $password = "";

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "social_media";

$connection = new mysqli($servername,$username,$password , $dbname);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

    <style>
        body{
            background-color: #0babf5;
            
        }
               table{
            width:100%;
            border:1px solid black;
            border-collapse:collapse;
            justify-content:center;
            align-items:center;
            align-self:center;
            text-align:center;
            justify-self:center;
            background-color: #444f92;
            border-radius:360px;
            color:white ;

        }
        tr,th,td{
             border:1px solid black;
               border-radius:10px;
            border-collapse:collapse;
        }
    </style>
</head>
<body>
    <center>
   
    <h4>users </h4>
  <table>
  <tr>
  <th>sn</th>
   <th>Image</th>
  <th>First name</th>
   <th>Last name</th>
  <th>email</th>
 
  <th>Suspend user</th>
 
  <th>Delete user </th>
  <th>unactive user</th>
    <th>Activate user</th>
    <th>View user</th>
    <th>Makes meme admin</th>
    <th>Verify user</th>
  </tr>
  <?php
    
    $select ="SELECT * FROM user_details";
    $select_query = mysqli_query($connection,$select);
  
    // $select = mysqli_query($connection,"SELECT * FROM user WHERE log_in_status >=3");
    $i = 1;
if(mysqli_num_rows($select_query) >0 ){
    
    while($row = mysqli_fetch_array($select_query)){
?>

    <tr>
       
    <td><?php echo $i++?></td>
           <td><img src="image/<?php echo $row["picturepr"]?>" alt="" width = "30px" height = "30px"></td>
    <td><?php echo $row["first_name"]?></td>
    <td><?php echo $row["last_name"]?></td>
    <td><?php echo $row["email"]?></td>

    <td><form  method="post">
        <input type="hidden" name="id" value = "<?php echo $row['id']?>">
        <button type="submit" name = "submit">Suspend</button>
    </td>
    <td>
        
        <input type="hidden" name="idd" value = "<?php echo $row['id']?>">
        <button type="submit" name = "delete">Delete</button>
   
    </td>
        <td>
         
        <input type="hidden" name="upd" value = "<?php echo $row['id']?>">
        <button type="submit" name = "update">Unactive user</button>
    
    </td>
            <td>
        
        <input type="hidden" name="up" value = "<?php echo $row['id']?>">
        <button type="submit" name = "updat">Activate user</button>
    
    </td>
         <td>
          <button type="" name = "updat">
         <input type="hidden" name="up" value = "<?php echo $row['id']?>">
              <a href="other_users_pp.php?id=<?php echo (int)$row['id']; ?>">
       View</button>

    </td>
           <td>
         <form method="post">
        <input type="hidden" name="up" value = "<?php echo $row['id']?>">
        <button type="submit" name = "updat">Memes Admin</button>
    </form>
       <td>
         <form method="post">
        <input type="hidden" name="up" value = "<?php echo $row['id']?>">
        <button type="submit" name = "updat">Verify</button>
         </td>
    </form>
    </td>
   
    </tr>
<?php 
    }
}
?>
    </table>
    </center>
</body>
</html> 
    </center>
</body>
</html> 