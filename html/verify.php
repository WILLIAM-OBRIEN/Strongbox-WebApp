<?php
//php file that will parse the verification link in order to 'activate' the users account
$conn = new PDO("mysql:host=35.205.202.112;dbname=Users","root","mtD{];ttcY^{9@>`");

if(isset($_GET['email']) && !empty($_GET['email']) AND isset($_GET['hash']) && !empty($_GET['hash']))
{
	$email = $_GET['email'];
	$verify_hash = $_GET['hash'];

	$verify_chk = $conn->prepare("select * from users where user_email='".$email."' AND verify_hash='".$verify_hash."'");
	$verify_chk->execute();
	$row = $verify_chk->fetch();
	//if the link has the wrong verification hash or email, will not verify the account if these are not found in the database
	if(empty($row))
	{
		echo('<script>alert("Error with verifying account!");</script>');
	}
	else
	{
		$validate = $conn->prepare("update users set active=1 where user_email='".$email."' AND verify_hash='".$verify_hash."' AND active=0");
	        $validate->execute();
		echo "<div>Your account has been activated, you can now ";
		echo "<a href='login.php'>login here</a></div>";
	}
}
else
{
	echo('<script>alert("Error with verifying account!");</script>');
}


?>
