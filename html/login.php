<script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
<script type="text/javascript">
function send_hash() {
          $.ajax({
        url:"registration.php",
        type: "POST",
	dataType: "json",
        data:"hash="+ sessionStorage.hash,
	dataType:'json',
        success: function(data){console.log(data);window.location="registration.php";},
	error: function(jqxhr, status, exception) {
             alert('Exception:', exception);
         }
        });
}
</script>
<form method="POST" action="home.php" id="send_hash">
<input type="hidden" id="p_hash" name="hash"/>
</form>
<?php
session_start();
if(isset($_SESSION['logged']))
{
	echo("<script>document.getElementById('p_hash').value = sessionStorage.hash;document.getElementById('send_hash').submit();</script>");
}

if(isset($_SESSION['username']))
{
        session_destroy();
}

if(isset($_REQUEST['submit']))
{
	$username=$_REQUEST['username'];
	$password=$_REQUEST['user_password'];
	$password_hash = hash('sha512', $password, true);

	$conn = new PDO("mysql:host=35.205.202.112;dbname=Users","root","mtD{];ttcY^{9@>`");
	$login_statement = $conn->prepare("select * from users where username=? and password_hash=?");
	$login_statement->bindParam(1,$username);
        $login_statement->bindParam(2,$password_hash);
	$login_statement->execute();
	$row = $login_statement->fetch();

	//this whole section checks to see if the user has either entered incorrect information, account is unactivated or is banned for failed password attempts
	$ban_statement = $conn->prepare("select * from users where username=?");
	$ban_statement->bindParam(1,$username);
        $ban_statement->execute();
        $ban_row = $ban_statement->fetch();

	//checks to see how many entries the user has entered in the 'ban_table', i.e. how many incorrect password attempts there have been over the past hour
	$fail_attempts = $conn->prepare("select count(*) from ban_table where u_id=".$ban_row['u_id']."");
        $fail_attempts->execute();
        $fail_row = $fail_attempts->fetch();
        $count = (int)$fail_row['count(*)'];
	$active_st = (int)$ban_row['active'];

	if(empty($row) || $row['active']== 0 || $count > 2)
	{
		echo('<script>alert("".$active_st."");</script>');
		if($active_st != 0 && $count <= 2)
		{
			//insert failed password attempt into the ban_table, 3 gives the user a 1 hour ban
			$fail_insert = $conn->prepare("insert into ban_table(u_id, fail_attempt) values(?, CURRENT_TIMESTAMP)");
			$fail_insert->bindParam(1,$ban_row['u_id']);
                	$fail_insert->execute();
			if($count == 0)
			{
				echo('<script>alert("Incorrect username/password! 2 attempts left...");</script>');
			}
			else if($count == 1)
			{
				echo('<script>alert("Incorrect username/password! 1 attempt left...");</script>');
			}
			else if($count > 1)
                	{
		                echo('<script>alert("Incorrect username/password! Your account has been banned for one hour...");</script>');
	                }
		}
		else if ($count>2)
		{
			echo('<script>alert("User is currently banned!");</script>');
		}
		else
		{
			echo('<script>alert("Incorrect username/password!");</script>');
		}
		echo('<script>window.location="login.php"</script>');
	}
	else
	{
		//creates a session of the user for their username and password, the password will be used to decrypt uploaded files.
		//disabled as sending messages costs money

		//$_SESSION['username']=$username;
		//$_SESSION['pwd']=$password;
		//echo('<script>window.location="authenticate.php"</script>');*/
		$_SESSION['logged']=$username;
		$password_hash = md5($password);
		echo "<script>sessionStorage.hash='".$password_hash."';</script>";
                echo("<script>document.getElementById('p_hash').value = sessionStorage.hash;document.getElementById('send_hash').submit();</script>");
	}
	//session_destroy();
}
?>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="shortcut icon" type="image/png" href="image.png">
<link rel="stylesheet" type="text/css" href="style.css">
<meta charset="utf-8">
<title>Login | Strongbox</title>
</head>
<centre>
<body>
<div class="login-page">
<div class="form">
<form method="post" action="login.php">
<img src=logofinal.png width="150" height="125"></img>
<p><h2>Welcome to <a href="explain.php">Strongbox!</a></h2></p><p></p>
    <input name="username" maxlength=20 type="text" placeholder="Enter your username..." autocomplete="off">
    <br>
    <input name="user_password"type="password" maxlength=30 placeholder="Enter your password...">
    <br>
   <input type="submit" name="submit" "value="Login">
    <br>
   Need an account? <a href="registration.php">Register here</a>

</form>
</center>
</div></div>
</body>
</html>
