<?php
// Grafik sederhana yang digambar dengan HTML dan CSS dari data query.
// Dipilih tanpa library supaya jalan offline dan isinya mudah dibaca:
// tinggi atau lebar batang adalah persentase nilai terhadap nilai terbesar.

function grafik_kosong($teks)
{
    echo '<p class="kosong">' . e($teks) . '</p>';
}

// Batang tegak, satu batang per label. $judul_tip berisi teks tooltip per batang.
function grafik_kolom($label, $nilai, $format, $judul_tip, $deskripsi)
{
    $maks = max($nilai);
    if ($maks <= 0) {
        grafik_kosong('Belum ada data untuk periode ini.');
        return;
    }
    $tampil_nilai = count($label) <= 8;
    echo '<div class="grafik-kolom' . ($tampil_nilai ? '' : ' tanpa-nilai') . '" role="group" aria-label="' . e($deskripsi) . '">';
    foreach ($label as $i => $teks) {
        $persen = $nilai[$i] > 0 ? max(3, round($nilai[$i] / $maks * 100)) : 0;
        echo '<div class="kolom" title="' . e($judul_tip[$i]) . '">';
        echo '<span class="nilai">' . e($format($nilai[$i])) . '</span>';
        echo '<div class="area"><div class="batang" style="height:' . $persen . '%"></div></div>';
        echo '<span class="label">' . e($teks) . '</span>';
        echo '</div>';
    }
    echo '</div>';
}

// Dua batang berdampingan per label, misalnya stok masuk dan stok keluar.
function grafik_ganda($label, $nilai_a, $nilai_b, $nama_a, $nama_b, $judul_tip, $deskripsi)
{
    $maks = max(max($nilai_a), max($nilai_b));
    if ($maks <= 0) {
        grafik_kosong('Belum ada pergerakan stok pada periode ini.');
        return;
    }
    echo '<div class="grafik-kolom ganda tanpa-nilai" role="group" aria-label="' . e($deskripsi) . '">';
    foreach ($label as $i => $teks) {
        $pa = $nilai_a[$i] > 0 ? max(3, round($nilai_a[$i] / $maks * 100)) : 0;
        $pb = $nilai_b[$i] > 0 ? max(3, round($nilai_b[$i] / $maks * 100)) : 0;
        echo '<div class="kolom" title="' . e($judul_tip[$i]) . '">';
        echo '<div class="area"><div class="batang a" style="height:' . $pa . '%"></div><div class="batang b" style="height:' . $pb . '%"></div></div>';
        echo '<span class="label">' . e($teks) . '</span>';
        echo '</div>';
    }
    echo '</div>';
    echo '<p class="legenda"><span class="kotak a"></span> ' . e($nama_a) . ' (' . array_sum($nilai_a) . ')'
        . ' <span class="kotak b"></span> ' . e($nama_b) . ' (' . array_sum($nilai_b) . ')</p>';
}

// Batang mendatar untuk peringkat, misalnya produk terlaris.
function grafik_mendatar($label, $nilai, $satuan)
{
    if (!$label) {
        grafik_kosong('Belum ada penjualan.');
        return;
    }
    $maks = max($nilai);
    echo '<ol class="grafik-mendatar">';
    foreach ($label as $i => $teks) {
        $persen = $maks > 0 ? max(4, round($nilai[$i] / $maks * 100)) : 0;
        echo '<li><span class="nama">' . e($teks) . '</span>';
        echo '<span class="jalur"><span class="batang" style="width:' . $persen . '%"></span></span>';
        echo '<span class="angka">' . (int) $nilai[$i] . ' ' . e($satuan) . '</span></li>';
    }
    echo '</ol>';
}
