<?php
require_once 'config.php';

if (!isLoggedIn()) {
    showMessage('Musisz się zalogować, aby polecać znajomym!', 'error');
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

// Sprawdź czy tabela polecenia istnieje
$conn->query("CREATE TABLE IF NOT EXISTS polecenia (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_uzytkownika INT NOT NULL,
    kod_polecajacy VARCHAR(50) UNIQUE NOT NULL,
    liczba_poleconych INT DEFAULT 0,
    znizka_procent INT DEFAULT 10,
    data_utworzenia TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_uzytkownika) REFERENCES uzytkownicy(id) ON DELETE CASCADE
)");

$conn->query("CREATE TABLE IF NOT EXISTS poleceni_znajomi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_polecenia INT NOT NULL,
    email_znajomego VARCHAR(100) NOT NULL,
    data_wyslania TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    czy_zarejestrowany BOOLEAN DEFAULT FALSE,
    czy_wykorzystany BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (id_polecenia) REFERENCES polecenia(id) ON DELETE CASCADE
)");

// Generuj kod polecający jeśli nie istnieje
$stmt = $conn->prepare("SELECT kod_polecajacy FROM uzytkownicy WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (empty($user['kod_polecajacy'])) {
    $kod = strtoupper(substr(md5(uniqid() . $user_id), 0, 8));
    $stmt = $conn->prepare("UPDATE uzytkownicy SET kod_polecajacy = ? WHERE id = ?");
    $stmt->bind_param("si", $kod, $user_id);
    $stmt->execute();
} else {
    $kod = $user['kod_polecajacy'];
}

// Sprawdź czy istnieje wpis w tabeli polecenia
$stmt = $conn->prepare("SELECT id FROM polecenia WHERE id_uzytkownika = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $stmt = $conn->prepare("INSERT INTO polecenia (id_uzytkownika, kod_polecajacy) VALUES (?, ?)");
    $stmt->bind_param("is", $user_id, $kod);
    $stmt->execute();
    $id_polecenia = $conn->insert_id;
} else {
    $row = $result->fetch_assoc();
    $id_polecenia = $row['id'];
}

