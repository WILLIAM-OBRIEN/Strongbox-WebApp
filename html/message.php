<head><title>Home | Strongbox</title>
<link rel="shortcut icon" type="image/png" href="image.png">
<link rel="stylesheet" type="text/css" href="style.css">
</head>
<?php
require __DIR__.'/vendor/autoload.php';
use phpseclib\Crypt\RSA;
session_start();
if(!isset($_SESSION['logged']))
{
        echo('<script>window.location="login.php"</script>');
}
//connect to online sql database
$conn = new PDO("mysql:host=35.205.202.112;dbname=Users","root","mtD{];ttcY^{9@>`");
?>
<form method="post" action="send_message.php" enctype="multipart/form-data">
<?php
$users = $conn->prepare("select * from users where u_id !=".$user_id."");
$users->execute();
while($row = $users->fetch())
{
	echo "<label class='container'>";
        echo "<input name='userID[]' type='checkbox' value='".$row['u_id']."'>".$row['username']."<span class='checkmark'></span>";
        echo "</label>";
}

?>
<input type="text" name="message" required></input>
<input type="submit" name="send" value="Send"></input>
</form>
