<?php
require_once 'config.php';

// Obsługa AJAX (POST) - najpierw, przed HTML
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    if (!isLoggedIn()) {
        echo json_encode(['error' => 'Musisz być zalogowany']);
        exit;
    }
    
    $id_uzytkownika = $_SESSION['user_id'];
    $id_konia = intval($_POST['id_konia'] ?? 0);
    $akcja = $_POST['akcja'] ?? 'dodaj';
    
    if ($id_konia <= 0) {
        echo json_encode(['error' => 'Nieprawidłowe ID konia']);
        exit;
    }
    
    if ($akcja === 'dodaj') {
        $stmt = $conn->prepare("INSERT IGNORE INTO ulubione (id_uzytkownika, id_konia) VALUES (?, ?)");
        $stmt->bind_param("ii", $id_uzytkownika, $id_konia);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'status' => 'dodano']);
        } else {
            echo json_encode(['error' => 'Błąd podczas dodawania']);
        }
    } else {
        $stmt = $conn->prepare("DELETE FROM ulubione WHERE id_uzytkownika = ? AND id_konia = ?");
        $stmt->bind_param("ii", $id_uzytkownika, $id_konia);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'status' => 'usunieto']);
        } else {
            echo json_encode(['error' => 'Błąd podczas usuwania']);
        }
    }
    exit;
}

// Obsługa GET (sprawdzanie czy w ulubionych)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['check']) && isset($_GET['id_konia'])) {
    header('Content-Type: application/json');
    
    if (!isLoggedIn()) {
        echo json_encode(['error' => 'Musisz być zalogowany']);
        exit;
    }
    
    $id_uzytkownika = $_SESSION['user_id'];
    $id_konia = intval($_GET['id_konia']);
    
    $stmt = $conn->prepare("SELECT id FROM ulubione WHERE id_uzytkownika = ? AND id_konia = ?");
    $stmt->bind_param("ii", $id_uzytkownika, $id_konia);
    $stmt->execute();
    $result = $stmt->get_result();
    
    echo json_encode(['is_favorite' => $result->num_rows > 0]);
    exit;
}

