// Semua interaksi sisi browser ada di file ini, ditulis dengan jQuery.
// Bagian 1 slider, 2 validasi form, 3 tombol jumlah, 4 konfirmasi hapus,
// 5 baris pembelian stok, 7 toast, 8 tambah ke keranjang tanpa reload,
// 9 jumlah keranjang langsung, 10 hitung mundur bayar, 11 tab mengikuti scroll,
// 12 animasi muncul, 6 cegah kirim ganda (dipasang paling akhir).

$(function () {

    // 1. Slider halaman utama (tanpa tombol panah).
    // Pindah sendiri tiap 5 detik. Di layar sentuh bisa digeser kiri/kanan dengan jari.
    // Berhenti otomatis saat kursor atau fokus ada di slider, dan tidak berjalan
    // otomatis bagi yang mengaktifkan "kurangi gerak" di perangkatnya.
    var $slider = $('#slider');
    if ($slider.length) {
        var $slide = $slider.find('.slide');
        var $titik = $slider.find('.titik');
        var urutan = 0;
        var timer = null;
        var kurangiGerak = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        var tampilkan = function (i) {
            urutan = (i + $slide.length) % $slide.length;
            $slide.removeClass('aktif').attr('aria-hidden', 'true');
            $slide.eq(urutan).addClass('aktif').attr('aria-hidden', 'false');
            $titik.removeClass('aktif').removeAttr('aria-current');
            $titik.eq(urutan).addClass('aktif').attr('aria-current', 'true');
        };
        var mulai = function () {
            if (kurangiGerak || $slide.length < 2) { return; }
            clearInterval(timer);
            timer = setInterval(function () { tampilkan(urutan + 1); }, 5000);
        };
        var berhenti = function () { clearInterval(timer); };

        $slider.on('click', '.titik', function () {
            tampilkan(parseInt($(this).data('ke'), 10));
            mulai();
        });
        $slider.on('mouseenter focusin', berhenti).on('mouseleave focusout', mulai);

        // Geser dengan jari: selisih horizontal lebih dari 40px dihitung sebagai geseran.
        // Geseran vertikal dibiarkan, supaya halaman tetap bisa di-scroll.
        var awalX = 0, awalY = 0;
        $slider.on('touchstart', function (e) {
            var t = e.originalEvent.touches[0];
            awalX = t.clientX; awalY = t.clientY;
            berhenti();
        });
        $slider.on('touchend', function (e) {
            var t = e.originalEvent.changedTouches[0];
            var dx = t.clientX - awalX, dy = t.clientY - awalY;
            if (Math.abs(dx) > 40 && Math.abs(dx) > Math.abs(dy)) {
                tampilkan(urutan + (dx < 0 ? 1 : -1));
            }
            mulai();
        });
        tampilkan(0);
        mulai();
    }

    // 2. Validasi form di browser sebelum dikirim ke server.
    // Aturan dibaca dari atribut input: data-wajib, data-tipe (email atau telepon),
    // data-min (panjang minimal), data-sama (harus sama dengan input lain).
    // Server tetap memeriksa ulang, karena pengecekan di browser bisa dilewati.
    var cekKolom = function ($f) {
        var nilai = $.trim($f.val());
        var label = $f.data('label') || 'Kolom ini';
        var pesan = '';

        if ($f.is('[data-wajib]') && nilai === '') {
            pesan = label + ' wajib diisi.';
        } else if (nilai !== '') {
            var tipe = $f.data('tipe');
            if (tipe === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(nilai)) {
                pesan = 'Format email belum benar, contoh: nama@email.com.';
            } else if (tipe === 'telepon' && !/^(62|0)[0-9]{8,13}$/.test(nilai.replace(/[\s\-+]/g, ''))) {
                pesan = 'Nomor WhatsApp berupa angka, contoh: 081234567890.';
            } else if ($f.data('min') && nilai.length < parseInt($f.data('min'), 10)) {
                pesan = label + ' minimal ' + $f.data('min') + ' karakter.';
            } else if ($f.data('sama') && nilai !== $($f.data('sama')).val()) {
                pesan = 'Konfirmasi password belum sama.';
            } else if ($f.is('[data-angka-min]') && (isNaN(nilai) || parseFloat(nilai) < parseFloat($f.data('angka-min')))) {
                pesan = label + ' harus berupa angka minimal ' + $f.data('angka-min') + '.';
            }
        }

        var $pesan = $('#' + $f.attr('id') + '-pesan');
        if (pesan !== '') {
            $f.addClass('is-invalid').attr('aria-invalid', 'true');
            $pesan.text(pesan);
            return false;
        }
        $f.removeClass('is-invalid').removeAttr('aria-invalid');
        $pesan.text('');
        return true;
    };

    $('form[data-validasi]').attr('novalidate', 'novalidate').on('submit', function (e) {
        var $form = $(this);
        var semuaBenar = true;
        var $pertama = null;

        $form.find('input, textarea, select').not('[type=hidden]').each(function () {
            if (!cekKolom($(this))) {
                semuaBenar = false;
                if (!$pertama) { $pertama = $(this); }
            }
        });

        // Pilihan metode bayar berupa radio, dicek terpisah.
        var $grup = $form.find('[data-radio-wajib]');
        if ($grup.length && !$grup.find('input:checked').length) {
            $grup.find('.invalid-feedback').text('Pilih salah satu metode pembayaran.').show();
            semuaBenar = false;
            if (!$pertama) { $pertama = $grup.find('input').first(); }
        }

        if (!semuaBenar) {
            e.preventDefault();
            e.stopImmediatePropagation();
            $pertama.trigger('focus');
        }
    });

    // Pesan error hilang begitu pengguna memperbaiki isiannya.
    // Saat mengetik, kolom yang sedang bertanda salah dicek ulang. Dengan begitu pesan salah
    // hilang sebelum pengguna menekan tombol kirim. Kalau baru hilang saat kolom kehilangan fokus,
    // tata letak bergeser tepat ketika tombol ditekan dan klik bisa meleset.
    $('form[data-validasi]').on('input', 'input, textarea', function () {
        if ($(this).hasClass('is-invalid')) { cekKolom($(this)); }
    });
    $('form[data-validasi]').on('blur change', 'input, textarea, select', function () {
        if ($(this).attr('type') !== 'hidden' && $(this).attr('type') !== 'radio') { cekKolom($(this)); }
    });
    $('form[data-validasi]').on('change', '[data-radio-wajib] input', function () {
        $(this).closest('[data-radio-wajib]').find('.invalid-feedback').hide();
    });

    // 3. Tombol jumlah (+ dan -). Batas diambil dari atribut min dan max input.
    $(document).on('click', '.stepper button', function () {
        var $input = $(this).siblings('input');
        var min = parseInt($input.attr('min'), 10) || 1;
        var max = parseInt($input.attr('max'), 10) || 9999;
        var nilai = parseInt($input.val(), 10) || min;
        nilai += parseInt($(this).data('langkah'), 10);
        $input.val(Math.min(max, Math.max(min, nilai))).trigger('change');
    });

    // 4. Konfirmasi sebelum menghapus atau membatalkan.
    // Dipasang langsung pada form (bukan lewat document) supaya berjalan sebelum
    // bagian 6 mengunci tombol. Kalau pengguna menekan Batal, tombol tetap aktif.
    $('form[data-konfirmasi]').on('submit', function (e) {
        if (!window.confirm($(this).data('konfirmasi'))) {
            e.preventDefault();
            e.stopImmediatePropagation();
        }
    });

    // 5. Form pembelian stok: tambah dan hapus baris produk, hitung perkiraan total.
    var $daftarBaris = $('#daftar-baris');
    if ($daftarBaris.length) {
        var hitungTotal = function () {
            var total = 0;
            $daftarBaris.find('.baris-beli').each(function () {
                var jumlah = parseInt($(this).find('.isi-jumlah').val(), 10) || 0;
                var harga = parseFloat($(this).find('.isi-harga').val()) || 0;
                total += jumlah * harga;
            });
            $('#perkiraan-total').text('Rp ' + total.toLocaleString('id-ID'));
        };
        $('#tambah-baris').on('click', function () {
            var $baru = $daftarBaris.find('.baris-beli').first().clone();
            $baru.find('input').val('');
            $baru.find('select').prop('selectedIndex', 0);
            $daftarBaris.append($baru);
            $baru.find('select').trigger('focus');
        });
        $daftarBaris.on('click', '.hapus-baris', function () {
            if ($daftarBaris.find('.baris-beli').length > 1) {
                $(this).closest('.baris-beli').remove();
                hitungTotal();
            }
        });
        $daftarBaris.on('input', 'input', hitungTotal);
        hitungTotal();
    }

    // 7. Pop up kecil (toast) untuk kabar singkat, tanpa memuat ulang halaman.
    var tampilToast = function (pesan, gagal, tautan) {
        var $t = $('<div class="toast-item"></div>').toggleClass('toast-gagal', !!gagal);
        $('<span></span>').text(pesan).appendTo($t);
        if (tautan) {
            $('<a></a>').attr('href', tautan.url).text(tautan.teks).appendTo($t);
        }
        $('#area-toast').append($t);
        setTimeout(function () { $t.addClass('tampil'); }, 20);
        setTimeout(function () {
            $t.removeClass('tampil');
            setTimeout(function () { $t.remove(); }, 300);
        }, 3200);
    };

    // Angka merah di ikon keranjang ikut berubah.
    var setLencana = function (jumlah) {
        var $l = $('#lencana-keranjang');
        $l.text(jumlah).prop('hidden', jumlah < 1);
        $l.parent().attr('aria-label', 'Keranjang belanja, ' + jumlah + ' produk');
    };

    // Jawaban server yang gagal (belum login, atau galat lain).
    var galatKeranjang = function (xhr) {
        var data = xhr.responseJSON || {};
        if (data.login) {
            window.location.href = data.login + '?lanjut=' + encodeURIComponent(window.location.pathname + window.location.search);
            return;
        }
        tampilToast(data.pesan || 'Gagal menyambung ke server. Coba lagi.', true);
    };

    // 8. Tambah ke keranjang tanpa memuat ulang halaman.
    // Form tetap berfungsi biasa kalau JavaScript mati (dikirim ke keranjang_aksi.php).
    $(document).on('submit', 'form[action$="keranjang_aksi.php"]', function (e) {
        var $form = $(this);
        if ($form.find('[name=aksi]').val() !== 'tambah') { return; }
        e.preventDefault();
        var $tombol = $form.find('button[type=submit]').prop('disabled', true);
        $.post($form.attr('action'), $form.serialize(), null, 'json')
            .done(function (d) {
                setLencana(d.jumlah_keranjang);
                tampilToast(d.pesan, !d.ok, d.ok ? { url: $('#lencana-keranjang').parent().attr('href'), teks: 'Lihat keranjang' } : null);
            })
            .fail(galatKeranjang)
            .always(function () { $tombol.prop('disabled', false); });
    });

    // 9. Halaman keranjang: jumlah berubah langsung saat + atau - ditekan, harga ikut berubah.
    // Jumlah minimal 1. Untuk menghapus produk, pakai tombol Hapus.
    var antrianUbah = {};
    $(document).on('change', '.form-jumlah-live input[name=jumlah]', function () {
        var $input = $(this);
        var $form = $input.closest('form');
        var $baris = $input.closest('[data-baris-keranjang]');
        var min = parseInt($input.attr('min'), 10) || 1;
        var max = parseInt($input.attr('max'), 10) || 999;
        var nilai = parseInt($input.val(), 10);
        if (isNaN(nilai) || nilai < min) { nilai = min; }
        if (nilai > max) { nilai = max; }
        $input.val(nilai);

        var kunci = $form.find('[name=id_produk]').val();
        clearTimeout(antrianUbah[kunci]);
        antrianUbah[kunci] = setTimeout(function () {
            $.post($form.attr('action'), $form.serialize(), null, 'json')
                .done(function (d) {
                    if (d.jumlah !== undefined) { $input.val(d.jumlah); }
                    if (d.subtotal) { $baris.find('[data-subtotal]').text(d.subtotal); }
                    if (d.total) { $('[data-total]').text(d.total); }
                    if (d.jenis !== undefined) {
                        $('[data-jenis]').text(d.jenis + ' jenis produk');
                        $('[data-barang]').text(d.barang + ' barang');
                    }
                    setLencana(d.jumlah_keranjang);
                    if (!d.ok) { tampilToast(d.pesan, true); }
                })
                .fail(galatKeranjang);
        }, 250);
    });
    // Enter di kolom jumlah tidak boleh memuat ulang halaman.
    $(document).on('submit', '.form-jumlah-live', function (e) { e.preventDefault(); });

    // 10. Hitung mundur batas bayar di halaman pembayaran.
    // Server yang membatalkan pesanan (lihat includes/pesanan.php). Di sini hanya tampilan
    // waktu sisa. Saat habis, halaman dimuat ulang untuk menampilkan status terbaru.
    var $mundur = $('.hitung-mundur');
    if ($mundur.length) {
        var sisa = parseInt($mundur.data('sisa'), 10) || 0;
        var tulis = function () {
            var m = Math.floor(sisa / 60), d = sisa % 60;
            $mundur.find('.waktu-sisa').text((m < 10 ? '0' : '') + m + ':' + (d < 10 ? '0' : '') + d);
            $mundur.toggleClass('mendesak', sisa <= 60);
        };
        tulis();
        var jam = setInterval(function () {
            sisa--;
            if (sisa <= 0) {
                clearInterval(jam);
                sisa = 0;
                tulis();
                setTimeout(function () { window.location.href = $mundur.data('selesai'); }, 2000);
                return;
            }
            tulis();
        }, 1000);
    }

    // 11. Garis biru di tab menu mengikuti posisi scroll (Beranda, Tentang Kami, Lokasi).
    // Hanya jalan di halaman beranda, yaitu halaman yang punya bagian #tentang dan #lokasi.
    var $tentang = $('#tentang'), $lokasi = $('#lokasi');
    if ($tentang.length && $lokasi.length) {
        var tandaiTab = function () {
            var batas = $(window).scrollTop() + $('.bar-atas').outerHeight() + 120;
            var bawahHalaman = $(window).scrollTop() + $(window).height() >= $(document).height() - 4;
            var aktif = 'beranda';
            if (bawahHalaman || $lokasi.offset().top <= batas) { aktif = 'lokasi'; }
            else if ($tentang.offset().top <= batas) { aktif = 'tentang'; }
            $('.tab-menu .tab').each(function () {
                var cocok = $(this).data('tab') === aktif;
                $(this).toggleClass('aktif', cocok);
                if (cocok) { $(this).attr('aria-current', 'location'); } else { $(this).removeAttr('aria-current'); }
            });
        };
        var menunggu = false;
        $(window).on('scroll resize', function () {
            if (menunggu) { return; }
            menunggu = true;
            requestAnimationFrame(function () { menunggu = false; tandaiTab(); });
        });
        tandaiTab();
    }

    // 12. Animasi muncul tipis saat elemen masuk layar.
    // Hanya elemen yang masih di bawah layar yang diberi animasi, jadi isi paling atas
    // tidak berkedip. Pengguna "kurangi gerak" dan browser lama langsung melihat isinya.
    if ('IntersectionObserver' in window && !(window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches)) {
        var pengamat = new IntersectionObserver(function (daftar) {
            daftar.forEach(function (x) {
                if (x.isIntersecting) {
                    var $el = $(x.target).addClass('tampil');
                    pengamat.unobserve(x.target);
                    // Setelah selesai, kembalikan ke keadaan biasa supaya efek hover tidak ikut tertunda.
                    setTimeout(function () { $el.removeClass('siap tampil').css('transition-delay', ''); }, 1000);
                }
            });
        }, { threshold: 0.12 });
        $('.muncul').each(function (i) {
            if (this.getBoundingClientRect().top < window.innerHeight) { return; }
            $(this).addClass('siap').css('transition-delay', (i % 4) * 60 + 'ms');
            pengamat.observe(this);
        });
    }

    // 6. Cegah kirim ganda: tombol kirim dikunci setelah form lolos validasi.
    // Dipasang paling akhir supaya form yang gagal validasi tidak ikut terkunci.
    // Tombol tidak boleh punya name dan value, karena tombol yang dikunci tidak ikut terkirim.
    $('form').on('submit', function (e) {
        if (e.isDefaultPrevented()) { return; }
        $(this).find('button[type=submit]').prop('disabled', true);
    });
    // Saat pengguna kembali dengan tombol Back, tombol dibuka lagi.
    $(window).on('pageshow', function () {
        $('form button[type=submit]').prop('disabled', false);
    });
});
