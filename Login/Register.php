<?PHP
    /*
        Status codes for the registration process:
            0 - Connection to database failed
            1 - User already exists
            2 - Password is not strong enough
            3 - Username or password is empty
            4 - New user added successfully
            5 - Passwords do not match
        
    */
    // Take in the input from the form
    $UName = isset($_POST['UName']) ? $_POST['UName'] : '';
    $Pass = isset($_POST['Pass']) ? $_POST['Pass'] : ''; 
    $Pass2 = isset($_POST['Pass2']) ? $_POST['Pass2'] : '';
    $Email = isset($_POST['Email']) ? $_POST['Email'] : '';
    
    // If the passwords do not match instantly return
    if($Pass != $Pass2){
        die("5");
    }

    // Allow to continue the registration process
    $AllowProceed = true;
    $ProceedLogin = false;

    // Connect to the database
    // Local usecase
    $servername = "localhost";
    $username = "root";
    $password = "";
    
    // Connect to database
    $conn = new mysqli($servername, $username, $password, "links_db");

    // Check if the connection was established
    if ($conn->connect_error) {
        die("0");
    }

    // Check if the user exists
    $sql = "SELECT * FROM Users WHERE UName = '$UName'";
    $result = $conn->query($sql);

    // If the user exists
    if ($result->num_rows > 0) {
        // TODO: Throw an error and return to the login page
        die("1");
        $AllowProceed = false;
    }

    if($AllowProceed){
        // Check if the password is strong enough
        if(strlen($Pass) < 8 || !preg_match('/[A-Z]/', $Pass) || !preg_match('/[a-z]/', $Pass) || !preg_match('/[0-9]/', $Pass) || !preg_match('/[^a-zA-Z\d]/', $Pass)){
            die("2");
        }
        else if($UName == "" || $Pass == "" || $Email == ""){
            die("3");
        }
        // Encrypt the password
        $hashedPass = password_hash($Pass, PASSWORD_DEFAULT);

        // Insert the user into the database
        $sql = "INSERT INTO Users (UName, Email, Pass) VALUES ('$UName', '$Email','$hashedPass')";

        if ($conn->query($sql) === TRUE) {
            die("4");
            $ProceedLogin = true;
        } else {
            die("Error: " . $sql . "<br>" . $conn->error);
        }
    }
?>