<?php
require __DIR__.'/vendor/autoload.php';
use phpseclib\Crypt\RSA;
session_start();
if(!isset($_SESSION['logged']))
{
        echo('<script>window.location="login.php"</script>');
}

$user_list = $_POST['userID'];
$message = $_POST['message'];
if(isset($_POST['send']))
{
        foreach($user_list as $u_list)
        {
		sendmessage($message, $u_list);
        }
}

function sendmessage($message, $receiver)
{
	//connect to online sql database
	$conn = new PDO("mysql:host=35.205.202.112;dbname=Users","root","mtD{];ttcY^{9@>`");
	//if upload button clicked with file attached to check for this) attempts to upload to db
	$username = $_SESSION['logged'];
	$user_chk = $conn->prepare("select u_id from users where username='".$username."'");
	$user_chk->execute();
	$user_row = $user_chk->fetch();
	$user_id = $user_row['u_id'];


	//-----Encrypt message process-----//
	$fetch_publickey = $conn->prepare("select user_publickey from users where u_id='".$receiver."'");
	$fetch_publickey->execute();
	$key_row = $fetch_publickey->fetch();
	$publickey = $key_row['user_publickey'];
	$rsa = new RSA();
	$rsa->loadKey($publickey);

	//--AES Encryption--//
        $aes_key_password = random_bytes(128);
        $method_1 = 'aes-256-cbc';//choose method of encryption; AES 256-bit
        $aes_key = hash('sha512', $aes_key_password, true);//generate key for encryption using given password
        $aes_iv_length = openssl_cipher_iv_length($method_1);//decides initialization vector length
        $aes_iv = openssl_random_pseudo_bytes($aes_iv_length);//generates an initialization vector for every encrypted file to prevent ciphertext duplicates
        $message = openssl_encrypt($message, $method_1, $aes_key, OPENSSL_RAW_DATA, $aes_iv);
	$encrypted_aes_key = $rsa->encrypt($aes_key);//encrypts the AES key used
	//-----End encrypt message process-----//

	//inserting encrypted message into sql database
	$date = date("F j, g:i a");//date of file upload (in a readable format)
        $upload_file = $conn->prepare("insert into messages(sender, receiver, message, time_sent, date_recorded, seen, aes_iv, aes_key) values(?,?,?,?, CURRENT_TIMESTAMP, 0,?,?)");
        $upload_file->bindParam(1,$user_id);
        $upload_file->bindParam(2,$receiver);
        $upload_file->bindParam(3,$message);
	$upload_file->bindParam(4,$date);
	$upload_file->bindParam(5,$aes_iv);
	$upload_file->bindParam(6,$encrypted_aes_key);
        $upload_file->execute();
}
if($_SERVER['REQUEST_METHOD'] == 'POST')
{
        header("Location: home.php");
}
?>
