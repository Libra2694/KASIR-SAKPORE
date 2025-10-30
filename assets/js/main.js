// Toggle Sidebar Mobile
document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.getElementById('sidebar');
    
    if (menuToggle && sidebar) {
        menuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
        });
    }

    // Close sidebar when clicking outside (mobile)
    document.addEventListener('click', function(event) {
        if (window.innerWidth <= 768) {
            if (!sidebar.contains(event.target) && !menuToggle.contains(event.target)) {
                sidebar.classList.remove('active');
            }
        }
    });

    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(function() {
                alert.remove();
            }, 500);
        }, 5000);
    });

    // Format currency inputs
    const currencyInputs = document.querySelectorAll('input[data-currency]');
    currencyInputs.forEach(function(input) {
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/[^\d]/g, '');
            if (value) {
                e.target.value = parseInt(value).toLocaleString('id-ID');
            }
        });
    });
});

// Confirm delete
function confirmDelete(message) {
    return confirm(message || 'Apakah Anda yakin ingin menghapus data ini?');
}

// Format number to currency
function formatCurrency(num) {
    return new Intl.NumberFormat('id-ID').format(num);
}

// Calculate total in transaction form
function calculateTotal() {
    const harga = parseFloat(document.getElementById('harga_jual')?.value.replace(/[^\d]/g, '') || 0);
    const jumlah = parseInt(document.getElementById('jumlah_beli')?.value || 0);
    const total = harga * jumlah;
    
    const totalInput = document.getElementById('total_harga');
    if (totalInput) {
        totalInput.value = formatCurrency(total);
    }
}

