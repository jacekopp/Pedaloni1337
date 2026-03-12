<?php
require_once 'config.php';

// Sprawdź czy użytkownik jest zalogowany
if (!isLoggedIn()) {
    showMessage('Musisz się zalogować, aby dokonać rezerwacji!', 'error');
    redirect('login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kon_id = intval($_POST['kon_id'] ?? 0);
    $data_od = $_POST['data_od'] ?? '';
    $data_do = $_POST['data_do'] ?? '';
    $user_id = $_SESSION['user_id'];

    // Walidacja
    if ($kon_id <= 0 || empty($data_od) || empty($data_do)) {
        showMessage('Wypełnij wszystkie pola!', 'error');
        redirect('index.php');
    }

    if (strtotime($data_od) < strtotime(date('Y-m-d'))) {
        showMessage('Data rozpoczęcia nie może być wcześniejsza niż dzisiaj!', 'error');
        redirect('panel.php?action=reserve&id=' . $kon_id);
    }

    if (strtotime($data_do) < strtotime($data_od)) {
        showMessage('Data zakończenia musi być późniejsza niż data rozpoczęcia!', 'error');
        redirect('panel.php?action=reserve&id=' . $kon_id);
    }

    // Sprawdź czy koń istnieje i jest dostępny
    $stmt = $conn->prepare("SELECT * FROM konie WHERE id = ? AND dostepny = 1");
    $stmt->bind_param("i", $kon_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        showMessage('Wybrany koń nie jest dostępny!', 'error');
        redirect('index.php');
    }

    // Sprawdź czy termin nie jest zajęty
    $stmt = $conn->prepare("SELECT id FROM rezerwacje WHERE id_konia = ? AND status != 'anulowana' AND ((data_od <= ? AND data_do >= ?) OR (data_od <= ? AND data_do >= ?) OR (data_od >= ? AND data_do <= ?))");
    $stmt->bind_param("issssss", $kon_id, $data_od, $data_od, $data_do, $data_do, $data_od, $data_do);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        showMessage('Wybrany termin jest już zajęty! Wybierz inne daty.', 'error');
        redirect('panel.php?action=reserve&id=' . $kon_id);
    }

    // Dodaj rezerwację
    $stmt = $conn->prepare("INSERT INTO rezerwacje (id_uzytkownika, id_konia, data_od, data_do) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $user_id, $kon_id, $data_od, $data_do);

    if ($stmt->execute()) {
        showMessage('Rezerwacja została złożona pomyślnie! Oczekuje na potwierdzenie.', 'success');
    } else {
        showMessage('Błąd podczas składania rezerwacji!', 'error');
    }

    redirect('panel.php');
} else {
    redirect('index.php');
}
?>
<?php
require_once 'config.php';

if (!isLoggedIn()) {
    showMessage('Musisz się zalogować, aby dokonać rezerwacji!', 'error');
    redirect('login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kon_id = intval($_POST['kon_id'] ?? 0);
    $data_od = $_POST['data_od'] ?? '';
    $data_do = $_POST['data_do'] ?? '';
    $user_id = $_SESSION['user_id'];

    // Walidacja
    if ($kon_id <= 0 || empty($data_od) || empty($data_do)) {
        showMessage('Wypełnij wszystkie pola!', 'error');
        redirect('index.php');
    }

    // Oblicz liczbę dni
    $dni = (strtotime($data_do) - strtotime($data_od)) / (60 * 60 * 24) + 1;
    
    // Pobierz cenę konia
    $stmt = $conn->prepare("SELECT cena_za_dobe FROM konie WHERE id = ?");
    $stmt->bind_param("i", $kon_id);
    $stmt->execute();
    $cena = $stmt->get_result()->fetch_assoc()['cena_za_dobe'];
    
    // Oblicz cenę podstawową
    $cena_podstawowa = $cena * $dni;
    
    // Sprawdź zniżkę od poleceń
    $znizka = 0;
    
    // Sprawdź czy użytkownik był polecony
    $stmt = $conn->prepare("SELECT czy_wykorzystany FROM poleceni_znajomi pz 
                            JOIN polecenia p ON pz.id_polecenia = p.id 
                            WHERE pz.email_znajomego = (SELECT email FROM uzytkownicy WHERE id = ?)");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0 && !$result->fetch_assoc()['czy_wykorzystany']) {
        $znizka += 10; // 10% za pierwsze polecenie
    }
    
    // Sprawdź zniżkę od polecania innych
    $stmt = $conn->prepare("SELECT COUNT(*) as liczba FROM poleceni_znajomi pz 
                            JOIN polecenia p ON pz.id_polecenia = p.id 
                            WHERE p.id_uzytkownika = ? AND pz.czy_zarejestrowany = TRUE");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $polecenia = $stmt->get_result()->fetch_assoc()['liczba'];
    
    $znizka += min($polecenia * 10, 40); // Max 40% z poleceń
    
    // Maksymalna zniżka 50%
    $znizka = min($znizka, 50);
    
    $cena_po_znizce = $cena_podstawowa * (1 - $znizka / 100);
    
    // Zapisz w sesji informację o zniżce
    $_SESSION['znizka'] = $znizka;
    $_SESSION['cena_po_znizce'] = $cena_po_znizce;
    
    // Reszta kodu rezerwacji...
}