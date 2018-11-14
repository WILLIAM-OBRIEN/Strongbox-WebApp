<?php
if(isset($_REQUEST['submit']))
{
	$Username=$_REQUEST['UserUsername'];
	$Password=$_REQUEST['UserPassword'];
	$Name=$_REQUEST['UserName'];
	$Email=$_REQUEST['UserEmail'];

	$con=mysqli_connect("35.205.202.112", "root", "mtD{];ttcY^{9@>`", "Users");
	$query=mysqli_query($con, "INSERT INTO users (UserUsername, UserPassword, UserName, UserEmail) VALUES ('".$Username."','".$Password."','".$Name."','".$Email."');") or die("Error in registration!!");

	#Go to the login page
	echo('<script>window.location="login.php"</script>');
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Registration</title>
</head>

<body>
<center>
<form method="post" action="registration.php">

    <input type="text" name="UserUsername" placeholder="Enter a Username">
    <br>
    <input type="password" name="UserPassword" placeholder="Enter a Password">
    <br>
    <input type="text" name="UserName" placeholder="Enter your name">
    <br>
    <input type="text" name="UserEmail" placeholder="Enter your email">
    <br>
    <input type="submit" name="submit" value="Register">

</form>
</center>
</body>
</html>
