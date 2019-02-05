<head>
<title>Share Files | Strongbox</title>
</head>
<body>
<?php
session_start();
if(!isset($_SESSION['logged']))
{
	echo('<script>window.location="login.php"</script>');
}
$conn = new PDO("mysql:host=35.205.202.112;dbname=Users","root","mtD{];ttcY^{9@>`");
//pass in username & user id
$username = $_SESSION['logged'];
$user_chk = $conn->prepare("select u_id from users where username='".$username."'");
$user_chk->execute();
$user_row = $user_chk->fetch();
$user_id = $user_row['u_id'];

$files = $conn->prepare("select * from filestorage where u_id='".$user_id."'");
$files->execute();
//creates hyperlink for downloading files stored on database which are decrypted using stored key (will be changed to localised method in future)
while($row = $files->fetch())
{
	echo "<div>";
	echo "<input name='fileID' type='checkbox' value='".$row['f_id']."'>".$row['file_name']."";
	echo "</div>";
}
/*
function FileSharing($file_id)
{
        echo "$file_id";
}*/
?>
<input type="button" onclick='fileshare()' value="FileID"/>
<script type="text/javascript">
function fileshare()
{
	var items=document.getElementsByName('fileID');
	var selectedItems="";
	for(var i=0; i<items.length; i++)
	{
		if(items[i].type=='checkbox' && items[i].checked==true)
		selectedItems+=items[i].value+"\n";
	}
	alert(selectedItems);
}
</script>
</body>
