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

$file_list = $_GET['fileID'];

if(isset($_GET['fileID']))
{
	foreach($file_list as $list)
	{
		//echo  $list."<br />";
		insertfiles($list);
	}
}

else
{
	echo "Nothing selected!";
}

function insertfiles($file_id)
{
	
}
?>
</body>

