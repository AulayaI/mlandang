        </div>
    </main>
</div>

<?php /* Tombol WhatsApp melayang juga tampil di halaman login dan daftar. */ ?>
<a class="wa-melayang" href="<?= e(link_wa(NOMOR_WA_TOKO, 'Halo ' . NAMA_TOKO . ', saya mau tanya tentang produk.')) ?>"
   target="_blank" rel="noopener noreferrer" aria-label="Chat WhatsApp penjual, membuka tab baru">
    <?= ikon_chat() ?>
</a>
<script src="<?= BASE_URL ?>assets/lib/jquery-3.6.1.min.js"></script>
<script src="<?= BASE_URL ?>assets/lib/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>assets/js/main.js"></script>
</body>
</html>
