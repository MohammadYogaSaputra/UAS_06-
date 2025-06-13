<?php
session_start();
require_once 'koneksi.php';

// ==================================================================
// BLOK KODE BARU: MENCEGAH PENGGUNA YANG SUDAH LOGIN MENGAKSES HALAMAN INI
// ==================================================================
// Jika admin sudah login, alihkan ke panel admin
if (isset($_SESSION['admin_username'])) {
    header("Location: admin.php");
    exit();
}
// Jika pengguna biasa sudah login, alihkan ke halaman utama
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
// ==================================================================

$errors = [];
$form_data = []; // Untuk menyimpan input pengguna jika ada error

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $no_hp = trim($_POST['no_hp']);

    $form_data = $_POST; // Simpan semua input

    // Validasi
    if (empty($nama_lengkap)) $errors[] = "Nama Lengkap wajib diisi.";
    if (empty($email)) $errors[] = "Email wajib diisi.";
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Format email tidak valid.";
    if (empty($password)) $errors[] = "Password wajib diisi.";
    elseif (strlen($password) < 6) $errors[] = "Password harus memiliki minimal 6 karakter.";

    // Lanjutkan jika tidak ada error dasar
    if (empty($errors)) {
        // Cek apakah email sudah terdaftar
        $stmt_check = $conn->prepare("SELECT id_user FROM user WHERE email = ?");
        $stmt_check->bind_param("s", $email);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $errors[] = "Email sudah terdaftar. Silakan gunakan email lain atau login.";
        } else {
            // Hash password untuk keamanan
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt_insert = $conn->prepare("INSERT INTO user (nama_lengkap, email, password, no_hp) VALUES (?, ?, ?, ?)");
            $stmt_insert->bind_param("ssss", $nama_lengkap, $email, $hashed_password, $no_hp);

            if ($stmt_insert->execute()) {
                $_SESSION['register_success'] = "Registrasi berhasil! Silakan login dengan akun baru Anda.";
                header("Location: login.php");
                exit();
            } else {
                $errors[] = "Registrasi gagal. Terjadi kesalahan pada server.";
            }
            $stmt_insert->close();
        }
        $stmt_check->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - Bioskop Online</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f1f5f9; }
        
        .form-input-container {
            position: relative;
        }
        .form-input-icon {
            position: absolute;
            left: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            pointer-events: none;
        }
        .form-input {
            padding-left: 2.5rem !important;
        }
        
        .error-message {
            color: #ef4444;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
        .secure-password {
            -webkit-text-security: disc;
            text-security: disc;
        }
    </style>
</head>
<body class="antialiased">
    <section class="w-full min-h-screen flex items-center justify-center py-8 md:py-12">
        <div class="flex flex-col md:flex-row bg-white shadow-2xl rounded-xl overflow-hidden max-w-4xl w-full mx-4 sm:mx-0">
            
            <div class="w-full md:w-1/2 p-6 sm:p-10 text-slate-900">
                <div class="flex flex-col justify-center h-full">
                    <div class="mb-6 text-center md:text-left">
                        <a href="index.php" class="text-3xl font-bold text-slate-900 mb-4 inline-block">BIOSKOP<span class="text-blue-500">ONLINE</span></a>
                        <h1 class="font-bold text-2xl sm:text-3xl text-slate-800">Buat Akun Baru</h1>
                        <p class="text-slate-500 text-sm sm:text-base mt-1">Isi detail Anda untuk mulai memesan tiket.</p>
                    </div>

                    <?php if (!empty($errors)): ?>
                    <div class="w-full p-3.5 mb-5 text-sm rounded-lg border bg-red-100 border-red-500 text-red-800" role="alert">
                        <span class="font-medium">Oops! Terjadi kesalahan:</span>
                        <ul class="mt-1.5 list-disc list-inside">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>

                    <form id="registerForm" action="user_register.php" method="POST" class="space-y-4" novalidate>
                        <div>
                            <label for="nama_lengkap" class="block text-sm font-medium text-slate-700 mb-1">Nama Lengkap</label>
                            <div class="form-input-container">
                                <div class="form-input-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" /></svg>
                                </div>
                                <input type="text" name="nama_lengkap" id="nama_lengkap" placeholder="contoh: Budi Setiawan" class="form-input bg-gray-100 border border-gray-300 text-slate-900 rounded-lg w-full p-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" value="<?php echo htmlspecialchars($form_data['nama_lengkap'] ?? ''); ?>" autofocus>
                            </div>
                            <p id="namaError" class="error-message hidden"></p>
                        </div>
                        
                        <div>
                            <label for="email" class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                            <div class="form-input-container">
                                <div class="form-input-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" /><path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" /></svg>
                                </div>
                                <input type="email" name="email" id="email" placeholder="contoh: budi@domain.com" class="form-input bg-gray-100 border border-gray-300 text-slate-900 rounded-lg w-full p-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>" autocomplete="off">
                            </div>
                            <p id="emailError" class="error-message hidden"></p>
                        </div>
                        
                        <div>
                            <label for="no_hp" class="block text-sm font-medium text-slate-700 mb-1">Nomor HP (Opsional)</label>
                            <div class="form-input-container">
                                <div class="form-input-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z" /></svg>
                                </div>
                                <input type="text" name="no_hp" id="no_hp" placeholder="contoh: 081234567890" class="form-input bg-gray-100 border border-gray-300 text-slate-900 rounded-lg w-full p-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" value="<?php echo htmlspecialchars($form_data['no_hp'] ?? ''); ?>">
                            </div>
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                            <div class="form-input-container">
                                <div class="form-input-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" /></svg>
                                </div>
                                <input type="text" name="password" id="password" placeholder="Buat password (minimal 6 karakter)" class="form-input bg-gray-100 border border-gray-300 text-slate-900 rounded-lg w-full p-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 secure-password" autocomplete="new-password">
                            </div>
                            <p id="passwordError" class="error-message hidden"></p>
                        </div>

                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold p-3 text-center rounded-lg shadow-md hover:shadow-lg transition duration-150 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-gray-100">
                            Daftar Akun
                        </button>
                    </form>
                    
                    <div class="text-center w-full mt-6">
                        <p class="text-sm text-slate-500">Sudah punya akun? <a href="login.php" class="font-medium text-blue-500 hover:text-blue-400 hover:underline">Masuk di sini</a></p>
                    </div>
                </div>
            </div>

            <div class="hidden md:flex md:w-1/2 p-8 items-center justify-center rounded-r-xl" style="background-image: url('poster/icon.svg'); background-size: contain; background-repeat: no-repeat; background-position: center;">
            </div>
      </div>
    </section>
    
    <script>
        const registerForm = document.getElementById('registerForm');
        
        const namaInput = document.getElementById('nama_lengkap');
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');

        const namaError = document.getElementById('namaError');
        const emailError = document.getElementById('emailError');
        const passwordError = document.getElementById('passwordError');

        // Fungsi untuk menampilkan error
        const showError = (input, errorElement, message) => {
            input.classList.add('border-red-500');
            input.classList.remove('border-gray-300');
            errorElement.textContent = message;
            errorElement.classList.remove('hidden');
        };

        // Fungsi untuk menyembunyikan error
        const hideError = (input, errorElement) => {
            input.classList.remove('border-red-500');
            input.classList.add('border-gray-300');
            errorElement.classList.add('hidden');
        };

        const validateEmail = (email) => {
            const re = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
            return re.test(String(email).toLowerCase());
        }

        registerForm.addEventListener('submit', function(e) {
            let isValid = true;

            // Reset semua error sebelum validasi
            hideError(namaInput, namaError);
            hideError(emailInput, emailError);
            hideError(passwordInput, passwordError);

            // Validasi Nama Lengkap
            if (namaInput.value.trim() === '') {
                showError(namaInput, namaError, 'Nama Lengkap tidak boleh kosong.');
                isValid = false;
            }

            // Validasi Email
            if (emailInput.value.trim() === '') {
                showError(emailInput, emailError, 'Email tidak boleh kosong.');
                isValid = false;
            } else if (!validateEmail(emailInput.value.trim())) {
                showError(emailInput, emailError, 'Format email tidak valid.');
                isValid = false;
            }

            // Validasi Password
            if (passwordInput.value.trim() === '') {
                showError(passwordInput, passwordError, 'Password tidak boleh kosong.');
                isValid = false;
            } else if (passwordInput.value.length < 6) {
                showError(passwordInput, passwordError, 'Password harus minimal 6 karakter.');
                isValid = false;
            }

            if (!isValid) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>