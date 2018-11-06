<?php
session_start();
if(!isset($_SESSION['logged']))
{
	echo('<script>window.location="login.php"</script>');		
}

function upload_object($bucketName, $objectName, $source)
{
    $storage = new StorageClient();
    $file = fopen($source, 'r');
    $bucket = $storage->bucket($bucketName);
    $object = $bucket->upload($file, [
        'name' => $objectName
    ]);
    printf('Uploaded %s to gs://%s/%s' . PHP_EOL, basename($source), $bucketName, $objectName);
}

?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Home Page</title>
</head>

<body>
<center>
<h1>Welcome to our system!</h1>
<br>
<a href="logout.php">Logout</a>
</center>

</body>
</html>
