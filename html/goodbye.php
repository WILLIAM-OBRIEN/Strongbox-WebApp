<?php
session_start();
if(!isset($_SESSION['deleted']))
{
        echo('<script>window.location="login.php"</script>');
}
?>
<html>
<head>
<link rel="shortcut icon" type="image/png" href="image.png">
<link rel="stylesheet" type="text/css" href="style.css">
<meta charset="utf-8">
<title>Goodbye | Strongbox</title>
</head>
<body>
<div class="login-page">
<div class="form">
<p>Your account was successfully deleted. Thank you for trying out strongbox!</p>
</div>
</form>
</div></div>
</body>
</html>

