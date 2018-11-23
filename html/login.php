<?php
session_start();

if(isset($_SESSION['logged']))
{
	echo("<script>window.location='home.php'</script>");
}

if(isset($_REQUEST['submit']))
{
	$username=$_REQUEST['username'];
	$password=$_REQUEST['user_password'];
	$password_hash = hash('sha512', $password, true);

	$conn = new PDO("mysql:host=35.205.202.112;dbname=Users","root","mtD{];ttcY^{9@>`");
	$login_statement = $conn->prepare("select * from users where username='".$username."' and password_hash ='".$password_hash."'");
	$login_statement->execute();
	$row = $login_statement->fetch();
	if(empty($row))
	{
		echo('<script>alert("Incorrect username/password!");</script>');
	}
	else
	{
		$_SESSION['logged']=$username;
		$_SESSION['password']=$password;
		echo('<script>window.location="home.php"</script>');
	}
}
?>

<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Login</title>
</head>

<body>
<center>
<form method="post" action="login.php">

    <input name="username" type="text" placeholder="Enter your username...">
    <br>
    <input name="user_password"type="password" placeholder="Enter your password...">
    <br>
    <input type="submit" name="submit" value="Login">
    <br>
    <a href="registration.php">Register</a>

</form>
</center>
</body>
</html>