// Jeśli nie POST ani GET z check, to wyświetl stronę HTML
if (!isLoggedIn()) {
    showMessage('Musisz się zalogować, aby zobaczyć ulubione!', 'error');
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

// Pobierz ulubione konie użytkownika
$stmt = $conn->prepare("SELECT k.* FROM ulubione u JOIN konie k ON u.id_konia = k.id WHERE u.id_uzytkownika = ? ORDER BY u.data_dodania DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$ulubione = $result->fetch_all(MYSQLI_ASSOC);

$message = getMessage();
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ulubione konie - KonZValony</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .favorite-header {
            text-align: center;
            margin-bottom: 3rem;
            padding: 2rem;
            background: linear-gradient(135deg, #fff5e6, #fff);
            border-radius: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }
        
        .favorite-header h1 {
            font-size: 2.5rem;
            color: var(--secondary);
            margin-bottom: 1rem;
        }
        
        .favorite-header h1 i {
            color: #ff4444;
            margin-right: 0.5rem;
        }
        
        .favorite-header p {
            color: var(--gray);
            font-size: 1.1rem;
        }
        
        .empty-favorites {
            text-align: center;
            padding: 5rem 2rem;
            background: white;
            border-radius: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }
        
        .empty-favorites i {
            font-size: 5rem;
            color: #ff4444;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        
        .empty-favorites h2 {
            color: var(--secondary);
            margin-bottom: 1rem;
            font-size: 2rem;
        }
        
        .empty-favorites p {
            color: var(--gray);
            margin-bottom: 2rem;
            font-size: 1.1rem;
        }
        
        .empty-favorites .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--primary);
            color: white;
            padding: 1rem 2rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .empty-favorites .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(230, 126, 34, 0.3);
        }
        
        .favorites-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }
        
        .favorite-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            position: relative;
            border: 1px solid rgba(0,0,0,0.05);
        }
        
        .favorite-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        }
        
        .favorite-card .remove-favorite {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: rgba(255,255,255,0.9);
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: #ff4444;
            font-size: 1.2rem;
            z-index: 10;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .favorite-card .remove-favorite:hover {
            background: #ff4444;
            color: white;
            transform: scale(1.1);
        }
        
        .favorite-image {
            height: 250px;
            overflow: hidden;
        }
        
        .favorite-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .favorite-card:hover .favorite-image img {
            transform: scale(1.1);
        }
        
        .favorite-info {
            padding: 1.5rem;
        }
        
        .favorite-info h3 {
            color: var(--secondary);
            margin-bottom: 0.5rem;
            font-size: 1.5rem;
        }
        
        .favorite-breed {
            color: var(--primary);
            font-weight: 600;
            margin-bottom: 1rem;
            font-style: italic;
        }
        
        .favorite-description {
            color: #666;
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }
        
        .favorite-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
            border-top: 1px solid #eef2f6;
            padding-top: 1rem;
        }
        
        .favorite-price {
            font-size: 1.3rem;
            font-weight: bold;
            color: #27ae60;
        }
        
        .favorite-price::before {
            content: '💰 ';
            font-size: 1rem;
        }
        
        .btn-reserve-small {
            background: var(--primary);
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            transition: all 0.3s ease;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
        }
        
        .btn-reserve-small:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(230, 126, 34, 0.3);
        }
        
        .badge {
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .badge.available {
            background: #d4edda;
            color: #155724;
        }
        
        .badge.unavailable {
            background: #f8d7da;
            color: #721c24;
        }
        
        @media (max-width: 768px) {
            .favorites-grid {
                grid-template-columns: 1fr;
            }
            
            .favorite-header h1 {
                font-size: 2rem;
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
            <p class="tagline">Twoje ulubione konie</p>
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

        <div class="favorite-header">
            <h1><i class="fas fa-heart"></i> Ulubione konie</h1>
            <p>Twoja prywatna kolekcja ulubionych koni. Możesz je szybko rezerwować i obserwować.</p>
        </div>

        <?php if (empty($ulubione)): ?>
            <div class="empty-favorites">
                <i class="far fa-heart"></i>
                <h2>Nie masz jeszcze ulubionych koni</h2>
                <p>Przejdź do stajni i kliknij serduszko przy koniach, które Ci się podobają.</p>
                <a href="index.php" class="btn-primary">
                    <i class="fas fa-horse"></i> Przeglądaj konie
                </a>
            </div>
        <?php else: ?>
            <div class="favorites-grid">
                <?php foreach ($ulubione as $kon): ?>
                    <div class="favorite-card" id="horse-<?php echo $kon['id']; ?>">
                        <button class="remove-favorite" onclick="removeFromFavorites(<?php echo $kon['id']; ?>, this)">
                            <i class="fas fa-times"></i>
                        </button>
                        <div class="favorite-image">
                            <img src="images/<?php echo htmlspecialchars($kon['zdjecie']); ?>" 
                                 alt="<?php echo htmlspecialchars($kon['nazwa']); ?>">
                        </div>
                        <div class="favorite-info">
                            <h3><?php echo htmlspecialchars($kon['nazwa']); ?></h3>
                            <p class="favorite-breed"><?php echo htmlspecialchars($kon['rasa']); ?>, wiek: <?php echo $kon['wiek']; ?> lat</p>
                            <p class="favorite-description"><?php echo htmlspecialchars($kon['opis']); ?></p>
                            <div class="favorite-footer">
                                <span class="favorite-price"><?php echo number_format($kon['cena_za_dobe'], 2); ?> PLN</span>
                                <?php if ($kon['dostepny']): ?>
                                    <span class="badge available">Dostępny</span>
                                    <a href="panel.php?action=reserve&id=<?php echo $kon['id']; ?>" class="btn-reserve-small">
                                        <i class="fas fa-calendar-check"></i> Rezerwuj
                                    </a>
                                <?php else: ?>
                                    <span class="badge unavailable">Wypożyczony</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <footer>
        <p>&copy; 2026 KonZValony - Wypożyczalnia koni z humorem</p>
    </footer>

    <script src="script.js"></script>
    <script>
    function removeFromFavorites(horseId, button) {
        if (confirm('Czy na pewno chcesz usunąć tego konia z ulubionych?')) {
            fetch('ulubione.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'id_konia=' + horseId + '&akcja=usun'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Usuń kartę z DOM
                    const card = button.closest('.favorite-card');
                    card.remove();
                    
                    // Sprawdź czy zostały jakieś ulubione
                    if (document.querySelectorAll('.favorite-card').length === 0) {
                        location.reload(); // Odśwież stronę żeby pokazać pusty stan
                    }
                    
                    // Zaktualizuj serduszko na stronie głównej (jeśli jesteśmy na niej)
                    updateHeartOnMainPage(horseId, false);
                }
            })
            .catch(error => console.error('Error:', error));
        }
    }
    
    function updateHeartOnMainPage(horseId, isFavorite) {
        // Jeśli jesteśmy na stronie głównej, zaktualizuj serduszko
        const heartBtn = document.querySelector(`.favorite-btn[data-horse-id="${horseId}"]`);
        if (heartBtn) {
            const icon = heartBtn.querySelector('i');
            if (isFavorite) {
                icon.classList.remove('far');
                icon.classList.add('fas');
            } else {
                icon.classList.remove('fas');
                icon.classList.add('far');
            }
        }
    }
    </script>
</body>
</html>