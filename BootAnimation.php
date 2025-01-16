<?php
    session_start();
    if(!isset($_SESSION['UName'])){
        header('Location: Login/Login.html');
        exit();
    }
    if(!isset($_SESSION['DoneBoot'])){
        $_SESSION['DoneBoot'] = true;
    }
    // else{
    //     header('Location: index.php');
    //     exit();
    // }
    $username = $_SESSION['UName'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loading User Data</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #121212;
            color: #ffffff;
            overflow: hidden;
        }

        #DataContainer {
            display: block;
            width: 500px;
            text-align: center;
        }

        span {
            font-size: 72px;
            font-weight: bold;
            color: #ffffff;
            text-transform: uppercase;
            letter-spacing: 0.2rem;
            position: relative;
            display: inline-block;
            margin-bottom: -12px;
        }

        span:nth-child(2) {
            background: linear-gradient(0deg, rgba(255, 255, 255, 0) 0%, rgba(255, 255, 255, 0.03) 50%, rgba(255, 255, 255, 0.2) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            filter: blur(2px); /* Adjust the blur value as needed */
            transform: rotateX(57deg);
            margin-top: -15px;
        }

        @keyframes pulse {
            0%, 100% { background-color: #121212; }
            50% { background-color: #1e1e1e; }
        }

        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #121212;
            animation: pulse 8s infinite;
            z-index: -1;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }
            100% {
                transform: rotate(360deg);
            }
        }

        @keyframes fadeOut {
            0% {
                opacity: 1;
            }
            100% {
                opacity: 0;
            }
        }

        @keyframes fadeIn {
            0% {
                opacity: 0;
            }
            100% {
                opacity: 1;
            }
        }

        @keyframes FloatUp{
            0%{
                position: absolute;
                top: 50%;
            }
            100%{
                position: absolute;
                top: 30px;
            }
        }

        @keyframes FloatDown{
            0%{
                position: absolute;
                top: 30px;
            }
            100%{
                position: absolute;
                top: calc(50% - 120px);
            }
        }

        @keyframes FloatUpContainer{
            0%{
                top: calc(50% - 130px);
            }
            100%{
                top: calc(50% - 150px);
            }
        }

        @keyframes FloatDownContainer{
            0%{
                top: calc(50% - 150px);
            }
            100%{
                top: calc(50% - 130px);
            }
        }

        /* Spinner animáció betöltés előtt */
        .spinner {
            width: 50px;
            height: 50px;
            border: 6px solid #555555;
            border-top: 6px solid #ff0000;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-left: auto;
            margin-right: auto;
            margin-top: 50px;
        }

        .Conatiner{
            width: 55%;
            margin-left: auto;
            margin-right: auto;
            height: 400px;
            align-items: center;
            display: inline-flex;
            justify-content: space-around;
            background-color: rgb(90, 90, 90);
            border-radius: 20px;
        }

    </style>
</head>
<body>
    <div id="DataContainer">
        <span>MovieFlix</span>
        <span>MovieFlix</span>
        <div class="Conatiner" style="display: none;">
            <div class="UserProfile" style="display: none;">
                <img src="<?php echo $_SESSION['PFP']; ?>" alt="<?PHP echo $_SESSION['UName'] ?>" style="border-radius: 50%; width: 170px; height: 170px; margin-top: 20px">
                <h3 style="margin-top: 135px">Welcome, <?PHP echo $_SESSION['UName']; ?>!</h3>
            </div>
        </div>
        <div class="spinner"></div>
    </div>
    <script>
       setTimeout(() => {
            const l = document.querySelector('.spinner');
            l.style.animation = 'spin 1s linear infinite, fadeOut 2s forwards'; // Add fadeOut animation
            console.log(l);
        }, 1500); // Add fadeOut animation after 2 seconds

        setTimeout(() => {
            const l = document.querySelector('#DataContainer');
            l.style.animation = 'FloatUp 1s forwards, fadeIn 2s forwards'; // Add fadeIn animation
        }, 3000);

        setTimeout(() => {
            const l1 = document.querySelector('.Conatiner');
            l1.style.display = 'inline-flex';
            l1.style.marginTop = 'calc(50% - 130px)';
            l1.style.animation = 'FloatUpContainer 2s forwards, fadeIn 2s forwards'; // Add fadeIn animation
            const l = document.querySelector('.UserProfile');
            l.style.display = 'block';
            l.style.animation = 'FloatUpContainer 2s forwards, fadeIn 2s forwards'; // Add fadeIn animation
        }, 4000); // Redirect to index.php after 5 seconds

        setTimeout(() => {
            const l1 = document.querySelector('.Conatiner');
            l1.style.animation = 'FloatUpContainer 2s reverse, fadeOut 2s forwards'; // Add fadeIn animation
            const l = document.querySelector('.UserProfile');
            l.style.display = 'block';
            l.style.animation = 'FloatUpContainer 2s forwards, fadeIn 2s forwards'; // Add fadeIn animation
        }, 6000); // Redirect to index.php after 5 seconds

        setTimeout(() => {
            const container = document.querySelector('.Conatiner');
            container.style.display = 'none';

            const dataContainer = document.querySelector('#DataContainer');
            dataContainer.style.animation = 'FloatDown 2s forwards';
        }, 8000); // Redirect to index.php after 5 seconds

        setTimeout(() => {
            const dataContainer = document.querySelector('#DataContainer');
            dataContainer.style.animation = 'fadeOut 2s forwards';
        }, 10000); // Redirect to index.php after 5 seconds

        setTimeout(() => {
            window.location.href = 'index.php';
        }, 12000); // Redirect to index.php after 5 seconds
    </script>
</body>
</html>