<?php
	$conn = new PDO("mysql:host=35.205.202.112;dbname=Users","root","mtD{];ttcY^{9@>`");

	$username = $_POST["username"];
	$password = $_POST["password"];
	$password_hash = hash('sha512', $password, true);

	$login_statement = $conn->prepare("select * from users where username='".$username."' and password_hash ='".$password_hash."'");
	$login_statement->execute();
	$row = $login_statement->fetch();

	if(empty($row))
	{
		$response["success"] = false;
	}
	else
	{
		$response["success"] = true;
	}

	echo json_encode($response);
?>
