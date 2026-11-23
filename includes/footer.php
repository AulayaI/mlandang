</main>

<?php
// Footer penuh (nama toko, deskripsi, ikon sosial) hanya untuk halaman yang mengisi $footer_penuh = true,
// yaitu beranda. Halaman lain hanya menampilkan satu baris hak cipta.
if (!empty($footer_penuh)):
?>
<footer class="kaki">
    <div class="container">
        <div class="kaki-atas">
            <div>
                <p class="merek-kaki"><?= ikon_kristal() ?> <?= e(NAMA_TOKO) ?></p>
                <p class="deskripsi-kaki"><?= e(TAGLINE) ?></p>
            </div>
            <?php if (LINK_INSTAGRAM !== '' || LINK_TIKTOK !== ''): ?>
                <ul class="sosial-kaki">
                    <?php if (LINK_TIKTOK !== ''): ?>
                        <li><a href="<?= e(LINK_TIKTOK) ?>" target="_blank" rel="noopener noreferrer" aria-label="TikTok <?= e(NAMA_TOKO) ?>, membuka tab baru"><?= ikon('tiktok') ?></a></li>
                    <?php endif; ?>
                    <?php if (LINK_INSTAGRAM !== ''): ?>
                        <li><a href="<?= e(LINK_INSTAGRAM) ?>" target="_blank" rel="noopener noreferrer" aria-label="Instagram <?= e(NAMA_TOKO) ?>, membuka tab baru"><?= ikon('instagram') ?></a></li>
                    <?php endif; ?>
                </ul>
            <?php endif; ?>
        </div>
        <div class="kaki-bawah">
            &copy; <?= date('Y') ?> <?= e(NAMA_TOKO) ?>
            <?php if (MODE_DEMO): ?>
                <span class="catatan-contoh">Data produk, foto, alamat, dan nomor masih contoh untuk tugas kuliah.</span>
            <?php endif; ?>
        </div>
    </div>
</footer>
<?php else: ?>
<footer class="kaki-mini">
    <div class="container">&copy; <?= date('Y') ?> <?= e(NAMA_TOKO) ?></div>
</footer>
<?php endif; ?>

<?php /* Tombol WhatsApp melayang: tampil di semua halaman pembeli, langsung membuka chat ke penjual. */ ?>
<a class="wa-melayang" href="<?= e(link_wa(NOMOR_WA_TOKO, 'Halo ' . NAMA_TOKO . ', saya mau tanya tentang produk.')) ?>"
   target="_blank" rel="noopener noreferrer" aria-label="Chat WhatsApp penjual, membuka tab baru">
    <?= ikon_chat() ?>
</a>

<?php /* Pop up kecil (toast) untuk kabar singkat, misalnya "berhasil masuk keranjang". Diisi oleh main.js. */ ?>
<div class="area-toast" id="area-toast" role="status" aria-live="polite"></div>

<script src="<?= BASE_URL ?>assets/lib/jquery-3.6.1.min.js"></script>
<script src="<?= BASE_URL ?>assets/lib/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>assets/js/main.js"></script>
</body>
</html>
