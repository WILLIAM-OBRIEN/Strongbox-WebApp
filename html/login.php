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
	if(empty($row) || $row['active']== 0)
	{
		echo('<script>alert("Incorrect username/password!");</script>');
	}
	else
	{
		//creates a session of the user for their username and password, the password will be used to decrypt uploaded files.
		$_SESSION['logged']=$username;
		$_SESSION['password']=$password;
		echo('<script>window.location="home.php"</script>');
	}
}
?>
<html>
<head>
<link rel="shortcut icon" type="image/png" href="image.png">
<link rel="stylesheet" type="text/css" href="style.css">
<meta charset="utf-8">
<title>Login | Strongbox</title>
</head>
<body>
<div class="login-page">
<div class="form">
<center>
<form method="post" action="login.php">
<img src=logofinal.png width="150" height="125"></img>
<p><h2>Welcome to Strongbox!</h2></p><p></p>
    <input name="username" type="text" placeholder="Enter your username...">
    <br>
    <input name="user_password"type="password" placeholder="Enter your password...">
    <br>
   <input type="submit" name="submit" value="Login">
    <br>
   Need an account? <a href="registration.php">Register here</a>

</form>
</center>
</div></div>
</body>
</html>
