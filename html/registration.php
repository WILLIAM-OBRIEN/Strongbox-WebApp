
<?php
require __DIR__.'/vendor/autoload.php';
use phpseclib\Crypt\RSA;
if(isset($_REQUEST['submit']))
{
	$Username=$_REQUEST['username'];
	$Password=$_REQUEST['user_password'];
	$Name=$_REQUEST['user_name'];
	$Email=$_REQUEST['user_email'];
	//Check to see if username already exists
	$conn = new PDO("mysql:host=35.205.202.112;dbname=Users","root","mtD{];ttcY^{9@>`");
	$username_check_statement = $conn->prepare("select * from users where username='".$Username."'");
        $username_check_statement->execute();
        $username_row = $username_check_statement->fetch();
        if(!empty($username_row ))
        {
                echo('<script>alert("Username is already taken!");</script>');
        }
	else
	{
		//generate the private key for user
		$rsa = new RSA();
		$keys = $rsa->createKey(4096);
		$Privatekey = $keys['privatekey'];
		$Publickey = $keys['publickey'];
		$password_hash = hash('sha512', $Password, true);//generate key for encryption using given password
		$E_privatekey = openssl_encrypt($Privatekey, 'aes-128-cbc' , $Password, OPENSSL_RAW_DATA , "1234567812345678");

		//insert statement for sql database
		$register_user = $conn->prepare("insert into users (username, password_hash, user_name, user_email, user_privatekey, user_publickey) values (?,?,?,?,?,?);");
		$register_user->bindParam(1,$Username);
		$register_user->bindParam(2,$password_hash);
		$register_user->bindParam(3,$Name);
		$register_user->bindParam(4,$Email);
		$register_user->bindParam(5,$E_privatekey);
		$register_user->bindParam(6,$Publickey);
		$register_user->execute();
		//Go to the login page
		echo('<script>window.location="login.php"</script>');
	}
}
?>
<html>
<head>
<meta charset="utf-8">
<title>Registration | Strongbox</title>
</head>
<body>
<center>
<form method="post" action="registration.php">
	<input type="text" name="username" placeholder="Enter a Username">
	<br>
	<input type="password" required name="user_password" placeholder="Enter a Password">
	<br>
	<input type="text" required name="user_name" placeholder="Enter your Name">
	<br>
	<input type="text" required name="user_email" placeholder="Enter your Email">
	<br>
	<input type="submit" required name="submit" value="Register">
</form>
<a href="login.php">Login Page</a>
</center>
</body>
</html>
