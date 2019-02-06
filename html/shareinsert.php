<body>
<?php
require __DIR__.'/vendor/autoload.php';
use phpseclib\Crypt\RSA;
session_start();
if(!isset($_SESSION['logged']))
{
        echo('<script>window.location="login.php"</script>');
}

$file_list = $_GET['fileID'];
if(isset($_GET['fileID']))
{
	foreach($file_list as $list)
	{
		//echo  $list."<br />";
		insertfiles($list);
	}
}

else
{
	echo "Nothing selected!";
}

function insertfiles($file_id)
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
	//-----end file decryption process-----//

	//passing file attributes from form
	$name = $file_row['file_name'];
	$type = $file_row['file_name'];
	$f_data = $file;
	$owner_id = $user_id;
	$shared_id = 1;//hardcoded for now to be shared automatically to a single account

	$upload_file = $conn->prepare("insert into sharedfilestorage(file_name, file_type, file_data, owner_id, shared_id) values(?,?,?,?,?)");
	$upload_file->bindParam(1,$name);
	$upload_file->bindParam(2,$type);
	$upload_file->bindParam(3,$f_data);
	$upload_file->bindParam(4,$owner_id);
	$upload_file->bindParam(5,$shared_id);
	$upload_file->execute();

}
if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	header("Location: home.php");
}
?>
</body>

