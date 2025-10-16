// Dashboard JavaScript untuk Perpustakaan ITBI
class DashboardManager {
    constructor() {
        this.init();
    }

    init() {
        this.initializeCharts();
        this.initializeEventListeners();
        this.initializeRealTimeUpdates();
        this.initializeNotifications();
    }

    // Initialize Charts
    initializeCharts() {
        // Chart untuk statistik peminjaman
        this.initPeminjamanChart();
        
        // Chart untuk buku populer
        this.initBukuPopulerChart();
        
        // Chart untuk trend bulanan
        this.initTrendChart();
    }

    initPeminjamanChart() {
        const ctx = document.getElementById('peminjamanChart');
        if (!ctx) return;

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Dipinjam', 'Dikembalikan', 'Terlambat'],
                datasets: [{
                    data: [12, 19, 3],
                    backgroundColor: [
                        'rgba(255, 193, 7, 0.8)',
                        'rgba(40, 167, 69, 0.8)',
                        'rgba(220, 53, 69, 0.8)'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = Math.round((value / total) * 100);
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
    }

    initBukuPopulerChart() {
        const ctx = document.getElementById('bukuChart');
        if (!ctx) return;

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Pemrograman Web', 'Manajemen Bisnis', 'Data Science', 'Kewirausahaan', 'Algoritma'],
                datasets: [{
                    label: 'Jumlah Dipinjam',
                    data: [12, 19, 8, 15, 7],
                    backgroundColor: [
                        'rgba(255, 107, 53, 0.8)',
                        'rgba(255, 165, 0, 0.8)',
                        'rgba(255, 69, 0, 0.8)',
                        'rgba(255, 140, 0, 0.8)',
                        'rgba(255, 99, 71, 0.8)'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 5
                        }
                    }
                }
            }
        });
    }

