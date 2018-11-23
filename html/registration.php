
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
	$keys = $rsa->createKey(4096);
	$Privatekey = $keys['privatekey'];
	$Publickey = $keys['publickey'];
	$password_hash = hash('sha512', $Password, true);//generate key for encryption using given password
	$E_privatekey = openssl_encrypt($Privatekey, 'aes-128-cbc' , $Password, OPENSSL_RAW_DATA , "1234567812345678");

	//connect to online mysql db
	$conn = new PDO("mysql:host=35.205.202.112;dbname=Users","root","mtD{];ttcY^{9@>`");
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
	/*$rsa = new RSA();
        $keys = $rsa->createKey(4096);
        $Privatekey = $keys['privatekey'];
        $Publickey = $keys['publickey'];
	echo $Privatekey;
	echo "<br>";
	$text = openssl_encrypt($Privatekey, 'aes-128-cbc' , $password, OPENSSL_RAW_DATA ,"1234567812345678");
	$text2 = openssl_decrypt($text, 'aes-128-cbc' , $password, OPENSSL_RAW_DATA ,"1234567812345678");
	echo $text2;*/
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
