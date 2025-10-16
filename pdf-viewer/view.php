<?php
include '../includes/config.php';
include '../includes/functions.php';

$id = $_GET['id'] ?? 0;

// Ambil data buku
$sql = "SELECT * FROM buku WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);
$buku = $stmt->fetch();

if (!$buku || !$buku['file_pdf']) {
    die('Buku tidak ditemukan!');
}

// Cek akses untuk mahasiswa
if (isset($_SESSION['mahasiswa_id'])) {
    // Cek apakah mahasiswa meminjam buku ini
    $sql = "SELECT COUNT(*) FROM peminjaman WHERE mahasiswa_id = ? AND buku_id = ? AND status IN ('dipinjam', 'terlambat')";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_SESSION['mahasiswa_id'], $id]);
    $is_borrowed = $stmt->fetchColumn() > 0;
    
    if (!$is_borrowed) {
        die('Anda harus meminjam buku ini terlebih dahulu!');
    }
} elseif (!isset($_SESSION['admin_id'])) {
    die('Akses ditolak!');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $buku['judul'] ?> - PDF Viewer</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f0f0;
        }
        
        .viewer-header {
            background: linear-gradient(135deg, #FF6B35, #FF4500);
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .viewer-header h1 {
            font-size: 1.2rem;
            margin: 0;
        }
        
        .close-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        
        .close-btn:hover {
            background: rgba(255,255,255,0.3);
        }
        
        .pdf-container {
            width: 100%;
            height: calc(100vh - 80px);
            background: white;
        }
        
        .pdf-container iframe {
            width: 100%;
            height: 100%;
            border: none;
        }
        
        .info-panel {
            background: white;
            padding: 1rem 2rem;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .book-info h2 {
            color: #333;
            margin-bottom: 0.5rem;
        }
        
        .book-info p {
            color: #666;
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="viewer-header">
        <h1>Perpustakaan Digital ITBI - PDF Viewer</h1>
        <a href="javascript:window.close()" class="close-btn">
            <i class="fas fa-times"></i> Tutup
        </a>
    </div>
    
    <div class="info-panel">
        <div class="book-info">
            <h2><?= $buku['judul'] ?></h2>
            <p>Oleh: <?= $buku['penulis'] ?> | Penerbit: <?= $buku['penerbit'] ?> (<?= $buku['tahun_terbit'] ?>)</p>
        </div>
        <div class="viewer-controls">
            <button onclick="zoomIn()" class="btn btn-sm"><i class="fas fa-search-plus"></i></button>
            <button onclick="zoomOut()" class="btn btn-sm"><i class="fas fa-search-minus"></i></button>
            <button onclick="fitWidth()" class="btn btn-sm">Fit Width</button>
        </div>
    </div>
    
    <div class="pdf-container">
        <iframe src="../uploads/buku/<?= $buku['file_pdf'] ?>#toolbar=0&navpanes=0" 
                id="pdfFrame" title="<?= $buku['judul'] ?>"></iframe>
    </div>

    <script>
        function zoomIn() {
            const iframe = document.getElementById('pdfFrame');
            const currentSrc = iframe.src;
            iframe.src = currentSrc.replace(/#.*$/, '') + '#zoom=150';
        }
        
        function zoomOut() {
            const iframe = document.getElementById('pdfFrame');
            const currentSrc = iframe.src;
            iframe.src = currentSrc.replace(/#.*$/, '') + '#zoom=50';
        }
        
        function fitWidth() {
            const iframe = document.getElementById('pdfFrame');
            const currentSrc = iframe.src;
            iframe.src = currentSrc.replace(/#.*$/, '') + '#zoom=page-width';
        }
    </script>
</body>
</html>