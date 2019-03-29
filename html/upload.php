<?php
require __DIR__.'/vendor/autoload.php';
use phpseclib\Crypt\RSA;
session_start();
if(!isset($_SESSION['logged']))
{
        echo('<script>window.location="login.php"</script>');
}
//connect to online sql database
$conn = new PDO("mysql:host=35.205.202.112;dbname=Users","root","mtD{];ttcY^{9@>`");
//if upload button clicked with file attached to check for this) attempts to upload to db
$username = $_SESSION['logged'];
$user_chk = $conn->prepare("select u_id from users where username='".$username."'");
$user_chk->execute();
$user_row = $user_chk->fetch();
$user_id = $user_row['u_id'];
if(isset($_POST['upload']))
{
	//pass in username
	$username = $_SESSION['logged'];
	//passing file attributes from form
	$name = $_FILES['file_upload']['name'];
	$type = $_FILES['file_upload']['type'];
	$file_data = $_FILES['file_upload']['tmp_name'];
	$file_contents = file_get_contents($file_data);
	$file_contents = gzencode($file_contents,9);//compresses file with maximum level of compression for gz (levels are 1-9)
	//-----begin file encryption process-----//
        //--AES Encryption--//
	$aes_key_password = random_bytes(128);
	$method_1 = 'aes-256-cbc';//choose method of encryption; AES 256-bit
	$aes_key = hash('sha512', $aes_key_password, true);//generate key for encryption using given password
	$aes_iv_length = openssl_cipher_iv_length($method_1);//decides initialization vector length
	$aes_iv = openssl_random_pseudo_bytes($aes_iv_length);//generates an initialization vector for every encrypted file to prevent ciphertext duplicates
	$data = openssl_encrypt($file_contents, $method_1, $aes_key, OPENSSL_RAW_DATA, $aes_iv);
	//--Blowfish Encryption--//
	$bf_key_password = random_bytes(128);
	$method_2 = 'blowfish';//choose method of encryption; blowfish
	$bf_key = hash('sha512', $bf_key_password, true);
	$bf_iv_length = openssl_cipher_iv_length($method_2);
        $bf_iv = openssl_random_pseudo_bytes($bf_iv_length);
        $data = openssl_encrypt($data, $method_2, $bf_key, OPENSSL_RAW_DATA, $bf_iv);
	//-----end file encryption process-----//
	//get users public key for encryption
	$fetch_publickey = $conn->prepare("select user_publickey from users where username='".$username."'");
	$fetch_publickey->execute();
	$key_row = $fetch_publickey->fetch();
	$publickey = $key_row['user_publickey'];
	$rsa = new RSA();
	$rsa->loadKey($publickey);
	//-----begin key encryption process-----//
	$encrypted_aes_key = $rsa->encrypt($aes_key);
	$encrypted_bf_key = $rsa->encrypt($bf_key);
	//-----end key encryption process-----//

	$date = date("F j, g:i a");//date of file upload
	//inserting encrypted file values into sql database
	$upload_file = $conn->prepare("insert into filestorage(file_name, file_type, file_data, aes_iv, bf_iv, aes_key, bf_key, u_id, date) values(?,?,?,?,?,?,?,?,?)");
	$upload_file->bindParam(1,$name);
	$upload_file->bindParam(2,$type);
	$upload_file->bindParam(3,$data);
	$upload_file->bindParam(4,$aes_iv);
	$upload_file->bindParam(5,$bf_iv);
	$upload_file->bindParam(6,$encrypted_aes_key);
	$upload_file->bindParam(7,$encrypted_bf_key);
	$upload_file->bindParam(8,$user_id);
	$upload_file->bindParam(9,$date);
	$upload_file->execute();
	//forces a redirect so a form isn't submitted multiple times
	if($_SERVER['REQUEST_METHOD'] == 'POST')
	{
		header("Location: home.php");
	}
}
//form used to require file for upload and password which will be used to generate a key for encryption/decryption
?>
</body>
