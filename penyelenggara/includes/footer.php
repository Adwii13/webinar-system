<footer class="mt-auto py-6 px-8 border-t border-slate-200 bg-white shrink-0">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="text-slate-500 text-sm font-medium">
                    &copy; 2026 UNIBI - University Webinar System
                </div>
                </div>
        </footer>
    </main> </div> <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// 1. Fungsi Buka-Tutup Sidebar Mobile
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');
    
    sidebar.classList.toggle('-translate-x-full');
    overlay.classList.toggle('hidden');
}

// 2. Fungsi Dropdown Profil Keluar (Penyelenggara)
function toggleProfileDropdown(event) {
    // Mencegah event bubbling agar tidak langsung ditutup oleh event listener document di bawah
    event.stopPropagation(); 
    
    const dropdown = document.getElementById('profileDropdown');
    const arrow = document.getElementById('profileArrow');
    
    dropdown.classList.toggle('hidden');
    
    if (dropdown.classList.contains('hidden')) {
        arrow.style.transform = 'rotate(0deg)';
    } else {
        arrow.style.transform = 'rotate(180deg)';
    }
}

// 3. Otomatis Tutup Dropdown saat pengguna mengklik di area mana saja luar profil
document.addEventListener('click', function() {
    const dropdown = document.getElementById('profileDropdown');
    const arrow = document.getElementById('profileArrow');
    
    if (dropdown && !dropdown.classList.contains('hidden')) {
        dropdown.classList.add('hidden');
        arrow.style.transform = 'rotate(0deg)';
    }
});

// Fungsi Switch View (Opsional jika ingin diaktifkan nanti)
// function switchView() {
//     if (confirm('Switch ke Student View?')) {
//         window.location.href = '../student/index.php';
//     }
// }
</script>

<style>
/* Animasi transisi naik ke atas saat menu popover keluar muncul */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(5px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in { 
    animation: fadeIn 0.15s ease-out forwards; 
}
</style>

</body>
</html>