// Funkcja do wysyłania maila przez Gmail SMTP
function sendGmailInvite($to, $kod, $link) {
    // POPRAWIONA ŚCIEŻKA do PHPMailer-master
    require_once __DIR__ . '/PHPMailer-master/src/Exception.php';
    require_once __DIR__ . '/PHPMailer-master/src/PHPMailer.php';
    require_once __DIR__ . '/PHPMailer-master/src/SMTP.php';
    
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    
    try {
        // Konfiguracja serwera SMTP Gmail
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'konzvalonyy@gmail.com'; // TWOJA SKRZYNKA
        $mail->Password   = 'rewx lfxj bcrp wssv';    // HASŁO APLIKACJI
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        
        // Ustawienia kodowania
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';
        
        // Nadawca i odbiorca
        $mail->setFrom('konzvalonyy@gmail.com', 'KonZValony');
        $mail->addAddress($to);
        $mail->addReplyTo('konzvalonyy@gmail.com', 'KonZValony');
        
        // Treść wiadomości HTML
        $mail->isHTML(true);
        $mail->Subject = '🎁 Zaproszenie do KonZValony - zyskaj 10% zniżki!';
        
        $mail->Body = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body {
                    font-family: "Segoe UI", Arial, sans-serif;
                    line-height: 1.6;
                    margin: 0;
                    padding: 0;
                    background: #f5f5f5;
                }
                .container {
                    max-width: 600px;
                    margin: 20px auto;
                    background: white;
                    border-radius: 20px;
                    overflow: hidden;
                    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
                }
                .header {
                    background: linear-gradient(135deg, #c17b4c, #9e5f34);
                    color: white;
                    padding: 30px;
                    text-align: center;
                }
                .header h1 {
                    margin: 0;
                    font-size: 2.5rem;
                    text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
                }
                .content {
                    padding: 40px 30px;
                    background: #fff;
                }
                .content h2 {
                    color: #2c3e50;
                    margin-top: 0;
                    text-align: center;
                }
                .kod-box {
                    background: #fef3e9;
                    border: 3px dashed #c17b4c;
                    border-radius: 15px;
                    padding: 20px;
                    text-align: center;
                    margin: 30px 0;
                }
                .kod {
                    font-size: 2.5rem;
                    font-weight: bold;
                    color: #c17b4c;
                    letter-spacing: 5px;
                    font-family: monospace;
                }
                .button {
                    display: inline-block;
                    background: linear-gradient(135deg, #c17b4c, #9e5f34);
                    color: white;
                    text-decoration: none;
                    padding: 15px 40px;
                    border-radius: 50px;
                    font-weight: bold;
                    font-size: 1.2rem;
                    margin: 20px 0;
                    box-shadow: 0 4px 15px rgba(193, 123, 76, 0.3);
                    transition: all 0.3s ease;
                }
                .button:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 6px 20px rgba(193, 123, 76, 0.4);
                }
                .features {
                    background: #f8f9fa;
                    border-radius: 15px;
                    padding: 20px;
                    margin: 30px 0;
                }
                .features ul {
                    list-style: none;
                    padding: 0;
                }
                .features li {
                    padding: 10px 0;
                    border-bottom: 1px solid #eef2f6;
                }
                .features li:last-child {
                    border-bottom: none;
                }
                .features i {
                    color: #c17b4c;
                    margin-right: 10px;
                }
                .footer {
                    background: #2c3e50;
                    color: white;
                    text-align: center;
                    padding: 20px;
                    font-size: 0.9rem;
                }
                .small {
                    color: #999;
                    font-size: 0.8rem;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>🐴 KonZValony</h1>
                    <p>Wypożyczalnia koni z charakterem!</p>
                </div>
                
                <div class="content">
                    <h2>✨ Masz zaproszenie od znajomego!</h2>
                    
                    <p>Hej! Znajomy poleca Ci super wypożyczalnię koni - <strong>KonZValony</strong>! 🏇</p>
                    
                    <p>To miejsce, gdzie konie mają więcej charakteru niż Twój były, a każda wizyta to niezapomniana przygoda!</p>
                    
                    <div class="kod-box">
                        <div style="margin-bottom: 10px; color: #666;">Twój osobisty kod rabatowy:</div>
                        <div class="kod">' . $kod . '</div>
                    </div>
                    
                    <div style="text-align: center;">
                        <a href="' . $link . '" class="button">
                            🎁 ODBIERZ ZNIŻKĘ 10% 🎁
                        </a>
                    </div>
                    
                    <div class="features">
                        <h3 style="color: #2c3e50; margin-top: 0;">Co zyskujesz?</h3>
                        <ul>
                            <li><i class="fas fa-check-circle"></i> <strong>10% zniżki</strong> na pierwszą rezerwację</li>
                            <li><i class="fas fa-check-circle"></i> Dostęp do <strong>wszystkich koni</strong> w stajni</li>
                            <li><i class="fas fa-check-circle"></i> Możliwość dodawania koni do <strong>ulubionych</strong></li>
                            <li><i class="fas fa-check-circle"></i> System <strong>poleceń i zniżek</strong> dla znajomych</li>
                            <li><i class="fas fa-check-circle"></i> Blog z <strong>poradami</strong> o koniach</li>
                        </ul>
                    </div>
                    
                    <p><strong>Twój znajomy również dostanie zniżkę</strong> za Twoją rejestrację - wszyscy wygrywają! 🤝</p>
                    
                    <p style="font-style: italic; color: #666;">Do zobaczenia w stajni! 🐎</p>
                </div>
                
                <div class="footer">
                    <p>© 2026 KonZValony - Wypożyczalnia koni z humorem</p>
                    <p class="small">To zaproszenie zostało wysłane automatycznie przez system poleceń.</p>
                </div>
            </div>
        </body>
        </html>';
        
        // Wersja tekstowa (dla klientów nieobsługujących HTML)
        $mail->AltBody = "Hej!\n\nZnajomy poleca Ci super wypożyczalnię koni - KonZValony!\n\n";
        $mail->AltBody .= "Twój kod rabatowy: " . $kod . "\n\n";
        $mail->AltBody .= "Zarejestruj się tutaj: " . $link . "\n\n";
        $mail->AltBody .= "Otrzymasz 10% zniżki na pierwszą rezerwację!\n\n";
        $mail->AltBody .= "Do zobaczenia w stajni!";
        
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        error_log("Błąd wysyłania maila: " . $mail->ErrorInfo);
        return false;
    }
}

// Obsługa wysyłania zaproszeń
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email_znajomego'])) {
    $email = trim($_POST['email_znajomego']);
    
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Sprawdź czy już nie wysłano zaproszenia na ten email
        $stmt = $conn->prepare("SELECT id FROM poleceni_znajomi WHERE id_polecenia = ? AND email_znajomego = ?");
        $stmt->bind_param("is", $id_polecenia, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 0) {
            $stmt = $conn->prepare("INSERT INTO poleceni_znajomi (id_polecenia, email_znajomego) VALUES (?, ?)");
            $stmt->bind_param("is", $id_polecenia, $email);
            
            if ($stmt->execute()) {
                // Wyślij maila przez Gmail SMTP - POPRAWIONY LINK z nazwą Twojego folderu
                $link = "http://" . $_SERVER['HTTP_HOST'] . "/Pedaloni1337-main/register.php?ref=" . $kod;
                
                if (sendGmailInvite($email, $kod, $link)) {
                    showMessage('Zaproszenie zostało wysłane! Dziękujemy!', 'success');
                } else {
                    // Jeśli mail się nie wysłał, usuń wpis z bazy
                    $conn->query("DELETE FROM poleceni_znajomi WHERE id = " . $conn->insert_id);
                    showMessage('Błąd podczas wysyłania maila. Sprawdź konfigurację Gmaila.', 'error');
                }
            } else {
                showMessage('Błąd podczas zapisywania zaproszenia!', 'error');
            }
        } else {
            showMessage('Zaproszenie na ten adres już zostało wysłane!', 'error');
        }
    } else {
        showMessage('Podaj poprawny adres email!', 'error');
    }
}

