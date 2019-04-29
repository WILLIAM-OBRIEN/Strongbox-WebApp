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

$user_list = $_POST['userID'];
if(isset($_POST['send']))
{
        foreach($user_list as $u_list)
        {
                sendrequest($u_list);
        }
}

function sendrequest($receiver)
{
        //connect to online sql database
        $conn = new PDO("mysql:host=35.205.202.112;dbname=Users","root","mtD{];ttcY^{9@>`");
        $username = $_SESSION['logged'];

	//-----begin key decryption process-----//
        $fetch_privatekey = $conn->prepare("select u_id, user_privatekey from users where username='".$username."'");//gets private key linked to user (in encrypted format)
        $fetch_privatekey->execute();
        $key_row = $fetch_privatekey->fetch();
	$user_id = $key_row['u_id'];
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
        $upload_file = $conn->prepare("insert into friends(send_friend, accept_friend, accepted, base_val, friend_val) values(?,?, 0,?,?)");
        $upload_file->bindParam(1,$user_id);
        $upload_file->bindParam(2,$receiver);
        $upload_file->bindParam(3,$base_val);
        $upload_file->bindParam(4,$friend_val);
        $upload_file->execute();

	if($_SERVER['REQUEST_METHOD'] == 'POST')
	{
		echo("<script>document.getElementById('p_hash').value = sessionStorage.hash;document.getElementById('send_hash').submit();</script>");
	}
}
