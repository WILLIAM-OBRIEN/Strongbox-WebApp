<?php
require __DIR__.'/vendor/autoload.php';
use phpseclib\Crypt\RSA;
if(isset($_REQUEST['submit']))
{
	$Username=$_REQUEST['username'];
	$Password=$_REQUEST['user_password'];
	$Name=$_REQUEST['user_name'];
	$Email=$_REQUEST['user_email'];
	//generate the private key for user
	$rsa = new RSA();
	$keys = $rsa->createKey(2048);
	$Privatekey = $keys['privatekey'];
	//connect to online mysql db
	$conn = new PDO("mysql:host=35.205.202.112;dbname=Users","root","mtD{];ttcY^{9@>`");
	$register_user = $conn->prepare("insert into users (username, user_password, user_name, user_email, user_privatekey) values ('".$Username."','".$Password."','".$Name."','".$Email."','".$Privatekey."');");
	$register_user->execute();
	#Go to the login page
	echo('<script>window.location="login.php"</script>');
}
?>
<html>
<head>
<meta charset="utf-8">
<title>Registration</title>
</head>
<body>
<center>
<form method="post" action="registration.php">
	<input type="text" name="username" placeholder="Enter a Username">
	<br>
	<input type="password" name="user_password" placeholder="Enter a Password">
	<br>
	<input type="text" name="user_name" placeholder="Enter your name">
	<br>
	<input type="text" name="user_email" placeholder="Enter your email">
	<br>
	<input type="submit" name="submit" value="Register">
</form>
</center>
</body>
</html>
