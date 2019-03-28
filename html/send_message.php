<?php
require __DIR__.'/vendor/autoload.php';
use phpseclib\Crypt\RSA;
session_start();
if(!isset($_SESSION['logged']))
{
        echo('<script>window.location="login.php"</script>');
}

$user_list = $_POST['userID'];
$message = $_POST['message'];
if(isset($_POST['send']))
{
        foreach($user_list as $u_list)
        {
		sendmessage($message, $u_list);
        }
}

function sendmessage($message, $receiver)
{
	//connect to online sql database
	$conn = new PDO("mysql:host=35.205.202.112;dbname=Users","root","mtD{];ttcY^{9@>`");
	//if upload button clicked with file attached to check for this) attempts to upload to db
	$username = $_SESSION['logged'];
	$user_chk = $conn->prepare("select u_id from users where username='".$username."'");
	$user_chk->execute();
	$user_row = $user_chk->fetch();
	$user_id = $user_row['u_id'];
	//inserting encrypted file values into sql database
	$date = date("F j, g:i a");//date of file upload
        $upload_file = $conn->prepare("insert into messages(sender, receiver, message, time_sent, date_recorded, seen) values(?,?,?,?, CURRENT_TIMESTAMP, 0)");
        $upload_file->bindParam(1,$user_id);
        $upload_file->bindParam(2,$receiver);
        $upload_file->bindParam(3,$message);
	$upload_file->bindParam(4,$date);
        $upload_file->execute();
}
if($_SERVER['REQUEST_METHOD'] == 'POST')
{
        header("Location: home.php");
}
?>
