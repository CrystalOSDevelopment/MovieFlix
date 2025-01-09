<?PHP
    session_start();
    if(!isset($_SESSION['UName'])){
        header('Location: Login/Login.html');
    }

    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "links_db";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movie Search</title>
    <link rel="stylesheet" href="Resources/Styles/index.css">
    <script src="homepage.js"></script>
    <!-- Read parameters if any is passed -->
    <style>
        body{
            background-image: linear-gradient(-120deg, #101010 55%, #6FBAFF 100%);
            background-size: cover;
            min-height: 100vh;
        }
        .header {
            z-index: 999;
            position: sticky;
            top: 0;
            left: 0;
            width: 100%;
            height: 70px;
            background-color: rgba(16, 16, 16, 0.75);
            color: white;
            border-bottom: 1px solid #747474;
            backdrop-filter: blur(5px);
            display: flex;
            align-items: center;
            padding: 0 20px;
            box-sizing: border-box;
        }
        
        .logo {
            height: 50px;
            margin-right: 30px;
        }
        .menu {
            display: flex;
            gap: 20px;
        }
        .menu-item {
            color: #fff;
            font-size: 16px;
            cursor: pointer;
            transition: color 0.3s;
        }
        .menu-item:hover {
            color: #1e90ff;
        }

        .filmhossz {
            /* margin-top: 3px; */
            top: 0px;
            position: absolute;
            right: 20px;
        }
        .Search{
            background-color: rgba(217, 217, 217, 0.5);
            border-radius: 31.5px;
            margin: 0px;
            width: 340px;
            margin-top: 73px;
            margin-left: 29px;
        }
        .Search input[type="text"] {
            border: none;
            padding-top: 20px;
            padding-bottom: 20px;
            border-radius: 31.5px;
            outline: none;
            width: 80%;
            background-color: transparent;
        }
        .Search input[type="submit"] {
            background-image: url('Resources/Images/search.png');
            background-size: 20px;
            background-repeat: no-repeat;
            background-position: center;
            background-color: transparent;
            border: none;
            margin-left: 20px;
            padding: 10px;
            cursor: pointer;
        }
        .Categories {
            padding-top: 6px;
            margin-left: 29px;
            background-color: rgba(240, 240, 240, 0.43);
            max-width: 402px;
            min-height: fit-content;
            padding-bottom: 200px;
            margin-top: 53px;
            border-radius: 23px;
            padding-left: 5px;
            /* padding-right: 5px; */
        }

        .categories-grid {
        display: inline-flex;
        flex-direction: row;
        flex-wrap: wrap;
        padding: 0px;
        margin: 0px;
        }   

        .categories-label {
            padding-left: 10px;
            font-size:13px;
        }

        .Option {
            display: flex;
            justify-content: center;
            align-items: center;
            border: 1px solid black;
            border-radius: 21px;
            margin: 2px; /* Reduced margin */
            cursor: pointer;
            margin: 5px;
            margin-bottom: 10px;
            padding: 10px;
            min-width: min-content;
            color: black;
        }
        form {
            display: flex;
            justify-content: flex-start;
            gap: 10px;
            margin: 0px auto; 
            flex-wrap: nowrap;
            flex-direction: row;
            align-items: stretch;
        }

        .SearchOptions {
            display: flex;
            flex-direction: column;
            margin-right: 30px;
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        categories-grid {
            display: flex; /* Alapértelmezetten nyitva asztali nézetben */
            flex-wrap: wrap;
            gap: 10px;
            padding: 10px;
            border-top: 1px solid rgba(0, 0, 0, 0.1);
        }

        .categories-header {
            display: none; /* Csak mobil nézetben jelenik meg */
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            background-color: rgba(240, 240, 240, 0.8);
            padding: 10px;
            border-radius: 5px;
        }

        .categories-arrow {
            font-size: 16px;
            transition: transform 0.3s;
        }

        @media (min-width: 840px) {
            #movieData {
                margin-left: 80px;
            }
        }


        /* Mobile compatibility */
        @media (max-width: 768px) {
            .categories-header {
                display: flex; /* Mobil nézetben jelenjen meg */
            }
            
            .categories-grid {
                display: none; /* Mobil nézetben alapértelmezetten rejtve */
            }

            .categories-grid.open {
                display: flex; /* Mobilon kinyitva jelenjen meg */
            }
        }

        @media (max-width: 768px) {
            body {
                background-image: linear-gradient(-120deg, #101010 55%, #6FBAFF 100%);
                background-size: cover;
                min-height: 100vh;
            }
            .header {
                flex-direction: column;
                align-items: center;
                padding: 25px 20px; 
            }
            #MainPage{
                display: none;
            }
           
            .SearchOptions {
                display: flex;
                flex-direction: column;
                margin-right: 30px;
            }

            .MainBody {
                display: block;
                margin: 0 auto;
            }

            .Search input[type="submit"] {
                width: fit-content;
            }
           
            .menu-item:last-child {
                border-bottom: none; /* Utolsó elem alatt ne legyen vonal */
            }
            .logo {
                display: none;
            }
            
            .Search {
                width: 95%;
                min-height: fit-content;
            }
            .Categories {
                /*padding: 10px;*/
                width: 95%;
                max-width: none;
                padding-left: 0px;
                padding-top: 10px;
                padding-bottom: 10px;
            }

            .categories-header{
                width: 85%;
                margin-left: auto;
                margin-right: auto;
            }
            .categories-grid {
                justify-content: center;
            }
            .Option {
                flex: 1 1 100%;
                text-align: center;
            }
            #movieData {
                flex-direction: column;
                margin-left: auto;
            }
            #movieData > .movie {
                width: 100%;
                margin-bottom: 20px;
            }
            #movieData > h2{
                margin-left: 0px;
            }

            #movieData > div, #movieData{
                width: 95%;
            }
        }

        @media (min-width: 1024px) {
            #movieData > div {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(45%, 0fr));
                gap: 40px;
                width: 95%;
                margin-left: 0px;
            }
        }

        @media (min-width: 1200px) {
            #movieData > div {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(243px, 0fr));
                gap: 40px;
                width: 95%;
                margin-left: 0px;
            }
        }

        #loader{
            display: none;
        }

        .HideTab {
            display: none;
        }

        .SettingsTab{
            top: 0;
            left: 0;
            max-height: 100vh;
            margin-top: 40px;
        }

        .Data{
            width: 70%;
            margin-left: auto;
            margin-right: auto;
            background-color: rgba(240, 240, 240, 0.43);
        }

        th{
            padding-right: 40px;
        }

        h4{
            margin: 10px;
        }

        a:hover{
            cursor: pointer;
        }

    </style>
