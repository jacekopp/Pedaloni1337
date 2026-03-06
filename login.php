<?php
require_once 'config.php';

// Jeśli już zalogowany, przekieruj na stronę główną
if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Wypełnij wszystkie pola!';
    } else {
        // Przygotuj zapytanie
        $stmt = $conn->prepare("SELECT id, username, haslo, rola FROM uzytkownicy WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            
            if (password_verify($password, $user['haslo'])) {
                // Zalogowano pomyślnie
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_role'] = $user['rola'];
                
                showMessage('Zalogowano pomyślnie! Witaj ' . $user['username'], 'success');
                redirect('index.php');
            } else {
                $error = 'Nieprawidłowe hasło!';
            }
        } else {
            $error = 'Nie znaleziono użytkownika!';
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
    <title>Logowanie - KonZValony</title>
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
                <li><a href="index.php">Stajnia</a></li>
                <li><a href="login.php" class="active">Logowanie</a></li>
                <li><a href="register.php">Rejestracja</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section class="auth-section">
            <div class="auth-container">
                <h2>Logowanie do stajni</h2>
                
                <?php if ($error): ?>
                    <div class="message message-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <form method="POST" action="" class="auth-form">
                    <div class="form-group">
                        <label for="username">Nazwa użytkownika lub email:</label>
                        <input type="text" id="username" name="username" required 
                               placeholder="Wprowadź nazwę lub email"
                               value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="password">Hasło:</label>
                        <input type="password" id="password" name="password" required 
                               placeholder="Twoje hasło">
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn-submit">Zaloguj się</button>
                    </div>

                    <p class="auth-links">
                        Nie masz konta? <a href="register.php">Zarejestruj się</a>
                    </p>
                </form>
            </div>
        </section>
    </main>

    <footer>
        <p>&copy; 2026 KonZValony - Wypożyczalnia koni z humorem</p>
    </footer>
</body>
</html>