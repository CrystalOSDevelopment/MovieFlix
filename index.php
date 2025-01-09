<?PHP
    session_start();
    if(!isset($_SESSION['UName'])){
        header('Location: Login/Login.html');
    }
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
            <div class="menu-item">Beállítások</div>
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
            default:
                break;
        }
    </script>
</body>
</html>