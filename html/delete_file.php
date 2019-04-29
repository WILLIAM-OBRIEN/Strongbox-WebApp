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
        }//prevents users from accessing page directly
        $username = $_SESSION['logged'];
        $conn = new PDO("mysql:host=35.205.202.112;dbname=Users","root","mtD{];ttcY^{9@>`");//connect to online database

        $id = isset($_GET['id'])? $_GET['id'] : "";
        $files = $conn->prepare("delete from filestorage where f_id=?");//selects file with id selected on home page
        $files->bindParam(1,$id);
        $files->execute();
	echo("<script>document.getElementById('p_hash').value = sessionStorage.hash;document.getElementById('send_hash').submit();</script>");
?>
