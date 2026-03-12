<?php
require_once 'config.php';

// Jeśli już zalogowany, przekieruj na stronę główną
if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';
$success = '';
$ref = isset($_GET['ref']) ? trim($_GET['ref']) : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $kod_polecajacy = trim($_POST['kod_polecajacy'] ?? '');

    // Walidacja
    if (empty($username) || empty($email) || empty($password)) {
        $error = 'Wypełnij wszystkie pola!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Podaj prawidłowy adres email!';
    } elseif (strlen($password) < 6) {
        $error = 'Hasło musi mieć co najmniej 6 znaków!';
    } elseif ($password !== $confirm_password) {
        $error = 'Hasła nie są zgodne!';
    } else {
        // Sprawdź czy użytkownik już istnieje
        $stmt = $conn->prepare("SELECT id FROM uzytkownicy WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = 'Użytkownik o podanej nazwie lub emailu już istnieje!';
        } else {
            // Hashowanie hasła
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Generuj własny kod polecający
            $wlasny_kod = strtoupper(substr(md5(uniqid() . $username), 0, 8));
            
            // Dodaj użytkownika
            $stmt = $conn->prepare("INSERT INTO uzytkownicy (username, email, haslo, kod_polecajacy) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $username, $email, $hashed_password, $wlasny_kod);
            
            if ($stmt->execute()) {
                $nowy_user_id = $conn->insert_id;
                
                // Jeśli był kod polecający, zaktualizuj statystyki
                if (!empty($kod_polecajacy)) {
                    // Znajdź osobę która poleciła
                    $stmt = $conn->prepare("SELECT id FROM uzytkownicy WHERE kod_polecajacy = ?");
                    $stmt->bind_param("s", $kod_polecajacy);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if ($result->num_rows > 0) {
                        $polecajacy = $result->fetch_assoc();
                        
                        // Znajdź id polecenia
                        $stmt = $conn->prepare("SELECT id FROM polecenia WHERE id_uzytkownika = ?");
                        $stmt->bind_param("i", $polecajacy['id']);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        
                        if ($result->num_rows > 0) {
                            $polecenie = $result->fetch_assoc();
                            
                            // Oznacz znajomego jako zarejestrowanego
                            $stmt = $conn->prepare("UPDATE poleceni_znajomi SET czy_zarejestrowany = TRUE WHERE id_polecenia = ? AND email_znajomego = ?");
                            $stmt->bind_param("is", $polecenie['id'], $email);
                            $stmt->execute();
                        }
                    }
                }
                
                $success = 'Rejestracja zakończona pomyślnie! Możesz się zalogować.';
            } else {
                $error = 'Błąd podczas rejestracji. Spróbuj ponownie.';
            }
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rejestracja - KonZValony</title>
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
                <li><a href="index.php">Stajnia</a></li>
                <li><a href="blog.php">Blog</a></li>
                <li><a href="mapa.php">Mapa</a></li>
                <li><a href="login.php">Logowanie</a></li>
                <li><a href="register.php" class="active">Rejestracja</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section class="auth-section">
            <div class="auth-container">
                <h2><i class="fas fa-horse-head"></i> Dołącz do stajni!</h2>
                
                <?php if ($error): ?>
                    <div class="message message-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="message message-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>

                <?php if (!empty($ref)): ?>
                    <div class="message message-info">
                        <i class="fas fa-gift"></i> 
                        Zostałeś zaproszony! Otrzymasz 10% zniżki na pierwszą rezerwację!
                    </div>
                <?php endif; ?>

                <form method="POST" action="" class="auth-form">
                    <div class="form-group">
                        <label for="username"><i class="fas fa-user"></i> Nazwa użytkownika:</label>
                        <input type="text" id="username" name="username" required 
                               placeholder="Wymyśl swoją niepowtarzalną nazwę"
                               value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="email"><i class="fas fa-envelope"></i> Adres email:</label>
                        <input type="email" id="email" name="email" required 
                               placeholder="Twój email"
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="password"><i class="fas fa-lock"></i> Hasło (min. 6 znaków):</label>
                        <input type="password" id="password" name="password" required 
                               placeholder="Twoje hasło">
                    </div>

                    <div class="form-group">
                        <label for="confirm_password"><i class="fas fa-lock"></i> Potwierdź hasło:</label>
                        <input type="password" id="confirm_password" name="confirm_password" required 
                               placeholder="Wpisz hasło ponownie">
                    </div>

                    <div class="form-group">
                        <label for="kod_polecajacy"><i class="fas fa-tag"></i> Kod polecający (opcjonalnie):</label>
                        <input type="text" id="kod_polecajacy" name="kod_polecajacy" 
                               placeholder="Wpisz kod jeśli masz"
                               value="<?php echo htmlspecialchars($_POST['kod_polecajacy'] ?? $ref); ?>">
                        <small>Masz kod od znajomego? Wpisz go i zyskaj 10% zniżki!</small>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn-submit">
                            <i class="fas fa-horse"></i> Zarejestruj się
                        </button>
                    </div>

                    <p class="auth-links">
                        Masz już konto? <a href="login.php">Zaloguj się</a>
                    </p>
                </form>

                <div class="demo-accounts">
                    <h3><i class="fas fa-info-circle"></i> Co zyskujesz?</h3>
                    <p>✓ 10% zniżki na pierwszą rezerwację (z kodem)</p>
                    <p>✓ Możliwość dodawania koni do ulubionych</p>
                    <p>✓ System poleceń i dodatkowe zniżki</p>
                    <p>✓ Dostęp do bloga i mapy dojazdu</p>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <p>&copy; 2026 KonZValony - Wypożyczalnia koni z humorem</p>
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
</body>
</html>