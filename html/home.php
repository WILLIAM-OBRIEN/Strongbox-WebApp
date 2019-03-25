
<head><title>Home | Strongbox</title>
<link rel="shortcut icon" type="image/png" href="image.png">
<link rel="stylesheet" type="text/css" href="style.css">
</head>
<style>
body {font-family: Arial;}
</style>
<body>
<div class="tab">
  <button class="tablinks" id="upload_tab" onclick="openTab(event, 'upload_files')">My Files</button>
  <button class="tablinks" id="share_tab"onclick="openTab(event, 'share')">Share files</button>
  <button class="tablinks" onclick="openTab(event, 'logout')">Logout</button>
</div>
<script>
window.onload = function() {
	document.getElementById("upload_tab").click();
}

function openTab(evt, action) {
  var i, tabcontent, tablinks;
  if(action=="logout"){window.location.href = "logout.php";}
  tabcontent = document.getElementsByClassName("file");
  for (i = 0; i < tabcontent.length; i++) {
    tabcontent[i].style.display = "none";
  }
  tablinks = document.getElementsByClassName("tablinks");
  for (i = 0; i < tablinks.length; i++) {
    tablinks[i].className = tablinks[i].className.replace(" active", "");
  }
  document.getElementById(action).style.display = "block";
  evt.currentTarget.className += " active";
}
</script>
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
	<div  id="upload_files" class="file">
	<form method="post" action="upload.php" enctype="multipart/form-data">
	<input type="file" name="file_upload" required></input>
	<input type="submit" name="upload" value="Upload"></input>
	</form>
	<?php

	//selects all files stored on the database and prints their contents
	$files = $conn->prepare("select * from filestorage where u_id='".$user_id."'");
	$files->execute();
	echo "<table>";
	echo "<tr>";
        echo "<th>File</th>";
        echo "<th>Size</th>";
        echo "</tr><tr></tr>";

	//creates hyperlink for downloading files stored on database which are decrypted using stored key (will be changed to localised method in future)
	while($row = $files->fetch())
	{
		$size = (int)strlen($row['file_data']);
		$size = $size/1000000;
		echo "<tr>";
		echo "<th><a href='display.php?id=".$row['f_id']."' target='_blank' download='".$row['file_name']."'>".$row['file_name']."</a></th>";
		if($size>=1){$size=round($size,1);echo "<th>". $size. "MB</th>";}
		else{$size=round($size*1000,1);echo "<th>". $size. "KB</th>";}
		echo "</tr>";
	}
	echo "</table>";

	$files = $conn->prepare("select * from sharedfilestorage where shared_id='".$user_id."'");
        $files->execute();
	if($files->rowCount() > 0)
	{
		echo "<h3><u>Shared Files</u></h3>";
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
	}
	?>
	<br>
	</div>
	</form>

	<div id="share" class="file">
	<?php
	$files = $conn->prepare("select * from filestorage where u_id='".$user_id."'");
	$files->execute();
	//creates hyperlink for downloading files stored on database which are decrypted using stored key (will be changed to localised method in future)
	echo "<form action= 'shareinsert.php' method= 'get'>";
	while($row = $files->fetch())
	{
			echo "<label class='container'>";
			echo "<input name='fileID[]' type='checkbox' value='".$row['f_id']."'>".$row['file_name']."<span class='checkmark'></span>";
			echo "</label>";
	}

	echo "<h2>Share with:</h2>";

	$users = $conn->prepare("select * from users");
	$users->execute();
	while($row = $users->fetch())
	{
			echo "<label class='container'>";
			echo "<input name='userID[]' type='checkbox' value='".$row['u_id']."'>".$row['username']."<span class='checkmark'></span>";
			echo "</label>";
	}
	?>
	<center><input type="submit" value="Share File(s)"/></center>
	</div>
</body>
