<?php
	require __DIR__.'/vendor/autoload.php';
	use phpseclib\Crypt\RSA;
	//prevents users from accessing page directly
	session_start();
	if(!isset($_SESSION['logged']))
	{
		echo('<script>window.location="login.php"</script>');
	}
	$username = $_SESSION['logged'];
	//connect to online database
	$conn = new PDO("mysql:host=35.205.202.112;dbname=Users","root","mtD{];ttcY^{9@>`");
	//allows for uploaded files to be downloaded unencrypted
	$id = isset($_GET['id'])? $_GET['id'] : "";
	$files = $conn->prepare("select * from filestorage where f_id=?");
	$files->bindParam(1,$id);
	$files->execute();
	//places file contents of single row for download
	$row = $files->fetch();
	//begin key decryption process
	$e_key = $row['file_key'];
	$user_privkey = $conn->prepare("select user_privatekey from users where username='".$username."'");
	$user_privkey->execute();
	$key_row = $user_privkey->fetch();
	$privatekey = $key_row['user_privatekey'];
	$p_key = openssl_decrypt($privatekey, 'aes-128-cbc' , 'password', OPENSSL_RAW_DATA ,"1234567812345678");
	$rsa = new RSA();
	$rsa->loadKey($p_key);
	$key = $rsa->decrypt($e_key);
	//begins decryption process for uploaded file
	$iv = $row['file_iv'];
	//$key = $row['file_key'];
	$method = 'aes-256-cbc';
	$file = openssl_decrypt($row['file_data'], $method, $key, OPENSSL_RAW_DATA, $iv);
	//prints out entire contents
	echo $file;
?>
