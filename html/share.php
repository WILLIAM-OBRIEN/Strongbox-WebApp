<head>
<title>Share Files | Strongbox</title>
</head>
<body>
<?php
session_start();
if(!isset($_SESSION['logged']))
{
	echo('<script>window.location="login.php"</script>');
}
$conn = new PDO("mysql:host=35.205.202.112;dbname=Users","root","mtD{];ttcY^{9@>`");
//pass in username & user id
$username = $_SESSION['logged'];
$user_chk = $conn->prepare("select u_id from users where username='".$username."'");
$user_chk->execute();
$user_row = $user_chk->fetch();
$user_id = $user_row['u_id'];

$files = $conn->prepare("select * from filestorage where u_id='".$user_id."'");
$files->execute();
//creates hyperlink for downloading files stored on database which are decrypted using stored key (will be changed to localised method in future)
echo "<form action= 'shareinsert.php' method= 'get'>";
while($row = $files->fetch())
{
	echo "<div>";
	echo "<input name='fileID[]' type='checkbox' value='".$row['f_id']."'>".$row['file_name']."";
	echo "</div>";
}
?>
<input type="submit" value="submit"/>
</form>
</body>
