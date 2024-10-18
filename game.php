<?php
session_start();
$songs = json_decode(file_get_contents('songs.json'), true);
$selectedSong = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['songId'])) {
    // Buscar la cançó seleccionada per la seva ID
    foreach ($songs as $song) {
        if ($song['id'] == $_POST['songId']) {
            $selectedSong = $song;
            break;
        }
    }

    // Verificar si s'ha trobat la cançó
    if (!$selectedSong) {
        die('Cançó no trobada.');
    }

    // Redirigir a la selecció de personatges amb el songId
    header('Location: personajes.php?songId=' . $selectedSong['id']);
    exit;
}
?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seleccionar i Jugar</title>
    <link rel="stylesheet" href="game.css">
    <link href='https://fonts.googleapis.com/css?family=Press+Start+2P' rel='stylesheet'>
</head>
<body>
    <div class="title">Selecciona una cançó per jugar</div>
    <div class="playlist" id="playlist">
        <?php
        if ($songs) {
            usort($songs, function($a, $b) {
                return strcmp($a['title'], $b['title']);
            });
            foreach ($songs as $song) {
                echo '<div class="song" data-index="' . $song['id'] . '">';
                echo '<img src="uploads/' . htmlspecialchars($song['cover']) . '" alt="Caràtula" width="100">';
                echo '<h3>' . htmlspecialchars($song['title']) . '</h3>';
                echo '<p>' . htmlspecialchars($song['artist']) . '</p>';
                echo '</div>';
            }
        } else {
            echo '<p>No hi ha cançons disponibles.</p>';
        }
        ?>
    </div>
    <button class="home-button" onclick="window.location.href='index.html';">Home</button>

    <script>
        const songs = document.querySelectorAll('.song');
        let currentIndex = 0;

        function updateSelection(index) {
            songs.forEach((song, i) => {
                song.classList.remove('selected');
                if (i === index) song.classList.add('selected');
            });
        }

        document.addEventListener('keydown', (event) => {
            if (event.key === 'ArrowRight') {
                currentIndex = (currentIndex + 1) % songs.length;
                updateSelection(currentIndex);
            } else if (event.key === 'ArrowLeft') {
                currentIndex = (currentIndex - 1 + songs.length) % songs.length;
                updateSelection(currentIndex);
            } else if (event.key === 'Enter') {
                const selectedSong = songs[currentIndex];
                const songId = selectedSong.dataset.index;
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'game.php';
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'songId';
                input.value = songId;
                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            }
        });

        updateSelection(currentIndex);
    </script>
</body>
</html>