</head>

<body>
    <div class="header">
        <img src="Resources/Images/logo.svg" alt="MovieFlix Logo" class="logo">
        <div class="menu">
            <div class="menu-item" id="MainPage">
                <a href="index.php" style="text-decoration: none; color: inherit;">Főoldal</a>
            </div>
            <div class="menu-item" id="Recents">Legutóbbiak</div>
            <div class="menu-item" id="Favorites">Kedvencek</div>
            <!-- <div class="menu-item">Profilom</div> -->
            <div class="menu-item" id="Settings">Beállítások</div>
        </div>
    </div>
    
    <div class="MainBody">
        <div class="SearchOptions">
            <div class="Search">
                <form id="searchForm">
                    <input type="submit" value="" id="fetchAllRecords">
                    <input type="text" id="SearchTerm" name="SearchTerm" placeholder="" value="">
                </form>
            </div>
            <div class="Categories">
                <div class="categories-header" onclick="toggleCategories()">
                    <span class="categories-label">Szűrés kategóriák szerint</span>
                    <span class="categories-arrow">&#9660;</span>
                </div>
                <div class="categories-grid" id="categoriesGrid">
                    <div class="Option">Év (növekvő)</div>
                    <div class="Option">Év (csökkenő)</div>
                    <div class="Option">Cím (A-Z)</div>
                    <div class="Option">Cím (Z-A)</div>
                    <div class="Option">Rendező</div>
                    <div class="Option">Színész</div>
                    <div class="Option">Műfaj</div>
                    <div class="Option">Értékelés (növekvő)</div>
                    <div class="Option">Értékelés (csökkenő)</div>
                    <div class="Option">Hossz (növekvő)</div>
                    <div class="Option">Hossz (csökkenő)</div>
                    <div class="Option">Nyelv</div>
                    <div class="Option">Ország</div>
                </div>
            </div>
            
        </div>
        <div id="movieData" style="margin-top: 40px;display: flex; flex-wrap: wrap;flex-direction: row; max-width: 90%;"></div>
    </div>

    <div class="SettingsTab HideTab">
        <div style="text-align: center; background: linear-gradient(0deg, rgb(20, 20, 20) 2%, rgba(20, 20, 20, 0.5) 100%); padding: 40px; padding-top: 0px; padding-right: 0px; width: 90%; margin-left: auto; margin-right: auto; border-radius: 12px; border: 1px solid white">
            <h2 style="padding-bottom: 20px;">Beállítások</h2>
            <div style="display: flex;">
                <div style="text-align:center; max-width: fit-content; margin-left: 40px">
                    <div id="PFP" style="background-color: white; width: 200px; height: 200px; border-radius: 50%; display: flex; align-items: center; overflow: hidden; border: 1px solid black">
                        <img src="<?php echo $_SESSION['PFP']; ?>" alt="USER PFP" width="250px">
                    </div>
                    <?PHP
                        echo "<h3>" . $_SESSION['UName'] . "</h3>";
                    ?>
                    <p><a style="text-decoration: underline; color: gray; font-style: italic;">Profilkép cseréje</a></p>
                    <p><a style="text-decoration: underline; color: red; font-style: italic;" id="SignOut">Kilépés</a></p>
                </div>
                <div class="Data">
                    <h3 style="margin-bottom: 40px;">Felhasználói adatok</h3>
                    <hr>
                    <table style="margin-left: auto; margin-right: auto; text-align: left;">
                        <tr>
                            <th>
                                <h4>Felhasználónév</h4>
                            </th>
                            <td>
                                <?PHP echo $_SESSION['UName']; ?>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <h4>Jelszó</h4>
                            </th>
                            <td>
                                <a style="text-decoration: underline; color: red; font-style: italic;" id="Change_password">Jelszó módosítása</a>
                                <div style="display: none;" id="PasswordNew">
                                    <div style="display: flex; flex-direction: column;">
                                        <input type="password" placeholder="Jelenlegi jelszó" id="CurrPass" style="margin-top: 10px;">
                                        <input type="password" placeholder="Új jelszó" id="NewPass1" style="margin-top: 10px;">
                                        <input type="password" placeholder="Új jelszó megerősítése" id="NewPass2" style="margin-top: 10px;">
                                        <button style="margin-top: 10px;" id="Save">Mentés</button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </table>
                    <h3 style="padding-bottom: 20px; padding-top: 20px;">Statisztika</h3>
                    <hr>
                    <table style="margin-left: auto; margin-right: auto; text-align: left;">
                        <tr>
                            <th>
                                <h4>Utoljára megtekintett film</h4>
                            </th>
                            <td>
                                <?PHP
                                    $stmt = $conn->prepare("SELECT * FROM Users WHERE UName = ?");
                                    $stmt->bind_param("s", $_SESSION['UName']);
                                    $stmt->execute();
                                    $result = $stmt->get_result();
                                    $users = $result->fetch_all(MYSQLI_ASSOC);
                                    $UserID = $users[0]['UserID'];
                                    $stmt = $conn->prepare("SELECT * FROM recents JOIN links ON recents.movieID = links.id WHERE recents.userID = ? ORDER BY recents.movieID DESC");
                                    $stmt->bind_param("i", $UserID);
                                    $stmt->execute();
                                    $result = $stmt->get_result();
                                    $recents = $result->fetch_all(MYSQLI_ASSOC);
                                    if(count($recents) > 0){
                                        $stmt = $conn->prepare("SELECT * FROM links WHERE id = ?");
                                        $stmt->bind_param("i", $recents[0]['movieID']);
                                        $stmt->execute();
                                        $result = $stmt->get_result();
                                        $movies = $result->fetch_all(MYSQLI_ASSOC);
                                        echo $movies[0]['movie_title'];
                                    }
                                    else{
                                        echo "Nincs adat";
                                    }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <h4>Kedvenc filmek száma</h4>
                            </th>
                            <td>
                                <?PHP
                                    $stmt = $conn->prepare("SELECT * FROM favorites WHERE userID = ?");
                                    $stmt->bind_param("i", $UserID);
                                    $stmt->execute();
                                    $result = $stmt->get_result();
                                    $favorites = $result->fetch_all(MYSQLI_ASSOC);
                                    echo count($favorites);
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <h4>Megtekintett filmek száma</h4>
                            </th>
                            <td>
                                <?PHP
                                    $stmt = $conn->prepare("SELECT * FROM recents WHERE userID = ?");
                                    $stmt->bind_param("i", $UserID);
                                    $stmt->execute();
                                    $result = $stmt->get_result();
                                    $recents = $result->fetch_all(MYSQLI_ASSOC);
                                    echo count($recents);
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <h4>Kedvenc kategória</h4>
                            </th>
                            <td>
                                <?PHP
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
                                        if($value >= $Count){
                                            $Out .= $key . ", ";
                                            $Count = $value;
                                        }
                                        else{
                                            break;
                                        }
                                    }
                                    echo substr($Out, 0, -2);
                                ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div id="loader" style="display: flex;"><div class="spinner"></div></div>

    <script>
        document.onreadystatechange = function () {
            if (document.readyState !== "complete") {
                document.querySelector(
                    "body").style.visibility = "visible";
                document.querySelector(
                    "#loader").style.visibility = "visible";
            } else {
                document.querySelector(
                    "#loader").style.display = "none";
                document.querySelector(
                    "body").style.visibility = "visible";
            }
        };


    </script>
    <script>
        document.getElementById('searchForm').addEventListener('submit', function(event) { // Change submit to click and it'll do real time search (tinkering with the code is needed to it tho)
            event.preventDefault();
            Search(event);
        });
    
        document.getElementById('Recents').addEventListener('click', function(event){
            event.preventDefault();
            Recents(event);
        });

        document.getElementById('Favorites').addEventListener('click', function(event){
            event.preventDefault();
            Favorites(event);
        });

        document.getElementById('Settings').addEventListener('click', function(event){
            event.preventDefault();
            Settings(event);
        });

        document.getElementById('Change_password').addEventListener('click', function(event){
            event.preventDefault();
            const passwordFields = document.getElementById('PasswordNew');
            passwordFields.style.display = passwordFields.style.display === 'none' ? 'block' : 'none';
            const Label = document.getElementById('Change_password');
            Label.innerText = Label.innerText === 'Jelszó módosítása' ? 'Mégse' : 'Jelszó módosítása';
        });

        document.getElementById('Save').addEventListener('click', function(event){
            event.preventDefault();
            const CurrPass = document.getElementById('CurrPass').value;
            const NewPass1 = document.getElementById('NewPass1').value;
            const NewPass2 = document.getElementById('NewPass2').value;
            if(NewPass1 !== NewPass2){
                alert('A két jelszó nem egyezik meg!');
                return;
            }
            if(NewPass1.length < 8 || !/[A-Z]/.test(NewPass1) || !/[a-z]/.test(NewPass1) || !/[0-9]/.test(NewPass1) || !/[^a-zA-Z\d]/.test(NewPass1)){
                alert('A jelszónak legalább 8 karakter hosszúnak kell lennie, tartalmaznia kell legalább egy nagybetűt, egy kisbetűt, egy számot és egy speciális karaktert!');
                return;
            }
            const data = new FormData();
            data.append('CurrPass', CurrPass);
            data.append('NewPass', NewPass1);
            fetch('Login/ChangePassword.php', {
                method: 'POST',
                body: data
            }).then(response => response.text()).then(data => {
                if(data === '4'){
                    alert('A jelszó sikeresen megváltozott!');
                }
                else if(data === '0'){
                    alert('A jelszó megváltoztatása sikertelen!');
                }
                else if(data === '1'){
                    alert('A felhasználó nem létezik!');
                }
                else if(data === '2'){
                    alert('A jelenlegi jelszó nem megfelelő!');
                }
                else if(data === '3'){
                    alert('A jelenlegi jelszó vagy az új jelszó üres!');
                }
            });
        });

        document.getElementById('SignOut').addEventListener('click', function(event){
            event.preventDefault();
            // Run the logout script in the background
            fetch('Login/Logout.php')
            .then(response => response.text())
            .then(data => {
                if(data === '0'){
                    alert('Sikertelen kijelentkezés!');
                }
                else if(data === '1'){
                    window.location.href = 'Login/Login.html';
                }
            });
        });
    </script>
    <script>
        let Options = document.querySelectorAll('.Option');
        Options.forEach(option => {
            option.addEventListener('click', function() {
                Options.forEach(option => option.style.backgroundColor = 'transparent');
                option.style.backgroundColor = 'rgba(0, 0, 0, 0.1)';
            });
        });
    </script>
    <script>
        if (window.innerWidth <= 768) {
            const toggleButton = document.querySelector('.categories-header');
            const categoriesGrid = document.getElementById('categoriesGrid');
            const arrow = document.querySelector('.categories-arrow');

            toggleButton.addEventListener('click', () => {
                categoriesGrid.classList.toggle('open');
                arrow.style.transform = categoriesGrid.classList.contains('open') 
                    ? 'rotate(180deg)' 
                    : 'rotate(0deg)';
            });
        }

    </script>

    <!-- This is where potential data reception via url goes -->
    <script>
        const params = new URLSearchParams(window.location.search);
        switch(params.get('Page')) {
            case 'Recents':
                Recents(event);
                break;
            case 'Favorites':
                Favorites(event);
                break;
            case 'Settings':
                Settings(event);
                break;
            default:
                break;
        }
    </script>
</body>
</html>