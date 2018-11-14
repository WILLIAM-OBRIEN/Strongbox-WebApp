<?php
$con=mysqli_connect("35.205.202.112", "root", "mtD{];ttcY^{9@>`", "Users");

    $username = $_POST["username"];
    $password = $_POST["password"];

    $statement = mysqli_prepare($con, "SELECT * FROM users WHERE UserUsername = ? AND UserPassword = ?");
    mysqli_stmt_bind_param($statement, "ss", $username, $password);
    mysqli_stmt_execute($statement);

    mysqli_stmt_store_result($statement);
    mysqli_stmt_bind_result($statement, $userID, $username, $password, $name, $email );

    $response = array();
    $response["success"] = false;
    while(mysqli_stmt_fetch($statement))
    {
        $response["success"] = true;
    }

    echo json_encode($response);
?>
