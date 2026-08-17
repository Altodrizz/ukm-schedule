<?php
/**
 * Helper validasi jadwal: mencegah 1 anggota punya 2 kegiatan
 * pada tanggal & rentang jam yang saling tabrakan (overlap).
 */

/**
 * Cari jadwal milik anggota yang bentrok dengan rentang waktu yang diajukan.
 *
 * Dua rentang dianggap bentrok bila: mulai_baru < selesai_lama AND selesai_baru > mulai_lama.
 * Jadwal yang bersambung (mis. 08:00-10:00 lalu 10:00-12:00) TIDAK dianggap bentrok.
 *
 * @param PDO      $pdo
 * @param int      $id_pengguna
 * @param string   $tanggal      format Y-m-d
 * @param string   $mulai        format H:i
 * @param string   $selesai      format H:i
 * @param int|null $abaikan_id   id_detail_jadwal yang dikecualikan (dipakai saat edit)
 * @return array|false baris jadwal bentrok pertama, atau false bila aman
 */
function cariJadwalBentrok(PDO $pdo, $id_pengguna, $tanggal, $mulai, $selesai, $abaikan_id = null) {
    $sql = "SELECT d.id_detail_jadwal, d.tanggal_tugas, d.waktu_mulai, d.waktu_selesai,
                   j.nama_jenis
            FROM detail_jadwal d
            JOIN jenis_jadwal j ON j.id_jenis_jadwal = d.id_jenis_jadwal
            WHERE d.id_pengguna = ?
              AND d.tanggal_tugas = ?
              AND ? < d.waktu_selesai
              AND ? > d.waktu_mulai";
    $params = [$id_pengguna, $tanggal, $mulai, $selesai];

    if ($abaikan_id !== null) {
        $sql .= " AND d.id_detail_jadwal <> ?";
        $params[] = $abaikan_id;
    }

    $sql .= " ORDER BY d.waktu_mulai LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $row = $stmt->fetch();

    return $row ?: false;
}

/**
 * Validasi lengkap sebuah input jadwal.
 *
 * @return string pesan error, atau string kosong bila valid
 */
function validasiJadwal(PDO $pdo, $id_pengguna, $tanggal, $mulai, $selesai, $abaikan_id = null) {
    if ($mulai >= $selesai) {
        return "Waktu selesai harus lebih besar dari waktu mulai.";
    }

    $bentrok = cariJadwalBentrok($pdo, $id_pengguna, $tanggal, $mulai, $selesai, $abaikan_id);
    if ($bentrok) {
        $nama = $pdo->prepare("SELECT nama_lengkap FROM pengguna WHERE id_pengguna = ?");
        $nama->execute([$id_pengguna]);
        $nama_anggota = $nama->fetchColumn() ?: 'Anggota';

        return sprintf(
            "Jadwal bentrok! %s sudah punya kegiatan %s pada %s pukul %s–%s. Satu anggota tidak boleh punya dua kegiatan di jam yang sama.",
            $nama_anggota,
            $bentrok['nama_jenis'],
            date('d/m/Y', strtotime($bentrok['tanggal_tugas'])),
            substr($bentrok['waktu_mulai'], 0, 5),
            substr($bentrok['waktu_selesai'], 0, 5)
        );
    }

    return '';
}
