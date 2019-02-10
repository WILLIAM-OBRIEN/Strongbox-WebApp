<?php
        //prevents users from accessing page directly
        session_start();
        if(!isset($_SESSION['logged']))
        {
                        echo('<script>window.location="login.php"</script>');
        }
        //connect to online database
        $conn = new PDO("mysql:host=35.205.202.112;dbname=Users","root","mtD{];ttcY^{9@>`");
        //allows for uploaded files to be downloaded in encypted format
        $id = isset($_GET['id'])? $_GET['id'] : "";
        $files = $conn->prepare("select * from sharedfilestorage where f_id=?");
        $files->bindParam(1,$id);
        $files->execute();
        $row = $files->fetch();
        //prints out entire contents
        echo $row['file_data'];
?>
