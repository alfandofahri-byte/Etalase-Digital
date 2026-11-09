<?php
// footer.php
?>
    </div> <!-- /.container -->
</main> <!-- /.main-content -->

<footer class="footer">
    <div class="container">
        <div class="footer-content">
            <div>
                <div class="footer-signature">
                    <i class="fa-solid fa-graduation-cap text-primary me-1"></i>
                    Fahri Alfando &bull; Kelas XI Rekayasa Perangkat Lunak
                </div>
                <div style="font-size: 0.85rem; color: var(--color-text-muted); margin-top: 0.25rem;">
                    SMK Telkom Lampung (Stella) &bull; Tugas Pemrograman Web &amp; Hak Akses Multi-Role
                </div>
            </div>
            <div style="display: flex; gap: 0.75rem; align-items: center;">
                <span class="badge badge-demo">
                    <i class="fa-solid fa-check-circle me-1"></i> Role: Super Admin, Admin, Siswa
                </span>
                <a href="#top" class="btn btn-outline btn-sm" title="Kembali ke atas">
                    <i class="fa-solid fa-arrow-up"></i> Ke Atas
                </a>
            </div>
        </div>
        <div class="footer-bottom">
            &copy; <?= date('Y') ?> Etalase Digital Stella. Dibuat dengan PHP Native, PDO MySQL, &amp; Vanilla CSS Modern.
        </div>
    </div>
</footer>

<!-- Custom JavaScript -->
<script src="script.js"></script>
</body>
</html>
