<?php
require_once 'config.php';

// Pobierz wszystkie konie z bazy
$sql = "SELECT * FROM konie ORDER BY nazwa";
$result = $conn->query($sql);
$konie = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $konie[] = $row;
    }
}

$message = getMessage();
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KonZValony - Wypożyczalnia koni z charakterem</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header>
        <div class="header-content">
            <div class="logo-container">
                <img src="images/image.png" alt="KonZValony - wypożyczalnia koni" class="logo">
            </div>
            <h1>KonZValony</h1>
            <p class="tagline">Wypożyczalnia koni, które mają więcej charakteru niż Twój były!</p>
        </div>
        <nav>
            <ul>
                <li><a href="index.php" class="active">Stajnia</a></li>
                <li><a href="blog.php">Blog</a></li>
                <li><a href="mapa.php">Mapa</a></li>
                <?php if (isLoggedIn()): ?>
                    <li><a href="panel.php">Panel</a></li>
                    <li><a href="ulubione.php">Ulubione</a></li>
                    <li><a href="polec.php">Poleć znajomym</a></li>
                    <li><a href="logout.php">Wyloguj (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a></li>
                <?php else: ?>
                    <li><a href="login.php">Logowanie</a></li>
                    <li><a href="register.php">Rejestracja</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <main>
        <?php if ($message): ?>
            <div class="message message-<?php echo $message['type']; ?>">
                <?php echo htmlspecialchars($message['text']); ?>
            </div>
        <?php endif; ?>

        <section class="hero">
            <h2>Poznaj nasze konie</h2>
            <p>Każdy z naszych koni to indywidualność z własną historią i charakterem. Wybierz swojego towarzysza przygód!</p>
        </section>

        <section class="horses-grid">
            <?php foreach ($konie as $kon): ?>
                <article class="horse-card">
                    <div class="horse-image">
                        <?php if (isLoggedIn()): ?>
                            <button class="favorite-btn" data-horse-id="<?php echo $kon['id']; ?>">
                                <i class="far fa-heart"></i>
                            </button>
                        <?php endif; ?>
                        <img src="images/<?php echo htmlspecialchars($kon['zdjecie']); ?>" 
                             alt="<?php echo htmlspecialchars($kon['nazwa']); ?> - nasz koń do wynajęcia">
                    </div>
                    <div class="horse-info">
                        <h3><?php echo htmlspecialchars($kon['nazwa']); ?></h3>
                        <p class="horse-breed">Rasa: <?php echo htmlspecialchars($kon['rasa']); ?>, wiek: <?php echo $kon['wiek']; ?> lat</p>
                        <p class="horse-description"><?php echo htmlspecialchars($kon['opis']); ?></p>
                        <div class="horse-footer">
                            <span class="horse-price"><?php echo number_format($kon['cena_za_dobe'], 2); ?> PLN/doba</span>
                            <?php if ($kon['dostepny']): ?>
                                <span class="badge available">Dostępny</span>
                                <?php if (isLoggedIn()): ?>
                                    <a href="panel.php?action=reserve&id=<?php echo $kon['id']; ?>" class="btn-reserve">Rezerwuj</a>
                                <?php else: ?>
                                    <a href="login.php" class="btn-reserve">Zaloguj się by rezerwować</a>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="badge unavailable">Wypożyczony</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>
    </main>

    <footer>
        <p>&copy; 2026 KonZValony - Wypożyczalnia koni, które rozweselą Cię nawet w deszczowy dzień!</p>
        <p class="small">* Nie ponosimy odpowiedzialności za rozerwanie oka saurona</p>
    </footer>

    <!-- Panel dostępności -->
    <div class="accessibility-panel">
        <button class="accessibility-toggle" aria-label="Otwórz panel dostępności">
            <span class="accessibility-icon">♿</span>
        </button>
        
        <div class="accessibility-menu">
            <h4>Ułatwienia dostępu</h4>
            
            <div class="accessibility-section">
                <p>Rozmiar czcionki:</p>
                <div class="font-size-controls">
                    <button class="font-size-btn small" data-size="small">A-</button>
                    <button class="font-size-btn normal active" data-size="normal">A</button>
                    <button class="font-size-btn large" data-size="large">A+</button>
                    <button class="font-size-btn xlarge" data-size="xlarge">A++</button>
                </div>
            </div>
            
            <div class="accessibility-section">
                <p>Kontrast:</p>
                <div class="contrast-controls">
                    <button class="contrast-btn normal active" data-contrast="normal">Normalny</button>
                    <button class="contrast-btn high" data-contrast="high">Wysoki</button>
                    <button class="contrast-btn dark" data-contrast="dark">Ciemny</button>
                </div>
            </div>
            
            <div class="accessibility-section">
                <p>Odstępy:</p>
                <div class="spacing-controls">
                    <button class="spacing-btn normal active" data-spacing="normal">Standard</button>
                    <button class="spacing-btn large" data-spacing="large">Szerokie</button>
                </div>
            </div>
            
            <div class="accessibility-section">
                <p>Animacje:</p>
                <div class="motion-controls">
                    <button class="motion-btn normal active" data-motion="normal">Włączone</button>
                    <button class="motion-btn reduced" data-motion="reduced">Wyłączone</button>
                </div>
            </div>
            
            <div class="accessibility-section">
                <p>Czytelność:</p>
                <div class="readability-controls">
                    <button class="readability-btn serif" data-readability="serif">Szeryfowa</button>
                    <button class="readability-btn sans active" data-readability="sans">Bezszeryfowa</button>
                    <button class="readability-btn mono" data-readability="mono">Monospace</button>
                </div>
            </div>
            
            <div class="accessibility-section">
                <p>Linia pomocnicza:</p>
                <div class="ruler-controls">
                    <button class="ruler-btn off active" data-ruler="off">Wyłącz</button>
                    <button class="ruler-btn line" data-ruler="line">Linia</button>
                    <button class="ruler-btn ruler" data-ruler="ruler">Linijka</button>
                </div>
            </div>
            
            <div class="accessibility-section">
                <button class="reset-all-btn">⟲ Resetuj wszystko</button>
            </div>
            
            <div class="accessibility-footer">
                <small>Ustawienia zapisują się automatycznie</small>
            </div>
        </div>
    </div>

    <script src="script.js"></script>
    <script>
// Obsługa ulubionych
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.favorite-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const horseId = this.dataset.horseId;
            const icon = this.querySelector('i');
            const isFavorite = icon.classList.contains('fas');
            
            fetch('ulubione.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'id_konia=' + horseId + '&akcja=' + (isFavorite ? 'usun' : 'dodaj')
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.status === 'dodano') {
                        icon.classList.remove('far');
                        icon.classList.add('fas');
                        console.log('Dodano do ulubionych');
                    } else {
                        icon.classList.remove('fas');
                        icon.classList.add('far');
                        console.log('Usunięto z ulubionych');
                    }
                } else {
                    console.error('Błąd:', data.error);
                }
            })
            .catch(error => console.error('Error:', error));
        });
        
        // Sprawdź czy koń jest w ulubionych
        fetch('ulubione.php?check=1&id_konia=' + btn.dataset.horseId)
            .then(response => response.json())
            .then(data => {
                if (data.is_favorite) {
                    const icon = btn.querySelector('i');
                    icon.classList.remove('far');
                    icon.classList.add('fas');
                }
            })
            .catch(error => console.error('Error:', error));
    });
});
</script>
</body>
</html>