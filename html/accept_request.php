<form method="POST" action="home.php" id="send_hash">
<input type="hidden" id="p_hash" name="hash"/>
</form>
<?php
require __DIR__.'/vendor/autoload.php';
use phpseclib\Crypt\RSA;
session_start();
if(!isset($_SESSION['logged']))
{
		echo('<script>window.location="login.php"</script>');
}
$id = $_POST['friend'];
if(isset($_POST['accept']))
{
	acceptrequest($id);
}

if(isset($_POST['decline']))
{
        declinerequest($id);
}

function acceptrequest($id)
{
        $conn = new PDO("mysql:host=35.205.202.112;dbname=Users","root","mtD{];ttcY^{9@>`");//connect to online database
        $username = $_SESSION['logged'];
        $user_chk = $conn->prepare("select u_id from users where username='".$username."'");
        $user_chk->execute();
        $user_row = $user_chk->fetch();
        $user_id = $user_row['u_id'];

	$fetch_baseval = $conn->prepare("select base_val from friends where send_friend=".$id." and accept_friend=".$user_id." and accepted=0");
	$fetch_baseval->execute();
	$row = $fetch_baseval->fetch();

	$update_friend = $conn->prepare("update friends set accepted=1 where send_friend=".$id." and accept_friend=".$user_id." and accepted=0");
        $update_friend->execute();

	$base_val = $row['base_val'];

	//-----begin key decryption process-----//
        $fetch_privatekey = $conn->prepare("select user_privatekey from users where username='".$username."'");//gets private key linked to user (in encrypted format)
        $fetch_privatekey->execute();
        $key_row = $fetch_privatekey->fetch();
        $encrypted_privatekey = $key_row['user_privatekey'];
	$password = $_POST['hash'];
        $user_privatekey = openssl_decrypt($encrypted_privatekey, 'aes-128-cbc' , $password, OPENSSL_RAW_DATA ,"1234567812345678");//decrypts private key linked to user using their password
        $rsa = new RSA();
        $rsa->loadKey($user_privatekey);

	//code to get the senders message key, decrypt it and combine with base value using modulo before inserting
        $get_messagekey= $conn->prepare("select m_key from message_keys where u_id=".$user_id."");
        $get_messagekey->execute();
        $msg_row = $get_messagekey->fetch();
        $mkey = $msg_row['m_key'];

        //decrypt message key
        $mkey = $rsa->decrypt($mkey);

	$base_val = 2;
	$prime_mod = "100000000000000000000000000000000000000000000000000000000019";

	$friend_val = bcpowmod($base_val, $mkey, $prime_mod);

	//inserting encrypted message into sql database
        $upload_file = $conn->prepare("insert into friends(send_friend, accept_friend, accepted, base_val, friend_val) values(?,?, 1,?,?)");
        $upload_file->bindParam(1,$user_id);
        $upload_file->bindParam(2,$id);
        $upload_file->bindParam(3,$base_val);
        $upload_file->bindParam(4,$friend_val);
        $upload_file->execute();

	echo("<script>document.getElementById('p_hash').value = sessionStorage.hash;document.getElementById('send_hash').submit();</script>");
}

function declinerequest($id)
{
        $conn = new PDO("mysql:host=35.205.202.112;dbname=Users","root","mtD{];ttcY^{9@>`");//connect to online database
        $username = $_SESSION['logged'];
        $user_chk = $conn->prepare("select u_id from users where username='".$username."'");
        $user_chk->execute();
        $user_row = $user_chk->fetch();
        $user_id = $user_row['u_id'];

	$fetch_baseval = $conn->prepare("delete from friends where send_friend=".$id." and accept_friend=".$user_id." and accepted=0");
        $fetch_baseval->execute();
        $row = $fetch_baseval->fetch();

	echo("<script>document.getElementById('p_hash').value = sessionStorage.hash;document.getElementById('send_hash').submit();</script>");
}

?>
