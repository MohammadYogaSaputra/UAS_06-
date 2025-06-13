<?php
// Selalu mulai session di baris paling atas
session_start();

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

// Panggil file koneksi terpusat
require_once 'koneksi.php';

// Inisialisasi variabel
$error_message = '';
$success_message = '';

// Cek jika ada pesan sukses dari halaman registrasi
if (isset($_SESSION['register_success'])) {
    $success_message = $_SESSION['register_success'];
    unset($_SESSION['register_success']); // Hapus pesan setelah ditampilkan
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $identifier = trim($_POST['identifier']); // Bisa email atau username
    $password = $_POST['password'];

    if (empty($identifier) || empty($password)) {
        $error_message = "Email/Username dan Password tidak boleh kosong.";
    } else {
        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            // --- PROSES LOGIN SEBAGAI PENGGUNA (karena menggunakan email) ---
            $stmt = $conn->prepare("SELECT id_user, nama_lengkap, email, password FROM user WHERE email = ?");
            $stmt->bind_param("s", $identifier);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                if (password_verify($password, $user['password'])) {
                    session_unset();
                    session_destroy();
                    session_start();
                    
                    $_SESSION['user_id'] = $user['id_user'];
                    $_SESSION['user_nama'] = $user['nama_lengkap'];
                    $_SESSION['user_email'] = $user['email'];
                    
                    header("Location: index.php");
                    exit();
                } else {
                    $error_message = "Password yang Anda masukkan salah.";
                }
            } else {
                $error_message = "Email tidak terdaftar.";
            }
            $stmt->close();

        } else {
            // --- PROSES LOGIN SEBAGAI ADMIN (karena bukan email, diasumsikan username) ---
            $stmt = $conn->prepare("SELECT id_admin, username, password FROM admin WHERE username = ?");
            $stmt->bind_param("s", $identifier);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $admin = $result->fetch_assoc();
                if (password_verify($password, $admin['password'])) {
                    session_unset();
                    session_destroy();
                    session_start();

                    $_SESSION['admin_id'] = $admin['id_admin'];
                    $_SESSION['admin_username'] = $admin['username'];
                    
                    header("Location: admin.php");
                    exit();
                } else {
                    $error_message = "Password admin salah.";
                }
            } else {
                $error_message = "Username admin tidak ditemukan.";
            }
            $stmt->close();
        }
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Bioskop Online</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Inter', sans-serif; 
            background-color: #f1f5f9;
        }
        .form-input-container {
            position: relative;
        }
        .form-input-icon {
            position: absolute;
            left: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af; /* gray-400 */
        }
        .form-input {
            padding-left: 2.5rem !important;
        }
        .error-message {
            color: #ef4444; /* red-500 */
            font-size: 0.875rem; /* text-sm */
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
                        <a href="index.php" class="text-3xl font-bold text-slate-900 mb-4 inline-block">BIOSKOP<span class="text-blue-600">ONLINE</span></a>
                        <h1 class="font-bold text-2xl sm:text-3xl text-slate-800">Selamat Datang Kembali</h1>
                        <p class="text-slate-500 text-sm sm:text-base mt-1">Masukkan detail akun Anda untuk masuk.</p>
                    </div>

                    <?php if (!empty($error_message)): ?>
                    <div class="w-full p-3.5 mb-5 text-sm rounded-lg border bg-red-100 border-red-500 text-red-800" role="alert">
                        <span class="font-medium">Oops!</span> <?php echo htmlspecialchars($error_message); ?>
                    </div>
                    <?php endif; ?>
                    <?php if($success_message): ?>
                    <div class="w-full p-3.5 mb-5 text-sm rounded-lg border bg-green-100 border-green-500 text-green-800" role="alert">
                        <span class="font-medium">Berhasil!</span> <?php echo htmlspecialchars($success_message); ?>
                    </div>
                    <?php endif; ?>

                    <form id="loginForm" action="login.php" method="POST" class="space-y-5" novalidate>
                        <div>
                            <label for="identifier" class="block text-sm font-medium text-slate-700 mb-1">Email <span class="text-red-500">*</span></label>
                            <div class="form-input-container">
                                <div class="form-input-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" /><path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" /></svg>
                                </div>
                                <input id="identifier" name="identifier" type="text" placeholder="Masukkan email Anda" class="form-input bg-gray-100 border border-gray-300 text-slate-900 rounded-lg w-full p-2.5 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150" required autocomplete="off" />
                            </div>
                            <p id="identifierError" class="error-message hidden"></p>
                        </div>
                        
                        <div>
                            <label for="password" class="block text-sm font-medium text-slate-700 mb-1">Password <span class="text-red-500">*</span></label>
                            <div class="form-input-container">
                                <div class="form-input-icon">
                                    <svg xmlns="poster/icon.svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" /></svg>
                                </div>
                                <input id="password" name="password" type="text" placeholder="Masukkan Password Anda" class="form-input secure-password bg-gray-100 border border-gray-300 text-slate-900 rounded-lg w-full p-2.5 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150" required autocomplete="new-password" />
                            </div>
                            <p id="passwordError" class="error-message hidden"></p>
                        </div>

                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold p-3 text-center rounded-lg shadow-md hover:shadow-lg transition duration-150 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-gray-100">
                            Masuk
                        </button>
                    </form>
                    
                    <div class="text-center w-full mt-6">
                        <p class="text-sm text-slate-500">Pengguna baru? <a href="user_register.php" class="font-medium text-blue-600 hover:text-blue-500 hover:underline">Daftar di sini</a></p>
                    </div>
                </div>
            </div>

            <div class="hidden md:flex md:w-1/2 p-8 items-center justify-center rounded-r-xl" style="background-image: url('poster/icon.svg'); background-size: contain; background-repeat: no-repeat; background-position: center;">
            </div>
        </div>
    </section>

    <script>
        const loginForm = document.getElementById('loginForm');
        const identifierInput = document.getElementById('identifier');
        const passwordInput = document.getElementById('password');

        const identifierError = document.getElementById('identifierError');
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

        loginForm.addEventListener('submit', function(e) {
            let isValid = true;

            // Reset errors
            hideError(identifierInput, identifierError);
            hideError(passwordInput, passwordError);

            // Validasi Identifier (Email/Username)
            if (identifierInput.value.trim() === '') {
                showError(identifierInput, identifierError, 'Email atau Username tidak boleh kosong.');
                isValid = false;
            }

            // Validasi Password
            if (passwordInput.value.trim() === '') {
                showError(passwordInput, passwordError, 'Password tidak boleh kosong.');
                isValid = false;
            }

            if (!isValid) {
                e.preventDefault(); // Mencegah form dikirim jika tidak valid
            }
        });
    </script>
</body>
</html>