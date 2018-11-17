<head><title>File Upload</title></head>
<body>
<?php
session_start();
if(!isset($_SESSION['logged']))
{
	echo('<script>window.location="login.php"</script>');
}
//connect to online sql database
$conn = new PDO("mysql:host=35.205.202.112;dbname=Users","root","mtD{];ttcY^{9@>`");
//if upload button clicked with file attached(add condition to check for this) attempts to upload to db
if(isset($_POST['upload']))
{
	$name = $_FILES['file_upload']['name'];
	$type = $_FILES['file_upload']['type'];
	$file_data = $_FILES['file_upload']['tmp_name'];
	$file_contents = file_get_contents($file_data);

	//file encryption process
	$key_password = $_FILES['key_encryption'];//read in password for key generation
	$method = 'aes-256-cbc';//choose method of encryption; AES 256-bit
	$key = substr(hash('sha256', $key_password, true), 0, 32);//generate key for encryption using given password
	$iv_length = openssl_cipher_iv_length($method);
	$iv = openssl_random_pseudo_bytes($iv_length);
	$data = base64_encode(openssl_encrypt($file_contents, $method, $key, OPENSSL_RAW_DATA, $iv));

	$upload_file = $conn->prepare("insert into filestorage(file_name, file_type, file_data) values(?,?,?)");
	$upload_file->bindParam(1,$name);
	$upload_file->bindParam(2,$type);
	$upload_file->bindParam(3,$data);
	$upload_file->execute();
	//forces a redirect so a form isn't submitted multiple times
	if($_SERVER['REQUEST_METHOD'] == 'POST')
	{
		header("Location: home.php");
	}
}
?>
    <form method="post" enctype="multipart/form-data">
        <div>
        <input type="file" name="file_upload" required/>
	<input type="text" name="key_encryption" required>
        <button name="upload">Upload</button>
        </div>
    </form>
    <p></p>
    <?php
	//selects all files stored on the database and prints their contents
	$files = $conn->prepare("select * from filestorage");
	$files->execute();
	while($row = $files->fetch())
	{
		/*$key_password = 'password';//read in password for key generation
		$method = 'aes-256-cbc';//choose method of encryption; AES 256-bit
		$key = substr(hash('sha256', $key_password, true), 0, 32);
		$iv_length = openssl_cipher_iv_length($method);
		$iv = openssl_random_pseudo_bytes($iv_length);
		$decrypted = openssl_decrypt(base64_decode($row['file_data']), $method, $key, OPENSSL_RAW_DATA, $iv);
		echo $decrypted;*/
		echo "<div>";
		echo "<li><a href='display.php?id=".$row['f_id']."' target='_blank' download=".$row['file_name'].">".$row['file_name']."</a></li>";
		echo "</div>";
	}
	/*$plaintext="Mikasa is best girl";
	$password = 'password';
	$method = 'aes-256-cbc';
	$key = substr(hash('sha256', $password, true), 0, 32);
	$iv = chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0);
        $encrypted = base64_encode(openssl_encrypt($plaintext, $method, $key, OPENSSL_RAW_DATA, $iv));
	echo $encrypted;?><br><?php
	$decrypted = openssl_decrypt(base64_decode($encrypted), $method, $key, OPENSSL_RAW_DATA, $iv);
	echo $decrypted;*/
    ?>
    </ol>
    <br>
    <a href="logout.php">Logout</a>
</body>
</html>
