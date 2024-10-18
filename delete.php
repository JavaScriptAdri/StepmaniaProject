<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['songId'])) {
    $songId = $_POST['songId'];

    // Cargar el archivo JSON
    $songs = json_decode(file_get_contents('songs.json'), true);
    $updatedSongs = [];

    // Buscar y eliminar la canción
    foreach ($songs as $song) {
        if ($song['id'] != $songId) {
            $updatedSongs[] = $song;
        } else {
            // Eliminar el archivo de la canción y la portada
            $songFilePath = 'uploads/' . $song['file'];
            $coverFilePath = 'uploads/' . $song['cover'];

            // Comprobar si los archivos existen antes de intentar borrarlos
            if (file_exists($songFilePath)) {
                unlink($songFilePath);
            }
            if (file_exists($coverFilePath)) {
                unlink($coverFilePath);
            }
        }
    }

    // Guardar la nueva lista en el JSON
    file_put_contents('songs.json', json_encode($updatedSongs, JSON_PRETTY_PRINT));
    
    header('Location: music.php');
    exit();
} else {
    echo "Formulario no válido.";
}
?>
