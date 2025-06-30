/**
 * Task Manager - JavaScript
 * Demonstracja: Clean code, user experience enhancements
 */

document.addEventListener('DOMContentLoaded', function() {
    // Inicjalizacja aplikacji
    initializeApp();
});

/**
 * Inicjalizacja głównych funkcjonalności aplikacji
 */
function initializeApp() {
    // Automatyczne ukrywanie alertów
    hideAlertsAfterDelay();
    
    // Walidacja formularza
    setupFormValidation();
    
    // Ulepszenia UX
    setupUIEnhancements();
    
    // Skróty klawiszowe
    setupKeyboardShortcuts();
}

/**
 * Automatyczne ukrywanie alertów po 5 sekundach
 */
function hideAlertsAfterDelay() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => {
                alert.remove();
            }, 300);
        }, 5000);
    });
}

/**
 * Walidacja formularza po stronie klienta
 */
function setupFormValidation() {
    const form = document.querySelector('.task-form');
    if (!form) return;
    
    const titleInput = form.querySelector('#title');
    const dueDateInput = form.querySelector('#due_date');
    
    // Walidacja tytułu
    if (titleInput) {
        titleInput.addEventListener('blur', function() {
            validateTitle(this);
        });
        
        titleInput.addEventListener('input', function() {
            clearValidationError(this);
        });
    }
    
    // Walidacja daty
    if (dueDateInput) {
        dueDateInput.addEventListener('change', function() {
            validateDueDate(this);
        });
    }
    
    // Walidacja przed wysłaniem
    form.addEventListener('submit', function(e) {
        let isValid = true;
        
        if (titleInput && !validateTitle(titleInput)) {
            isValid = false;
        }
        
        if (dueDateInput && !validateDueDate(dueDateInput)) {
            isValid = false;
        }
        
        if (!isValid) {
            e.preventDefault();
            showValidationAlert('Proszę poprawić błędy w formularzu');
        }
    });
}

/**
 * Walidacja tytułu zadania
 */
function validateTitle(input) {
    const value = input.value.trim();
    
    if (value.length === 0) {
        showValidationError(input, 'Tytuł zadania jest wymagany');
        return false;
    }
    
    if (value.length > 255) {
        showValidationError(input, 'Tytuł zadania nie może być dłuższy niż 255 znaków');
        return false;
    }
    
    clearValidationError(input);
    return true;
}

/**
 * Walidacja daty wykonania
 */
function validateDueDate(input) {
    const value = input.value;
    
    if (value) {
        const selectedDate = new Date(value);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        if (selectedDate < today) {
            showValidationError(input, 'Data wykonania nie może być z przeszłości');
            return false;
        }
    }
    
    clearValidationError(input);
    return true;
}

/**
 * Wyświetlanie błędu walidacji
 */
function showValidationError(input, message) {
    clearValidationError(input);
    
    input.style.borderColor = '#e53e3e';
    
    const errorDiv = document.createElement('div');
    errorDiv.className = 'validation-error';
    errorDiv.style.color = '#e53e3e';
    errorDiv.style.fontSize = '0.875em';
    errorDiv.style.marginTop = '5px';
    errorDiv.textContent = message;
    
    input.parentNode.appendChild(errorDiv);
}

/**
 * Czyszczenie błędu walidacji
 */
function clearValidationError(input) {
    input.style.borderColor = '';
    
    const existingError = input.parentNode.querySelector('.validation-error');
    if (existingError) {
        existingError.remove();
    }
}

/**
 * Wyświetlanie alertu walidacji
 */
function showValidationAlert(message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-error';
    alertDiv.innerHTML = `
        <i class="fas fa-exclamation-triangle"></i>
        ${message}
    `;
    
    const container = document.querySelector('.container');
    const header = container.querySelector('.header');
    container.insertBefore(alertDiv, header.nextSibling);
    
    // Automatyczne ukrycie
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}

/**
 * Ulepszenia interfejsu użytkownika
 */
function setupUIEnhancements() {
    // Animacja hover dla kart zadań
    const taskCards = document.querySelectorAll('.task-card');
    taskCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-3px)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
    
    // Potwierdzenie usunięcia zadania
    const deleteButtons = document.querySelectorAll('button[title="Usuń"]');
    deleteButtons.forEach(button => {
        const form = button.closest('form');
        form.addEventListener('submit', function(e) {
            if (!confirm('Czy na pewno chcesz usunąć to zadanie? Ta operacja nie może być cofnięta.')) {
                e.preventDefault();
            }
        });
    });
    
    // Tooltips dla ikon
    setupTooltips();
    
    // Licznik znaków dla opisu
    setupCharacterCounter();
}

/**
 * Skróty klawiszowe
 */
