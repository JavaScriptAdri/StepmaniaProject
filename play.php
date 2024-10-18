<?php
session_start();

$songs = json_decode(file_get_contents('songs.json'), true);

$songTitle = 'Cançó no trobada';
$songArtist = 'Artista desconegut';
$songFile = '';
$songDuration = 0; // Nueva variable para la duración de la canción

if (isset($_GET['songId'])) {
    $selectedSong = null;
    foreach ($songs as $song) {
        if ($song['id'] == $_GET['songId']) {
            $selectedSong = $song;
            break;
        }
    }

    if ($selectedSong) {
        $songTitle = htmlspecialchars($selectedSong['title']);
        $songArtist = htmlspecialchars($selectedSong['artist']);
        $songFile = 'uploads/' . htmlspecialchars($selectedSong['file']);
        $songDuration = $selectedSong['duration']; // Obtener la duración de la canción desde el JSON
    } else {
        die('Cançó no trobada.');
    }
}

$character = isset($_GET['character']) ? $_GET['character'] : 'charlton';
$characterGameGif = "personajes/{$character}_game.gif";
?>

<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jugant: <?php echo $songTitle; ?></title>
    <link rel="stylesheet" href="game.css">
    <link href='https://fonts.googleapis.com/css?family=Press+Start+2P' rel='stylesheet'>
    <style>
        .barra-progreso {
            margin-top: 20px;
            width: 80%;
            text-align: center;
        }

        progress {
            width: 100%;
            height: 30px;
        }
    </style>
</head>
<body>

    <div id="song-info">Jugant: <?php echo $songTitle; ?> de <?php echo $songArtist; ?></div>
    
    <!-- Mostrar el GIF del personatge -->
    <div id="character-gif">
        <img src="<?php echo $characterGameGif; ?>" alt="Personatge">
    </div>

    <div id="score">Puntuació: <span id="points">0</span></div>
    <div id="game-area">
        <div id="up" class="square"><img src="images/arrow_up.png" alt="Amunt"></div>
        <div id="right" class="square"><img src="images/arrow_right.png" alt="Dreta"></div>
        <div id="down" class="square"><img src="images/arrow_down.png" alt="Avall"></div>
        <div id="left" class="square"><img src="images/arrow_left.png" alt="Esquerra"></div>
    </div>

    <div class="barra-progreso">
        <progress id="file" max="100" value="0">0%</progress>
        <span id="progress-text">0%</span>
    </div>

    <audio id="background-audio" style="display: none;" onended="endGame()">
        <source src="<?php echo $songFile; ?>" type="audio/mpeg">
        El teu navegador no suporta l'element d'àudio.
    </audio>

    <!-- Botón para iniciar el juego -->
    <button class="start-button" onclick="startGame()">Començar Joc</button>

    <!-- Botón para seleccionar otra canción -->
    <form action="game.php" method="POST">
        <button class="select-song-button">Seleccionar una altra cançó</button>
    </form>

    <script>
    const scoreDisplay = document.getElementById('points');
    const squares = {
        up: document.getElementById('up'),
        down: document.getElementById('down'),
        left: document.getElementById('left'),
        right: document.getElementById('right')
    };
    let score = 0;
    let isPaused = true;
    let activeSquare = null;
    let audio = document.getElementById('background-audio');
    let progressBar = document.getElementById('file');
    let progressText = document.getElementById('progress-text');
    let currentValue = 0;
    let increment = 0;
    let flechaInterval = null;

    const songDuration = <?php echo $songDuration; ?>; // Duración de la canción desde el JSON

    // Funció per establir una cookie
    function setCookie(name, value, days) {
        const d = new Date();
        d.setTime(d.getTime() + (days * 24 * 60 * 60 * 1000));
        const expires = "expires=" + d.toUTCString();
        document.cookie = name + "=" + value + ";" + expires + ";path=/";
    }

    // Funció per obtenir el valor d'una cookie
    function getCookie(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(';').shift();
        return null;
    }

    // Funció per guardar la puntuació a les cookies
    function saveScore(name, score) {
        const scoresCookie = getCookie('scores');
        let scores = [];

        // Si ja existeixen puntuacions, les obtenim i les parsegem des del JSON
        if (scoresCookie) {
            scores = JSON.parse(scoresCookie);
        }

        // Afegim la nova puntuació
        scores.push({ name, score });

        // Guardem l'array de puntuacions com a JSON a la cookie
        setCookie('scores', JSON.stringify(scores), 7); // 7 dies de duració
    }

    // Funció per acabar el joc i guardar la puntuació
    function endGame() {
        const playerName = prompt("Introdueix el teu nom:");

        // Guardem la puntuació a les cookies
        saveScore(playerName, score);

        // Redirigim a la pàgina de scoreboard
        window.location.href = 'scoreboard.html';
    }

    function randomSquare() {
        const directions = ['up', 'down', 'left', 'right'];
        return directions[Math.floor(Math.random() * 4)];
    }

    function activateSquare() {
        if (isPaused || activeSquare) return;
        const direction = randomSquare();
        activeSquare = squares[direction];
        const img = activeSquare.querySelector('img');
        img.style.display = 'block';

        setTimeout(() => {
            if (activeSquare) {
                updateScore(-50);
                deactivateSquare();
            }
        }, 1000);
    }

    function deactivateSquare() {
        if (activeSquare) {
            const img = activeSquare.querySelector('img');
            img.style.display = 'none';
            activeSquare = null;
        }
    }

    function updateScore(amount) {
        score += amount;
        scoreDisplay.innerText = score;
    }

    function updateProgressBar() {
        currentValue += increment;
        if (currentValue < 100) {
            progressBar.value = currentValue;
            progressText.textContent = Math.floor(currentValue) + '%';
            setTimeout(updateProgressBar, 1000); // Actualización cada segundo
        } else {
            progressBar.value = 100;
            progressText.textContent = '100%';
            clearInterval(flechaInterval); // Detenim les fletxes
            audio.pause(); // Detenim la cançó
            isPaused = true;

            // Cridem a la funció per acabar el joc
            endGame();
        }
    }

    function startGame() {
        isPaused = false;
        audio.play();

        // Fem servir la duració del JSON per calcular l'increment
        increment = 100 / songDuration;
        updateProgressBar(); // Iniciem la barra de progrés

        // Iniciem la generació de fletxes
        flechaInterval = setInterval(activateSquare, 1500);
    }

    document.addEventListener('keydown', (event) => {
        if (isPaused || !activeSquare) return;

        const keyMap = {
            'ArrowUp': 'up',
            'ArrowDown': 'down',
            'ArrowLeft': 'left',
            'ArrowRight': 'right'
        };

        const pressedKey = keyMap[event.key];
        if (pressedKey) {
            if (activeSquare.id === pressedKey) {
                updateScore(100);
            } else {
                updateScore(-50);
            }
            deactivateSquare();
        }
    });
    </script>

</body>
</html>
