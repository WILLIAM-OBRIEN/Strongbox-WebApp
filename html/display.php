<?php
	//prevents users from accessing page directly
	session_start();
	if(!isset($_SESSION['logged']))
	{
		echo('<script>window.location="login.php"</script>');
	}
	//connect to online database
	$conn = new PDO("mysql:host=35.205.202.112;dbname=Users","root","mtD{];ttcY^{9@>`");
	//allows for uploaded files to be downloaded
	$id = isset($_GET['id'])? $_GET['id'] : "";
	$files = $conn->prepare("select * from filestorage where f_id=?");
	$files->bindParam(1,$id);
	$files->execute();
	//places file contents of single row for dowload
	$row = $files->fetch();
	$iv = $row['file_iv'];
	$key = $row['file_key'];
	$method = 'aes-256-cbc';
	$file = openssl_decrypt($row['file_data'], $method, $key, OPENSSL_RAW_DATA, $iv);
	//prints out entire contents
	echo $file;
?>
