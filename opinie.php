<?php
require_once 'config.php';

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Musisz być zalogowany']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_konia = intval($_POST['id_konia']);
    $ocena = intval($_POST['ocena']);
    $komentarz = trim($_POST['komentarz']);
    $id_uzytkownika = $_SESSION['user_id'];
    
    // Sprawdź czy użytkownik miał rezerwację tego konia
    $stmt = $conn->prepare("SELECT id FROM rezerwacje WHERE id_uzytkownika = ? AND id_konia = ? AND status = 'potwierdzona' AND data_do < CURDATE()");
    $stmt->bind_param("ii", $id_uzytkownika, $id_konia);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['error' => 'Możesz oceniać tylko konie, które już wypożyczyłeś']);
        exit;
    }
    
    // Sprawdź czy już nie oceniał
    $stmt = $conn->prepare("SELECT id FROM opinie WHERE id_uzytkownika = ? AND id_konia = ?");
    $stmt->bind_param("ii", $id_uzytkownika, $id_konia);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo json_encode(['error' => 'Już dodałeś opinię o tym koniu']);
        exit;
    }
    
    // Dodaj opinię
    $stmt = $conn->prepare("INSERT INTO opinie (id_uzytkownika, id_konia, ocena, komentarz) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiis", $id_uzytkownika, $id_konia, $ocena, $komentarz);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => 'Opinia dodana! Dziękujemy!']);
    } else {
        echo json_encode(['error' => 'Błąd podczas dodawania opinii']);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id_konia'])) {
    $id_konia = intval($_GET['id_konia']);
    
    $stmt = $conn->prepare("SELECT o.*, u.username FROM opinie o JOIN uzytkownicy u ON o.id_uzytkownika = u.id WHERE o.id_konia = ? AND o.status = 'zatwierdzona' ORDER BY o.data_dodania DESC");
    $stmt->bind_param("i", $id_konia);
    $stmt->execute();
    $result = $stmt->get_result();
    $opinie = $result->fetch_all(MYSQLI_ASSOC);
    
    // Średnia ocena
    $stmt = $conn->prepare("SELECT AVG(ocena) as srednia, COUNT(*) as liczba FROM opinie WHERE id_konia = ? AND status = 'zatwierdzona'");
    $stmt->bind_param("i", $id_konia);
    $stmt->execute();
    $stats = $stmt->get_result()->fetch_assoc();
    
    echo json_encode([
        'opinie' => $opinie,
        'srednia' => round($stats['srednia'] ?? 0, 1),
        'liczba' => $stats['liczba'] ?? 0
    ]);
}
?>