function setupKeyboardShortcuts() {
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + N - nowe zadanie (fokus na tytuł)
        if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
            e.preventDefault();
            const titleInput = document.querySelector('#title');
            if (titleInput) {
                titleInput.focus();
                titleInput.scrollIntoView({ behavior: 'smooth' });
            }
        }
        
        // Escape - anuluj edycję
        if (e.key === 'Escape') {
            const editUrl = new URL(window.location);
            if (editUrl.searchParams.has('edit')) {
                window.location.href = 'index.php';
            }
        }
    });
}

/**
 * Tooltips dla ikon
 */
function setupTooltips() {
    const iconButtons = document.querySelectorAll('.btn-icon[title]');
    iconButtons.forEach(button => {
        button.addEventListener('mouseenter', function() {
            showTooltip(this, this.getAttribute('title'));
        });
        
        button.addEventListener('mouseleave', function() {
            hideTooltip();
        });
    });
}

/**
 * Wyświetlanie tooltip
 */
function showTooltip(element, text) {
    const tooltip = document.createElement('div');
    tooltip.className = 'custom-tooltip';
    tooltip.textContent = text;
    tooltip.style.cssText = `
        position: absolute;
        background: #2d3748;
        color: white;
        padding: 6px 10px;
        border-radius: 4px;
        font-size: 12px;
        z-index: 1000;
        pointer-events: none;
        opacity: 0;
        transition: opacity 0.2s;
        white-space: nowrap;
    `;
    
    document.body.appendChild(tooltip);
    
    const rect = element.getBoundingClientRect();
    const tooltipWidth = tooltip.offsetWidth;
    const tooltipHeight = tooltip.offsetHeight;
    
    // Pozycja X - wyśrodkowana względem elementu, z sprawdzeniem granic ekranu
    let leftPos = rect.left + rect.width / 2 - tooltipWidth / 2;
    
    // Sprawdź czy tooltip nie wychodzi poza prawą krawędź
    if (leftPos + tooltipWidth > window.innerWidth - 10) {
        leftPos = window.innerWidth - tooltipWidth - 10;
    }
    
    // Sprawdź czy tooltip nie wychodzi poza lewą krawędź
    if (leftPos < 10) {
        leftPos = 10;
    }
    
    // Pozycja Y - sprawdź czy jest miejsce nad elementem
    let topPos = rect.top + window.scrollY - tooltipHeight - 8;
    
    // Jeśli tooltip wychodziłby poza górną krawędź, pokaż go pod elementem
    if (topPos < window.scrollY + 10) {
        topPos = rect.bottom + window.scrollY + 8;
        
        // Dodaj małą strzałkę wskazującą w górę (opcjonalnie)
        tooltip.style.cssText += `
            &::before {
                content: '';
                position: absolute;
                top: -4px;
                left: 50%;
                transform: translateX(-50%);
                border-left: 4px solid transparent;
                border-right: 4px solid transparent;
                border-bottom: 4px solid #2d3748;
            }
        `;
    }
    
    tooltip.style.left = leftPos + 'px';
    tooltip.style.top = topPos + 'px';
    
    // Animacja pojawiania się
    setTimeout(() => {
        tooltip.style.opacity = '1';
    }, 10);
}

/**
 * Ukrywanie tooltip
 */
function hideTooltip() {
    const tooltip = document.querySelector('.custom-tooltip');
    if (tooltip) {
        tooltip.style.opacity = '0';
        setTimeout(() => {
            tooltip.remove();
        }, 200);
    }
}

/**
 * Licznik znaków dla pola opisu
 */
function setupCharacterCounter() {
    const descriptionTextarea = document.querySelector('#description');
    if (!descriptionTextarea) return;
    
    const maxLength = 1000; // Przykładowy limit
    
    const counter = document.createElement('div');
    counter.className = 'character-counter';
    counter.style.cssText = `
        text-align: right;
        font-size: 0.8rem;
        color: #666;
        margin-top: 5px;
    `;
    
    descriptionTextarea.parentNode.appendChild(counter);
    
    function updateCounter() {
        const length = descriptionTextarea.value.length;
        counter.textContent = `${length}/${maxLength} znaków`;
        
        if (length > maxLength * 0.9) {
            counter.style.color = '#e53e3e';
        } else if (length > maxLength * 0.7) {
            counter.style.color = '#f39c12';
        } else {
            counter.style.color = '#666';
        }
    }
    
    descriptionTextarea.addEventListener('input', updateCounter);
    updateCounter(); // Inicjalne wywołanie
}

/**
 * Funkcje pomocnicze
 */

// Formatowanie daty
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('pl-PL');
}

// Debugowanie (tylko w trybie rozwoju)
function debug(message, data = null) {
    if (window.location.hostname === 'localhost') {
        console.log(`[Task Manager] ${message}`, data);
    }
}

// Eksportowanie funkcji globalnych
window.TaskManager = {
    validateTitle,
    validateDueDate,
    formatDate,
    debug
}; 