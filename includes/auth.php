<?php
// Cek login admin
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
}

// Cek login mahasiswa
function isMahasiswaLoggedIn() {
    return isset($_SESSION['mahasiswa_id']);
}

// Require admin login
function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        redirect('../login.php');
    }
}

// Require mahasiswa login
function requireMahasiswaLogin() {
    if (!isMahasiswaLoggedIn()) {
        redirect('../login.php');
    }
}
?>