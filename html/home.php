
<!DOCTYPE html>
<html>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<head><title>Home | Strongbox</title>
<link rel="shortcut icon" type="image/png" href="image.png">
<link rel="stylesheet" type="text/css" href="style.css">
</head>
<script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
<body>
<div class="tab">
  <button class="tablinks" id="upload_tab" onclick="openTab(event, 'upload_files')">My Files</button>
  <button class="tablinks" id="share_tab"onclick="openTab(event, 'share')">Share files</button>
  <button class="tablinks" id="message_tab"onclick="openTab(event, 'message')">Messages</button>
  <button class="tablinks" id="inbox_tab"onclick="openTab(event, 'inbox')">Inbox</button>
  <button class="tablinks" id="friends_tab"onclick="openTab(event, 'friends')">Friends</button>
  <button class="tablinks" id="account_tab"onclick="openTab(event, 'account')">Account</button>
  <button style="float:right;" class="tablinks" id="logout_tab" onclick="openTab(event, 'logout')"><b>Logout</b></button>
</div>
<script>
	function close_modal(){
	document.getElementById('modal').style.display = "none";
	}
	function close_delete(){
        document.getElementById('warning').style.display = "none";
        }
	function open_warning(){
        document.getElementById('warning').style.display = "block";
        }
	function delete_account(){
	window.location="delete_account.php"
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
	//if(action="inbox"){document.getElementById("inbox").style.paddingLeft= "0px";}
	evt.currentTarget.className += " active";
	}
	function openMessages(evt, id) {
          var i, content, links;
          content = document.getElementsByClassName("message");
          for (i = 0; i < content.length; i++) {
                        content[i].style.display = "none";
          }
          links = document.getElementsByClassName("links");
          for (i = 0; i < links.length; i++) {
                        links[i].className = links[i].className.replace(" active", "");
          }
          document.getElementById(id).style.display = "block";
          evt.currentTarget.className += " active";
        }

function checkFile(){
var upload = document.getElementById("file_uploader");
upload.onchange = function() {
    if(this.files[0].size > 10485760){
       alert("File is too big! (10MB Max)");
       this.value = "";
    }
}
}

function send_hash() {
          $.ajax({
            url:"home.php",
            type: "POST",
            dataType: "json",
           data: {"hash": sessionStorage.hash}
         });
     }
</script>

<?php
require __DIR__.'/vendor/autoload.php';
use phpseclib\Crypt\RSA;
session_start();
$password = $_POST['hash'];