    initTrendChart() {
        const ctx = document.getElementById('trendChart');
        if (!ctx) return;

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'],
                datasets: [{
                    label: 'Peminjaman',
                    data: [65, 59, 80, 81, 56, 72],
                    backgroundColor: 'rgba(255, 107, 53, 0.1)',
                    borderColor: 'rgba(255, 107, 53, 1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    // Event Listeners
    initializeEventListeners() {
        // Search functionality
        this.initSearch();
        
        // Filter functionality
        this.initFilters();
        
        // Modal handlers
        this.initModals();
        
        // Form validation
        this.initFormValidation();
        
        // Table interactions
        this.initTableInteractions();
    }

    initSearch() {
        const searchInputs = document.querySelectorAll('.search-input');
        searchInputs.forEach(input => {
            input.addEventListener('input', this.debounce((e) => {
                this.performSearch(e.target.value);
            }, 300));
        });
    }

    initFilters() {
        const filterButtons = document.querySelectorAll('.filter-btn');
        filterButtons.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                this.applyFilters(btn.dataset.filter);
            });
        });
    }

    initModals() {
        // Close modal on outside click
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal')) {
                this.closeModal(e.target);
            }
        });

        // Close modal on ESC key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closeAllModals();
            }
        });
    }

    initFormValidation() {
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            form.addEventListener('submit', (e) => {
                if (!this.validateForm(form)) {
                    e.preventDefault();
                    this.showFormErrors(form);
                }
            });
        });
    }

    initTableInteractions() {
        // Row selection
        const tableRows = document.querySelectorAll('.table tbody tr');
        tableRows.forEach(row => {
            row.addEventListener('click', (e) => {
                if (!e.target.closest('a, button')) {
                    row.classList.toggle('selected');
                }
            });
        });

        // Bulk actions
        this.initBulkActions();
    }

    initBulkActions() {
        const selectAll = document.querySelector('.select-all');
        if (selectAll) {
            selectAll.addEventListener('change', (e) => {
                const checkboxes = document.querySelectorAll('.row-select');
                checkboxes.forEach(checkbox => {
                    checkbox.checked = e.target.checked;
                });
            });
        }
    }

    // Real-time Updates
    initializeRealTimeUpdates() {
        // Update stats every 30 seconds
        setInterval(() => {
            this.updateDashboardStats();
        }, 30000);

        // Check for new notifications
        setInterval(() => {
            this.checkNotifications();
        }, 60000);
    }

    async updateDashboardStats() {
        try {
            const response = await fetch('api/dashboard-stats.php');
            const data = await response.json();
            
            this.updateStatCards(data.stats);
            this.updateCharts(data.charts);
            
        } catch (error) {
            console.error('Error updating dashboard stats:', error);
        }
    }

    updateStatCards(stats) {
        const statCards = document.querySelectorAll('.stat-number');
        statCards.forEach(card => {
            const statType = card.closest('.stat-card').querySelector('h3').textContent;
            if (stats[statType]) {
                this.animateValue(card, parseInt(card.textContent), stats[statType], 1000);
            }
        });
    }

    updateCharts(chartData) {
        // Update chart data here
        // Implementation depends on specific chart library
    }

    // Notifications
    initializeNotifications() {
        this.checkNotifications();
        
        // Notification bell click
        const notificationBell = document.querySelector('.notification-bell');
        if (notificationBell) {
            notificationBell.addEventListener('click', () => {
                this.toggleNotificationPanel();
            });
        }
    }

    async checkNotifications() {
        try {
            const response = await fetch('api/notifications.php');
            const notifications = await response.json();
            
            this.updateNotificationBadge(notifications.length);
            this.updateNotificationPanel(notifications);
            
        } catch (error) {
            console.error('Error checking notifications:', error);
        }
    }

    updateNotificationBadge(count) {
        const badge = document.querySelector('.notification-badge');
        if (badge) {
            badge.textContent = count;
            badge.style.display = count > 0 ? 'block' : 'none';
        }
    }

    updateNotificationPanel(notifications) {
        const panel = document.querySelector('.notification-panel');
        if (panel) {
            panel.innerHTML = notifications.map(notif => `
                <div class="notification-item ${notif.read ? 'read' : 'unread'}">
                    <div class="notification-icon">
                        <i class="fas ${this.getNotificationIcon(notif.type)}"></i>
                    </div>
                    <div class="notification-content">
                        <p class="notification-text">${notif.message}</p>
                        <small class="notification-time">${this.formatTime(notif.timestamp)}</small>
                    </div>
                </div>
            `).join('');
        }
    }

    toggleNotificationPanel() {
        const panel = document.querySelector('.notification-panel');
        if (panel) {
            panel.classList.toggle('show');
        }
    }

    // Utility Functions
    debounce(func, wait) {
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

    animateValue(element, start, end, duration) {
        const range = end - start;
        const startTime = performance.now();
        
        function updateValue(currentTime) {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            const value = start + (range * progress);
            element.textContent = Math.round(value).toLocaleString();
            
            if (progress < 1) {
                requestAnimationFrame(updateValue);
            }
        }
        
        requestAnimationFrame(updateValue);
    }

    validateForm(form) {
        let isValid = true;
        const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
        
        inputs.forEach(input => {
            if (!input.value.trim()) {
                this.markInvalid(input, 'Field ini wajib diisi');
                isValid = false;
            } else {
                this.markValid(input);
            }
            
            // Email validation
            if (input.type === 'email' && input.value) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(input.value)) {
                    this.markInvalid(input, 'Format email tidak valid');
                    isValid = false;
                }
            }
            
            // Password strength
            if (input.type === 'password' && input.value) {
                if (input.value.length < 6) {
                    this.markInvalid(input, 'Password minimal 6 karakter');
                    isValid = false;
                }
            }
        });
        
        return isValid;
    }

    markInvalid(input, message) {
        input.classList.add('error');
        let feedback = input.nextElementSibling;
        
        if (!feedback || !feedback.classList.contains('feedback')) {
            feedback = document.createElement('div');
            feedback.className = 'feedback invalid';
            input.parentNode.insertBefore(feedback, input.nextSibling);
        }
        
        feedback.textContent = message;
    }

    markValid(input) {
        input.classList.remove('error');
        const feedback = input.nextElementSibling;
        if (feedback && feedback.classList.contains('feedback')) {
            feedback.remove();
        }
    }

    showFormErrors(form) {
        const firstError = form.querySelector('.error');
        if (firstError) {
            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            firstError.focus();
        }
    }

    getNotificationIcon(type) {
        const icons = {
            'peminjaman': 'fa-book',
            'pengembalian': 'fa-undo',
            'denda': 'fa-money-bill-wave',
            'system': 'fa-cog',
            'info': 'fa-info-circle'
        };
        return icons[type] || 'fa-bell';
    }

    formatTime(timestamp) {
        const now = new Date();
        const time = new Date(timestamp);
        const diff = now - time;
        
        const minutes = Math.floor(diff / 60000);
        const hours = Math.floor(diff / 3600000);
        const days = Math.floor(diff / 86400000);
        
        if (minutes < 1) return 'Baru saja';
        if (minutes < 60) return `${minutes} menit lalu`;
        if (hours < 24) return `${hours} jam lalu`;
        if (days < 7) return `${days} hari lalu`;
        
        return time.toLocaleDateString('id-ID');
    }

    // Modal Functions
    openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
    }

    closeModal(modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    closeAllModals() {
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => this.closeModal(modal));
    }

    // Search and Filter
    performSearch(query) {
        const table = document.querySelector('.table');
        if (!table) return;
        
        const rows = table.querySelectorAll('tbody tr');
        const lowerQuery = query.toLowerCase();
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(lowerQuery) ? '' : 'none';
        });
    }

    applyFilters(filter) {
        // Implementation depends on specific filter requirements
        console.log('Applying filter:', filter);
    }

    // Export functionality
    exportTable(tableId, format = 'csv') {
        const table = document.getElementById(tableId);
        if (!table) return;
        
        let data = [];
        const headers = [];
        
        // Get headers
        table.querySelectorAll('thead th').forEach(th => {
            headers.push(th.textContent.trim());
        });
        
        // Get data
        table.querySelectorAll('tbody tr').forEach(row => {
            const rowData = [];
            row.querySelectorAll('td').forEach(cell => {
                rowData.push(cell.textContent.trim());
            });
            data.push(rowData);
        });
        
        if (format === 'csv') {
            this.exportToCSV(headers, data, `export-${new Date().getTime()}.csv`);
        } else if (format === 'excel') {
            this.exportToExcel(headers, data, `export-${new Date().getTime()}.xlsx`);
        }
    }

    exportToCSV(headers, data, filename) {
        let csvContent = headers.join(',') + '\n';
        data.forEach(row => {
            csvContent += row.map(cell => `"${cell}"`).join(',') + '\n';
        });
        
        const blob = new Blob([csvContent], { type: 'text/csv' });
        this.downloadBlob(blob, filename);
    }

    exportToExcel(headers, data, filename) {
        // Simple Excel export using CSV (for real Excel export, use a library like SheetJS)
        this.exportToCSV(headers, data, filename.replace('.xlsx', '.csv'));
    }

    downloadBlob(blob, filename) {
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }
}

