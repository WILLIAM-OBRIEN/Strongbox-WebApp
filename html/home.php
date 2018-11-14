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
	$data = file_get_contents($file_data);
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
        <input type="file" name="file_upload"/>
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
		echo "<div>";
		echo "<li><a href='display.php?id=".$row['f_id']."' target='_blank'>".$row['file_name']."</a></li>";
		echo "</div>";
	}
    ?>
    </ol>
    <br>
    <a href="logout.php">Logout</a>
</body>
</html>
