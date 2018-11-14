<?php
	//connect to online database
	$conn = new PDO("mysql:host=35.205.202.112;dbname=Users","root","mtD{];ttcY^{9@>`");
	//allows for uploaded files to be displayed
	$id = isset($_GET['id'])? $_GET['id'] : "";
	$files = $conn->prepare("select * from filestorage where f_id=?");
	$files->bindParam(1,$id);
	$files->execute();
	$row = $files->fetch();
	header("Content-Type:".$row['file_type']);
	echo $row['file_data'];
?>
