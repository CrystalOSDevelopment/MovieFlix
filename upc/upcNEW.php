<?php
  $MovieID = $_GET['MovieID'];

  $pdo = new PDO('mysql:host=localhost;dbname=links_db', 'root', '');
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  $stmt = $pdo->prepare("SELECT * FROM trailers WHERE imdb_id = :imdb_id");
  $stmt->execute(['imdb_id' => $MovieID]);
  $movie = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($movie) {
    // Set the title of the page
    $URL = $movie['trailer_link'];
  }

  // Get movie title form links by imdb_code
  $stmt = $pdo->prepare("SELECT * FROM links WHERE imdb_code = :imdb_code");
  $stmt->execute(['imdb_code' => $MovieID]);
  $movie = $stmt->fetch(PDO::FETCH_ASSOC);
  $MovieTitle = $movie['movie_title'];

  // If the movie name is longer than 20 characters, cut it
  if (strlen($MovieTitle) > 27) {
    $MovieTitle = substr($MovieTitle, 0, 27) . "...";
  }

  // Get the url of the movie
  $stmt = $pdo->prepare("SELECT * FROM links WHERE imdb_code = :imdb_code");
  $stmt->execute(['imdb_code' => $MovieID]);
  $movie = $stmt->fetch(PDO::FETCH_ASSOC);
  if ($movie) {
    $MovieLink = $movie['link'];
  }
