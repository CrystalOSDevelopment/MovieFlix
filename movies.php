<?php
    session_start();
    if(!isset($_SESSION['UName'])) {
        header('Location: Login/Login.html');
        exit();
    }

    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "links_db";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $year = isset($_GET['year']) ? $_GET['year'] : '';
    $search = isset($_GET['search']) ? '%' . $_GET['search'] . '%' : '%';
    $orderBy = isset($_GET['orderBy']) ? $_GET['orderBy'] : '';
    $genre = isset($_GET['genre']) ? $_GET['genre'] : '';
    $wantRecents = isset($_GET['wantRecents']) ? $_GET['wantRecents'] : '';
    $addtoFavorites = isset($_GET['addtoFavorites']) ? $_GET['addtoFavorites'] : '';
    $deleteMovie = isset($_GET['deleteMovie']) ? $_GET['deleteMovie'] : ''; // This is a movieID
    $wantFavorites = isset($_GET['wantFavorites']) ? $_GET['wantFavorites'] : '';
    $wantSimilar = isset($_GET['wantSimilar']) ? $_GET['wantSimilar'] : '';

    $UserID = 0;

    $stmt = $conn->prepare("SELECT * FROM Users WHERE UName = ?");
    $stmt->bind_param("s", $_SESSION['UName']);
    $stmt->execute();
    $result = $stmt->get_result();
    $users = $result->fetch_all(MYSQLI_ASSOC);
    $UserID = $users[0]['UserID'];

    $Command = "";

    if($orderBy != "") {
        switch($orderBy) {
            case '0':
                // Order by movie year in ascending order
                $orderBy = 'release_date ASC';
                break;
            case '1':
                // Order by movie year in descending order
                $orderBy = 'release_date DESC';
                break;
            case '2':
                // Order by movie title in ascending order
                $orderBy = 'movie_title ASC';
                break;
            case '3':
                // Order by movie title in descending order
                $orderBy = 'movie_title DESC';
                break;
            case '4':
                // Filter to only specific director (This is a TODO)
                $Command = "SELECT * FROM links WHERE DirANDActors LIKE ?";
                break;
            case '5':
                // Filter to only specific actor (This is a TODO)
                $Command = "SELECT * FROM links WHERE DirANDActors LIKE ?";
                break;
            case '6':
                // Filter to only specific genre (AKA Category)
                $Command = "SELECT * FROM links WHERE Category LIKE ?";
                break;
            case '7':
                // Order by rating in ascending order (This is a TODO, probably will be solved by joining tables)
                $Command = "SELECT * FROM links WHERE movie_title LIKE ? ORDER BY Rating ASC";
                break;
            case '8':
                // Order by rating in descending order (This is a TODO, probably will be solved by joining tables)
                $Command = "SELECT * FROM links WHERE movie_title LIKE ? ORDER BY Rating DESC";
                break;
            case '9':
                // Order by movie length in ascending order
                $orderBy = 'movie_length ASC';
                break;
            case '10':
                // Order by movie length in descending order
                $orderBy = 'movie_length DESC';
                break;
        }
    }

    if ($year != "") {
        $stmt = $conn->prepare("SELECT * FROM links WHERE release_date = ?");
        $stmt->bind_param("s", $year);
    }
    else if($Command != "") {
        $stmt = $conn->prepare($Command);
        $stmt->bind_param("s", $search);
    }
    else if($wantRecents != "") {
        // Join recents and links tables to get the most recent movies
        // Order is in reverse chronological order
        $stmt = $conn->prepare("SELECT * FROM recents JOIN links ON recents.movieID = links.id WHERE recents.userID = ? ORDER BY recents.userID DESC");
        $stmt->bind_param("i", $UserID);
    }
    else if($addtoFavorites != "") {
        // Check if the movie is already in the favorites table
        $stmt = $conn->prepare("SELECT * FROM favorites WHERE movieID = ? AND userID = ?");
        $stmt->bind_param("ii", $addtoFavorites, $UserID);
        $stmt->execute();

        $result = $stmt->get_result();
        $movies = $result->fetch_all(MYSQLI_ASSOC);

        if(count($movies) == 0) {
            // If the movie is not in the favorites table, add it
            $stmt = $conn->prepare("INSERT INTO favorites (movieID, userID) VALUES (?, ?)");
            $stmt->bind_param("ii", $addtoFavorites, $UserID);
            $stmt->execute();
            echo "true";
        }
        else {
            // If the movie is already in the favorites table, remove it
            $stmt = $conn->prepare("DELETE FROM favorites WHERE movieID = ? AND userID = ?");
            $stmt->bind_param("ii", $addtoFavorites, $UserID);
            $stmt->execute();
            echo "false";
        }
        $stmt->close();
        $conn->close();
        return;
    }
    else if($deleteMovie != "") {
        // Delete the movie from the favorites table
        $stmt = $conn->prepare("DELETE FROM favorites WHERE movieID = ?");
        $stmt->bind_param("i", $deleteMovie);
        $stmt->execute();

        // Delete the movie from the recents table
        $stmt = $conn->prepare("DELETE FROM recents WHERE movieID = ?");
        $stmt->bind_param("i", $deleteMovie);
        $stmt->execute();

        // Before deleting from the links table, we need to get the imdbID
        $stmt = $conn->prepare("SELECT imdb_code FROM links WHERE id = ?");
        $stmt->bind_param("i", $deleteMovie);
        $stmt->execute();
        $result = $stmt->get_result();
        $imdbID = $result->fetch_all(MYSQLI_ASSOC)[0]['imdb_code'];

        // Delete the movie from the links table
        $stmt = $conn->prepare("DELETE FROM links WHERE id = ?");
        $stmt->bind_param("i", $deleteMovie);
        $stmt->execute();

        // Delete the movie from the trailers table
        $stmt = $conn->prepare("DELETE FROM trailers WHERE imdb_id = ?");
        $stmt->bind_param("s", $imdbID);
        $stmt->execute();

        echo "true";
        $stmt->close();
        $conn->close();
        return;
    }
    else if($wantFavorites != "") {
        // Join favorites and links tables to get the favorite movies
        $stmt = $conn->prepare("SELECT * FROM favorites JOIN links ON favorites.movieID = links.id WHERE favorites.userID = ? ORDER BY favorites.userID DESC");
        $stmt->bind_param("i", $UserID);
    }
    else if($genre != "") {
        $stmt = $conn->prepare("SELECT * FROM links WHERE Category LIKE ? " . ($orderBy != "" ? "ORDER BY " . $orderBy : "") . " LIMIT 8");
        $genre = '%' . $genre . '%';
        $stmt->bind_param("s", $genre);
    }
    else if($wantSimilar != "") {
        // Get the most common category
        // Join recents movieID on links id
        $stmt = $conn->prepare("SELECT * FROM recents JOIN links ON recents.movieID = links.id WHERE recents.userID = ?");
        $stmt->bind_param("i", $UserID);
        $stmt->execute();
        $result = $stmt->get_result();
        $recents = $result->fetch_all(MYSQLI_ASSOC);
        $Frequency = array();
        foreach($recents as $recent){
            $ExplodeGenre = explode(",", $recent['Category']);
            foreach($ExplodeGenre as $genre){
                if(array_key_exists($genre, $Frequency)){
                    $Frequency[$genre] += 1;
                }
                else{
                    $Frequency[$genre] = 1;
                }
            }
        }
        arsort($Frequency);
        // If there are multiple ones with the same frequency, return with all of them
        $Count = 0;
        $Out = "";
        foreach($Frequency as $key => $value){
            $Out .= $key . ", ";
            $Count = $value;
        }
        echo substr($Out, 0, -2);
        return;
    }
    else {
        $stmt = $conn->prepare("SELECT * FROM links WHERE movie_title LIKE ? " . ($orderBy != "" ? "ORDER BY " . $orderBy : ""));
        $stmt->bind_param("s", $search);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $movies = $result->fetch_all(MYSQLI_ASSOC);
    if($wantRecents != "") {
        $movies = array_reverse($movies);
    }

    echo json_encode($movies);

    $stmt->close();
    $conn->close();
?>