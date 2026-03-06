// Główny plik JavaScript dla strony KonZValony

document.addEventListener('DOMContentLoaded', function() {
    // Inicjalizacja wszystkich funkcji
    initMobileMenu();
    initFormValidation();
    initDatePickers();
    initHorseModal();
    initAccessibility();
});

// Mobilne menu
function initMobileMenu() {
    const nav = document.querySelector('nav ul');
    if (!nav) return;
    
    // Dodaj przycisk menu dla mobile
    const menuButton = document.createElement('button');
    menuButton.className = 'mobile-menu-btn';
    menuButton.innerHTML = '☰ Menu';
    menuButton.setAttribute('aria-label', 'Rozwiń menu');
    menuButton.setAttribute('aria-expanded', 'false');
    
    const header = document.querySelector('header');
    if (header && window.innerWidth <= 768) {
        header.insertBefore(menuButton, nav);
        
        menuButton.addEventListener('click', function() {
            const expanded = this.getAttribute('aria-expanded') === 'true' ? false : true;
            nav.style.display = nav.style.display === 'none' ? 'flex' : 'none';
            this.setAttribute('aria-expanded', expanded);
            this.innerHTML = expanded ? '✕ Zamknij' : '☰ Menu';
        });
    }
    
    // Ukryj domyślnie na mobile
    if (window.innerWidth <= 768) {
        nav.style.display = 'none';
    }
    
    // Obsługa zmiany rozmiaru okna
    window.addEventListener('resize', function() {
        if (window.innerWidth <= 768) {
            nav.style.display = 'none';
            menuButton.style.display = 'block';
        } else {
            nav.style.display = 'flex';
            menuButton.style.display = 'none';
        }
    });
}

// Walidacja formularzy
function initFormValidation() {
    const forms = document.querySelectorAll('.auth-form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            let isValid = true;
            const inputs = this.querySelectorAll('input[required]');
            
            inputs.forEach(input => {
                if (!input.value.trim()) {
                    showFieldError(input, 'To pole jest wymagane');
                    isValid = false;
                } else if (input.type === 'email' && !isValidEmail(input.value)) {
                    showFieldError(input, 'Podaj poprawny adres email');
                    isValid = false;
                } else if (input.id === 'password' && input.value.length < 6) {
                    showFieldError(input, 'Hasło musi mieć co najmniej 6 znaków');
                    isValid = false;
                } else if (input.id === 'confirm_password') {
                    const password = document.getElementById('password');
                    if (password && input.value !== password.value) {
                        showFieldError(input, 'Hasła nie są zgodne');
                        isValid = false;
                    }
                } else {
                    removeFieldError(input);
                }
            });
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    });
    
    // Usuwanie błędów podczas pisania
    document.querySelectorAll('input').forEach(input => {
        input.addEventListener('input', function() {
            removeFieldError(this);
        });
    });
}

function isValidEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function showFieldError(field, message) {
    removeFieldError(field);
    
    field.style.borderColor = '#e74c3c';
    const error = document.createElement('div');
    error.className = 'field-error';
    error.textContent = message;
    error.style.color = '#e74c3c';
    error.style.fontSize = '0.85rem';
    error.style.marginTop = '0.25rem';
    
    field.parentNode.appendChild(error);
}

function removeFieldError(field) {
    field.style.borderColor = '';
    const error = field.parentNode.querySelector('.field-error');
    if (error) {
        error.remove();
    }
}

// Inicjalizacja pól daty
function initDatePickers() {
    const dateInputs = document.querySelectorAll('input[type="date"]');
    const today = new Date().toISOString().split('T')[0];
    
    dateInputs.forEach(input => {
        if (!input.value) {
            input.min = today;
        }
        
        // Walidacja dat - data_do nie może być wcześniejsza niż data_od
        if (input.id === 'data_do') {
            const dataOd = document.getElementById('data_od');
            if (dataOd) {
                input.min = dataOd.value || today;
                
                dataOd.addEventListener('change', function() {
                    input.min = this.value || today;
                    if (input.value && input.value < this.value) {
                        input.value = this.value;
                    }
                });
            }
        }
    });
}

