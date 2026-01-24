// script.js
document.addEventListener('DOMContentLoaded', function() {
    // Animasi untuk stat cards
    const statCards = document.querySelectorAll('.stat-card');
    statCards.forEach((card, index) => {
        card.style.animationDelay = `${index * 0.1}s`;
        card.classList.add('animate__animated', 'animate__fadeInUp');
    });
    
    // Form validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.style.borderColor = '#ef4444';
                    field.style.boxShadow = '0 0 0 3px rgba(239, 68, 68, 0.1)';
                    
                    // Remove error style on focus
                    field.addEventListener('focus', function() {
                        this.style.borderColor = '#4f46e5';
                        this.style.boxShadow = '0 0 0 3px rgba(79, 70, 229, 0.1)';
                    });
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                showNotification('Harap isi semua field yang wajib diisi!', 'error');
            }
        });
    });
    
    // Auto-hide notifications
    const notifications = document.querySelectorAll('.notification');
    notifications.forEach(notification => {
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 5000);
    });
});

// Global functions
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
        <span>${message}</span>
        <button class="notification-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 5000);
}

function confirmAction(message, callback) {
    const modal = document.createElement('div');
    modal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 2000;
    `;
    
    modal.innerHTML = `
        <div style="background: white; padding: 30px; border-radius: 12px; max-width: 400px; width: 90%;">
            <h3 style="margin-bottom: 15px; color: #1e293b;">Konfirmasi</h3>
            <p style="margin-bottom: 25px; color: #64748b;">${message}</p>
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button onclick="this.closest('div[style]').remove()" class="btn btn-secondary" style="padding: 10px 20px;">
                    Batal
                </button>
                <button onclick="callbackAction()" class="btn btn-${message.includes('hapus') || message.includes('tolak') ? 'danger' : 'primary'}" style="padding: 10px 20px;">
                    Ya, Lanjutkan
                </button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    window.callbackAction = function() {
        callback();
        modal.remove();
    };
}

// Export to PDF/Excel
function exportReport(type) {
    showNotification(`Sedang mengekspor laporan dalam format ${type.toUpperCase()}...`, 'success');
    // In real implementation, this would make an AJAX request
    setTimeout(() => {
        showNotification('Laporan berhasil diekspor!', 'success');
    }, 1500);
}