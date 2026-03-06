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
    
   
    $stmt = $conn->prepare("UPDATE konie SET nazwa=?, rasa=?, wiek=?, opis=?, cena_za_dobe=?, dostepny=?, zdjecie=? WHERE id=?");
    
    $stmt->bind_param("ssisdisi", $nazwa, $rasa, $wiek, $opis, $cena, $dostepny, $zdjecie, $horse_id);
    //                     ^  ^  ^  ^  ^  ^  ^  ^
    //                     1  2  3  4  5  6  7  8
    
    if ($stmt->execute()) {
        showMessage('Dane konia zostały zaktualizowane!' . ($zdjecie != $current['zdjecie'] ? ' Nowe zdjęcie zostało przesłane.' : ''), 'success');
    } else {
        showMessage('Błąd podczas aktualizacji danych: ' . $conn->error, 'error');
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
            border: 2px dashed #e67e22;
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
        small {
            color: #666;
            font-size: 0.85rem;
            display: block;
            margin-top: 5px;
        }
        /* Style dla zarządzania rezerwacjami */
        .filters {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .filter-btn {
            padding: 8px 20px;
            background: #ecf0f1;
            color: #2c3e50;
            text-decoration: none;
            border-radius: 25px;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        .filter-btn:hover {
            background: #bdc3c7;
        }
        .filter-btn.active {
            background: #e67e22;
            color: white;
        }
        .reservation-admin-card {
            background: white;
            border-radius: 12px;
            margin-bottom: 20px;
            padding: 20px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            border-left: 5px solid #f39c12;
            transition: transform 0.3s ease;
        }
        .reservation-admin-card:hover {
            transform: translateX(5px);
            box-shadow: 0 6px 15px rgba(0,0,0,0.15);
        }
        .btn-confirm {
            background: #27ae60;
            color: white;
            padding: 8px 15px;
            text-decoration: none;
            border-radius: 5px;
            text-align: center;
            transition: all 0.3s ease;
            display: inline-block;
        }
        .btn-confirm:hover {
            background: #229954;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(39, 174, 96, 0.3);
        }
        .btn-cancel {
            background: #e74c3c;
            color: white;
            padding: 8px 15px;
            text-decoration: none;
            border-radius: 5px;
            text-align: center;
            transition: all 0.3s ease;
            display: inline-block;
        }
        .btn-cancel:hover {
            background: #c0392b;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(231, 76, 60, 0.3);
        }
        .reservations-summary {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            margin-top: 30px;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-top: 15px;
        }
        .summary-item {
            background: white;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }
        .summary-number {
            font-size: 24px;
            font-weight: bold;
        }
        @media (max-width: 768px) {
            .summary-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media (max-width: 480px) {
            .summary-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
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
                <li><a href="panel.php" class="<?php echo $active_tab === 'rezerwacje' ? 'active' : ''; ?>">Moje rezerwacje</a></li>
                <?php if (isAdmin()): ?>
                    <li><a href="panel.php?tab=admin" class="<?php echo $active_tab === 'admin' ? 'active' : ''; ?>">Zarządzanie końmi</a></li>
                    <li><a href="panel.php?tab=users" class="<?php echo $active_tab === 'users' ? 'active' : ''; ?>">Użytkownicy</a></li>
                    <li><a href="panel.php?tab=all_reservations" class="<?php echo $active_tab === 'all_reservations' ? 'active' : ''; ?>">Wszystkie rezerwacje</a></li>
                <?php endif; ?>
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
            <!-- Formularz rezerwacji -->
            <section class="reservation-form">
                <h2>Rezerwacja: <?php echo htmlspecialchars($kon['nazwa']); ?></h2>
                <form method="POST" action="process_reservation.php">
                    <input type="hidden" name="kon_id" value="<?php echo $kon['id']; ?>">
                    <div class="form-group">
                        <label for="data_od">Data rozpoczęcia:</label>
                        <input type="date" id="data_od" name="data_od" required min="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="form-group">
                        <label for="data_do">Data zakończenia:</label>
                        <input type="date" id="data_do" name="data_do" required min="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn-submit">Zarezerwuj</button>
                        <a href="index.php" class="btn-cancel">Anuluj</a>
                    </div>
                </form>
            </section>
        <?php endif; ?>

        <?php if ($active_tab === 'rezerwacje'): ?>
            <!-- Moje rezerwacje -->
            <section class="my-reservations">
                <h2>Moje rezerwacje</h2>
                <?php if (empty($rezerwacje)): ?>
                    <p class="empty-state">Nie masz jeszcze żadnych rezerwacji. <a href="index.php">Zobacz dostępne konie!</a></p>
                <?php else: ?>
                    <div class="reservations-grid">
                        <?php foreach ($rezerwacje as $rez): ?>
                            <div class="reservation-card">
                                <div class="reservation-image">
                                    <img src="images/<?php echo htmlspecialchars($rez['zdjecie']); ?>" 
                                         alt="<?php echo htmlspecialchars($rez['kon_nazwa']); ?>">
                                </div>
                                <div class="reservation-info">
                                    <h3><?php echo htmlspecialchars($rez['kon_nazwa']); ?></h3>
                                    <p><strong>Termin:</strong> <?php echo $rez['data_od']; ?> do <?php echo $rez['data_do']; ?></p>
                                    <p><strong>Status:</strong> 
                                        <span class="badge status-<?php echo $rez['status']; ?>">
                                            <?php echo $rez['status']; ?>
                                        </span>
                                    </p>
                                    <p><strong>Data rezerwacji:</strong> <?php echo $rez['data_rezerwacji']; ?></p>
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
                <h2>Zarządzanie końmi</h2>
                
                <?php if (isset($edit_horse) && $edit_horse): ?>
                <!-- Formularz edycji konia -->
                <div class="edit-form">
                    <h3>✏️ Edytuj konia: <?php echo htmlspecialchars($edit_horse['nazwa']); ?></h3>
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
                            <input type="number" id="edit_wiek" name="wiek" min="1" max="67" value="<?php echo $edit_horse['wiek']; ?>" required>
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
                            <small style="color: #666; display: block; margin-top: 5px;">
                                Aktualne zdjęcie: <?php echo htmlspecialchars($edit_horse['zdjecie']); ?>
                            </small>
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
                            <button type="submit" name="update_horse" class="btn-submit">💾 Zapisz zmiany</button>
                            <a href="panel.php?tab=admin" class="btn-cancel">✕ Anuluj</a>
                        </div>
                    </form>
                </div>
                <?php endif; ?>

                <!-- Formularz dodawania konia -->
                <div class="add-form">
                    <h3>➕ Dodaj nowego konia</h3>
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
                            <small style="color: #666; display: block; margin-top: 5px;">
                                Dozwolone formaty: JPG, PNG, GIF. Maksymalny rozmiar: 2MB.
                                Jeśli nie wybierzesz zdjęcia, zostanie użyte domyślne.
                            </small>
                        </div>
                        
                        <div class="image-preview" id="imagePreview" style="display: none; margin-bottom: 15px;">
                            <p>Podgląd:</p>
                            <img id="preview" src="#" alt="Podgląd zdjęcia" style="max-width: 200px; max-height: 200px; border-radius: 10px; border: 2px solid #e67e22;">
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" name="add_horse" class="btn-submit">Dodaj konia</button>
                        </div>
                    </form>
                </div>

                <!-- Lista koni do edycji -->
                <div class="horses-list">
                    <h3>📋 Lista koni</h3>
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
                                <td><?php echo $kon['wiek']; ?></td>
                                <td><?php echo $kon['cena_za_dobe']; ?> PLN</td>
                                <td><?php echo $kon['dostepny'] ? '✅ Tak' : '❌ Nie'; ?></td>
                                <td class="actions">
                                    <a href="?action=edit_horse&id=<?php echo $kon['id']; ?>&tab=admin" class="btn-edit">✏️ Edytuj</a>
                                    <a href="?action=delete_horse&id=<?php echo $kon['id']; ?>&tab=admin" 
                                       onclick="return confirm('Czy na pewno usunąć tego konia?')" 
                                       class="btn-delete">🗑️ Usuń</a>
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
                <h2>Zarządzanie użytkownikami</h2>
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
                                <span class="badge role-<?php echo $user['rola']; ?>">
                                    <?php echo $user['rola']; ?>
                                </span>
                            </td>
                            <td><?php echo $user['data_rejestracji']; ?></td>
                            <td class="actions">
                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                    <a href="?action=toggle_admin&id=<?php echo $user['id']; ?>&tab=users" 
                                       class="btn-role">🔄 Zmień rolę</a>
                                    <a href="?action=delete_user&id=<?php echo $user['id']; ?>&tab=users" 
                                       onclick="return confirm('Czy na pewno usunąć tego użytkownika?')" 
                                       class="btn-delete">🗑️ Usuń</a>
                                <?php else: ?>
                                    <span class="text-muted">(To Ty)</span>
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
                <h2>📋 Wszystkie rezerwacje</h2>
                
                <?php if (empty($all_reservations)): ?>
                    <div class="empty-state">
                        <p>Brak rezerwacji w systemie.</p>
                    </div>
                <?php else: ?>
                    <!-- Filtry statusu -->
                    <div class="filters">
                        <a href="?tab=all_reservations&filter=all" class="filter-btn <?php echo !isset($_GET['filter']) || $_GET['filter'] === 'all' ? 'active' : ''; ?>">Wszystkie</a>
                        <a href="?tab=all_reservations&filter=oczekujaca" class="filter-btn <?php echo isset($_GET['filter']) && $_GET['filter'] === 'oczekujaca' ? 'active' : ''; ?>">Oczekujące</a>
                        <a href="?tab=all_reservations&filter=potwierdzona" class="filter-btn <?php echo isset($_GET['filter']) && $_GET['filter'] === 'potwierdzona' ? 'active' : ''; ?>">Potwierdzone</a>
                        <a href="?tab=all_reservations&filter=anulowana" class="filter-btn <?php echo isset($_GET['filter']) && $_GET['filter'] === 'anulowana' ? 'active' : ''; ?>">Anulowane</a>
                    </div>
                    
                    <!-- Lista rezerwacji -->
                    <div class="reservations-list">
                        <?php 
                        $filter = $_GET['filter'] ?? 'all';
                        $pending_count = 0;
                        $confirmed_count = 0;
                        $cancelled_count = 0;
                        
                        foreach ($all_reservations as $rez): 
                            if ($filter !== 'all' && $rez['status'] !== $filter) continue;
                            
                            // Liczenie dla statystyk
                            if ($rez['status'] == 'oczekujaca') $pending_count++;
                            elseif ($rez['status'] == 'potwierdzona') $confirmed_count++;
                            elseif ($rez['status'] == 'anulowana') $cancelled_count++;
                        ?>
                        <div class="reservation-admin-card" style="border-left-color: <?php 
                            echo $rez['status'] == 'potwierdzona' ? '#27ae60' : ($rez['status'] == 'anulowana' ? '#e74c3c' : '#f39c12'); 
                        ?>;">
                            <div style="display: grid; grid-template-columns: 100px 1fr auto; gap: 20px; align-items: center;">
                                <!-- Zdjęcie konia -->
                                <div class="reservation-image">
                                    <img src="images/<?php echo htmlspecialchars($rez['zdjecie']); ?>" 
                                         alt="<?php echo htmlspecialchars($rez['kon_nazwa']); ?>"
                                         style="width: 100px; height: 100px; object-fit: cover; border-radius: 10px;">
                                </div>
                                
                                <!-- Szczegóły rezerwacji -->
                                <div class="reservation-details">
                                    <h3 style="margin-bottom: 10px; color: #2c3e50;">
                                        🐴 <?php echo htmlspecialchars($rez['kon_nazwa']); ?>
                                    </h3>
                                    
                                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px;">
                                        <div>
                                            <strong>👤 Użytkownik:</strong><br>
                                            <?php echo htmlspecialchars($rez['username']); ?><br>
                                            <small><?php echo htmlspecialchars($rez['email']); ?></small>
                                        </div>
                                        
                                        <div>
                                            <strong>📅 Termin:</strong><br>
                                            Od: <?php echo $rez['data_od']; ?><br>
                                            Do: <?php echo $rez['data_do']; ?>
                                        </div>
                                        
                                        <div>
                                            <strong>📊 Status:</strong><br>
                                            <span class="badge status-<?php echo $rez['status']; ?>" style="display: inline-block; margin-top: 5px;">
                                                <?php echo $rez['status']; ?>
                                            </span>
                                        </div>
                                        
                                        <div>
                                            <strong>🕒 Data rezerwacji:</strong><br>
                                            <small><?php echo $rez['data_rezerwacji']; ?></small>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Akcje -->
                                <div class="reservation-actions" style="display: flex; flex-direction: column; gap: 10px;">
                                    <?php if ($rez['status'] !== 'potwierdzona'): ?>
                                        <a href="?action=update_status&reservation_id=<?php echo $rez['id']; ?>&status=potwierdzona&tab=all_reservations" 
                                           class="btn-confirm" 
                                           onclick="return confirm('Potwierdzić tę rezerwację?')">
                                            ✅ Potwierdź
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php if ($rez['status'] !== 'anulowana'): ?>
                                        <a href="?action=update_status&reservation_id=<?php echo $rez['id']; ?>&status=anulowana&tab=all_reservations" 
                                           class="btn-cancel" 
                                           onclick="return confirm('Na pewno anulować tę rezerwację?')">
                                            ❌ Anuluj
                                        </a>
                                    <?php endif; ?>
                                    
                                    <a href="?action=delete_reservation&id=<?php echo $rez['id']; ?>&tab=all_reservations" 
                                       class="btn-delete" 
                                       style="background: #95a5a6; color: white; padding: 8px 15px; text-decoration: none; border-radius: 5px; text-align: center;"
                                       onclick="return confirm('Czy na pewno usunąć tę rezerwację? Tej operacji nie można cofnąć!')">
                                        🗑️ Usuń
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Podsumowanie -->
                    <div class="reservations-summary">
                        <h3>📊 Podsumowanie rezerwacji:</h3>
                        <div class="summary-grid">
                            <div class="summary-item" style="background: #ecf0f1;">
                                <div class="summary-number"><?php echo count($all_reservations); ?></div>
                                <div>Wszystkich</div>
                            </div>
                            <div class="summary-item" style="background: #fff3cd;">
                                <div class="summary-number"><?php echo $pending_count; ?></div>
                                <div>Oczekujących</div>
                            </div>
                            <div class="summary-item" style="background: #d4edda;">
                                <div class="summary-number"><?php echo $confirmed_count; ?></div>
                                <div>Potwierdzonych</div>
                            </div>
                            <div class="summary-item" style="background: #f8d7da;">
                                <div class="summary-number"><?php echo $cancelled_count; ?></div>
                                <div>Anulowanych</div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </section>
        <?php endif; ?>
    </main>

    <footer>
        <p>&copy; 2026 KonZValony - Wypożyczalnia koni z humorem</p>
    </footer>

    <script src="script.js"></script>
    <script>
        // Podgląd zdjęcia przed wysłaniem
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

        // Podgląd zdjęcia w formularzu edycji
        document.getElementById('edit_zdjecie')?.addEventListener('change', function(e) {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    // Stwórz lub pokaż podgląd nowego zdjęcia
                    let editPreview = document.getElementById('editPreview');
                    if (!editPreview) {
                        const previewDiv = document.createElement('div');
                        previewDiv.className = 'image-preview';
                        previewDiv.innerHTML = '<p>Nowe zdjęcie:</p><img id="editPreview" src="#" style="max-width: 200px; max-height: 200px; border-radius: 10px; border: 2px solid #e67e22;">';
                        document.querySelector('.edit-form').appendChild(previewDiv);
                        editPreview = document.getElementById('editPreview');
                    }
                    editPreview.src = e.target.result;
                }
                
                reader.readAsDataURL(this.files[0]);
            }
        });
    </script>
</body>
</html>