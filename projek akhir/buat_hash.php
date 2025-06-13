<?php
// Inisialisasi variabel
$password_to_hash = '';
$hashed_password = '';
$error_message = '';

// Proses form jika ada data yang dikirim
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $password_to_hash = $_POST['password'];

    if (empty($password_to_hash)) {
        $error_message = 'Kolom password tidak boleh kosong.';
    } else {
        // Membuat hash dari password menggunakan algoritma default yang aman
        $hashed_password = password_hash($password_to_hash, PASSWORD_DEFAULT);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembuat Hash Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .copy-btn:hover .copy-icon { display: none; }
        .copy-btn:hover .check-icon { display: inline-block; }
        .check-icon { display: none; }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="w-full max-w-lg bg-white p-8 rounded-xl shadow-lg">
        <h1 class="text-2xl font-bold text-center text-gray-800 mb-2">Pembuat Hash Password Aman</h1>
        <p class="text-center text-gray-500 mb-6">Gunakan alat ini untuk membuat password admin pertama Anda.</p>

        <?php if($error_message): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative mb-4" role="alert">
            <span><?php echo htmlspecialchars($error_message); ?></span>
        </div>
        <?php endif; ?>
        
        <form action="buat_hash.php" method="POST" class="space-y-4">
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Masukkan Password Baru</label>
                <input type="text" name="password" id="password" required 
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-yellow-500 focus:border-yellow-500"
                       placeholder="Contoh: admin12345"
                       value="<?php echo htmlspecialchars($password_to_hash); ?>">
            </div>
            <button type="submit" class="w-full bg-yellow-500 text-white font-bold py-3 px-4 rounded-lg hover:bg-yellow-600">Buat Hash</button>
        </form>

        <?php if ($hashed_password): ?>
        <div class="mt-6 p-4 bg-gray-50 rounded-lg">
            <label class="block text-sm font-medium text-gray-700">Hasil Hash (Siap untuk disalin)</label>
            <div class="mt-2 relative">
                <input type="text" id="hashed_result" readonly
                       class="w-full p-3 bg-gray-200 border-gray-300 text-gray-700 font-mono text-sm rounded-md"
                       value="<?php echo htmlspecialchars($hashed_password); ?>">
                <button onclick="copyToClipboard()" title="Salin ke Clipboard" class="copy-btn absolute inset-y-0 right-0 px-3 flex items-center text-gray-600 hover:text-green-600">
                    <svg class="copy-icon h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                    <svg class="check-icon h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                </button>
            </div>
             <p class="text-xs text-gray-500 mt-2">Salin kode di atas dan tempelkan ke kolom `password` di tabel `admin` pada phpMyAdmin.</p>
        </div>
        <?php endif; ?>
    </div>

    <script>
        function copyToClipboard() {
            const input = document.getElementById('hashed_result');
            input.select();
            input.setSelectionRange(0, 99999); // For mobile devices
            
            // Menggunakan document.execCommand sebagai fallback karena navigator.clipboard mungkin diblokir
            try {
                document.execCommand('copy');
            } catch (err) {
                alert('Gagal menyalin secara otomatis. Silakan salin manual.');
            }
        }
    </script>
</body>
</html>
