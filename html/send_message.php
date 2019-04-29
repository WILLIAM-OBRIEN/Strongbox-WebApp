<form method="POST" action="home.php" id="send_hash">
<input type="hidden" id="p_hash" name="hash"/>
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
if(isset($_POST['send']) && isset($_POST['message']))
{
        if(strlen($message) < 1600)
        {
                foreach($user_list as $u_list)
                {
                        sendmessage($message, $u_list);
                }
        }
        else
        {
                echo('<script>Message is too long for sending!</script>');
        }
}

function sendmessage($message, $receiver)
{
	//connect to online sql database
	$conn = new PDO("mysql:host=35.205.202.112;dbname=Users","root","mtD{];ttcY^{9@>`");
	//if upload button clicked with file attached to check for this) attempts to upload to db
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

        //code to get the senders message key, combine their mkey with the shared frend_val and encrypt the message and insert it
        $get_messagekey= $conn->prepare("select m_key from message_keys where u_id=".$user_id."");
        $get_messagekey->execute();
        $msg_row = $get_messagekey->fetch();
        $mkey = $msg_row['m_key'];

        //decrypt message key
        $mkey = $rsa->decrypt($mkey);

	$fetch_friendval = $conn->prepare("select friend_val from friends where send_friend=".$receiver." and accept_friend=".$user_id." and accepted=1");
        $fetch_friendval->execute();
        $row = $fetch_friendval->fetch();
	$friend_val = $row['friend_val'];

	$prime_mod = "100000000000000000000000000000000000000000000000000000000019";
	$mkey = bcpowmod($friend_val, $mkey, $prime_mod);

        $message = filter_var($message, FILTER_SANITIZE_STRING);

	//encrypt actual message
        $message = openssl_encrypt($message, 'aes-128-cbc' , $mkey, OPENSSL_RAW_DATA , "1234567812345678");

	//inserting encrypted message into sql database
	$date = date("F j, g:i a");//date of file upload (in a readable format)
        $upload_file = $conn->prepare("insert into messages(sender, receiver, message, time_sent, date_recorded, seen) values(?,?,?,?, CURRENT_TIMESTAMP, 0)");
        $upload_file->bindParam(1,$user_id);
        $upload_file->bindParam(2,$receiver);
        $upload_file->bindParam(3,$message);
	$upload_file->bindParam(4,$date);
        $upload_file->execute();
}
if($_SERVER['REQUEST_METHOD'] == 'POST')
{
	echo("<script>document.getElementById('p_hash').value = sessionStorage.hash;document.getElementById('send_hash').submit();</script>");
}
?>