// Modal do edycji koni
function initHorseModal() {
    const modal = document.getElementById('editModal');
    if (!modal) return;
    
    const closeBtn = modal.querySelector('.close');
    const editForm = document.getElementById('editForm');
    
    // Zamknij modal po kliknięciu na X
    if (closeBtn) {
        closeBtn.addEventListener('click', function() {
            modal.style.display = 'none';
        });
    }
    
    // Zamknij modal po kliknięciu poza nim
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
    
    // Walidacja formularza edycji
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            const cena = document.getElementById('edit_cena');
            const wiek = document.getElementById('edit_wiek');
            
            if (cena && cena.value <= 0) {
                e.preventDefault();
                showFieldError(cena, 'Cena musi być większa od 0');
            }
            
            if (wiek && (wiek.value < 1 || wiek.value > 40)) {
                e.preventDefault();
                showFieldError(wiek, 'Wiek musi być między 1 a 40 lat');
            }
        });
    }
}

// Funkcja globalna do edycji konia (wywoływana z panelu)
window.editHorse = function(id) {
    const modal = document.getElementById('editModal');
    if (!modal || typeof konieData === 'undefined') return;
    
    const horse = konieData.find(h => h.id === id);
    if (!horse) return;
    
    // Wypełnij formularz danymi
    document.getElementById('edit_id').value = horse.id;
    document.getElementById('edit_nazwa').value = horse.nazwa;
    document.getElementById('edit_rasa').value = horse.rasa;
    document.getElementById('edit_wiek').value = horse.wiek;
    document.getElementById('edit_opis').value = horse.opis;
    document.getElementById('edit_cena').value = horse.cena_za_dobe;
    document.getElementById('edit_dostepny').checked = horse.dostepny == 1;
    
    // Pokaż modal
    modal.style.display = 'block';
    
    // Ustaw fokus na pierwsze pole
    document.getElementById('edit_nazwa').focus();
};

// Funkcje dostępności
function initAccessibility() {
    // Dodaj skip link dla nawigacji klawiaturą
    const skipLink = document.createElement('a');
    skipLink.href = '#main-content';
    skipLink.className = 'skip-link';
    skipLink.textContent = 'Przejdź do treści';
    skipLink.style.cssText = `
        position: absolute;
        top: -40px;
        left: 0;
        background: #e67e22;
        color: white;
        padding: 8px;
        z-index: 100;
        text-decoration: none;
    `;
    
    skipLink.addEventListener('focus', function() {
        this.style.top = '0';
    });
    
    skipLink.addEventListener('blur', function() {
        this.style.top = '-40px';
    });
    
    document.body.insertBefore(skipLink, document.body.firstChild);
    
    // Dodaj atrybuty ARIA do głównych sekcji
    const main = document.querySelector('main');
    if (main) {
        main.id = 'main-content';
        main.setAttribute('tabindex', '-1');
        main.setAttribute('role', 'main');
    }
    
    // Obsługa powiększania czcionki
    const fontSizeBtn = document.createElement('button');
    fontSizeBtn.className = 'font-size-btn';
    fontSizeBtn.innerHTML = 'A+';
    fontSizeBtn.setAttribute('aria-label', 'Zwiększ czcionkę');
    fontSizeBtn.style.cssText = `
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: #e67e22;
        color: white;
        border: none;
        border-radius: 50%;
        width: 50px;
        height: 50px;
        font-size: 1.5rem;
        cursor: pointer;
        z-index: 99;
        box-shadow: 0 4px 10px rgba(0,0,0,0.3);
    `;
    
    let fontSize = 100;
    fontSizeBtn.addEventListener('click', function() {
        fontSize = fontSize === 100 ? 120 : 100;
        document.documentElement.style.fontSize = fontSize + '%';
    });
    
    document.body.appendChild(fontSizeBtn);
}

// Animacje i efekty
function addHoverEffects() {
    const cards = document.querySelectorAll('.horse-card');
    
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
}

// Potwierdzenia dla ważnych akcji
function confirmAction(message) {
    return confirm(message || 'Czy na pewno chcesz wykonać tę operację?');
}

// Eksport funkcji do globalnego zasięgu
window.confirmAction = confirmAction;

// Inicjalizacja po pełnym załadowaniu strony
window.addEventListener('load', function() {
    addHoverEffects();
    
    // Ukryj komunikaty po 5 sekundach
    const messages = document.querySelectorAll('.message');
    messages.forEach(message => {
        setTimeout(() => {
            message.style.opacity = '0';
            setTimeout(() => {
                message.style.display = 'none';
            }, 500);
        }, 5000);
    });
});

// Obsługa błędów ładowania obrazów
document.querySelectorAll('img').forEach(img => {
    img.addEventListener('error', function() {
        this.src = 'images/default-horse.jpg';
        this.alt = 'Domyślne zdjęcie konia';
    });
});