// Pobierz statystyki
$stmt = $conn->prepare("SELECT COUNT(*) as liczba FROM poleceni_znajomi WHERE id_polecenia = ?");
$stmt->bind_param("i", $id_polecenia);
$stmt->execute();
$result = $stmt->get_result();
$stat = $result->fetch_assoc();
$liczba_poleconych = $stat['liczba'];

// Pobierz listę poleconych
$stmt = $conn->prepare("SELECT email_znajomego, data_wyslania, czy_zarejestrowany FROM poleceni_znajomi WHERE id_polecenia = ? ORDER BY data_wyslania DESC");
$stmt->bind_param("i", $id_polecenia);
$stmt->execute();
$result = $stmt->get_result();
$poleceni = $result->fetch_all(MYSQLI_ASSOC);

// Oblicz zniżkę (max 50%)
$znizka = min(10 * count(array_filter($poleceni, fn($p) => $p['czy_zarejestrowany'])), 50);
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Poleć znajomym - KonZValony</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .polec-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        .polec-container h1 {
            text-align: center;
            color: var(--secondary);
            margin-bottom: 2rem;
            font-size: 2.5rem;
        }
        .polec-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }
        .polec-card {
            background: white;
            padding: 2rem;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .polec-card h2 {
            color: var(--secondary);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .kod-box {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }
        .kod {
            flex: 1;
            padding: 1rem;
            background: #f8fafc;
            border: 2px dashed var(--primary);
            border-radius: 10px;
            font-family: monospace;
            font-size: 1.3rem;
            font-weight: bold;
            color: var(--primary);
            text-align: center;
            letter-spacing: 2px;
        }
        .copy-btn {
            padding: 1rem 1.5rem;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }
        .copy-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }
        .info-text {
            font-size: 0.95rem;
            color: var(--gray);
            line-height: 1.6;
            padding: 1rem;
            background: #f8fafc;
            border-radius: 10px;
        }
        .info-text i {
            color: var(--primary);
            margin-right: 0.5rem;
        }
        .stat-item {
            display: flex;
            justify-content: space-between;
            padding: 1rem 0;
            border-bottom: 1px solid #eef2f6;
        }
        .stat-item:last-child {
            border-bottom: none;
        }
        .stat-item strong {
            color: var(--primary);
            font-size: 1.2rem;
        }
        .polec-form .form-group {
            margin-bottom: 1rem;
        }
        .polec-form input {
            width: 100%;
            padding: 1rem;
            border: 2px solid #eef2f6;
            border-radius: 10px;
            font-size: 1rem;
        }
        .btn-submit {
            width: 100%;
            padding: 1rem;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }
        .btn-submit:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }
        .polec-list {
            background: white;
            padding: 2rem;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .admin-table {
            width: 100%;
            border-collapse: collapse;
        }
        .admin-table th {
            background: var(--secondary);
            color: white;
            padding: 1rem;
            text-align: left;
        }
        .admin-table td {
            padding: 1rem;
            border-bottom: 1px solid #eef2f6;
        }
        @media (max-width: 768px) {
            .polec-grid {
                grid-template-columns: 1fr;
            }
            .kod-box {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <div class="logo-container">
                <img src="images/image.png" alt="KonZValony" class="logo">
            </div>
            <h1>KonZValony</h1>
            <p class="tagline">Poleć znajomym i zyskaj zniżki!</p>
        </div>
        <nav>
            <ul>
                <li><a href="index.php">Stajnia</a></li>
                <li><a href="blog.php">Blog</a></li>
                <li><a href="mapa.php">Mapa</a></li>
                <?php if (isLoggedIn()): ?>
                    <li><a href="panel.php">Panel</a></li>
                    <li><a href="ulubione.php">Ulubione</a></li>
                    <li><a href="polec.php" class="active">Poleć znajomym</a></li>
                    <li><a href="logout.php">Wyloguj (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a></li>
                <?php else: ?>
                    <li><a href="login.php">Logowanie</a></li>
                    <li><a href="register.php">Rejestracja</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <main>
        <?php if ($message = getMessage()): ?>
            <div class="message message-<?php echo $message['type']; ?>">
                <?php echo htmlspecialchars($message['text']); ?>
            </div>
        <?php endif; ?>

        <div class="polec-container">
            <h1><i class="fas fa-gift"></i> Poleć znajomym i zyskaj zniżki!</h1>
            
            <div class="polec-grid">
                <div class="polec-card">
                    <h2><i class="fas fa-qrcode"></i> Twój kod polecający</h2>
                    <div class="kod-box">
                        <span class="kod"><?php echo $kod; ?></span>
                        <button class="copy-btn" onclick="copyCode()">
                            <i class="far fa-copy"></i> Kopiuj
                        </button>
                    </div>
                    <p class="info-text">
                        <i class="fas fa-info-circle"></i>
                        Każda osoba, która zarejestruje się z Twoim kodem, dostaje 10% zniżki na pierwszą rezerwację. Ty dostajesz 10% zniżki po jej pierwszej rezerwacji!
                    </p>
                </div>
                
                <div class="polec-card">
                    <h2><i class="fas fa-envelope"></i> Wyślij zaproszenie</h2>
                    <form method="POST" class="polec-form">
                        <div class="form-group">
                            <label>Email znajomego:</label>
                            <input type="email" name="email_znajomego" required 
                                   placeholder="przyjaciel@example.com">
                        </div>
                        <button type="submit" class="btn-submit">
                            <i class="fas fa-paper-plane"></i> Wyślij zaproszenie
                        </button>
                    </form>
                </div>
                
                <div class="polec-card">
                    <h2><i class="fas fa-chart-line"></i> Twoje statystyki</h2>
                    <div class="stat-item">
                        <span>Wysłane zaproszenia:</span>
                        <strong><?php echo $liczba_poleconych; ?></strong>
                    </div>
                    <div class="stat-item">
                        <span>Zarejestrowani znajomi:</span>
                        <strong><?php echo count(array_filter($poleceni, fn($p) => $p['czy_zarejestrowany'])); ?></strong>
                    </div>
                    <div class="stat-item">
                        <span>Twoja zniżka:</span>
                        <strong><?php echo $znizka; ?>%</strong>
                    </div>
                </div>
            </div>
            
            <?php if (!empty($poleceni)): ?>
                <div class="polec-list">
                    <h2><i class="fas fa-history"></i> Historia zaproszeń</h2>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Email</th>
                                <th>Data wysłania</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($poleceni as $p): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($p['email_znajomego']); ?></td>
                                    <td><?php echo $p['data_wyslania']; ?></td>
                                    <td>
                                        <?php if ($p['czy_zarejestrowany']): ?>
                                            <span class="badge available">✓ Zarejestrowany</span>
                                        <?php else: ?>
                                            <span class="badge unavailable">⏳ Oczekuje</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        <p>&copy; 2026 KonZValony - Wypożyczalnia koni z humorem</p>
    </footer>

    <script src="script.js"></script>
    <script>
    function copyCode() {
        const kod = document.querySelector('.kod').textContent;
        navigator.clipboard.writeText(kod).then(() => {
            alert('Kod skopiowany do schowka!');
        }).catch(() => {
            alert('Nie udało się skopiować kodu');
        });
    }
    </script>
</body>
</html>