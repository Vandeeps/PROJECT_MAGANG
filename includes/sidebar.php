<aside class="sidebar" id="sidebarNav">
    <div>
        <div class="sidebar-header">
            <img src="img/Logo Telkom Dark.webp" alt="Logo Telkom Property" class="sidebar-logo">
        </div>
        <ul class="sidebar-menu">
            <li class="<?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">
                <a href="index.php"><span>Beranda</span></a>
            </li>
            <li class="<?= in_array(basename($_SERVER['PHP_SELF']), ['dokumen.php', 'update_dokumen.php']) ? 'active' : '' ?>">
                <a href="dokumen.php"><span>Dokumen</span></a>
            </li>
            <li class="<?= basename($_SERVER['PHP_SELF']) == 'unggah.php' ? 'active' : '' ?>">
                <a href="unggah.php"><span>Unggah Baru</span></a>
            </li>
            <li class="<?= basename($_SERVER['PHP_SELF']) == 'histori.php' ? 'active' : '' ?>">
                <a href="histori.php"><span>Histori</span></a>
            </li>
        </ul>
    </div>
    
    <div class="sidebar-footer">
        <div id="dropupMenu" class="dropup-menu">
            <button type="button" class="dropup-item" onclick="bukaModalPengaturan()">
                Pengaturan
            </button>
            <button type="button" class="dropup-item logout" onclick="bukaModalLogout()">
                Keluar
            </button>
        </div>
        <div class="user-profile" onclick="toggleDropup(event)">
            <div class="user-info">
                <span class="user-info-text"><?= htmlspecialchars($namaPengguna ?? 'User') ?></span>
            </div>
            <span class="user-arrow">▲</span>
        </div>
    </div>
</aside>

<!-- Modal Pengaturan (Dark Mode & Navigasi Ubah Password) -->
<div id="modalPengaturan" class="modal-overlay">
    <div class="modal-box" style="max-width: 380px; text-align: left;">
        <div class="modal-header" style="font-weight: bold; border-bottom: 1px solid #eee; padding-bottom: 8px; margin-bottom: 15px;">
            Pengaturan Sistem
        </div>
        
        <div class="modal-body" style="font-size: 14px;">
            <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 15px; margin-bottom: 15px; border-bottom: 1px solid #eee;">
                <div>
                    <strong>Mode Tampilan</strong>
                    <div style="font-size: 12px; color: #666;">Aktifkan tema gelap</div>
                </div>
                <label style="position: relative; display: inline-block; width: 44px; height: 24px;">
                    <input type="checkbox" id="toggleDarkMode" onchange="gantiTema(this.checked)" style="opacity: 0; width: 0; height: 0;">
                    <span id="sliderTema" style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .3s; border-radius: 24px;"></span>
                </label>
            </div>

            <div style="margin-bottom: 15px;">
                <strong>Keamanan Akun</strong>
                <div style="font-size: 12px; color: #666; margin-bottom: 8px;">Perbarui password akun Anda secara berkala</div>
                <a href="ubah_password.php" style="display: block; text-align: center; background: #333; color: #fff; padding: 8px; border-radius: 4px; text-decoration: none; font-size: 13px;">Buka Halaman Ubah Password</a>
            </div>

            <div class="modal-footer" style="display: flex; justify-content: flex-end; padding: 0; margin-top: 20px;">
                <button type="button" class="btn-modal-cancel" onclick="tutupModalPengaturan()">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Keluar (Logout) -->
<div id="modalLogout" class="modal-overlay">
    <div class="modal-box" style="max-width: 380px;">
        <div class="modal-header" style="color: #d32f2f; font-weight: bold;">Konfirmasi Keluar</div>
        <div class="modal-body">
            Apakah Anda yakin ingin keluar dari sesi ini?
        </div>
        <div class="modal-footer" style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 15px;">
            <button type="button" class="btn-modal-cancel" onclick="tutupModalLogout()">Batal</button>
            <a href="logout.php" class="btn-modal-confirm" style="background: #d32f2f; color: white; text-decoration: none; display: inline-block; padding: 8px 16px; border-radius: 4px;">Ya, Keluar</a>
        </div>
    </div>
</div>

<script>
    function bukaModalLogout() {
        let dropup = document.getElementById("dropupMenu");
        if (dropup) dropup.classList.remove("active");
        document.getElementById("modalLogout").classList.add("active");
    }

    function tutupModalLogout() {
        document.getElementById("modalLogout").classList.remove("active");
    }

    function bukaModalPengaturan() {
        let dropup = document.getElementById("dropupMenu");
        if (dropup) dropup.classList.remove("active");
        
        const isDark = localStorage.getItem("theme") === "dark";
        let toggle = document.getElementById("toggleDarkMode");
        if (toggle) toggle.checked = isDark;
        
        document.getElementById("modalPengaturan").classList.add("active");
    }

    function tutupModalPengaturan() {
        document.getElementById("modalPengaturan").classList.remove("active");
    }

    function gantiTema(isDark) {
        if (isDark) {
            document.body.classList.add("dark-mode");
            localStorage.setItem("theme", "dark");
        } else {
            document.body.classList.remove("dark-mode");
            localStorage.setItem("theme", "light");
        }
    }

    (function initTheme() {
        if (localStorage.getItem("theme") === "dark") {
            document.body.classList.add("dark-mode");
        }
    })();
</script>