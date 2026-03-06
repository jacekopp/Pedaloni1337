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
                <?php if (isLoggedIn()): ?>
                    <li><a href="panel.php">Moje rezerwacje</a></li>
                    <?php if (isAdmin()): ?>
                        <li><a href="panel.php?tab=admin">Panel admina</a></li>
                    <?php endif; ?>
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

    <script src="script.js"></script>
</body>
</html>