?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8" />
    <title>UPC Videótár</title>
    <link rel="stylesheet" href="globals.css" />
    <link rel="stylesheet" href="../Resources/Styles/style3.css" />
    </head>
    <style>
        body {
            background-color: black;
            margin: 0;
        }
        
        div {
            font-family: sans-serif;
        }

        input[type="range"] {
          -webkit-appearance: none;
          appearance: none;
          height: 10px;
          width: 100%;
          margin-top: 10px;
          background: transparent;
          border-radius: 0px;
          outline: none;
          position: relative;
          bottom: 8px;
          
        }

        input[type="range"]::-webkit-slider-thumb {
          -webkit-appearance: none;
          appearance: none;
          width: 5px;
          height: 14px;
          background: #afafaf;
          border-radius: 0%;
          cursor: pointer;
        }

        input[type="range"]::-moz-range-thumb {
          width: 20px;
          height: 20px;
          background: #3498db;
          border-radius: 50%;
          cursor: pointer;
        }

        .container {
            transition: opacity 0.3s;
            opacity: 0;
          transform: scale(2.5);
          top: 5%;
          left: 30%;
          max-width: 800px; /* Példa: maximális szélesség 800 pixel */
          margin: 0px;
          text-align: center;
          width: 100%;
          box-sizing: border-box;
        }

        .container:hover {
                opacity: 1; /* Ha az egér fölötte van, láthatóvá válik */
            }
            #fullscreenButton {
          cursor: pointer;
        }
    </style>
  </head>
  <body>
    <video id="myVideo" autoplay style="position: fixed; object-fit:contain;" width="100%" height="100%"> 
      <source src="<?php echo htmlspecialchars($URL); ?>" type="video/mp4">
      Your browser does not support the video tag.
    </video>

    <script>
      document.addEventListener('DOMContentLoaded', function() {
        var myVideo = document.getElementById('myVideo');
        myVideo.play();
      });
    </script>

      <div style="position: absolute;">
          <img id="myImage" style="transform: scale(1.9); left: 50%; position: relative; margin-top: 10%; border:0px solid black;" src="press5.png" alt="PRESS" width="300px" height="auto">
      </div>

      <div class="frame">
        <div class="group-wrapper" style="transform:scale(1.3)">
        <div class="group">
        <div class="overlap">
        <div class="overlap-group">
        <div class="rectangle"></div>
        <img class="union" src="double-arrow.png" />
        <div class="text-wrapper">22:11</div>
        <div class="div">Műsor1</div>
        </div>
        <div class="overlap-2">
        <div class="rectangle-2"></div>
        <div class="text-wrapper-2">23:02</div>
        <div class="text-wrapper-3">Műsor2</div>
        </div>
        <img class="vector" src="arrow.png" />
        <img class="img" src="arrow.png" style="transform: rotate(-180deg);" />
        <div class="overlap-3">
        <img class="group-2" src="hd.png" />
        <img class="group-3" src="sound.png" />
        <img class="group-4" src="cc.png" />
        <img id="fullscreenButton" onclick="openFullscreen();" class="group-5" src="fullscreen.png"/>
        <div class="slider">
        <div class="overlap-group-2">
        <div class="rectangle-3"></div>
        <div class="rectangle-4"></div>
        <div class="rectangle-5"></div>
        <input type="range" id="seekbar" value="0" step="0.01" onchange="seekVideo()" style="position: relative;">
        </div>
        </div>
        </div>
        <div id="idoDiv" class="element">2005.02.21.&nbsp;&nbsp;15:24</div>
        <div id="currentVideoName" class="text-wrapper-4"><?php echo $MovieTitle; ?></div>
        <div class="text-wrapper-5">13</div>
        <p class="element-aj-nl">15:15- 15:30&nbsp;&nbsp;- Ajánló - Filmajánló</p>
        </div>
        <div class="overlap-4">
        
        <div class="info">
        <img class="frutiger-aero-button" src="i.png" />
        <div class="text-wrapper-6">Info</div>
        </div>
        <div class="kiv">
        <img class="group-6" src="ok.png" />
        <div class="text-wrapper-7">Kiválaszt</div>
        </div>
        <div class="overlap-5">
        <div class="kiv-2">
        <img class="group-6" src="txt.png" />
        <div class="text-wrapper-7">Szöveg</div>
        </div>
        <div class="kiv-3">
        <img class="group-7" src="blue.png" />
        <div class="text-wrapper-7">Kedvencek</div>
        </div>
        </div>
        </div>
        </div>
        </div>
      </div>
  </body>

  <script>
      var myVideo = document.getElementById("myVideo");
      var currentVideoNameDiv = document.getElementById("currentVideoName");

      function displayCurrentVideoName() {
          // Get the file name from the video's current source
          var fileName = myVideo.currentSrc.split('/').pop();
          fileName = fileName.replace(/\.[^/.]+$/, "");
          //currentVideoNameDiv.innerText = "" + fileName;
      }

      function startAutoplay() {
          myVideo.play();
          displayCurrentVideoName();
      }
      document.addEventListener("keydown", function(event) {
          if (event.keyCode === 32) { // 32 is the keycode for space
              startAutoplay();
          }
      });

      // Add your other scripts here
  </script>

  <script>
    var myVideo = document.getElementById("myVideo");
    var seekbar = document.getElementById("seekbar");

    function seekVideo() {
      var seekto = myVideo.duration * (seekbar.value / 100);
      myVideo.currentTime = seekto;
    }

    myVideo.addEventListener("timeupdate", function() {
      var newTime = myVideo.currentTime * (100 / myVideo.duration);
      seekbar.value = newTime;
    });
  </script> 

  <script>
      function pontosIdo() {
          const now = new Date();
          const ev = now.getFullYear();
          const honapok = ["jan", "feb", "márc", "ápr", "máj", "jún", "júl", "aug", "szept", "okt", "nov", "dec"];
          const honap = honapok[now.getMonth()];
          const nap = now.getDate();
          const ora = now.getHours();
          const perc = now.getMinutes();
  
          const pontosIdoString = `${ev} ${honap} ${nap}. ${ora}:${perc < 10 ? '0' : ''}${perc}`;
          document.getElementById('idoDiv').innerText = pontosIdoString;
      }
  
      // Frissítsük az időt minden másodpercben
      setInterval(pontosIdo, 1000);
  
      // Az oldal betöltésekor is inicializáljuk az időt
      pontosIdo();
  </script>

  <script>
      var images = ["press.png", "press2.png", "press.png"];
      var currentIndex = 0;
      var showCounter = 0;
      var maxShowCount = 6;

      // Kép megváltoztatása és elrejtése 3 másodperces időközönként
      var intervalId = setInterval(function() {
          changeImage();
          showCounter++;

          if (showCounter >= maxShowCount) {
              clearInterval(intervalId);
              hideImage();
          }

      }, 3000);

      // Kép megváltoztatása JavaScript segítségével
      function changeImage() {
          var image = document.getElementById("myImage");
          currentIndex = (currentIndex + 1) % images.length;
          image.src = images[currentIndex];
          image.alt = "Kép " + (currentIndex + 1);
      }

      // Kép elrejtése JavaScript segítségével
      function hideImage() {
          var image = document.getElementById("myImage");
          image.style.display = "none";
      }
  </script>

  <script>
    var elem = document.getElementById("myvideo");
    function openFullscreen() {
      if (elem.requestFullscreen) {
        elem.requestFullscreen();
      } else if (elem.webkitRequestFullscreen) { /* Safari */
        elem.webkitRequestFullscreen();
      } else if (elem.msRequestFullscreen) { /* IE11 */
        elem.msRequestFullscreen();
      }
    }
  </script>
