// Utility functions
class ITBILibrary {
    // Show loading spinner
    static showLoading() {
        const spinner = document.createElement('div');
        spinner.className = 'loading-spinner';
        spinner.innerHTML = `
            <div class="spinner-overlay">
                <div class="spinner"></div>
                <p>Memproses...</p>
            </div>
        `;
        document.body.appendChild(spinner);
    }

    // Hide loading spinner
    static hideLoading() {
        const spinner = document.querySelector('.loading-spinner');
        if (spinner) {
            spinner.remove();
        }
    }

    // Show notification
    static showNotification(message, type = 'success') {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <i class="fas ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle'}"></i>
                <span>${message}</span>
                <button class="notification-close" onclick="this.parentElement.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;

        document.body.appendChild(notification);

        // Auto remove after 5 seconds
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 5000);
    }

    // Format date
    static formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('id-ID', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
    }

    // Format currency
    static formatCurrency(amount) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(amount);
    }

    // Validate form
    static validateForm(form) {
        const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
        let isValid = true;

        inputs.forEach(input => {
            if (!input.value.trim()) {
                input.classList.add('error');
                isValid = false;
            } else {
                input.classList.remove('error');
            }
        });

        return isValid;
    }

    // Search functionality
    static debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
}

// Auto-hide flash messages
document.addEventListener('DOMContentLoaded', function() {
    // Auto hide flash messages after 5 seconds
    const flashMessages = document.querySelectorAll('#msg-flash');
    flashMessages.forEach(message => {
        setTimeout(() => {
            message.style.opacity = '0';
            setTimeout(() => message.remove(), 300);
        }, 5000);
    });

    // Add loading to forms
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (ITBILibrary.validateForm(this)) {
                ITBILibrary.showLoading();
            }
        });
    });

    // Add search debounce
    const searchInputs = document.querySelectorAll('input[type="search"]');
    searchInputs.forEach(input => {
        input.addEventListener('input', ITBILibrary.debounce(function(e) {
            if (e.target.value.length >= 3 || e.target.value.length === 0) {
                e.target.form.submit();
            }
        }, 500));
    });
});

// Add CSS for new components
const style = document.createElement('style');
style.textContent = `
    .loading-spinner {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }

    .spinner-overlay {
        background: white;
        padding: 2rem;
        border-radius: 10px;
        text-align: center;
        box-shadow: 0 5px 15px rgba(0,0,0,0.3);
    }

    .spinner {
        border: 4px solid #f3f3f3;
        border-top: 4px solid var(--primary);
        border-radius: 50%;
        width: 40px;
        height: 40px;
        animation: spin 1s linear infinite;
        margin: 0 auto 1rem;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .notification {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 1000;
        min-width: 300px;
        max-width: 500px;
    }

    .notification-content {
        background: white;
        padding: 1rem;
        border-radius: 5px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.2);
        display: flex;
        align-items: center;
        border-left: 4px solid;
    }

    .notification-success .notification-content {
        border-left-color: var(--success);
    }

    .notification-error .notification-content {
        border-left-color: var(--danger);
    }

    .notification-info .notification-content {
        border-left-color: var(--info);
    }

    .notification-content i:first-child {
        margin-right: 0.5rem;
        font-size: 1.2rem;
    }

    .notification-success .notification-content i:first-child {
        color: var(--success);
    }

    .notification-error .notification-content i:first-child {
        color: var(--danger);
    }

    .notification-info .notification-content i:first-child {
        color: var(--info);
    }

    .notification-content span {
        flex: 1;
    }

    .notification-close {
        background: none;
        border: none;
        cursor: pointer;
        color: #666;
        margin-left: 0.5rem;
    }

    .error {
        border-color: var(--danger) !important;
    }

    @media (max-width: 768px) {
        .notification {
            left: 20px;
            right: 20px;
            min-width: auto;
        }
    }
`;
document.head.appendChild(style);