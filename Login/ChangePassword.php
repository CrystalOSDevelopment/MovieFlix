<?php

session_start();
if(!isset($_SESSION['UName'])){
    die("-1");
}

/*
    Return code documentation:
        -1 - User is not logged in
        0 - Connection failed
        1 - User does not exist
        2 - Password is incorrect
        3 - Username or password is empty
        4 - Password changed successfully
*/

$CurrPass = $_POST['CurrPass'];
$NewPass = $_POST['NewPass'];

$AllowProceed = true;

// Connect to the database
$servername = "localhost";
$username = "root";
$password = "";

$conn = new mysqli($servername, $username, $password, "links_db");

// If the connection failed return
if ($conn->connect_error) {
    die("0");
}

// Check if the user exists
$sql = "SELECT * FROM Users WHERE UName = '$_SESSION[UName]'";
$result = $conn->query($sql);

// If the user does not exist
if($result->num_rows == 0){
    die("1");
}
else{
    // Check if the password is correct
    $row = $result->fetch_assoc();
    if(!password_verify($CurrPass, $row['Pass'])){
        die("2");
    }
    else if($CurrPass == "" || $NewPass == ""){
        die("3");
    }
    else{
        // Encrypt the password
        $hashedPass = password_hash($NewPass, PASSWORD_DEFAULT);

        // Update the password
        $sql = "UPDATE Users SET Pass = '$hashedPass' WHERE UName = '$_SESSION[UName]'";

        if ($conn->query($sql) === TRUE) {
            echo "4";
        } else {
            die("Error: " . $sql . "<br>" . $conn->error);
        }
    }
}

?>