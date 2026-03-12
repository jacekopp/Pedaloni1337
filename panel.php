<?php
require_once 'config.php';

// Sprawdź czy użytkownik jest zalogowany
if (!isLoggedIn()) {
    showMessage('Musisz się zalogować, aby zobaczyć tę stronę!', 'error');
    redirect('login.php');
}

$message = getMessage();
$active_tab = $_GET['tab'] ?? 'rezerwacje';
$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? 0;

// Obsługa rezerwacji
if ($action === 'reserve' && $id > 0 && isLoggedIn()) {
    $stmt = $conn->prepare("SELECT * FROM konie WHERE id = ? AND dostepny = 1");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $kon = $result->fetch_assoc();
        $show_reservation_form = true;
        
        // Oblicz zniżkę przed pokazaniem formularza
        $user_id = $_SESSION['user_id'];
        
        // Sprawdź czy użytkownik był polecony
        $stmt = $conn->prepare("SELECT czy_wykorzystany FROM poleceni_znajomi pz 
                                JOIN polecenia p ON pz.id_polecenia = p.id 
                                WHERE pz.email_znajomego = (SELECT email FROM uzytkownicy WHERE id = ?)");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $znizka = 0;
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if (!$row['czy_wykorzystany']) {
                $znizka += 10; // 10% za pierwsze polecenie
            }
        }
        
        // Sprawdź zniżkę od polecania innych
        $stmt = $conn->prepare("SELECT COUNT(*) as liczba FROM poleceni_znajomi pz 
                                JOIN polecenia p ON pz.id_polecenia = p.id 
                                WHERE p.id_uzytkownika = ? AND pz.czy_zarejestrowany = TRUE");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $polecenia = $result->fetch_assoc()['liczba'];
        
        $znizka += min($polecenia * 10, 40); // Max 40% z poleceń
        
        // Maksymalna zniżka 50%
        $znizka = min($znizka, 50);
        
        // Oblicz cenę po zniżce
        $cena_po_znizce = $kon['cena_za_dobe'] * (1 - $znizka / 100);
        
        // Zapisz w sesji
        $_SESSION['znizka'] = $znizka;
        $_SESSION['cena_po_znizce'] = $cena_po_znizce;
        
    } else {
        showMessage('Wybrany koń nie jest dostępny!', 'error');
        redirect('index.php');
    }
}

