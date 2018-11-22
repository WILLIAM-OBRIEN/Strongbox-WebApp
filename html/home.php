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
	//get users public key for encryption
	$user_pubkey = $conn->prepare("select user_publickey from users where username='".$username."'");
	$user_pubkey->execute();
	$key_row = $user_pubkey->fetch();
	$publickey = $key_row['user_publickey'];
	$rsa = new RSA();
	$rsa->loadKey($publickey);
	//file encryption process
	$key_password = $_GET['key_encryption'];//read in password for key generation
	$method = 'aes-256-cbc';//choose method of encryption; AES 256-bit
	$key = hash('sha256', $key_password, true);//generate key for encryption using given password
	$iv_length = openssl_cipher_iv_length($method);//decides initialization vector length
	$iv = openssl_random_pseudo_bytes($iv_length);//generates an initialization vector for every encrypted file to prevent ciphertext duplicates
	$data = openssl_encrypt($file_contents, $method, $key, OPENSSL_RAW_DATA, $iv);
	//key encryption process
	$e_key = $rsa->encrypt($key);
	//----unencrypt test
	/*$user_privkey = $conn->prepare("select user_privatekey from users where username='".$username."'");
        $user_privkey->execute();
        $key_row = $user_privkey->fetch();
        $privatekey = $key_row['user_privatekey'];
	$p_key = openssl_decrypt($privatekey, 'aes-128-cbc' , 'password', OPENSSL_RAW_DATA ,"1234567812345678");
	$rsa->loadKey($p_key);
	$final_key = $rsa->decrypt($e_key);*/
	//inserting encrypted file values into sql database
	$upload_file = $conn->prepare("insert into filestorage(file_name, file_type, file_data, file_iv, file_key) values(?,?,?,?,?)");
	$upload_file->bindParam(1,$name);
	$upload_file->bindParam(2,$type);
	$upload_file->bindParam(3,$data);
	$upload_file->bindParam(4,$iv);
	$upload_file->bindParam(5,$e_key);
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
	<input type="text" name="key_encryption" required>
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
</html>
