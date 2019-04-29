<form method="POST" action="home.php" id="send_hash">
<input type="hidden" id="p_hash" name="hash"/>
</form>
<?php
require __DIR__.'/vendor/autoload.php';
use phpseclib\Crypt\RSA;
session_start();
if(!isset($_SESSION['logged']))
{
        echo('<script>window.location="login.php"</script>');
}

$file_list = $_GET['fileID'];
$user_list = $_GET['userID'];
if(isset($_GET['fileID']) && isset($_GET['userID']))
{
	foreach($file_list as $list)
	{
		foreach($user_list as $u_list)
        	{
			insertfiles($list, $u_list);
		}
	}
}

else
{
	echo "<script type='text/javascript'>alert('Please choose a file & user for sharing!');</script>";
}

function insertfiles($file_id, $shared_id)
{
	$conn = new PDO("mysql:host=35.205.202.112;dbname=Users","root","mtD{];ttcY^{9@>`");
	//pass in username & user id
	$username = $_SESSION['logged'];
	$user_chk = $conn->prepare("select u_id from users where username='".$username."'");
	$user_chk->execute();
	$user_row = $user_chk->fetch();
	$user_id = $user_row['u_id'];

	$file_id = (int)$file_id;
        $files = $conn->prepare("select * from filestorage where f_id=?");//selects file with id selected on home page
        $files->bindParam(1,$file_id);
        $files->execute();
        $file_row = $files->fetch();

	//-----begin key decryption process-----//
	$fetch_privatekey = $conn->prepare("select user_privatekey from users where username='".$username."'");//gets private key linked to user (in encrypted format)
	$fetch_privatekey->execute();
	$key_row = $fetch_privatekey->fetch();
	$encrypted_privatekey = $key_row['user_privatekey'];
	//$password = $key_row['user_password'];//get user password to decrypt user private key
	$password = $_GET['hash'];
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
	//-----end file decryption process-----//

	//passing file attributes from form
        $name = $file_row['file_name'];
        $type = $file_row['file_type'];
        $file_contents = $file;
        $owner_id = $user_id;

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
	$fetch_publickey = $conn->prepare("select user_publickey from users where u_id='".$shared_id."'");
	$fetch_publickey->execute();
	$key_row = $fetch_publickey->fetch();
	$publickey = $key_row['user_publickey'];
	$rsa = new RSA();
	$rsa->loadKey($publickey);

	//-----begin key encryption process-----//
	$encrypted_aes_key = $rsa->encrypt($aes_key);
	$encrypted_bf_key = $rsa->encrypt($bf_key);
	//-----end key encryption process-----//

	$upload_file = $conn->prepare("insert into sharedfilestorage(file_name, file_type, file_data, aes_iv, bf_iv, aes_key, bf_key, owner_id, shared_id) values(?,?,?,?,?,?,?,?,?)");
	$upload_file->bindParam(1,$name);
	$upload_file->bindParam(2,$type);
	$upload_file->bindParam(3,$data);
	$upload_file->bindParam(4,$aes_iv);
	$upload_file->bindParam(5,$bf_iv);
	$upload_file->bindParam(6,$encrypted_aes_key);
	$upload_file->bindParam(7,$encrypted_bf_key);
	$upload_file->bindParam(8,$owner_id);
	$upload_file->bindParam(9,$shared_id);
	$upload_file->execute();
}
if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	echo("<script>document.getElementById('p_hash').value = sessionStorage.hash;document.getElementById('send_hash').submit();</script>");
}
?>
</body>

