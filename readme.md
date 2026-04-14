KonZValony - Humorystyczna wypożyczalnia koni

Opis projektu
KonZValony to humorystyczna wypożyczalnia koni stworzona jako projekt szkolny. Strona pozwala na przeglądanie dostępnych koni, rezerwację terminów oraz zarządzanie stajnią przez administratora. Wszystkie konie mają zabawne, dwuznaczne opisy bez wulgaryzmów, które mają rozbawić użytkowników.

Funkcjonalności

Dla wszystkich niezalogowanych użytkowników:
Przeglądanie listy dostępnych koni z humorystycznymi opisami
Filtrowanie i sortowanie koni
Responsywny design działa na telefonach i komputerach
Dostosowanie do WCAG wysoki kontrast, alt text, nawigacja klawiaturą

Dla zalogowanych użytkowników:
Rezerwowanie wybranych koni na konkretne terminy
Podgląd własnych rezerwacji


Dla administratora panel admina:
Dodawanie nowych koni z możliwością przesyłania zdjęć
Edycja danych istniejących koni
Usuwanie koni
Zarządzanie użytkownikami zmiana ról, usuwanie
Zarządzanie wszystkimi rezerwacjami potwierdzanie, anulowanie
Podgląd statystyk rezerwacji

Technologie
Backend PHP 8+ proceduralny
Baza danych MySQL phpMyAdmin
Serwer XAMPP Apache

Struktura plików
konzvalony/
index.php - Strona główna lista koni
login.php - Logowanie użytkowników
register.php - Rejestracja nowych użytkowników
logout.php - Wylogowywanie
panel.php - Panel użytkownika admina
process_reservation.php - Obsługa rezerwacji
config.php - Konfiguracja bazy danych i sesji
style.css - Główny arkusz stylów
script.js - Skrypty JavaScript
database.sql - Struktura bazy z przykładowymi danymi
README.md - Ten plik
images/ - Folder na zdjęcia koni
image.png - Logo strony
default-horse.jpg - Domyślne zdjęcie
horse1.jpg - Przykładowe zdjęcia
horse2.jpg
horse3.jpg
horse4.jpg
...

Baza danych
Baza danych konzvalony zawiera 3 tabele:

Tabela uzytkownicy
id - INT klucz główny
username - VARCHAR(50) nazwa użytkownika
email - VARCHAR(100) adres email
haslo - VARCHAR(255) zahashowane hasło
rola - ENUM user admin rola użytkownika
data_rejestracji - TIMESTAMP data rejestracji

Tabela konie
id - INT klucz główny
nazwa - VARCHAR(100) imię konia
rasa - VARCHAR(100) rasa część humorystyczna
wiek - INT wiek w latach
opis - TEXT humorystyczny opis
cena_za_dobe - DECIMAL(10,2) cena za dobę
zdjecie - VARCHAR(255) nazwa pliku ze zdjęciem
dostepny - TINYINT(1) czy dostępny do wypożyczenia

Tabela rezerwacje
id - INT klucz główny
id_uzytkownika - INT klucz obcy do uzytkownicy
id_konia - INT klucz obcy do konie
data_od - DATE początek rezerwacji
data_do - DATE koniec rezerwacji
status - ENUM oczekujaca potwierdzona anulowana
data_rezerwacji - TIMESTAMP data złożenia rezerwacji

Instalacja krok po kroku

Wymagania:
Zainstalowany XAMPP https://www.apachefriends.org/
Przeglądarka internetowa

Kroki instalacji:

Pobierz i rozpakuj projekt do folderu C:\xampp\htdocs\konzvalony\

Uruchom XAMPP i włącz moduły Apache i MySQL

Zaimportuj bazę danych:
Otwórz http://localhost/phpmyadmin
Kliknij Nowa baza danych
Nazwij bazę konzvalony z kodowaniem utf8mb4_polish_ci
Kliknij zakładkę Import
Wybierz plik baza.sql z folderu projektu
Kliknij Wykonaj

Opcjonalnie wygeneruj hasła:
Otwórz http://localhost/konzvalony/generuj_hasla.php
Skopiuj wygenerowane hashe
W phpMyAdmin zaktualizuj hasła w tabeli uzytkownicy

Uruchom stronę pod adresem http://localhost/konzvalony/

Domyślne konta testowe

Po instalacji możesz zalogować się na:

Administrator:
Login admin
Hasło admin123
Uprawnienia Pełny dostęp do zarządzania końmi, użytkownikami i rezerwacjami

Zwykły użytkownik:
Login janek
Hasło user123

Drugi użytkownik:
Login kasia
Hasło user123

Instrukcja obsługi

Dla zwykłego użytkownika:

Wejdź na stronę główną

Przeglądaj konie z humorystycznymi opisami

Kliknij Zaloguj się 

Po zalogowaniu możesz rezerwować konie klikając Rezerwuj

W panelu Moje rezerwacje sprawdzisz status swoich rezerwacji

Dla administratora:

Zaloguj się na konto admina

W panelu masz dodatkowe zakładki:
Zarządzanie końmi - dodawanie, edycja, usuwanie koni
Użytkownicy - zarządzanie kontami, zmiana ról, usuwanie
Wszystkie rezerwacje - potwierdzanie, anulowanie rezerwacji

Responsywność
Strona została zaprojektowana z myślą o różnych urządzeniach:
Desktop pełna wersja z dużą ilością informacji
Tablet siatka 2-kolumnowa, zmniejszone marginesy
Telefon wszystko w jednej kolumnie, menu w formie hamburgera

WCAG dostępność
Strona spełnia podstawowe wymogi dostępności:
Odpowiedni kontrast tekstu
Atrybuty alt dla wszystkich obrazków
Możliwość nawigacji klawiaturą
Czytelna struktura nagłówków
Etykiety dla pól formularzy
Skip link do pominięcia nawigacji

Autorzy
Jacek Stolarz , Patryk Rybak , Kamil Szeliga

Licencja
Projekt edukacyjny - można dowolnie modyfikować i rozwijać.