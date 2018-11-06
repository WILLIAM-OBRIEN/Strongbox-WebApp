<?php
session_start();

if(isset($_SESSION['logged']))
{
	echo("<script>window.location='home.php'</script>");
}

if(isset($_REQUEST['submit']))
{
	$username=$_REQUEST['UserUsername'];
	$password=$_REQUEST['UserPassword'];

	$con=mysqli_connect("35.205.202.112", "root", "mtD{];ttcY^{9@>`", "Users");
	$query=mysqli_query($con, "SELECT * FROM users WHERE UserUsername='".$username."' AND UserPassword='".$password."'");	
	$row=mysqli_fetch_array($query, MYSQLI_NUM);
	if(empty($row))
	{
		echo('<script>alert("Incorrect username/password!");</script>');
	}
	else
	{
		$_SESSION['logged']="OK";
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

    <input name="UserUsername" type="text" placeholder="Enter your username...">
    <br>
    <input name="UserPassword" type="password" placeholder="Enter your password...">
    <br>
    <input type="submit" name="submit" value="Login">

</form>
</center>
</body>
</html>