if(isset($_POST['hash'])){
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

//checks to see if the user has any seen messages
$seen_msg = $conn->prepare("select distinct(sender) from messages where receiver='".$user_id."' and seen = 0");
$seen_msg->execute();
echo "<script>";
if($seen_msg->rowCount() > 0)
{
		//JavaScript function that navigates user to inbox and updates unseen messages to seen
		echo "function open_mail(){ document.getElementById('inbox_tab').click();document.getElementById('all_msg').click();}";
		echo "window.onload = function(){open_mail();}";
		echo "</script>";
		echo "<div id='modal'><div class='modalbox'><p>You have unread messages!</p><button onclick='close_modal()'>Close</button>";
		echo "</div></div>";
		$make_seen = $conn->prepare("update messages set seen = 1, date_recorded = date_recorded where receiver='".$user_id."'");
		$make_seen->execute();
}
else
{
		echo "</script>";
}

//form used to require file for upload and password which will be used to generate a key for encryption/decryption
?>
	<div  id="upload_files" class="file">
	<form method="post" action="upload.php" enctype="multipart/form-data">
	<input type="file" id="file_uploader" onclick="checkFile();" name="file_upload" required></input>
	<input type="submit" name="upload" value="Upload"></input>
	</form>
<?php
	//selects all files stored on the database and prints their contents
	$files = $conn->prepare("select * from filestorage where u_id='".$user_id."'");
	$files->execute();
	if($files->rowCount() > 0)
        {
		echo "<table style='font-size:2.3vw;'>";
		echo "<tr>";
		echo "<td class='file_row'><b>File</b></td>";
		echo "<td class='file_row'><b>Size</b></td>";
		echo "<td class='file_row'><b>Upload Date</b></td>";
		echo "</tr><tr></tr>";
		//creates hyperlink for downloading files stored on database which are decrypted using stored key (will be changed to localised method in future)
		while($row = $files->fetch())
		{
				$size = (int)strlen($row['file_data']);
				$size = $size/1000000;
				echo "<tr>";
				if(strlen($row['file_name'])>30){
				//echo "check</br>";
		echo "<td class='file_row'><a href='display.php?id=".$row['f_id']."&hash=".$password."' target='_blank' download='".$row['file_name']."' style='font-size:1.1vw;'>".$row['file_name']."</a></td>";
				}
				else{
				echo "<td class='file_row'><a href='display.php?id=".$row['f_id']."&hash=".$password."' target='_blank' download='".$row['file_name']."'>".$row['file_name']."</a></td>";}
				if($size>=1){$size=round($size,1);echo "<td class='file_row'>". $size. "MB</td>";}
				else{$size=round($size*1000,1);echo "<td class='file_row'>". $size. "KB</td>";}
				echo "<td class='file_row'>". $row['date']. "</td>";
				echo "<td class='file_row'><a href='delete_file.php?id=".$row['f_id']."&hash=".$password."'>Delete</a></td>";
				echo "</tr>";
		}
		echo "</table>";
	}
	else
	{
		echo "No files...";
	}

	$files = $conn->prepare("select * from sharedfilestorage where shared_id='".$user_id."'");
        $files->execute();
	if($files->rowCount() > 0)
	{
		echo "<h3><u>Shared Files</u></h3>";
     		//creates hyperlink for downloading files stored on database which are decrypted using stored key (will be changed to localised method in future)
		echo "<table style='font-size:3vw;'>";
                echo "<tr>";
                echo "<td class='file_row'><b>File</b></td>";
                echo "<td class='file_row'><b>Shared By</b></td>";
                echo "</tr><tr></tr>";
	        while($row = $files->fetch())
	        {
			$owner = $conn->prepare("select username from users where u_id='".$row['owner_id']."'");
			$owner->execute();
			$owner_name = $owner->fetch();
                	echo "<tr>";
	                echo "<td class='file_row'><a href='download.php?id=".$row['f_id']."&hash=".$password."' target='_blank' download='".$row['file_name']."'>".$row['file_name']."</a></td>";
			echo "<td class='file_row'>".$owner_name['username']."</td><td class='file_row'><a href='delete_sharedfile.php?id=".$row['f_id']."&hash=".$password."'>Remove</a></td>";
                	echo "</tr>";
	        }
		echo "</table>";
	}
	?>
	<br>
	</div>
	</form>

	<!--**********share_files**********-->
	<div id="share" class="file">
	<?php
	$files = $conn->prepare("select * from filestorage where u_id='".$user_id."'");
	$files->execute();
	//creates hyperlink for downloading files stored on database which are decrypted using stored key (will be changed to localised method in future)
	if($files->rowCount()>0)
        {
		echo "<form action= 'shareinsert.php' method= 'get'>";
		while($row = $files->fetch())
		{
			if(strlen($row['file_name'])>30){echo "<label class='container' style='font-size:1.5vw;'>";
			echo "<input name='fileID[]' type='checkbox' value='".$row['f_id']."'style='font-size:1.1vw;' >".$row['file_name']."<span class='checkmark'></span>";
			}
			else{echo "<label class='container'>";
			echo "<input name='fileID[]' type='checkbox' value='".$row['f_id']."'>".$row['file_name']."<span class='checkmark'></span>";}
			echo "</label>";
		}


		$users = $conn->prepare("select accept_friend, username from users join friends on users.u_id=friends.accept_friend where send_friend=".$user_id." and accepted=1;");
	        $users->execute();
		if($users->rowCount()>0)
       		{
			echo "<h2>Share with:</h2>";
			while($row = $users->fetch())
			{
				echo "<label class='container'>";
				echo "<input name='userID[]' type='checkbox' value='".$row['accept_friend']."'>".$row['username']."<span class='checkmark'></span>";
				echo "</label>";
			}
			echo "<input type='hidden' id='p_hash' name='hash' value=".$password."/>";
			echo "<center><input type='submit' value='Share File(s)'/></center>";
			echo "</form>";
		}
		else{echo "</br> Add friends to share files...";}
	}
	else
	{
		echo "No files to share...";
	}
	?>
	</div>





	<!--**********messaging**********-->
	<div id="message" class="file">
	<form name="message_send" id="message_send" method="post" action="send_message.php" enctype="multipart/form-data">
	<?php
	$users = $conn->prepare("select accept_friend, username from users join friends on users.u_id=friends.accept_friend where send_friend=".$user_id." and accepted=1;");
	$users->execute();
	if($users->rowCount()>0)
        {
		while($row = $users->fetch())
		{
	        	echo "<label class='container' id='message_chk'>";
		        echo "<input name='userID[]' type='checkbox' value='".$row['accept_friend']."'>".$row['username']."<span class='checkmark'></span>";
	        	echo "</label>";
		}

	?>
		<input type="text" id="message_text" maxlength="1600" placeholder="Send a message..."name="message" autocomplete="off" required></input>
		<input type="submit" name="send" value="Send"></input>
	<?php
	}
	else{echo "Add a friend to message them!";}
	echo "<input type='hidden' id='p_hash' name='hash' value=".$password."/>";
	?>
	</form>
	</div>





	<!--**********inbox**********-->
        <div id="inbox" class="file" style="padding-left: 0px;width: 120%;">
	<link rel="stylesheet" type="text/css" href="style.css">
        <?php
	$senders = $conn->prepare("select username, sender from messages join users on messages.sender=users.u_id where receiver=".$user_id." group by u_id");
        $senders->execute();
	if($senders->rowCount()>0)
	{
		echo "<table class='m_table' style='width:100%'>";
                echo "<tr>";
                echo "<td>";

		//creates the inbox buttons
		echo "<div class='mtab'>";
		echo "<button class='links' id='all_msg' onclick='openMessages(event, 0)'>All Messages</button>";
		echo "</div>";

		while($row = $senders->fetch())
        	{
			echo "<div class='mtab'>";
                	echo "<button class='links' onclick='openMessages(event, ".$row['sender'].")'>".$row['username']."</button>";
			echo "</div>";
	        }
		echo "</td>";
		echo "<td>";

		//-----First lets get the private key, decrypt the message key and then decrypt and view the message-----//
		//code to get the receivers message key decrypt it using their private key and then decrypt the message
		$password = $_POST['hash'];
        	$get_messagekey= $conn->prepare("select m_key from message_keys where u_id=".$user_id."");
	        $get_messagekey->execute();
        	$msg_row = $get_messagekey->fetch();
	        $mkey = $msg_row['m_key'];
		$fetch_privatekey = $conn->prepare("select user_privatekey from users where username='".$username."'");//gets private key linked to user (in encrypted format)
		$fetch_privatekey->execute();
		$key_row = $fetch_privatekey->fetch();
		$encrypted_privatekey = $key_row['user_privatekey'];
		$user_privatekey = openssl_decrypt($encrypted_privatekey, 'aes-128-cbc' , $password, OPENSSL_RAW_DATA ,"1234567812345678");//decrypts private key linked to user using their passwo$
		$rsa = new RSA();
		$rsa->loadKey($user_privatekey);

        	//decrypt message key
	        $mkey = $rsa->decrypt($mkey);
/////////////////////
		echo $mkey;
		echo $password;
		$m_array = array();

		//create messages for each user found to have sent a message for this user
		$senders = $conn->prepare("select username, sender  from messages join users on messages.sender=users.u_id where receiver=".$user_id." group by u_id");
                $senders->execute();
		while($row = $senders->fetch())
                {
			$get_messages = $conn->prepare("select * from messages where sender=".$row['sender']." and receiver=".$user_id." order by date_recorded desc");
			$get_messages->execute();
			$fetch_friendval = $conn->prepare("select friend_val from friends where send_friend=".$row['sender']." and accept_friend=".$user_id." and accepted=1");
		        $fetch_friendval->execute();
        		$f_row = $fetch_friendval->fetch();
	        	$friend_val = $f_row['friend_val'];

			$prime_mod = "100000000000000000000000000000000000000000000000000000000019";
			$key = bcpowmod($friend_val, $mkey, $prime_mod);

			//here is where messages are decrypted and displayed to the user
			echo "<div id='".$row['sender']."'class='message'>";
			echo "<table style='width:100%'>";
			$i = 0;
			$m_array[$i] = array();
			while($m_row = $get_messages->fetch())
			{
	                        $message = openssl_decrypt($m_row['message'], 'aes-128-cbc' , $key, OPENSSL_RAW_DATA ,"1234567812345678");//decrypts aes encyption part of file
				if (ctype_space($message)){$message="< User tried to send some dodgy code >";}
				echo "<tr><td class='message_text'>" .$message." </td><td class='date_text'>".$m_row['time_sent']."</td></tr>";
				$m_array[$i]['id'] = $row['username'];
				$m_array[$i]['message'] = $message;
				$m_array[$i]['date'] = $m_row['time_sent'];
				$i++;
			}
			echo "</table>";
			//this is the reply function
			echo "<form method='post' style='padding-bottom: 5px;' action='send_message.php' enctype='multipart/form-data'>";
			echo "<p></p>";
			echo "<input name='userID[]' style='display:none' checked type='checkbox' value='".$row['sender']."'>";
			echo "<input type='text' maxlength='500' name='message' autocomplete='off' style='margin:0;' required placeholder='Reply to user...'></input> ";
			echo "<input type='hidden' id='p_hash' name='hash' value=".$password."/>";
			echo "<input type='submit' name='send' id='send_messagebtn' value='Send'></input>";
			echo "</form>";
			echo "</div>";
                }

		function sortFunction( $a, $b )
		{
                        return strtotime($b["date"]) - strtotime($a["date"]);
                }
                usort($m_array, "sortFunction");

		//this is the all messages inbox
		echo "<div id='0'class='message'>";
		echo "<table style='width:100%'>";
		$length = count($m_array);
		for ($i=0;$i<=$length-1;$i++)
		{
			echo "<tr><td class='message_text'>" .$m_array[$i]['message']."</td><td class='date_text'>" .$m_array[$i]['date']."</br>from ".$m_array[$i]['id']."</td></tr>";
		}
		echo "</table>";
		echo "</div>";
	}
	else
        {
		echo "<center>No messages...</center>";
	}
        ?>
	</td>
	</tr>
	</table>
        </form>
        </div>
	<!--**********Friends**********-->
        <div id="friends" class="file">
	<form method="post" action="accept_request.php" enctype="multipart/form-data">
        <?php
	$requests = $conn->prepare("select send_friend, username from friends join users on friends.send_friend=users.u_id where accepted = 0 and accept_friend=".$user_id."");
        $requests->execute();
	if($requests->rowCount()>0)
        {
		echo "<table style='border-collapse: collapse;'>";
		while($row = $requests->fetch())
        	{
			echo "<tr style='border: dotted 1px black;'><td><input name='friend' type='checkbox' checked style='display:none' value='".$row['send_friend']."'>";
			echo "" .$row['username']. " sent you a friend request:</td><td></br><input type='submit' name='accept' value='Accept' id='accept_button'></input>";
			echo "<input id='decline_button' type='submit' name='decline' value='Decline'></input></tr></td>";
		}
		echo "</table>";
		echo "</br>";
	}
	echo "<input type='hidden' id='p_hash' name='hash' value=".$password."/>";
	echo "</form>";

	echo "<h2>Add Friends: </h2>";
	$friends = $conn->prepare("select accept_friend, send_friend from friends where send_friend=".$user_id." or accept_friend=".$user_id."");
        $friends->execute();
        $f_array = array();
        $i = 0;
        while($accept_row = $friends->fetch())
        {
                $f_array[$i]=$accept_row['send_friend'];
                $i++;
                $f_array[$i]=$accept_row['accept_friend'];
                $i++;
        }

	echo "<form method='post' action='friend_request.php' enctype='multipart/form-data'>";
        $users = $conn->prepare("select * from users where u_id !=".$user_id."");
        $users->execute();
	$no_friends = 0;
	$length = count($f_array);
        while($row = $users->fetch())
        {
		$chk = 0;
                for ($i=0;$i<=$length-1;$i++)
                {
                        if($f_array[$i]==$row['u_id'])
                        {
                                //echo " Fail!</br>";
                                $chk = 1;
                        }
                }
		if($chk == 0)
		{
			$no_friends = 1;
			//echo " Succeed!</br>";
			echo "<label class='container'>";
			echo "<input name='userID[]' type='checkbox' value='".$row['u_id']."'>".$row['username']."<span class='checkmark'></span>";
			echo "</label>";
		}
        }//doesnt prevent applying for a friend request if one has been already sent atm (the person with the request already applying for one)
	echo "<input type='hidden' id='p_hash' name='hash' value=".$password."/>";
	if($no_friends == 0)
	{echo "No other accounts to add...";}
	else
	{echo "<input type='submit' name='send' value='Add friends'></input>";}
        ?>
        </form>
        </div>


	<!--**********account**********-->
        <div id="account" class="file">
        <?php
        $users = $conn->prepare("select * from users where u_id=".$user_id."");
        $users->execute();
	$row = $users->fetch();
	echo "<h2><b><u>" .$row['username']. "</u></b></h2>";
	echo "<p><b>Phone number:  </b> ".$row['phone_number']."</p>";
	echo "<p><b>Email address: </b>".$row['user_email']."</p>";
	echo "</br>";

	//echo "<form method='post'  action='delete_account.php' enctype='multipart/form-data'>";
	//echo "<div id='warning'><div class='modalbox'><p>Are you sure you want to delete your account?</p><button onclick='close_modal()'>Close</button>";
	echo "<input type='submit' id='deleteButton' style='background-color: #ED4337;color:white;' onclick='open_warning()' value='Delete account'></input>";
        ?>
        </div>
	<div id='warning' style = 'display: none;'><div  class='modalbox'><p>Are you sure you want to delete your account?</p>
	<button id='delete_yes' style='cursor: pointer;border:solid black 1px;background-color: #ED4337;'onclick='delete_account()'>Yes</button>
	<button id='delete_no' style='cursor: pointer;background-color: #ccc;border:solid grey 1px;' onclick='close_delete()'>No</button></div>
</div>
</body>
</html>
<?php }
else{
	echo "<script>window.location.href = 'logout.php';</script>";
}
?>
