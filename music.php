<?php
ob_start(); // Activa el buffer de sortida per evitar problemes amb les capçaleres

function getMimeType($filePath) {
    $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    switch ($extension) {
        case 'mp3':
            return 'audio/mpeg';
        case 'ogg':
            return 'audio/ogg';
        case 'txt':
            return 'text/plain';
        case 'jpg':
        case 'jpeg':
            return 'image/jpeg';
        case 'png':
            return 'image/png';
        case 'gif':
            return 'image/gif';
        default:
            return 'application/octet-stream'; // tipus de fitxer per defecte
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Comprovar si s'ha enviat una sol·licitud per eliminar una cançó
    if (isset($_POST['deleteSong'])) {
        $songs = json_decode(file_get_contents('songs.json'), true) ?: [];
        $songIdToDelete = intval($_POST['songId']);
        
        foreach ($songs as $key => $song) {
            if ($song['id'] === $songIdToDelete) {
                // Eliminar el fitxer de música i la caràtula del servidor
                unlink('uploads/' . $song['file']);
                if ($song['cover'] && file_exists('uploads/' . $song['cover'])) {
                    unlink('uploads/' . $song['cover']);
                }
                // Eliminar la cançó de l'array
                unset($songs[$key]);
                break;
            }
        }

        file_put_contents('songs.json', json_encode(array_values($songs), JSON_PRETTY_PRINT));
        header('Location: music.php'); // Redirigir després de l'eliminació
        exit();
    }

    $uploadDir = 'uploads/';
    
    // Crear el directori si no existeix
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $songs = json_decode(file_get_contents('songs.json'), true) ?: [];

    // Validació dels fitxers de música
    if (isset($_FILES['song']) && $_FILES['song']['error'] == 0) {
        $audioFileType = getMimeType($_FILES['song']['name']);
        if (!in_array($audioFileType, ['audio/mpeg', 'audio/ogg'])) {
            die('Format de cançó no vàlid. Només es permeten MP3 i OGG.');
        }
    } else {
        die('Fitxer de música és obligatori.');
    }

    // Validació del fitxer de caràtula
    if (isset($_FILES['cover']) && $_FILES['cover']['error'] == 0) {
        $coverFileType = getMimeType($_FILES['cover']['name']);
        if (!in_array($coverFileType, ['image/jpeg', 'image/png', 'image/gif'])) {
            die('Format de caràtula no vàlid. Només es permeten JPG, PNG i GIF.');
        }
    }

    // Validació del fitxer de joc
    $gameFilePath = '';
    if (isset($_FILES['gameFile']) && $_FILES['gameFile']['error'] == 0) {
        $gameFileType = getMimeType($_FILES['gameFile']['name']);
        if ($gameFileType !== 'text/plain') {
            die('El fitxer de joc ha de ser un fitxer de text (.txt).');
        }
        $gameFilePath = $uploadDir . basename($_FILES['gameFile']['name']);
        move_uploaded_file($_FILES['gameFile']['tmp_name'], $gameFilePath);
    }

    // Validació del textarea
    $gameData = isset($_POST['gameData']) ? trim($_POST['gameData']) : '';
    if (!empty($gameData) && empty($gameFilePath)) {
        $lines = explode("\n", $gameData);
        $numberOfElements = intval(array_shift($lines));

        if ($numberOfElements <= 0) {
            die('El número d\'elements ha de ser positiu.');
        }

        foreach ($lines as $line) {
            $parts = explode('#', trim($line));
            if (count($parts) < 3) {
                die('Format de fitxer de joc no vàlid. Cada línia ha de contenir: tecla # instant inicial # instant final.');
            }
            // Validació dels valors
            $key = trim($parts[0]);
            $start = floatval(trim($parts[1]));
            $end = floatval(trim($parts[2]));

            if ($start < 0 || $end < 0 || $start >= $end) {
                die('Els instants han de ser valors no negatius i l\'instant inicial ha de ser menor que l\'instant final.');
            }
        }

        // Crear fitxer de joc
        $gameFilePath = $uploadDir . uniqid('game_') . '.txt';
        file_put_contents($gameFilePath, "$numberOfElements\n" . implode("\n", $lines));
    }

    // Pujar el fitxer de música
    $uploadSongFile = $uploadDir . basename($_FILES['song']['name']);
    move_uploaded_file($_FILES['song']['tmp_name'], $uploadSongFile);

    // Crear un ID únic per a la nova cançó
    $newId = count($songs) > 0 ? end($songs)['id'] + 1 : 1;

    // Guardar les dades de la cançó
    $newSong = [
        'id' => $newId,
        'title' => htmlspecialchars($_POST['songName']),
        'artist' => htmlspecialchars($_POST['artist']),
        'file' => basename($_FILES['song']['name']),
        'url_game' => basename($gameFilePath),
        'duration' => htmlspecialchars($_POST['duration']), // Utilitzem la duració obtinguda
        'cover' => isset($_FILES['cover']) ? basename($_FILES['cover']['name']) : 'default_cover.png'
    ];
    
    $songs[] = $newSong;

    // Desar la caràtula
    if (isset($_FILES['cover']) && $_FILES['cover']['error'] == 0) {
        move_uploaded_file($_FILES['cover']['tmp_name'], $uploadDir . $newSong['cover']);
    }

    file_put_contents('songs.json', json_encode($songs, JSON_PRETTY_PRINT));
    header('Location: music.php'); // Redirigir després de la pujada per evitar el reenviament
    exit();
}

// Finalitzar el buffer de sortida
ob_end_flush();
?>

<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestor de Música</title>
    <link rel="stylesheet" href="music.css">
    <link href='https://fonts.googleapis.com/css?family=Press Start 2P' rel='stylesheet'>
</head>
<body>
    <div class="container">
        <div class="upload-form">
            <h2>Afegeix una nova cançó</h2>
            <form action="upload.php" method="post" enctype="multipart/form-data">
                <input type="text" name="songName" placeholder="Títol de la cançó" required>
                <input type="text" name="artist" placeholder="Artista" required>
                <input type="file" name="song" accept="audio/mpeg, audio/ogg" required onchange="calculateDuration(this)">
                <input type="hidden" name="duration" id="durationInput"> <!-- Campo oculto para la duración -->
                <input type="file" name="cover" accept="image/*">
                <input type="file" name="gameFile" accept=".txt">
                <textarea name="gameData" placeholder="Dades del joc (opcional)"></textarea>
                <button type="submit">Pujar Cançó</button>
                <a href="index.html" class="home-button">Home</a>
            </form>
        </div>

        <div class="playlist">
            <h2>Playlist</h2>
            <?php
            $songs = json_decode(file_get_contents('songs.json'), true);
            if ($songs) {
                // Ordenar cançons per títol
                usort($songs, function ($a, $b) {
                    return strcmp($a['title'], $b['title']);
                });
                
                foreach ($songs as $song) {
                    echo '<div class="song">';
                    echo '<img src="uploads/' . htmlspecialchars($song['cover']) . '" alt="Caràtula" width="100">';
                    echo '<h3>' . htmlspecialchars($song['title']) . '</h3>';
                    echo '<p>' . htmlspecialchars($song['artist']) . '</p>';
                    echo '<p>Durada: ' . htmlspecialchars($song['duration']) . '</p>';
                    echo '<audio controls><source src="uploads/' . htmlspecialchars($song['file']) . '" type="audio/mpeg"></audio>';
                    echo '<form action="upload.php" method="post">';
                    echo '<input type="hidden" name="songId" value="' . htmlspecialchars($song['id']) . '">';
                    echo '<button type="submit" name="deleteSong">Eliminar</button>';
                    echo '</form>';
                    echo '</div>';
                }
            } else {
                echo '<p>No hi ha cançons a la playlist.</p>';
            }
            ?>
        </div>
    </div>

    <script>
        function calculateDuration(input) {
            const file = input.files[0];
            const audio = new Audio(URL.createObjectURL(file));
            audio.addEventListener('loadedmetadata', function() {
                document.getElementById('durationInput').value = audio.duration; // Assignar la durada al camp ocult
            });
        }
    </script>
</body>
</html>
