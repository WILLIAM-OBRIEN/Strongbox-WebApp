<html>
<head>
<link rel="shortcut icon" type="image/png" href="image.png">
<link rel="stylesheet" type="text/css" href="style.css">
<meta charset="utf-8">
<title>Explanation | Strongbox</title>
</head>
<style>
.explain{
text-align: left;
}
</style>
<body>
<div class="login-page">
<div class="form">
<h2>What is Strongbox?</h2>
<div class="explain">
<font size="2">Strongbox is a cloud storage service where I can't read your files. This is done through local encryption using both AES-256 and Blowfish.
<p></p> The keys used in these algorithms are unique to each file and are themselves encrypted, with a public/private key pair unique to each user. 
These user keys are encrypted with a computational hash of the user's password. 
<p></p>At no point will I store a user's password on the servers.
 What this means is that I have no access to the encryption keys used, and that I am physically not able to read a user's stored files.
<p></p><b>Why did I do this?</b> To provide a cloud storage service that truly protects the confidentiality of my users.</font></div>
<p></p><a href="login.php">You can login here</a>
</div>
</form>
</div></div>
</body>
</html>
