<?php
$songId = isset($_GET['songId']) ? $_GET['songId'] : '';
?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seleccionar Personatge</title>
    <link rel="stylesheet" href="personajes.css">
    <link href='https://fonts.googleapis.com/css?family=Press+Start+2P' rel='stylesheet'>
</head>
<body>
    <div class="title">Selecciona un Personatge</div>
    <div class="container">
        <div class="playlist" id="characterPlaylist">
            <div class="character" data-character="charlton">
                <img src="personajes/charlton_menu.gif" alt="Charlton">
                <h3>Charlton</h3>
            </div>
            <div class="character" data-character="homer">
                <img src="personajes/homer_menu.gif" alt="Homer">
                <h3>Homer</h3>
            </div>
            <div class="character" data-character="patricio">
                <img src="personajes/patricio_menu.gif" alt="Patricio">
                <h3>Patricio</h3>
            </div>
            <div class="character" data-character="sheldon">
                <img src="personajes/sheldon_menu.gif" alt="Sheldon">
                <h3>Sheldon</h3>
            </div>
            <div class="character" data-character="spongebob">
                <img src="personajes/spongebob_menu.gif" alt="SpongeBob">
                <h3>SpongeBob</h3>
            </div>
        </div>
    </div>

    <script>
        // Crear un objeto que asocie cada personaje con su archivo de sonido
        const characterSounds = {
            charlton: 'sonidospersonaje/the-fresh-prince-of-bel-air-made-with-Voicemod.mp3',
            homer: 'sonidospersonaje/HOMER SIMPSON HERE - AUDIO FROM JAYUZUMI.COM.mp3',
            patricio: 'sonidospersonaje/bob-esponja-audio-audio.mp3',
            sheldon: 'sonidospersonaje/I\'M GOING TO STAND HERE - AUDIO FROM JAYUZUMI.COM.mp3',
            spongebob: 'sonidospersonaje/bob-esponja-audio-audio-esponja.mp3'
        };

        // Función para reproducir el sonido del personaje
        function playCharacterSound(character) {
            const sound = new Audio(characterSounds[character]);
            sound.play();
        }

        const characters = document.querySelectorAll('.character');
        let currentIndex = 0;

        // Marcar el personaje seleccionado inicialmente
        characters[currentIndex].classList.add('selected');

        function updateSelection() {
            characters.forEach((character, index) => {
                character.classList.toggle('selected', index === currentIndex);
            });
        }

        // Controlar la navegación con las teclas de flecha y reproducir sonido
        document.addEventListener('keydown', function (event) {
            if (event.key === 'ArrowDown') {
                currentIndex = (currentIndex + 1) % characters.length;
                updateSelection();
                playCharacterSound(characters[currentIndex].dataset.character);
            } else if (event.key === 'ArrowUp') {
                currentIndex = (currentIndex - 1 + characters.length) % characters.length;
                updateSelection();
                playCharacterSound(characters[currentIndex].dataset.character);
            } else if (event.key === 'Enter') {
                const selectedCharacter = characters[currentIndex].dataset.character;
                window.location.href = `play.php?character=${selectedCharacter}&songId=<?php echo $songId; ?>`;
            } else if (event.key === 'Escape') {
                window.location.href = 'index.html';
            }
        });

        // Reproducir sonido al pasar el ratón sobre un personaje
        characters.forEach(character => {
            character.addEventListener('mouseenter', function() {
                playCharacterSound(this.dataset.character);
            });

            // Selección de personaje con clic
            character.addEventListener('click', function() {
                const selectedCharacter = this.dataset.character;
                window.location.href = `play.php?character=${selectedCharacter}&songId=<?php echo $songId; ?>`;
            });
        });
    </script>
</body>
</html>
