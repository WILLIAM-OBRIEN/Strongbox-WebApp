<?php
	//connect to online database
	$conn = new PDO("mysql:host=35.205.202.112;dbname=Users","root","mtD{];ttcY^{9@>`");
	//allows for uploaded files to be displayed
	$id = isset($_GET['id'])? $_GET['id'] : "";
	$files = $conn->prepare("select * from filestorage where f_id=?");
	$files->bindParam(1,$id);
	$files->execute();
	$row = $files->fetch();
	//var_dump($row);
	$key_password = "password";
	$iv = $row['file_iv'];
	$key = $row['file_key'];
	$method = 'aes-256-cbc';
	$file = openssl_decrypt($row['file_data'], $method, $key, OPENSSL_RAW_DATA, $iv);
	echo $file;
?>
