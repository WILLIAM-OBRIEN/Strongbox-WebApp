
<head><title>File Upload</title></head>
<body>
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
//if upload button clicked with file attached(add condition to check for this) attempts to upload to db
//echo $row2['user_publickey'];
if(isset($_POST['upload']))
{
	//pass in username
	$username = $_SESSION['logged'];
	//passing file attributes from form
	$name = $_FILES['file_upload']['name'];
	$type = $_FILES['file_upload']['type'];
	$file_data = $_FILES['file_upload']['tmp_name'];
	$file_contents = file_get_contents($file_data);

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

	//inserting encrypted file values into sql database
	$upload_file = $conn->prepare("insert into filestorage(file_name, file_type, file_data, aes_iv, bf_iv, aes_key, bf_key) values(?,?,?,?,?,?,?)");
	$upload_file->bindParam(1,$name);
	$upload_file->bindParam(2,$type);
	$upload_file->bindParam(3,$data);
	$upload_file->bindParam(4,$aes_iv);
	$upload_file->bindParam(5,$bf_iv);
	$upload_file->bindParam(6,$encrypted_aes_key);
	$upload_file->bindParam(7,$encrypted_bf_key);
	$upload_file->execute();
	//forces a redirect so a form isn't submitted multiple times
	if($_SERVER['REQUEST_METHOD'] == 'POST')
	{
		header("Location: home.php");
	}
}

//form used to require file for upload and password which will be used to generate a key for encryption/decryption
?>
	<form method="post" enctype="multipart/form-data">
	<div>
	<input type="file" name="file_upload" required/>
	<button name="upload">Upload</button>
	</div>
	</form>
	<br>
	<?php
	//selects all files stored on the database and prints their contents
	$files = $conn->prepare("select * from filestorage");
	$files->execute();
	//creates hyperlink for downloading files stored on database which are decrypted using stored key (will be changed to localised method in future)
	while($row = $files->fetch())
	{
		echo "<div>";
		echo "<li><a href='download.php?id=".$row['f_id']."' target='_blank' download=".$row['file_name'].">".$row['file_name']."</a>";
		echo "<a href='display.php?id=".$row['f_id']."' target='_blank' download=".$row['file_name'].">(Decrypted)</a></li>";
		echo "</div>";
	}
    ?>
    <br>
    <a href="logout.php">Logout</a>
</body>
