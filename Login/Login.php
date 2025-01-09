<?PHP
    /*
        Status codes for the login process:
            0 - Connection to database failed
            1 - User does not exist
            2 - Password is incorrect
            3 - Username or password is empty
            4 - User logged in successfully
            5 - Passwords do not match
    */
    $UName = isset($_POST['UName']) ? $_POST['UName'] : '';
    $Pass = isset($_POST['Pass']) ? $_POST['Pass'] : '';

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
    $sql = "SELECT * FROM Users WHERE UName = '$UName'";
    $result = $conn->query($sql);

    // If the user does not exist
    if($result->num_rows == 0){
        die("1");
    }
    else{
        // Check if the password is correct
        $row = $result->fetch_assoc();
        if(!password_verify($Pass, $row['Pass'])){
            die("2");
        }
        else if($UName == "" || $Pass == ""){
            die("3");
        }
        else{
            // Start the session
            session_start();
            $_SESSION['UName'] = $UName;
            // $_SESSION['UID'] = $row['UserID'];
            die("4");
        }
    }
?>