// Obsługa edycji konia
if ($action === 'edit_horse' && $id > 0 && isAdmin()) {
    $stmt = $conn->prepare("SELECT * FROM konie WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_horse = $result->fetch_assoc();
    
    if (!$edit_horse) {
        showMessage('Nie znaleziono konia!', 'error');
        redirect('panel.php?tab=admin');
    }
}

// Obsługa zarządzania rezerwacjami przez admina
if (isAdmin()) {
    // Pobierz wszystkie rezerwacje z danymi użytkowników i koni
    $sql = "SELECT r.*, 
                   k.nazwa as kon_nazwa, 
                   k.zdjecie,
                   u.username,
                   u.email
            FROM rezerwacje r 
            JOIN konie k ON r.id_konia = k.id 
            JOIN uzytkownicy u ON r.id_uzytkownika = u.id 
            ORDER BY r.data_rezerwacji DESC";
    $all_reservations = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
    
    // Obsługa zmiany statusu rezerwacji
    if ($action === 'update_status' && isset($_GET['reservation_id']) && isset($_GET['status'])) {
        $reservation_id = intval($_GET['reservation_id']);
        $new_status = $_GET['status'];
        $allowed_statuses = ['oczekujaca', 'potwierdzona', 'anulowana'];
        
        if (in_array($new_status, $allowed_statuses)) {
            $stmt = $conn->prepare("UPDATE rezerwacje SET status = ? WHERE id = ?");
            $stmt->bind_param("si", $new_status, $reservation_id);
            
            if ($stmt->execute()) {
                showMessage('Status rezerwacji został zmieniony na: ' . $new_status, 'success');
            } else {
                showMessage('Błąd podczas zmiany statusu!', 'error');
            }
        }
        redirect('panel.php?tab=all_reservations');
    }
    
    // Obsługa usuwania rezerwacji
    if ($action === 'delete_reservation' && $id > 0) {
        $stmt = $conn->prepare("DELETE FROM rezerwacje WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            showMessage('Rezerwacja została usunięta!', 'success');
        } else {
            showMessage('Błąd podczas usuwania rezerwacji!', 'error');
        }
        redirect('panel.php?tab=all_reservations');
    }
}

// Obsługa CRUD dla administratora
if (isAdmin()) {
    // Dodawanie nowego konia ze zdjęciem
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_horse'])) {
        $nazwa = trim($_POST['nazwa']);
        $rasa = trim($_POST['rasa']);
        $wiek = intval($_POST['wiek']);
        $opis = trim($_POST['opis']);
        $cena = floatval($_POST['cena']);
        $zdjecie = 'default-horse.jpg'; // Domyślne zdjęcie
        
        // Obsługa przesyłania zdjęcia
        if (isset($_FILES['zdjecie']) && $_FILES['zdjecie']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = __DIR__ . '/images/';
            $file_name = $_FILES['zdjecie']['name'];
            $file_tmp = $_FILES['zdjecie']['tmp_name'];
            $file_size = $_FILES['zdjecie']['size'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            // Dozwolone formaty
            $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];
            
            // Sprawdź format
            if (in_array($file_ext, $allowed_exts)) {
                // Sprawdź rozmiar (max 2MB)
                if ($file_size <= 2 * 1024 * 1024) {
                    // Generuj unikalną nazwę pliku
                    $new_file_name = uniqid() . '_' . time() . '.' . $file_ext;
                    $upload_path = $upload_dir . $new_file_name;
                    
                    // Przenieś plik
                    if (move_uploaded_file($file_tmp, $upload_path)) {
                        $zdjecie = $new_file_name;
                    } else {
                        showMessage('Błąd podczas przesyłania pliku!', 'error');
                    }
                } else {
                    showMessage('Plik jest za duży! Maksymalny rozmiar to 2MB.', 'error');
                }
            } else {
                showMessage('Niedozwolony format pliku! Dozwolone: JPG, PNG, GIF.', 'error');
            }
        }
        
        $stmt = $conn->prepare("INSERT INTO konie (nazwa, rasa, wiek, opis, cena_za_dobe, zdjecie) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssisds", $nazwa, $rasa, $wiek, $opis, $cena, $zdjecie);
        
        if ($stmt->execute()) {
            showMessage('Koń dodany pomyślnie!' . ($zdjecie != 'default-horse.jpg' ? ' Zdjęcie zostało przesłane.' : ''), 'success');
        } else {
            showMessage('Błąd podczas dodawania konia!', 'error');
        }
        redirect('panel.php?tab=admin');
    }
    
    // Aktualizacja konia ze zdjęciem
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_horse'])) {
        $horse_id = intval($_POST['horse_id']);
        $nazwa = trim($_POST['nazwa']);
        $rasa = trim($_POST['rasa']);
        $wiek = intval($_POST['wiek']);
        $opis = trim($_POST['opis']);
        $cena = floatval($_POST['cena']);
        $dostepny = isset($_POST['dostepny']) ? 1 : 0;
        
        // Pobierz aktualne zdjęcie
        $stmt = $conn->prepare("SELECT zdjecie FROM konie WHERE id = ?");
        $stmt->bind_param("i", $horse_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $current = $result->fetch_assoc();
        $zdjecie = $current['zdjecie'];
        
        // Obsługa nowego zdjęcia
        if (isset($_FILES['zdjecie']) && $_FILES['zdjecie']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = __DIR__ . '/images/';
            $file_name = $_FILES['zdjecie']['name'];
            $file_tmp = $_FILES['zdjecie']['tmp_name'];
            $file_size = $_FILES['zdjecie']['size'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (in_array($file_ext, $allowed_exts) && $file_size <= 2 * 1024 * 1024) {
                $new_file_name = uniqid() . '_' . time() . '.' . $file_ext;
                $upload_path = $upload_dir . $new_file_name;
                
                if (move_uploaded_file($file_tmp, $upload_path)) {
                    // Usuń stare zdjęcie jeśli nie jest domyślne
                    if ($zdjecie != 'default-horse.jpg' && file_exists($upload_dir . $zdjecie)) {
                        unlink($upload_dir . $zdjecie);
                    }
                    $zdjecie = $new_file_name;
                }
            }
        }
        
        $stmt = $conn->prepare("UPDATE konie SET nazva=?, rasa=?, wiek=?, opis=?, cena_za_dobe=?, dostepny=?, zdjecie=? WHERE id=?");
        $stmt->bind_param("ssisdsi", $nazwa, $rasa, $wiek, $opis, $cena, $dostepny, $zdjecie, $horse_id);
        
        if ($stmt->execute()) {
            showMessage('Dane konia zostały zaktualizowane!' . ($zdjecie != $current['zdjecie'] ? ' Nowe zdjęcie zostało przesłane.' : ''), 'success');
        } else {
            showMessage('Błąd podczas aktualizacji danych!', 'error');
        }
        redirect('panel.php?tab=admin');
    }
    
    // Usuwanie konia (z usunięciem zdjęcia)
    if ($action === 'delete_horse' && $id > 0) {
        // Pobierz nazwę zdjęcia przed usunięciem
        $stmt = $conn->prepare("SELECT zdjecie FROM konie WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $kon = $result->fetch_assoc();
        
        // Usuń zdjęcie jeśli nie jest domyślne
        if ($kon && $kon['zdjecie'] != 'default-horse.jpg') {
            $upload_dir = __DIR__ . '/images/';
            if (file_exists($upload_dir . $kon['zdjecie'])) {
                unlink($upload_dir . $kon['zdjecie']);
            }
        }
        
        // Usuń konia z bazy
        $stmt = $conn->prepare("DELETE FROM konie WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            showMessage('Koń usunięty ze stajni wraz ze zdjęciem!', 'success');
        } else {
            showMessage('Błąd podczas usuwania!', 'error');
        }
        redirect('panel.php?tab=admin');
    }
    
    // Usuwanie użytkownika
    if ($action === 'delete_user' && $id > 0 && $id != $_SESSION['user_id']) {
        $stmt = $conn->prepare("DELETE FROM uzytkownicy WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            showMessage('Użytkownik usunięty!', 'success');
        } else {
            showMessage('Błąd podczas usuwania użytkownika!', 'error');
        }
        redirect('panel.php?tab=users');
    }
    
    // Zmiana roli użytkownika
    if ($action === 'toggle_admin' && $id > 0) {
        $stmt = $conn->prepare("UPDATE uzytkownicy SET rola = IF(rola='admin', 'user', 'admin') WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        showMessage('Rola użytkownika zmieniona!', 'success');
        redirect('panel.php?tab=users');
    }
}

// Pobierz rezerwacje użytkownika
$user_id = $_SESSION['user_id'];
$sql = "SELECT r.*, k.nazwa as kon_nazwa, k.zdjecie 
        FROM rezerwacje r 
        JOIN konie k ON r.id_konia = k.id 
        WHERE r.id_uzytkownika = ? 
        ORDER BY r.data_rezerwacji DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$rezerwacje = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Pobierz wszystkich użytkowników (dla admina)
if (isAdmin()) {
    $users = $conn->query("SELECT id, username, email, rola, data_rejestracji FROM uzytkownicy ORDER BY data_rejestracji DESC")->fetch_all(MYSQLI_ASSOC);
    $konie_admin = $conn->query("SELECT * FROM konie ORDER BY nazwa")->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel użytkownika - KonZValony</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .edit-form {
            background: #f0f8ff;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            border: 2px solid #e67e22;
            animation: slideIn 0.5s ease;
        }
        .btn-edit {
            background: #3498db;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            margin-right: 0.5rem;
        }
        .btn-edit:hover {
            background: #2980b9;
        }
        /* Style dla przesyłania zdjęć */
        input[type="file"] {
            padding: 10px;
            border: 2px dashed var(--primary);
            border-radius: 8px;
            background: #fff9f0;
            width: 100%;
            cursor: pointer;
        }
        input[type="file"]:hover {
            background: #fff0e0;
        }
        .image-preview {
            margin: 15px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
            text-align: center;
        }
        .image-preview p {
            margin-bottom: 10px;
            color: #2c3e50;
            font-weight: bold;
        }
        .current-image {
            margin: 15px 0;
            padding: 15px;
            background: #f0f8ff;
            border-radius: 10px;
            text-align: center;
        }
        .current-image p {
            margin-bottom: 10px;
            color: #2c3e50;
            font-weight: bold;
        }
        /* Style dla zniżki */
        .discount-banner {
            background: linear-gradient(135deg, #e8f5e9, #c8e6c9);
            border: 2px solid #4caf50;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 25px;
            box-shadow: 0 4px 15px rgba(76, 175, 80, 0.2);
        }
        .discount-banner h3 {
            color: #2e7d32;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .discount-banner h3 i {
            font-size: 1.8rem;
        }
        .discount-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px dashed #81c784;
        }
        .discount-row:last-child {
            border-bottom: none;
        }
        .discount-label {
            font-size: 1.1rem;
            color: #2e7d32;
        }
        .discount-value {
            font-size: 1.3rem;
            font-weight: bold;
            color: #4caf50;
        }
        .price-after {
            font-size: 1.5rem;
            color: #c17b4c;
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
            <p class="tagline">Panel użytkownika</p>
        </div>
        <nav>
            <ul>
                <li><a href="index.php">Stajnia</a></li>
                <li><a href="blog.php">Blog</a></li>
                <li><a href="mapa.php">Mapa</a></li>
                <li><a href="panel.php" class="<?php echo $active_tab === 'rezerwacje' ? 'active' : ''; ?>">Moje rezerwacje</a></li>
                <?php if (isAdmin()): ?>
                    <li><a href="panel.php?tab=admin" class="<?php echo $active_tab === 'admin' ? 'active' : ''; ?>">Zarządzanie końmi</a></li>
                    <li><a href="panel.php?tab=users" class="<?php echo $active_tab === 'users' ? 'active' : ''; ?>">Użytkownicy</a></li>
                    <li><a href="panel.php?tab=all_reservations" class="<?php echo $active_tab === 'all_reservations' ? 'active' : ''; ?>">Wszystkie rezerwacje</a></li>
                <?php endif; ?>
                <li><a href="polec.php">Poleć znajomym</a></li>
                <li><a href="logout.php">Wyloguj (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <?php if ($message): ?>
            <div class="message message-<?php echo $message['type']; ?>">
                <?php echo htmlspecialchars($message['text']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($show_reservation_form) && $show_reservation_form): ?>
            <!-- Formularz rezerwacji z sekcją zniżki -->
            <section class="reservation-form">
                <h2><i class="fas fa-calendar-check"></i> Rezerwacja: <?php echo htmlspecialchars($kon['nazwa']); ?></h2>
                
                <!-- SEKCJA ZNIŻKI - DODANA TUTAJ -->
                <?php if (isset($_SESSION['znizka']) && $_SESSION['znizka'] > 0): ?>
                    <div class="discount-banner">
                        <h3><i class="fas fa-gift"></i> Masz aktywną zniżkę! 🎉</h3>
                        <div class="discount-row">
                            <span class="discount-label"><i class="fas fa-tag"></i> Twoja zniżka:</span>
                            <span class="discount-value"><?php echo $_SESSION['znizka']; ?>%</span>
                        </div>
                        <div class="discount-row">
                            <span class="discount-label"><i class="fas fa-horse"></i> Cena regularna:</span>
                            <span style="text-decoration: line-through; color: #999;"><?php echo number_format($kon['cena_za_dobe'], 2); ?> PLN/doba</span>
                        </div>
                        <div class="discount-row">
                            <span class="discount-label"><i class="fas fa-calculator"></i> Cena po zniżce:</span>
                            <span class="price-after"><strong><?php echo number_format($_SESSION['cena_po_znizce'], 2); ?> PLN</strong>/doba</span>
                        </div>
                        <p style="margin-top: 15px; font-size: 0.9rem; color: #2e7d32;">
                            <i class="fas fa-info-circle"></i> Zniżka została naliczona automatycznie z Twojego programu poleceń!
                        </p>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="process_reservation.php">
                    <input type="hidden" name="kon_id" value="<?php echo $kon['id']; ?>">
                    <div class="form-row">
                        <div class="date-input-group">
                            <label for="data_od"><i class="fas fa-calendar-alt"></i> Data rozpoczęcia:</label>
                            <input type="date" id="data_od" name="data_od" required min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="date-input-group">
                            <label for="data_do"><i class="fas fa-calendar-check"></i> Data zakończenia:</label>
                            <input type="date" id="data_do" name="data_do" required min="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn-submit">
                            <i class="fas fa-check-circle"></i> Potwierdź rezerwację
                        </button>
                        <a href="index.php" class="btn-cancel">
                            <i class="fas fa-times"></i> Anuluj
                        </a>
                    </div>
                </form>
            </section>
        <?php endif; ?>

        <?php if ($active_tab === 'rezerwacje'): ?>
            <!-- Moje rezerwacje -->
            <section class="my-reservations">
                <div class="section-header">
                    <h2><i class="fas fa-calendar-alt"></i> Moje rezerwacje</h2>
                    <?php if (isset($_SESSION['znizka']) && $_SESSION['znizka'] > 0): ?>
                        <span class="section-badge">
                            <i class="fas fa-tag"></i> Twoja zniżka: <?php echo $_SESSION['znizka']; ?>%
                        </span>
                    <?php endif; ?>
                </div>
                
                <?php if (empty($rezerwacje)): ?>
                    <div class="empty-state">
                        <i class="fas fa-calendar-times empty-state-icon"></i>
                        <h3>Brak rezerwacji</h3>
                        <p>Nie masz jeszcze żadnych rezerwacji. Przejdź do stajni i wybierz konia!</p>
                        <a href="index.php" class="btn-primary">
                            <i class="fas fa-horse"></i> Zobacz konie
                        </a>
                    </div>
                <?php else: ?>
                    <div class="reservations-grid">
                        <?php foreach ($rezerwacje as $rez): ?>
                            <div class="reservation-card">
                                <div class="reservation-image">
                                    <img src="images/<?php echo htmlspecialchars($rez['zdjecie']); ?>" 
                                         alt="<?php echo htmlspecialchars($rez['kon_nazwa']); ?>">
                                    <span class="status-badge status-<?php echo $rez['status']; ?>">
                                        <?php echo $rez['status']; ?>
                                    </span>
                                </div>
                                <div class="reservation-info">
                                    <h3><?php echo htmlspecialchars($rez['kon_nazwa']); ?></h3>
                                    <div class="reservation-dates">
                                        <div class="date-item">
                                            <div class="label">Od</div>
                                            <div class="value"><?php echo date('d.m.Y', strtotime($rez['data_od'])); ?></div>
                                        </div>
                                        <div class="date-separator">→</div>
                                        <div class="date-item">
                                            <div class="label">Do</div>
                                            <div class="value"><?php echo date('d.m.Y', strtotime($rez['data_do'])); ?></div>
                                        </div>
                                    </div>
                                    <div class="reservation-meta">
                                        <div class="meta-item">
                                            <i class="fas fa-clock"></i>
                                            Rezerwacja: <?php echo date('d.m.Y', strtotime($rez['data_rezerwacji'])); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        <?php endif; ?>

        <?php if (isAdmin() && $active_tab === 'admin'): ?>
            <!-- Panel zarządzania końmi (CRUD) -->
            <section class="admin-panel">
                <h2><i class="fas fa-horse-head"></i> Zarządzanie końmi</h2>
                
                <?php if (isset($edit_horse) && $edit_horse): ?>
                <!-- Formularz edycji konia -->
                <div class="edit-form">
                    <h3><i class="fas fa-edit"></i> Edytuj konia: <?php echo htmlspecialchars($edit_horse['nazwa']); ?></h3>
                    <form method="POST" action="" enctype="multipart/form-data">
                        <input type="hidden" name="horse_id" value="<?php echo $edit_horse['id']; ?>">
                        
                        <div class="form-group">
                            <label for="edit_nazwa">Nazwa konia:</label>
                            <input type="text" id="edit_nazwa" name="nazwa" value="<?php echo htmlspecialchars($edit_horse['nazwa']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_rasa">Rasa:</label>
                            <input type="text" id="edit_rasa" name="rasa" value="<?php echo htmlspecialchars($edit_horse['rasa']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_wiek">Wiek (lata):</label>
                            <input type="number" id="edit_wiek" name="wiek" min="1" max="40" value="<?php echo $edit_horse['wiek']; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_opis">Opis (humorystyczny):</label>
                            <textarea id="edit_opis" name="opis" rows="4" required><?php echo htmlspecialchars($edit_horse['opis']); ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_cena">Cena za dobę (PLN):</label>
                            <input type="number" id="edit_cena" name="cena" min="1" step="0.01" value="<?php echo $edit_horse['cena_za_dobe']; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_zdjecie">Zmień zdjęcie (opcjonalnie):</label>
                            <input type="file" id="edit_zdjecie" name="zdjecie" accept="image/jpeg,image/png,image/gif,image/jpg">
                            <small>Aktualne zdjęcie: <?php echo htmlspecialchars($edit_horse['zdjecie']); ?></small>
                        </div>

                        <div class="current-image">
                            <p>Aktualne zdjęcie:</p>
                            <img src="images/<?php echo htmlspecialchars($edit_horse['zdjecie']); ?>" 
                                 alt="Aktualne zdjęcie" 
                                 style="max-width: 200px; max-height: 200px; border-radius: 10px; border: 2px solid #e67e22;">
                        </div>
                        
                        <div class="form-group checkbox">
                            <label>
                                <input type="checkbox" name="dostepny" <?php echo $edit_horse['dostepny'] ? 'checked' : ''; ?>> Dostępny
                            </label>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" name="update_horse" class="btn-submit">
                                <i class="fas fa-save"></i> Zapisz zmiany
                            </button>
                            <a href="panel.php?tab=admin" class="btn-cancel">
                                <i class="fas fa-times"></i> Anuluj
                            </a>
                        </div>
                    </form>
                </div>
                <?php endif; ?>

                <!-- Formularz dodawania konia -->
                <div class="add-form">
                    <h3><i class="fas fa-plus-circle"></i> Dodaj nowego konia</h3>
                    <form method="POST" action="" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="nazwa">Nazwa konia:</label>
                            <input type="text" id="nazwa" name="nazwa" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="rasa">Rasa:</label>
                            <input type="text" id="rasa" name="rasa" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="wiek">Wiek (lata):</label>
                            <input type="number" id="wiek" name="wiek" min="1" max="40" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="opis">Opis (humorystyczny):</label>
                            <textarea id="opis" name="opis" rows="4" required></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="cena">Cena za dobę (PLN):</label>
                            <input type="number" id="cena" name="cena" min="1" step="0.01" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="zdjecie">Zdjęcie konia:</label>
                            <input type="file" id="zdjecie" name="zdjecie" accept="image/jpeg,image/png,image/gif,image/jpg">
                            <small>Dozwolone formaty: JPG, PNG, GIF. Maksymalny rozmiar: 2MB.</small>
                        </div>
                        
                        <div class="image-preview" id="imagePreview" style="display: none;">
                            <p>Podgląd:</p>
                            <img id="preview" src="#" alt="Podgląd zdjęcia" style="max-width: 200px; max-height: 200px; border-radius: 10px; border: 2px solid #e67e22;">
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" name="add_horse" class="btn-submit">
                                <i class="fas fa-horse"></i> Dodaj konia
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Lista koni -->
                <div class="horses-list">
                    <h3><i class="fas fa-list"></i> Lista koni</h3>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nazwa</th>
                                <th>Rasa</th>
                                <th>Wiek</th>
                                <th>Cena</th>
                                <th>Dostępny</th>
                                <th>Akcje</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($konie_admin as $kon): ?>
                            <tr>
                                <td><?php echo $kon['id']; ?></td>
                                <td><?php echo htmlspecialchars($kon['nazwa']); ?></td>
                                <td><?php echo htmlspecialchars($kon['rasa']); ?></td>
                                <td><?php echo $kon['wiek']; ?> lat</td>
                                <td><?php echo $kon['cena_za_dobe']; ?> PLN</td>
                                <td><?php echo $kon['dostepny'] ? '✅ Tak' : '❌ Nie'; ?></td>
                                <td class="actions">
                                    <a href="?action=edit_horse&id=<?php echo $kon['id']; ?>&tab=admin" class="btn-edit">
                                        <i class="fas fa-edit"></i> Edytuj
                                    </a>
                                    <a href="?action=delete_horse&id=<?php echo $kon['id']; ?>&tab=admin" 
                                       onclick="return confirm('Czy na pewno usunąć tego konia?')" 
                                       class="btn-delete">
                                        <i class="fas fa-trash"></i> Usuń
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        <?php endif; ?>

        <?php if (isAdmin() && $active_tab === 'users'): ?>
            <!-- Zarządzanie użytkownikami -->
            <section class="users-panel">
                <h2><i class="fas fa-users"></i> Zarządzanie użytkownikami</h2>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nazwa</th>
                            <th>Email</th>
                            <th>Rola</th>
                            <th>Data rejestracji</th>
                            <th>Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>
                                <span class="badge <?php echo $user['rola']; ?>">
                                    <?php echo $user['rola']; ?>
                                </span>
                            </td>
                            <td><?php echo $user['data_rejestracji']; ?></td>
                            <td class="actions">
                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                    <a href="?action=toggle_admin&id=<?php echo $user['id']; ?>&tab=users" 
                                       class="btn-role">
                                        <i class="fas fa-sync-alt"></i> Zmień rolę
                                    </a>
                                    <a href="?action=delete_user&id=<?php echo $user['id']; ?>&tab=users" 
                                       onclick="return confirm('Czy na pewno usunąć tego użytkownika?')" 
                                       class="btn-delete">
                                        <i class="fas fa-user-slash"></i> Usuń
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted"><i class="fas fa-user-check"></i> To Ty</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>
        <?php endif; ?>

        <?php if (isAdmin() && $active_tab === 'all_reservations'): ?>
            <!-- Panel zarządzania wszystkimi rezerwacjami -->
            <section class="reservations-panel">
                <h2><i class="fas fa-calendar-alt"></i> Wszystkie rezerwacje</h2>
                
                <div class="filters">
                    <a href="?tab=all_reservations&filter=all" class="filter-btn <?php echo !isset($_GET['filter']) || $_GET['filter'] === 'all' ? 'active' : ''; ?>">
                        <i class="fas fa-list"></i> Wszystkie
                    </a>
                    <a href="?tab=all_reservations&filter=oczekujaca" class="filter-btn <?php echo isset($_GET['filter']) && $_GET['filter'] === 'oczekujaca' ? 'active' : ''; ?>">
                        <i class="fas fa-clock"></i> Oczekujące
                    </a>
                    <a href="?tab=all_reservations&filter=potwierdzona" class="filter-btn <?php echo isset($_GET['filter']) && $_GET['filter'] === 'potwierdzona' ? 'active' : ''; ?>">
                        <i class="fas fa-check-circle"></i> Potwierdzone
                    </a>
                    <a href="?tab=all_reservations&filter=anulowana" class="filter-btn <?php echo isset($_GET['filter']) && $_GET['filter'] === 'anulowana' ? 'active' : ''; ?>">
                        <i class="fas fa-times-circle"></i> Anulowane
                    </a>
                </div>
                
                <?php if (empty($all_reservations)): ?>
                    <div class="empty-state">
                        <i class="fas fa-calendar-times empty-state-icon"></i>
                        <h3>Brak rezerwacji</h3>
                        <p>W systemie nie ma jeszcze żadnych rezerwacji.</p>
                    </div>
                <?php else: ?>
                    <?php 
                    $filter = $_GET['filter'] ?? 'all';
                    foreach ($all_reservations as $rez): 
                        if ($filter !== 'all' && $rez['status'] !== $filter) continue;
                    ?>
                    <div class="reservation-admin-card <?php echo $rez['status']; ?>">
                        <div style="display: grid; grid-template-columns: 100px 1fr auto; gap: 20px; align-items: center;">
                            <div class="reservation-image">
                                <img src="images/<?php echo htmlspecialchars($rez['zdjecie']); ?>" 
                                     alt="<?php echo htmlspecialchars($rez['kon_nazwa']); ?>"
                                     style="width: 100px; height: 100px; object-fit: cover; border-radius: 10px;">
                            </div>
                            
                            <div class="reservation-details">
                                <h3><?php echo htmlspecialchars($rez['kon_nazwa']); ?></h3>
                                <p><strong>Użytkownik:</strong> <?php echo htmlspecialchars($rez['username']); ?> (<?php echo htmlspecialchars($rez['email']); ?>)</p>
                                <p><strong>Termin:</strong> <?php echo $rez['data_od']; ?> do <?php echo $rez['data_do']; ?></p>
                                <p><strong>Status:</strong> <span class="badge status-<?php echo $rez['status']; ?>"><?php echo $rez['status']; ?></span></p>
                                <p><small>Data rezerwacji: <?php echo $rez['data_rezerwacji']; ?></small></p>
                            </div>
                            
                            <div class="actions" style="flex-direction: column;">
                                <?php if ($rez['status'] !== 'potwierdzona'): ?>
                                    <a href="?action=update_status&reservation_id=<?php echo $rez['id']; ?>&status=potwierdzona&tab=all_reservations" 
                                       class="btn-confirm" 
                                       onclick="return confirm('Potwierdzić tę rezerwację?')">
                                        <i class="fas fa-check"></i> Potwierdź
                                    </a>
                                <?php endif; ?>
                                
                                <?php if ($rez['status'] !== 'anulowana'): ?>
                                    <a href="?action=update_status&reservation_id=<?php echo $rez['id']; ?>&status=anulowana&tab=all_reservations" 
                                       class="btn-cancel" 
                                       onclick="return confirm('Na pewno anulować tę rezerwację?')">
                                        <i class="fas fa-ban"></i> Anuluj
                                    </a>
                                <?php endif; ?>
                                
                                <a href="?action=delete_reservation&id=<?php echo $rez['id']; ?>&tab=all_reservations" 
                                   class="btn-delete" 
                                   onclick="return confirm('Czy na pewno usunąć tę rezerwację?')">
                                    <i class="fas fa-trash"></i> Usuń
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </section>
        <?php endif; ?>
    </main>

    <footer>
        <p>&copy; 2026 KonZValony - Wypożyczalnia koni z humorem</p>
    </footer>

    <script src="script.js"></script>
    <script>
    // Podgląd zdjęcia
    document.getElementById('zdjecie')?.addEventListener('change', function(e) {
        const preview = document.getElementById('preview');
        const previewDiv = document.getElementById('imagePreview');
        
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                previewDiv.style.display = 'block';
            }
            reader.readAsDataURL(this.files[0]);
        }
    });

    // Automatyczne obliczanie ceny po zniżce
    document.getElementById('data_od')?.addEventListener('change', calculatePrice);
    document.getElementById('data_do')?.addEventListener('change', calculatePrice);
    
    function calculatePrice() {
        const dataOd = document.getElementById('data_od')?.value;
        const dataDo = document.getElementById('data_do')?.value;
        const znizka = <?php echo $_SESSION['znizka'] ?? 0; ?>;
        const cenaZaDobe = <?php echo $kon['cena_za_dobe'] ?? 0; ?>;
        
        if (dataOd && dataDo && dataOd <= dataDo) {
            const dni = (new Date(dataDo) - new Date(dataOd)) / (1000 * 60 * 60 * 24) + 1;
            const cenaRegularna = dni * cenaZaDobe;
            const cenaPoZnizce = cenaRegularna * (1 - znizka/100);
            
            // Tutaj możesz dodać wyświetlanie całkowitej ceny
            console.log('Dni:', dni, 'Cena regularna:', cenaRegularna, 'Po zniżce:', cenaPoZnizce);
        }
    }
    </script>
</body>
</html>