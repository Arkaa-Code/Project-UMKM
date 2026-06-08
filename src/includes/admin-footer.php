        </main>
    </div>
    
    <script>
    // Auto-hide alerts after 5 seconds
    document.addEventListener('DOMContentLoaded', function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.transition = 'opacity 0.3s ease';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            }, 5000);
        });
    });
    
    // Confirm delete actions
    document.querySelectorAll('.btn-danger').forEach(btn => {
        if (btn.textContent.includes('Hapus') || btn.querySelector('i.bi-trash')) {
            btn.addEventListener('click', function(e) {
                if (!confirm('Apakah Anda yakin ingin menghapus data ini?')) {
                    e.preventDefault();
                }
            });
        }
    });
    </script>
</body>
</html>
