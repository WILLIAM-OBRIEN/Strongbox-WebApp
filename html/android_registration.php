<?php
	require __DIR__.'/vendor/autoload.php';
	use phpseclib\Crypt\RSA;

	$conn = new PDO("mysql:host=35.205.202.112;dbname=Users","root","mtD{];ttcY^{9@>`");

	$Name = $_POST["name"];
	$Email = $_POST["email"];
	$Username = $_POST["username"];
	$Password = $_POST["password"];

	//generate the private key for user
	$rsa = new RSA();
	$keys = $rsa->createKey(4096);
	$Privatekey = $keys['privatekey'];
	$Publickey = $keys['publickey'];
	$password_hash = hash('sha512', $Password, true);//generate key for encryption using given password
	$E_privatekey = openssl_encrypt($Privatekey, 'aes-128-cbc' , $Password, OPENSSL_RAW_DATA , "1234567812345678");

	//insert statement for sql database
	$register_user = $conn->prepare("insert into users (username, password_hash, user_name, user_email, user_privatekey, user_publickey)values (?,?,?,?,?,?);");
	$register_user->bindParam(1,$Username);
	$register_user->bindParam(2,$password_hash);
	$register_user->bindParam(3,$Name);
	$register_user->bindParam(4,$Email);
	$register_user->bindParam(5,$E_privatekey);
	$register_user->bindParam(6,$Publickey);
	$register_user->execute();

    $response = array();
    $response["success"] = true;

    echo json_encode($response);
?>
