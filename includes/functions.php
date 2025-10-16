<?php
// Fungsi untuk redirect
function redirect($url) {
    header("Location: $url");
    exit();
}

// Fungsi untuk menampilkan pesan
function flash($name = '', $message = '', $class = 'alert alert-success') {
    if(!empty($name)) {
        if(!empty($message) && empty($_SESSION[$name])) {
            $_SESSION[$name] = $message;
            $_SESSION[$name . '_class'] = $class;
        } else if(empty($message) && !empty($_SESSION[$name])) {
            $class = !empty($_SESSION[$name . '_class']) ? $_SESSION[$name . '_class'] : '';
            echo '<div class="'.$class.'" id="msg-flash">'.$_SESSION[$name].'</div>';
            unset($_SESSION[$name]);
            unset($_SESSION[$name . '_class']);
        }
    }
}

// Fungsi untuk menghitung denda
function hitungDenda($tanggal_jatuh_tempo) {
    $hari_terlambat = max(0, (strtotime(date('Y-m-d')) - strtotime($tanggal_jatuh_tempo)) / (60 * 60 * 24));
    return $hari_terlambat * 5000; // Denda Rp 5.000 per hari
}

// Fungsi untuk cek apakah mahasiswa terkena sanksi
function cekSanksi($mahasiswa_id) {
    global $pdo;
    $sql = "SELECT COUNT(*) FROM peminjaman WHERE mahasiswa_id = ? AND status = 'terlambat' 
            AND tanggal_kembali >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$mahasiswa_id]);
    return $stmt->fetchColumn() > 0;
}
?>