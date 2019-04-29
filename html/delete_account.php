
<?php
session_start();
if(!isset($_SESSION['logged']))
{
        echo('<script>window.location="login.php"</script>');
}

//connect to online sql database
$conn = new PDO("mysql:host=35.205.202.112;dbname=Users","root","mtD{];ttcY^{9@>`");
$username = $_SESSION['logged'];
$users = $conn->prepare("select u_id from users where username='".$username."'");
$users->execute();
$row = $users->fetch();
$user_id = $row['u_id'];
$deletes = $conn->prepare("delete from ban_table where u_id='".$user_id."'");
$deletes->execute();
$deletes = $conn->prepare("delete from friends where send_friend='".$user_id."' or accept_friend='".$user_id."'");
$deletes->execute();
$deletes = $conn->prepare("delete from messages where sender='".$user_id."' or receiver='".$user_id."'");
$deletes->execute();
$deletes = $conn->prepare("delete from message_keys where u_id='".$user_id."'");
$deletes->execute();
$deletes = $conn->prepare("delete from sharedfilestorage where owner_id='".$user_id."' or shared_id='".$user_id."'");
$deletes->execute();
$deletes = $conn->prepare("delete from filestorage where u_id='".$user_id."'");
$deletes->execute();
$deletes = $conn->prepare("delete from users where u_id='".$user_id."'");
$deletes->execute();

unset($_SESSION['logged']);
unset($_SESSION['password']);
$_SESSION['deleted']="gonzo";
echo('<script>sessionStorage.hash="";window.location="goodbye.php"</script>');

?>
