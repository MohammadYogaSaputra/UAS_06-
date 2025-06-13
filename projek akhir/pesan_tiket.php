<?php
session_start();
require_once 'koneksi.php';

// ==================================================================
// BAGIAN 1: API UNTUK JAVASCRIPT (TIDAK ADA PERUBAHAN)
// ==================================================================
if (isset($_GET['action']) && $_GET['action'] == 'get_schedule_details') {
    header('Content-Type: application/json');
    error_reporting(0);
    ini_set('display_errors', 0);
    try {
        if (!$conn) throw new Exception("Koneksi ke database gagal.");
        $id_jadwal_ajax = isset($_GET['id_jadwal']) ? intval($_GET['id_jadwal']) : 0;
        if ($id_jadwal_ajax <= 0) throw new Exception("ID Jadwal tidak valid.");
        $stmt_info = $conn->prepare("SELECT s.kapasitas, j.harga FROM jadwal j JOIN studio s ON j.id_studio = s.id_studio WHERE j.id_jadwal = ?");
        $stmt_info->bind_param("i", $id_jadwal_ajax);
        $stmt_info->execute();
        $schedule_info = $stmt_info->get_result()->fetch_assoc();
        $stmt_info->close();
        if (!$schedule_info) throw new Exception("Data jadwal tidak ditemukan.");
        $stmt_kursi = $conn->prepare("SELECT nomor_kursi FROM kursi_terpesan WHERE id_jadwal = ?");
        $stmt_kursi->bind_param("i", $id_jadwal_ajax);
        $stmt_kursi->execute();
        $result_kursi = $stmt_kursi->get_result();
        $booked_seats = [];
        while ($row = $result_kursi->fetch_assoc()) $booked_seats[] = $row['nomor_kursi'];
        $stmt_kursi->close();
        echo json_encode(['success' => true, 'kapasitas' => $schedule_info['kapasitas'] ?? 60, 'harga' => $schedule_info['harga'] ?? 0, 'kursi_terpesan' => $booked_seats]);
    } catch (Exception $e) {
        http_response_code(400); 
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    if ($conn) $conn->close();
    exit();
}

// ==================================================================
// BAGIAN 2: LOGIKA HALAMAN & FORM (TIDAK ADA PERUBAHAN LOGIKA POST)
// ==================================================================
if (!isset($_SESSION['user_id'])) {
    $_SESSION['login_error'] = "Anda harus login terlebih dahulu untuk memesan tiket.";
    header("Location: login.php");
    exit();
}
if (!isset($_GET['id_film']) || !filter_var($_GET['id_film'], FILTER_VALIDATE_INT)) {
    header("Location: index.php");
    exit();
}
$id_film = intval($_GET['id_film']);
$id_user = $_SESSION['user_id'];
$error_message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_jadwal = filter_input(INPUT_POST, 'id_jadwal', FILTER_VALIDATE_INT);
    $selected_seats_str = filter_input(INPUT_POST, 'selected_seats', FILTER_SANITIZE_STRING);
    $total_harga = filter_input(INPUT_POST, 'total_harga', FILTER_VALIDATE_INT);
    if (!$id_jadwal || empty($selected_seats_str) || $total_harga === false) {
        $error_message = "Data pesanan tidak lengkap atau tidak valid. Silakan coba lagi.";
    } else {
        $selected_seats = explode(',', $selected_seats_str);
        $jumlah_tiket = count($selected_seats);
        $kode_booking = 'INV-' . time() . rand(100, 999);
        $conn->begin_transaction();
        try {
            $stmt_pemesanan = $conn->prepare("INSERT INTO pemesanan (id_user, id_jadwal, jumlah_tiket, total_harga, kode_booking, tgl_pemesanan) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt_pemesanan->bind_param("iiiss", $id_user, $id_jadwal, $jumlah_tiket, $total_harga, $kode_booking);
            $stmt_pemesanan->execute();
            $id_pemesanan_baru = $stmt_pemesanan->insert_id;
            $stmt_pemesanan->close();
            $stmt_kursi = $conn->prepare("INSERT INTO kursi_terpesan (id_jadwal, id_pemesanan, nomor_kursi) VALUES (?, ?, ?)");
            foreach ($selected_seats as $kursi) {
                $kursi = trim($kursi);
                $stmt_kursi->bind_param("iis", $id_jadwal, $id_pemesanan_baru, $kursi);
                $stmt_kursi->execute();
            }
            $stmt_kursi->close();
            $conn->commit();
            $_SESSION['pesanan_sukses'] = "Pemesanan tiket berhasil! Kode booking Anda adalah: <strong>$kode_booking</strong>. Cek tiket Anda di halaman 'Film Saya'.";
            header("Location: index.php");
            exit();
        } catch (mysqli_sql_exception $exception) {
            $conn->rollback();
            $error_message = "Gagal memproses pesanan. Mungkin kursi yang Anda pilih sudah dipesan orang lain. Silakan coba lagi.";
        }
    }
}
$stmt_film = $conn->prepare("SELECT judul, poster, durasi, genre FROM film WHERE id_film = ?");
$stmt_film->bind_param("i", $id_film);
$stmt_film->execute();
$film = $stmt_film->get_result()->fetch_assoc();
if (!$film) { header("Location: index.php"); exit(); }
$stmt_film->close();

// ==================================================================
// PERUBAHAN UTAMA: MENGAMBIL DAN MENYUSUN DATA JADWAL BERDASARKAN STUDIO
// ==================================================================
$sql_jadwal = "SELECT j.id_jadwal, j.tanggal, j.jam, j.harga, s.nama_studio 
               FROM jadwal j 
               JOIN studio s ON j.id_studio = s.id_studio 
               WHERE j.id_film = ? AND j.tanggal >= CURDATE() 
               ORDER BY s.nama_studio, j.tanggal, j.jam"; // DIUBAH ORDER BY
$stmt_jadwal = $conn->prepare($sql_jadwal);
$stmt_jadwal->bind_param("i", $id_film);
$stmt_jadwal->execute();
$result_jadwal = $stmt_jadwal->get_result();

// LOGIKA PENGELOMPOKAN DATA BERDASARKAN STUDIO
$schedules_by_studio = [];
while ($row = $result_jadwal->fetch_assoc()) {
    $studio_name = htmlspecialchars($row['nama_studio']);
    $formatted_date = date('D, d M Y', strtotime($row['tanggal']));
    
    if (!isset($schedules_by_studio[$studio_name])) {
        $schedules_by_studio[$studio_name] = [];
    }
    if (!isset($schedules_by_studio[$studio_name][$formatted_date])) {
        $schedules_by_studio[$studio_name][$formatted_date] = [];
    }
    
    $schedules_by_studio[$studio_name][$formatted_date][] = [
        'id' => $row['id_jadwal'],
        'time' => date('H:i', strtotime($row['jam'])),
        'price' => $row['harga']
    ];
}
$jadwal_kosong = empty($schedules_by_studio);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesan Tiket - <?php echo htmlspecialchars($film['judul']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        html, body { max-width: 100%; overflow-x: hidden; }
        body { font-family: 'Inter', sans-serif; background-color: #f1f5f9; color: #1e293b; }
        .schedule-time-btn { padding: 0.5rem 1rem; border: 1px solid #d1d5db; border-radius: 0.375rem; background-color: white; transition: all 0.2s; cursor: pointer; font-weight: 500; }
        .schedule-time-btn:hover { border-color: #3b82f6; color: #3b82f6; }
        .schedule-time-btn.selected { background-color: #3b82f6; color: white; border-color: #3b82f6; font-weight: 700; transform: scale(1.05); }
        .schedule-time-btn:disabled { opacity: 0.5; cursor: not-allowed; }
        .seat { transition: background-color 0.2s, transform 0.1s; aspect-ratio: 1 / 1; display: flex; align-items: center; justify-content: center; border-radius: 0.25rem; font-weight: 600; font-size: clamp(0.5rem, 2.5vw, 0.75rem); }
        .seat.available { background-color: #d1d5db; color: #4b5563; cursor: pointer; }
        .seat.available:hover { background-color: #9ca3af; transform: scale(1.1); }
        .seat.selected { background-color: #3b82f6; border: 2px solid #60a5fa; transform: scale(1.05); color: white; }
        .seat.booked { background-color: #f3f4f6; color: #9ca3af; cursor: not-allowed; border: 1px dashed #d1d5db; }
        .screen { background-color: #374151; height: 5px; width: 80%; margin: 0 auto 2rem auto; box-shadow: 0 0 20px 5px rgba(55, 65, 81, 0.3); border-radius: 50%; }
        input{
    color: transparent;
    text-shadow: 0 0 0 red;
}

input:focus{
    outline: none;
}
    </style>
</head>
<body class="w-full">
    <header class="bg-white w-full z-20 py-4 border-b border-gray-200">
         <nav class="container mx-auto px-4 sm:px-6 flex justify-between items-center">
            <a href="index.php" class="text-2xl font-bold text-slate-900">BIOSKOP<span class="text-blue-500">ONLINE</span></a>
             <div class="flex items-center space-x-5">
                <a href="index.php" class="text-gray-600 hover:text-slate-900 transition">Kembali</a>
                <form action="logout.php" method="POST">
                    <button type="submit" class="bg-red-600 text-white px-5 py-2 rounded-lg hover:bg-red-700 text-sm font-semibold transition">Logout</button>
                </form>
            </div>
        </nav>
    </header>

    <main class="container mx-auto px-4 sm:px-6 py-8 sm:py-12">
        <div class="mb-8">
            <h1 class="text-3xl md:text-4xl font-extrabold mb-2"><?php echo htmlspecialchars($film['judul']); ?></h1>
            <p class="text-gray-500"><?php echo htmlspecialchars($film['genre']); ?> | <?php echo htmlspecialchars($film['durasi']); ?> Menit</p>
        </div>
        <div class="flex flex-col lg:flex-row gap-8 lg:gap-12">
            <div class="lg:w-1/3">
                <div class="sticky top-28"><img src="<?php echo htmlspecialchars($film['poster']); ?>" alt="Poster Film" class="w-full h-auto object-cover rounded-lg shadow-2xl"></div>
            </div>
            <div class="lg:w-2/3">
                <div class="bg-white p-4 sm:p-8 rounded-lg shadow-xl">
                    <form id="booking-form" action="pesan_tiket.php?id_film=<?php echo $id_film; ?>" method="POST">
                        <input type="hidden" name="id_jadwal" id="id_jadwal_input">
                        <input type="hidden" name="selected_seats" id="selected_seats_input">
                        <input type="hidden" name="total_harga" id="total_harga_input">
                        <?php if($error_message): ?><div class="p-4 mb-6 rounded-lg bg-red-100 text-red-700 border border-red-300"><?php echo $error_message; ?></div><?php endif; ?>
                        
                        <div class="mb-8">
                            <h2 class="flex items-center text-xl font-bold mb-4"><span class="bg-blue-600 text-white rounded-full w-8 h-8 flex items-center justify-center font-bold mr-3 flex-shrink-0 text-base">1</span>Pilih Jadwal Tayang</h2>
                            <div id="schedule-container" class="space-y-5 p-1">
                                <?php if($jadwal_kosong): ?>
                                    <div class="p-4 bg-gray-100 rounded-lg text-gray-500 text-center">Tidak ada jadwal tersedia untuk film ini.</div>
                                <?php else: ?>
                                    <?php foreach($schedules_by_studio as $studio_name => $dates): ?>
                                        <div class="schedule-studio-group space-y-3">
                                            <h3 class="font-bold text-xl text-slate-800 bg-gray-100 p-3 rounded-lg border"><?php echo $studio_name; ?></h3>
                                            <div class="space-y-3 pl-4 border-l-2 border-gray-200">
                                                <?php foreach($dates as $date => $times): ?>
                                                    <div>
                                                        <h4 class="font-semibold text-gray-700 mb-2"><?php echo $date; ?></h4>
                                                        <div class="flex flex-wrap gap-2">
                                                            <?php foreach($times as $schedule): ?>
                                                                <button type="button" class="schedule-time-btn" 
                                                                        data-id_jadwal="<?php echo $schedule['id']; ?>"
                                                                        data-harga="<?php echo $schedule['price']; ?>"
                                                                        data-studio="<?php echo htmlspecialchars($studio_name); ?>"
                                                                        data-tanggal="<?php echo $date; ?>"
                                                                        data-jam="<?php echo $schedule['time']; ?>">
                                                                    <?php echo $schedule['time']; ?>
                                                                </button>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>


                        <div id="seat-section" class="mb-8 hidden">
                            <h2 class="text-2xl font-bold mb-4 flex items-center"><span class="bg-blue-600 text-white rounded-full w-8 h-8 flex items-center justify-center font-bold mr-3 flex-shrink-0">2</span> Pilih Kursi</h2>
                            <div id="seat-selection-area" class="p-4 sm:p-6 bg-gray-50 rounded-lg border">
                                <div id="seat-loader" class="text-center py-10"><p class="text-gray-400">Pilih jadwal untuk memuat kursi...</p></div>
                                <div id="seat-map-wrapper" class="hidden">
                                    <div class="screen" title="Layar Bioskop"></div>
                                    <div id="seat-map-container" class="grid grid-cols-12 gap-1 sm:gap-2 max-w-xl mx-auto"></div>
                                    <div class="flex justify-center flex-wrap gap-x-4 gap-y-2 mt-6 pt-4 border-t border-gray-200 text-sm"><div class="flex items-center"><span class="w-4 h-4 bg-blue-600 rounded mr-2"></span>Pilihanmu</div><div class="flex items-center"><span class="w-4 h-4 bg-gray-300 rounded mr-2"></span>Tersedia</div><div class="flex items-center"><span class="w-4 h-4 bg-gray-100 border border-dashed border-gray-400 rounded mr-2"></span>Terisi</div></div>
                                </div>
                            </div>
                        </div>

                        <div id="summary-area" class="hidden">
                            <h2 class="text-2xl font-bold mb-4 flex items-center"><span class="bg-blue-600 text-white rounded-full w-8 h-8 flex items-center justify-center font-bold mr-3 flex-shrink-0">3</span> Konfirmasi Pesanan</h2>
                            <div class="bg-gray-50 p-4 sm:p-6 rounded-lg space-y-3 text-gray-600 border">
                                <div class="flex flex-col sm:flex-row justify-between"><span>Jadwal:</span> <span id="summary-jadwal" class="font-semibold text-slate-800 text-left sm:text-right"></span></div>
                                <div class="flex flex-col sm:flex-row justify-between"><span>Kursi Dipilih:</span> <span id="summary-kursi" class="font-semibold text-slate-800 text-left sm:text-right break-all">-</span></div>
                                <div class="flex justify-between text-xl border-t border-gray-200 pt-3 mt-3">
                                    <span class="font-bold">Total Bayar:</span>
                                    <span id="summary-harga" class="font-bold text-blue-600">Rp 0</span>
                                </div>
                            </div>
                            <button id="confirm-button" type="submit" disabled 
                                    class="w-full mt-6 bg-blue-600 text-white font-bold py-3 px-4 rounded-lg transition duration-300 hover:bg-blue-700 disabled:bg-gray-400 disabled:text-gray-100">
                                Pilih Jadwal & Kursi Terlebih Dahulu
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const scheduleContainer = document.getElementById('schedule-container');
        const seatSection = document.getElementById('seat-section');
        const seatLoader = document.getElementById('seat-loader');
        const seatMapWrapper = document.getElementById('seat-map-wrapper');
        const seatMapContainer = document.getElementById('seat-map-container');
        const summaryArea = document.getElementById('summary-area');
        const confirmButton = document.getElementById('confirm-button');
        const idJadwalInput = document.getElementById('id_jadwal_input');
        const selectedSeatsInput = document.getElementById('selected_seats_input');
        const totalHargaInput = document.getElementById('total_harga_input');
        const summaryJadwal = document.getElementById('summary-jadwal');
        const summaryKursi = document.getElementById('summary-kursi');
        const summaryHarga = document.getElementById('summary-harga');

        let currentPrice = 0;
        let selectedSeats = [];

        async function loadSeatMap(idJadwal) {
            seatMapContainer.innerHTML = '';
            updateSummary();
            seatLoader.innerHTML = '<p class="text-gray-400">Memuat data kursi...</p>';
            seatLoader.classList.remove('hidden');
            seatMapWrapper.classList.add('hidden');
            seatSection.classList.remove('hidden');
            summaryArea.classList.remove('hidden');

            try {
                const response = await fetch(`pesan_tiket.php?action=get_schedule_details&id_jadwal=${idJadwal}`);
                if (!response.ok) throw new Error('Gagal mengambil data dari server.');
                const data = await response.json();
                if (!data.success) throw new Error(data.error || 'Terjadi kesalahan.');
                
                generateSeats(data.kapasitas, data.kursi_terpesan);
                seatLoader.classList.add('hidden');
                seatMapWrapper.classList.remove('hidden');
            } catch (error) {
                seatLoader.innerHTML = `<p class="text-red-500 p-4 bg-red-50 rounded-md">${error.message}</p>`;
            }
        }
        
        scheduleContainer.addEventListener('click', e => {
            const target = e.target;
            if (target.classList.contains('schedule-time-btn')) {
                const currentSelected = scheduleContainer.querySelector('.schedule-time-btn.selected');
                if (currentSelected) {
                    currentSelected.classList.remove('selected');
                }
                target.classList.add('selected');

                const idJadwal = target.dataset.id_jadwal;
                currentPrice = parseInt(target.dataset.harga) || 0;
                idJadwalInput.value = idJadwal;
                
                const dateText = target.dataset.tanggal;
                const timeText = target.dataset.jam;
                const studioText = target.dataset.studio;
                summaryJadwal.textContent = `${studioText} | ${dateText}, ${timeText}`;
                
                loadSeatMap(idJadwal);
            }
        });

        function updateSummary() {
            selectedSeats = Array.from(document.querySelectorAll('.seat.selected')).map(seat => seat.dataset.seat);
            selectedSeats.sort((a, b) => {
                const letterA = a.charAt(0); const letterB = b.charAt(0);
                const numA = parseInt(a.substring(1)); const numB = parseInt(b.substring(1));
                if (letterA < letterB) return -1; if (letterA > letterB) return 1; return numA - numB;
            });
            summaryKursi.textContent = selectedSeats.length > 0 ? selectedSeats.join(', ') : '-';
            const total = selectedSeats.length * currentPrice;
            summaryHarga.textContent = `Rp ${total.toLocaleString('id-ID')}`;
            selectedSeatsInput.value = selectedSeats.join(',');
            totalHargaInput.value = total;

            const isScheduleSelected = !!idJadwalInput.value;
            confirmButton.disabled = selectedSeats.length === 0 || !isScheduleSelected;
            if (!isScheduleSelected) {
                 confirmButton.textContent = 'Pilih Jadwal Terlebih Dahulu';
            } else if (selectedSeats.length === 0) {
                 confirmButton.textContent = 'Pilih Kursi Terlebih Dahulu';
            } else {
                 confirmButton.textContent = 'Konfirmasi & Pesan Tiket';
            }
        }

        function generateSeats(kapasitas, bookedSeats = []) {
            seatMapContainer.innerHTML = '';
            const rows = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H']; const cols = 12;
            let generatedCount = 0;
            rows.forEach(row => {
                if (generatedCount >= kapasitas) return;
                for (let col = 1; col <= cols; col++) {
                    if (generatedCount >= kapasitas) break;
                    const seatId = `${row}${col}`;
                    const seat = document.createElement('div');
                    seat.classList.add('seat'); seat.dataset.seat = seatId; seat.textContent = seatId;
                    if (bookedSeats.includes(seatId)) {
                        seat.classList.add('booked');
                    } else {
                        seat.classList.add('available');
                        seat.addEventListener('click', () => { 
                            seat.classList.toggle('selected'); 
                            updateSummary(); 
                        });
                    }
                    seatMapContainer.appendChild(seat);
                    generatedCount++;
                }
            });
            updateSummary();
        }
    });
    </script>
</body>
</html>