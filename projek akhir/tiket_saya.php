<?php
session_start();
require_once 'koneksi.php';

// Pastikan pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$id_user = $_SESSION['user_id'];
$bookings = [];

// Ambil semua data pemesanan milik pengguna yang sedang login
$sql = "SELECT 
            p.kode_booking, 
            p.tgl_pemesanan, 
            f.judul, 
            f.poster, 
            j.tanggal AS tanggal_nonton
        FROM pemesanan p
        JOIN jadwal j ON p.id_jadwal = j.id_jadwal
        JOIN film f ON j.id_film = f.id_film
        WHERE p.id_user = ?
        ORDER BY p.tgl_pemesanan DESC";

$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param("i", $id_user);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $bookings[] = $row;
    }
    $stmt->close();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tiket Saya - Bioskop Online</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Inter', sans-serif; 
            background-color: #f8fafc; 
            color: #1e293b; 
        }
        html, body {
            max-width: 100%;
            overflow-x: hidden;
        }
    </style>
</head>
<body class="w-full">
    <header class="bg-white w-full z-20 py-4 border-b border-gray-200 sticky top-0">
        <nav class="container mx-auto px-6 flex justify-between items-center">
            <a href="index.php" class="text-2xl font-bold text-slate-900">BIOSKOP<span class="text-blue-500">ONLINE</span></a>
            <div class="flex items-center space-x-8">
                <a href="index.php" class="text-gray-500 hover:text-blue-500 font-medium transition">Beranda</a>
                <a href="tiket_saya.php" class="text-slate-700 hover:text-blue-500 font-medium transition">Film Saya</a>
            </div>
        </nav>
    </header>

    <main class="container mx-auto px-4 sm:px-6 py-12">
        <h1 class="text-3xl md:text-4xl font-bold text-slate-900 mb-8 border-b border-gray-200 pb-4">Riwayat Pemesanan Tiket Anda</h1>

        <?php if (empty($bookings)): ?>
            <div class="text-center py-16 bg-white rounded-lg shadow-md">
                <p class="text-gray-500 text-lg">Anda belum pernah memesan tiket.</p>
                <a href="index.php" class="mt-4 inline-block bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 font-semibold transition">Cari Film Sekarang</a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8">
                <?php foreach ($bookings as $booking): ?>
                    <a href="struk.php?booking=<?php echo htmlspecialchars($booking['kode_booking']); ?>" class="block bg-white rounded-lg overflow-hidden shadow-lg hover:shadow-blue-500/10 hover:scale-105 transition-all duration-300 group">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <img src="<?php echo htmlspecialchars($booking['poster'] ?: 'https://placehold.co/128x192/e2e8f0/475569?text=N/A'); ?>" alt="Poster" class="w-24 sm:w-28 h-auto object-cover aspect-[2/3]">
                            </div>
                            <div class="p-4 flex flex-col justify-between flex-grow min-w-0">
                                <div>
                                    <h3 class="font-bold text-base sm:text-lg text-slate-800 group-hover:text-blue-500 transition-colors break-words"><?php echo htmlspecialchars($booking['judul']); ?></h3>
                                    <p class="text-sm text-gray-500 mt-1">Nonton: <?php echo date('d M Y', strtotime($booking['tanggal_nonton'])); ?></p>
                                </div>
                                <div class="mt-3 pt-3 border-t border-gray-200">
                                    <p class="text-xs text-gray-400">Kode Booking:</p>
                                    <p class="font-mono text-sm text-blue-600 break-all"><?php echo htmlspecialchars($booking['kode_booking']); ?></p>
                                </div>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>