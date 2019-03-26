
<?php
require __DIR__.'/vendor/autoload.php';
use phpseclib\Crypt\RSA;
if(isset($_REQUEST['submit']))
{
	$Username=$_REQUEST['username'];
	$Password=$_REQUEST['user_password'];
	$Name=$_REQUEST['user_name'];
	$Email=$_REQUEST['user_email'];
	$verify_hash = md5(rand(0,1000));//generates hash to verify email account for activation
	$active = 0;
	//Check to see if username already exists
	$conn = new PDO("mysql:host=35.205.202.112;dbname=Users","root","mtD{];ttcY^{9@>`");
	$username_check_statement = $conn->prepare("select * from users where username='".$Username."'");
        $username_check_statement->execute();
        $username_row = $username_check_statement->fetch();
        if(!empty($username_row ))
        {
                echo('<script>alert("Username is already taken!");</script>');
        }//should probably do something similar like this with email addresses
	else
	{
		//makes sure the basic format is an email address
		if(!preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/", $Email))
		{
			 echo('<script>alert("Invalid email!");</script>');
		}
		else
		{
			//constructs the email that is going to be sent to the given email address
			//this email will be used for verifying account
			$subject = 'Signup | Verification';
			$message = '
			Thanks for signing up!
			Your account has been created, you can login with the following credentials after you have activated your account by pressing the url below.
			------------------------
			Username: '.$Username.'
			Password: '.$Password.'
			------------------------

			Please click this link to activate your account:
			http://www.strongboxencryption.com/verify.php?email='.$Email.'&hash='.$verify_hash.'';
			exec(" echo '".$message."' | mail -s '".$subject."' ".$Email."");//sends email

			//generate the private key for user
			$rsa = new RSA();
			$keys = $rsa->createKey(4096);
			$Privatekey = $keys['privatekey'];
			$Publickey = $keys['publickey'];
			$password_hash = hash('sha512', $Password, true);//generate key for encryption using given password
			$E_privatekey = openssl_encrypt($Privatekey, 'aes-128-cbc' , $Password, OPENSSL_RAW_DATA , "1234567812345678");

			//insert statement for sql database
			$register_user = $conn->prepare("insert into users (username, password_hash, user_name, user_email, user_privatekey, user_publickey, verify_hash, active) values (?,?,?,?,?,?,?,?);");
			$register_user->bindParam(1,$Username);
			$register_user->bindParam(2,$password_hash);
			$register_user->bindParam(3,$Name);
			$register_user->bindParam(4,$Email);
			$register_user->bindParam(5,$E_privatekey);
			$register_user->bindParam(6,$Publickey);
			$register_user->bindParam(7,$verify_hash);
			$register_user->bindParam(8,$active);
			$register_user->execute();
			//Go to the login page
			echo('<script>window.location="thanks.php"</script>');
		}
	}
}
?>
<html>
<head>
<link rel="stylesheet" type="text/css" href="style.css">
<meta charset="utf-8">
<title>Registration | Strongbox</title>
<link rel="shortcut icon" type="image/png" href="image.png">
</head>
<body>
<div class="login-page">
<div class="form">
<center>
<form method="post" action="registration.php">
	<input type="text" required name="username" placeholder="Enter a Username">
	<br>
	<input type="password" pattern=".{0}|.{7,}" required title="7 characters minimum" name="user_password" placeholder="Enter a Password">
	<br>
	<input type="text" required name="user_name" placeholder="Enter your Name">
	<br>
	<input type="text" required name="user_email" placeholder="Enter your Email">
	<br>
	<input type="submit" required name="submit" value="Register">
</form>
Have an account? <a href="login.php">Login here</a>
</center>
</div></div>
</body>
</html>
