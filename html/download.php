<?php
	require __DIR__.'/vendor/autoload.php';
	use phpseclib\Crypt\RSA;
	session_start();
	if(!isset($_SESSION['logged']))
	{
			echo('<script>window.location="login.php"</script>');
	}//prevents users from accessing page directly
	$username = $_SESSION['logged'];
	$conn = new PDO("mysql:host=35.205.202.112;dbname=Users","root","mtD{];ttcY^{9@>`");//connect to online database
	$id = isset($_GET['id'])? $_GET['id'] : "";
	$files = $conn->prepare("select * from sharedfilestorage where f_id=?");
	$files->bindParam(1,$id);
	$files->execute();
	$file_row = $files->fetch();//places file contents into single row for download
	//-----begin key decryption process-----//
	$fetch_privatekey = $conn->prepare("select user_privatekey from users where username='".$username."'");//gets private key linked to user (in encrypted format)
	$fetch_privatekey->execute();
	$key_row = $fetch_privatekey->fetch();
	$encrypted_privatekey = $key_row['user_privatekey'];
	//$password = $key_row['user_password'];//get user password to decrypt user private key
	$password = $_SESSION['password'];
	$user_privatekey = openssl_decrypt($encrypted_privatekey, 'aes-128-cbc' , $password, OPENSSL_RAW_DATA ,"1234567812345678");//decrypts private key linked to user using their password
	$rsa = new RSA();
	$rsa->loadKey($user_privatekey);
	//decrypt aes key
	$encrypted_aes_key = $file_row['aes_key'];
	$aes_key = $rsa->decrypt($encrypted_aes_key);//decrypt file aes key using users (now decrypted) private key
	//decrypt blowfish key
	$encrypted_bf_key = $file_row['bf_key'];
        $bf_key = $rsa->decrypt($encrypted_bf_key);
	//-----end key decryption process-----//
	//-----begin file decryption process-----//
	//---Blowfish decrypt---//
	$data = $file_row['file_data'];
	$method_2 = 'blowfish';
	$bf_iv = $file_row['bf_iv'];
	$data = openssl_decrypt($data, $method_2, $bf_key, OPENSSL_RAW_DATA, $bf_iv);
	//---AES decrypt---//
	$aes_iv = $file_row['aes_iv'];//gets file iv for decryption
	$method_1 = 'aes-256-cbc';
	$file = openssl_decrypt($data, $method_1, $aes_key, OPENSSL_RAW_DATA, $aes_iv);//decrypts aes encyption part of file
	$file = gzdecode($file);
	echo $file;//prints (now decrypted) out entire file contents
	//-----end file decryption process-----//
?>
