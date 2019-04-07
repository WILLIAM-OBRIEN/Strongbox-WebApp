<?php
        require __DIR__.'/vendor/autoload.php';
        use phpseclib\Crypt\RSA;
        session_start();
        if(!isset($_SESSION['logged']))
        {
                        echo('<script>window.location="login.php"</script>');
        }//prevents users from accessing page directly
        $username = $_SESSION['logged'];
        $conn = new PDO("mysql:host=35.205.202.112;dbname=Users","root","mtD{];ttcY^{9@>`");//connect to online da$

        $id = isset($_GET['id'])? $_GET['id'] : "";
        $files = $conn->prepare("delete from sharedfilestorage where f_id=?");//selects file with id selected on home pa$
        $files->bindParam(1,$id);
        $files->execute();
        echo('<script>window.location="home.php"</script>');
?>
