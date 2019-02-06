
<head><title>Home | Strongbox</title></head>
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
//pass in username & user id
$username = $_SESSION['logged'];
$user_chk = $conn->prepare("select u_id from users where username='".$username."'");
$user_chk->execute();
$user_row = $user_chk->fetch();
$user_id = $user_row['u_id'];
//form used to require file for upload and password which will be used to generate a key for encryption/decryption
?>
	<form method="post" action="upload.php" enctype="multipart/form-data">
	<div>
	<input type="file" name="file_upload" required/>
	<input type="submit" name="upload" value="Upload"></input>
	</div>
	</form>
	<br>
	<?php
	//selects all files stored on the database and prints their contents
	$files = $conn->prepare("select * from filestorage where u_id='".$user_id."'");
	$files->execute();
	//creates hyperlink for downloading files stored on database which are decrypted using stored key (will be changed to localised method in future)
	while($row = $files->fetch())
	{
		echo "<div>";
		echo "<a href='display.php?id=".$row['f_id']."' target='_blank' download='".$row['file_name']."'>".$row['file_name']."</a>";
		echo "</div>";
	}
	echo "<h2>Shared Files</h2>";
	$files = $conn->prepare("select * from sharedfilestorage where shared_id='".$user_id."'");
        $files->execute();
        //creates hyperlink for downloading files stored on database which are decrypted using stored key (will be changed to localised method in future)
        while($row = $files->fetch())
        {
		$owner = $conn->prepare("select username from users where u_id='".$row['owner_id']."'");
		$owner->execute();
		$owner_name = $owner->fetch();
                echo "<div>";
                echo "<a href='download.php?id=".$row['f_id']."' target='_blank' download='".$row['file_name']."'>".$row['file_name']."</a>";
		echo "   shared by ".$owner_name['username']." ";
                echo "</div>";
        }
	?>
	<br>
	<form action="share.php">
		<input type="submit" value="Share Files" />
	</form>
	<a href="logout.php">Logout</a>
</body>
