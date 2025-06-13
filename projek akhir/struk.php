<?php
session_start();
require_once 'koneksi.php';

// --- BAGIAN KEAMANAN ---
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
if (!isset($_GET['booking'])) {
    header("Location: index.php");
    exit();
}

$kode_booking = $_GET['booking'];
$id_user = $_SESSION['user_id'];
$booking_details = null;

// --- BAGIAN PENGAMBILAN DATA ---
$sql = "SELECT 
            p.kode_booking, 
            p.total_harga, 
            p.tgl_pemesanan,
            f.judul, 
            f.poster, 
            f.genre, 
            f.durasi,
            j.tanggal, 
            j.jam,
            s.nama_studio,
            u.nama_lengkap,
            (SELECT GROUP_CONCAT(kt.nomor_kursi ORDER BY kt.nomor_kursi SEPARATOR ', ') 
             FROM kursi_terpesan kt 
             WHERE kt.id_pemesanan = p.id_pemesanan) AS kursi
        FROM pemesanan p
        JOIN jadwal j ON p.id_jadwal = j.id_jadwal
        JOIN film f ON j.id_film = f.id_film
        JOIN studio s ON j.id_studio = s.id_studio
        JOIN user u ON p.id_user = u.id_user
        WHERE p.kode_booking = ? AND u.id_user = ?";

$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param("si", $kode_booking, $id_user);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $booking_details = $result->fetch_assoc();
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
    <title>E-Tiket: <?php echo $booking_details ? htmlspecialchars($booking_details['judul']) : 'Tidak Ditemukan'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Inter', sans-serif; 
            background-color: #f1f5f9; 
            color: #1e293b; 
        }
        html, body {
            max-width: 100%;
            overflow-x: hidden;
        }
    </style>
</head>
<body class="w-full antialiased">
    <header class="bg-white w-full z-20 py-4 border-b border-gray-200">
        <nav class="container mx-auto px-6 flex justify-between items-center">
            <a href="index.php" class="text-2xl font-bold text-slate-900">BIOSKOP<span class="text-blue-500">ONLINE</span></a>
            <div class="flex items-center space-x-5">
                <a href="tiket_saya.php" class="text-gray-500 hover:text-slate-900 transition text-sm text-center">Kembali ke Tiket Saya</a>
            </div>
        </nav>
    </header>

    <main class="container mx-auto px-4 sm:px-6 py-8 sm:py-12 flex justify-center">
        <div class="bg-white shadow-2xl rounded-xl overflow-hidden max-w-4xl w-full">
            <?php if ($booking_details): ?>
                <div class="p-4 sm:p-6 md:p-8">
                    <div class="text-center mb-6 pb-6 border-b border-gray-200">
                        <h1 class="text-xl sm:text-2xl font-bold text-slate-900">E-Tiket / Struk Pembelian</h1>
                        <p class="text-blue-600 font-mono mt-1 text-base sm:text-lg break-all"><?php echo htmlspecialchars($booking_details['kode_booking']); ?></p>
                    </div>

                    <div class="flex flex-col md:flex-row md:items-start gap-6 md:gap-8">
                        <div class="w-full md:w-1/3 flex-shrink-0">
                            <img src="<?php echo htmlspecialchars($booking_details['poster'] ?: 'https://placehold.co/400x600/e2e8f0/475569?text=N/A'); ?>" alt="Poster Film" class="w-full max-w-xs mx-auto md:max-w-none h-auto object-cover rounded-lg shadow-lg">
                        </div>
                        <div class="w-full md:w-2/3 flex flex-col space-y-5">
                            <div class="space-y-2">
                                <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-slate-900"><?php echo htmlspecialchars($booking_details['judul']); ?></h2>
                                <p class="text-base text-gray-500"><?php echo htmlspecialchars($booking_details['genre']); ?> | <?php echo htmlspecialchars($booking_details['durasi']); ?> Menit</p>
                            </div>
                            
                            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                <div class="grid grid-cols-3 gap-x-4 gap-y-3 text-sm sm:text-base">
                                    <span class="col-span-1 text-gray-500">Jadwal</span>
                                    <span class="col-span-2 font-semibold"><?php echo date('d F Y', strtotime($booking_details['tanggal'])); ?>, <?php echo date('H:i', strtotime($booking_details['jam'])); ?></span>

                                    <span class="col-span-1 text-gray-500">Studio</span>
                                    <span class="col-span-2 font-semibold"><?php echo htmlspecialchars($booking_details['nama_studio']); ?></span>

                                    <span class="col-span-1 text-gray-500">Kursi</span>
                                    <span class="col-span-2 font-semibold text-blue-700 break-all"><?php echo htmlspecialchars($booking_details['kursi']); ?></span>
                                    
                                    <span class="col-span-1 text-gray-500">Pemesan</span>
                                    <span class="col-span-2 font-semibold break-all"><?php echo htmlspecialchars($booking_details['nama_lengkap']); ?></span>
                                    
                                    <span class="col-span-1 text-gray-500">Tgl. Pesan</span>
                                    <span class="col-span-2 font-semibold"><?php echo date('d M Y, H:i', strtotime($booking_details['tgl_pemesanan'])); ?></span>
                                </div>
                            </div>
                            
                            <div class="flex flex-col sm:flex-row justify-between sm:items-center bg-blue-50 p-4 rounded-lg mt-auto">
                                <span class="text-base sm:text-lg font-bold text-blue-900">TOTAL BAYAR</span>
                                <span class="text-xl sm:text-2xl font-bold text-blue-700 mt-1 sm:mt-0">Rp <?php echo number_format($booking_details['total_harga'], 0, ',', '.'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="p-8 sm:p-12 text-center">
                    <h1 class="text-xl sm:text-2xl font-bold text-red-500 mb-2">Tiket Tidak Ditemukan</h1>
                    <p class="text-gray-500">Kode booking yang Anda masukkan tidak valid atau tiket ini bukan milik Anda.</p>
                    <a href="index.php" class="mt-6 inline-block bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 font-semibold transition">Kembali ke Beranda</a>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>