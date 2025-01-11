<?php

    /* 
        Status codes for the profile picture change process:
            0 - Connection to database failed
            1 - Profile picture changed successfully
            2 - Profile picture change failed
    */

    $PFP = isset($_POST['PFP']) ? $_POST['PFP'] : '';
    $UserID = isset($_POST['UName']) ? $_POST['UName'] : '';

    if ($PFP == "" || $UserID == "") {
        die("2");
    }

    // Connect to the database
    $servername = "localhost";
    $username = "root";
    $password = "";

    $conn = new mysqli($servername, $username, $password, "links_db");

    // If the connection failed return
    if ($conn->connect_error) {
        die("0");
    }

    // Update the profile picture using prepared statements
    $sql = "UPDATE Users SET PFP = ? WHERE UName = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $PFP, $UserID);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            die("1"); // Profile picture changed successfully
        } else {
            die("2"); // No rows affected
        }
    } else {
        die("2"); // Profile picture change failed
    }

    $stmt->close();
    $conn->close();
?>