// Initialize dashboard when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.dashboard = new DashboardManager();
    
    // Additional initialization for specific pages
    if (document.querySelector('.pinjam-page')) {
        initializePinjamPage();
    }
    
    if (document.querySelector('.profile-page')) {
        initializeProfilePage();
    }
});

// Page-specific initializations
function initializePinjamPage() {
    // Book selection functionality
    const bookCards = document.querySelectorAll('.book-card-select');
    bookCards.forEach(card => {
        card.addEventListener('click', function(e) {
            if (!e.target.closest('.book-actions')) {
                const checkbox = this.querySelector('input[type="checkbox"]');
                if (!checkbox.disabled) {
                    checkbox.checked = !checkbox.checked;
                    this.classList.toggle('selected', checkbox.checked);
                    updateSelectionCount();
                }
            }
        });
    });

    function updateSelectionCount() {
        const selected = document.querySelectorAll('input[name="buku_id[]"]:checked').length;
        const countElement = document.getElementById('selectedCount');
        if (countElement) {
            countElement.textContent = `${selected} buku terpilih`;
        }
    }
}

function initializeProfilePage() {
    // Password strength indicator
    const passwordInput = document.getElementById('new_password');
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            checkPasswordStrength(this.value);
        });
    }

    function checkPasswordStrength(password) {
        let strength = 0;
        const feedback = document.getElementById('password-strength');
        
        if (!feedback) return;
        
        if (password.length >= 6) strength++;
        if (password.length >= 8) strength++;
        if (/[a-z]/.test(password)) strength++;
        if (/[A-Z]/.test(password)) strength++;
        if (/[0-9]/.test(password)) strength++;
        if (/[^a-zA-Z0-9]/.test(password)) strength++;
        
        let message = '';
        let color = '';
        
        if (strength <= 2) {
            message = 'Lemah';
            color = '#dc3545';
        } else if (strength <= 4) {
            message = 'Cukup';
            color = '#ffc107';
        } else {
            message = 'Kuat';
            color = '#28a745';
        }
        
        feedback.textContent = message;
        feedback.style.color = color;
    }
}

// Global utility functions
function formatDate(dateString) {
    const options = { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    };
    return new Date(dateString).toLocaleDateString('id-ID', options);
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(amount);
}

function showLoading() {
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

function hideLoading() {
    const spinner = document.querySelector('.loading-spinner');
    if (spinner) {
        spinner.remove();
    }
}

// Auto-hide alerts
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
});