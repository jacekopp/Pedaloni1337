<?php
require_once 'config.php';

// Jeśli już zalogowany, przekieruj na stronę główną
if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

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
            
            // Dodaj użytkownika
            $stmt = $conn->prepare("INSERT INTO uzytkownicy (username, email, haslo) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $email, $hashed_password);
            
            if ($stmt->execute()) {
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
                <li><a href="login.php">Logowanie</a></li>
                <li><a href="register.php" class="active">Rejestracja</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section class="auth-section">
            <div class="auth-container">
                <h2>Dołącz do stajni KonZValony!</h2>
                
                <?php if ($error): ?>
                    <div class="message message-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="message message-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>

                <form method="POST" action="" class="auth-form">
                    <div class="form-group">
                        <label for="username">Nazwa użytkownika:</label>
                        <input type="text" id="username" name="username" required 
                               placeholder="Wymyśl swoją niepowtarzalną nazwę"
                               value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="email">Adres email:</label>
                        <input type="email" id="email" name="email" required 
                               placeholder="Twój email"
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="password">Hasło (min. 6 znaków):</label>
                        <input type="password" id="password" name="password" required 
                               placeholder="Twoje hasło">
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Potwierdź hasło:</label>
                        <input type="password" id="confirm_password" name="confirm_password" required 
                               placeholder="Wpisz hasło ponownie">
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn-submit">Zarejestruj się</button>
                    </div>

                    <p class="auth-links">
                        Masz już konto? <a href="login.php">Zaloguj się</a>
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