<?php
// ===================================================================================
// BAGIAN 1: LOGIKA UTAMA (KONEKSI, AUTENTIKASI, VALIDASI, PROSES FORM)
// ===================================================================================
session_start();
require_once 'koneksi.php';

if (!isset($_SESSION['admin_username'])) {
    header("Location: login.php");
    exit();
}

$view = $_GET['view'] ?? 'film';
$action = $_GET['action'] ?? 'add';
$id = $_GET['id'] ?? 0;
$errors = [];
$success_message = '';

// --- FUNGSI UNTUK UPLOAD GAMBAR ---
function upload_image($file_input_name, $existing_image_path = '') {
    $target_dir = "posters/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    if (isset($_FILES[$file_input_name]) && $_FILES[$file_input_name]['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES[$file_input_name];
        $image_name = time() . '_' . basename($file["name"]);
        $target_file = $target_dir . $image_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        $check = getimagesize($file["tmp_name"]);
        if ($check === false) return ['error' => "File bukan gambar."];
        if (!in_array($imageFileType, ['jpg', 'png', 'jpeg', 'gif'])) return ['error' => "Hanya format JPG, JPEG, PNG & GIF yang diizinkan."];

        if (move_uploaded_file($file["tmp_name"], $target_file)) {
            if (!empty($existing_image_path) && file_exists($existing_image_path)) {
                unlink($existing_image_path);
            }
            return ['success' => $target_file];
        } else {
            return ['error' => "Terjadi kesalahan saat mengunggah file."];
        }
    }
    return ['success' => $existing_image_path];
}

// --- PROSES SEMUA FORM SUBMISSION (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_type = $_POST['form_type'] ?? '';

    if ($form_type === 'film') {
        $id_film = $_POST['id_film'] ?? 0;
        $judul = trim($_POST['judul']);
        
        // [MODIFIKASI] Proses genre dari array checkbox menjadi string
        $genre_array = $_POST['genre'] ?? [];
        $genre = !empty($genre_array) && is_array($genre_array) ? implode(', ', $genre_array) : '';
        
        $durasi = trim($_POST['durasi']);
        $sinopsis = trim($_POST['sinopsis']);
        
        $poster_path = $_POST['existing_poster'] ?? '';
        $gambar_latar_path = $_POST['existing_gambar_latar'] ?? '';

        if (empty($judul)) $errors['judul'] = 'Judul film wajib diisi.';
        if (empty($genre)) $errors['genre'] = 'Genre wajib dipilih (minimal satu).'; // Pesan error disesuaikan
        if (empty($durasi) || !filter_var($durasi, FILTER_VALIDATE_INT) || $durasi <= 0) $errors['durasi'] = 'Durasi harus berupa angka positif.';
        
        if ($id_film == 0 && (!isset($_FILES['poster']) || $_FILES['poster']['error'] != UPLOAD_ERR_OK)) {
             $errors['poster'] = 'Poster wajib diunggah.';
        }

        if (empty($errors)) {
            $poster_result = upload_image('poster', $poster_path);
            $latar_result = upload_image('gambar_latar', $gambar_latar_path);

            if (isset($poster_result['error'])) $errors['poster'] = $poster_result['error'];
            if (isset($latar_result['error'])) $errors['gambar_latar'] = $latar_result['error'];
            
            if(empty($errors)) {
                $poster_path = $poster_result['success'];
                $gambar_latar_path = $latar_result['success'];

                if ($id_film > 0) {
                    $stmt = $conn->prepare("UPDATE film SET judul=?, genre=?, durasi=?, sinopsis=?, poster=?, gambar_latar=? WHERE id_film=?");
                    $stmt->bind_param("ssisssi", $judul, $genre, $durasi, $sinopsis, $poster_path, $gambar_latar_path, $id_film);
                } else {
                    $stmt = $conn->prepare("INSERT INTO film (judul, genre, durasi, sinopsis, poster, gambar_latar) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("ssisss", $judul, $genre, $durasi, $sinopsis, $poster_path, $gambar_latar_path);
                }
                if ($stmt->execute()) { header("Location: admin.php?view=film&status=sukses"); exit(); }
            }
        }
    }

    if ($form_type === 'studio') {
        $id_studio = $_POST['id_studio'] ?? 0;
        $nama_studio = trim($_POST['nama_studio']);
        $kapasitas = trim($_POST['kapasitas']);
        
        if (empty($nama_studio)) $errors['nama_studio'] = 'Nama studio wajib diisi.';
        if (empty($kapasitas) || !filter_var($kapasitas, FILTER_VALIDATE_INT) || $kapasitas <= 0) $errors['kapasitas'] = 'Kapasitas harus berupa angka positif.';

        if (empty($errors)) {
            if ($id_studio > 0) {
                $stmt = $conn->prepare("UPDATE studio SET nama_studio=?, kapasitas=? WHERE id_studio=?");
                $stmt->bind_param("sii", $nama_studio, $kapasitas, $id_studio);
            } else {
                $stmt = $conn->prepare("INSERT INTO studio (nama_studio, kapasitas) VALUES (?, ?)");
                $stmt->bind_param("si", $nama_studio, $kapasitas);
            }
            if ($stmt->execute()) { header("Location: admin.php?view=studio&status=sukses"); exit(); }
        }
    }

    if ($form_type === 'jadwal') {
        $id_jadwal = $_POST['id_jadwal'] ?? 0;
        $id_film = trim($_POST['id_film']);
        $id_studio = trim($_POST['id_studio']);
        $tanggal = trim($_POST['tanggal']);
        $jam = trim($_POST['jam']);
        $harga = trim($_POST['harga']);

        if (empty($id_film)) $errors['id_film'] = 'Film wajib dipilih.';
        if (empty($id_studio)) $errors['id_studio'] = 'Studio wajib dipilih.';
        if (empty($tanggal)) $errors['tanggal'] = 'Tanggal wajib diisi.';
        if (empty($jam)) $errors['jam'] = 'Jam wajib diisi.';
        if (empty($harga) || !filter_var($harga, FILTER_VALIDATE_INT) || $harga < 0) $errors['harga'] = 'Harga harus berupa angka.';
        
        if (empty($errors)) {
            if ($id_jadwal > 0) {
                $stmt = $conn->prepare("UPDATE jadwal SET id_film=?, id_studio=?, tanggal=?, jam=?, harga=? WHERE id_jadwal=?");
                $stmt->bind_param("iissii", $id_film, $id_studio, $tanggal, $jam, $harga, $id_jadwal);
            } else {
                $stmt = $conn->prepare("INSERT INTO jadwal (id_film, id_studio, tanggal, jam, harga) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("iissi", $id_film, $id_studio, $tanggal, $jam, $harga);
            }
            if ($stmt->execute()) { header("Location: admin.php?view=jadwal&status=sukses"); exit(); }
        }
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'delete' && $id > 0) { $delete_view = $_GET['view']; if ($delete_view === 'film') { $stmt_get_path = $conn->prepare("SELECT poster, gambar_latar FROM film WHERE id_film=?"); $stmt_get_path->bind_param("i", $id); $stmt_get_path->execute(); $paths = $stmt_get_path->get_result()->fetch_assoc(); if ($paths) { if (!empty($paths['poster']) && file_exists($paths['poster'])) unlink($paths['poster']); if (!empty($paths['gambar_latar']) && file_exists($paths['gambar_latar'])) unlink($paths['gambar_latar']); } $stmt_get_path->close(); $stmt = $conn->prepare("DELETE FROM film WHERE id_film = ?"); $stmt->bind_param("i", $id); if ($stmt->execute()) { header("Location: admin.php?view=film&status=dihapus"); exit(); } } if ($delete_view === 'studio') { $stmt = $conn->prepare("DELETE FROM studio WHERE id_studio = ?"); $stmt->bind_param("i", $id); if ($stmt->execute()) { header("Location: admin.php?view=studio&status=dihapus"); exit(); } } if ($delete_view === 'jadwal') { $stmt = $conn->prepare("DELETE FROM jadwal WHERE id_jadwal = ?"); $stmt->bind_param("i", $id); if ($stmt->execute()) { header("Location: admin.php?view=jadwal&status=dihapus"); exit(); } } }
if (isset($_GET['status'])) { if($_GET['status'] === 'sukses') $success_message = "Data berhasil disimpan!"; if($_GET['status'] === 'dihapus') $success_message = "Data berhasil dihapus!"; }
$form_data = []; if ($action === 'edit' && $id > 0) { if ($view === 'film') { $stmt = $conn->prepare("SELECT * FROM film WHERE id_film = ?"); } if ($view === 'studio') { $stmt = $conn->prepare("SELECT * FROM studio WHERE id_studio = ?"); } if ($view === 'jadwal') { $stmt = $conn->prepare("SELECT * FROM jadwal WHERE id_jadwal = ?"); } $stmt->bind_param("i", $id); $stmt->execute(); $form_data = $stmt->get_result()->fetch_assoc() ?: []; } if (!empty($errors)) { $form_data = $_POST; }

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Bioskop Online</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        .tab-active { border-bottom-color: #2563eb; color: #1d4ed8; font-weight: 600; }
        .tab-inactive { border-bottom-color: transparent; color: #4b5563; }
        .form-input { transition: border-color 0.2s, box-shadow 0.2s; }
        .form-input:focus { border-color: #2563eb; box-shadow: 0 0 0 1px #2563eb; }
        .btn-primary { transition: all 0.2s; }
        .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .list-item { list-style: none; }
        .list-item::before, .list-item::after { content: none !important; display: none !important; }
        .list-item:hover { background-color: #f9fafb; border-color: #e5e7eb; }
    </style>
</head>
<body>
    <header class="bg-white shadow-sm sticky top-0 z-10">
        <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <h1 class="text-2xl font-bold text-gray-800">ADMIN<span class="text-blue-600">PANEL</span></h1>
                <div class="flex items-center space-x-4">
                    <a href="index.php" class="flex items-center space-x-2 text-sm font-medium text-gray-600 hover:text-blue-600 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                        <span>Lihat Situs</span>
                    </a>
                    <form action="logout.php" method="POST">
                        <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-md text-sm font-semibold hover:bg-red-700 transition-all duration-200 hover:scale-105">Logout</button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <main class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="border-b border-gray-200 mb-6">
            <nav class="-mb-px flex space-x-8">
                <a href="?view=film" class="py-4 px-1 border-b-2 text-sm font-medium transition-colors duration-200 <?php echo $view === 'film' ? 'tab-active' : 'tab-inactive'; ?>">Kelola Film</a>
                <a href="?view=studio" class="py-4 px-1 border-b-2 text-sm font-medium transition-colors duration-200 <?php echo $view === 'studio' ? 'tab-active' : 'tab-inactive'; ?>">Kelola Studio</a>
                <a href="?view=jadwal" class="py-4 px-1 border-b-2 text-sm font-medium transition-colors duration-200 <?php echo $view === 'jadwal' ? 'tab-active' : 'tab-inactive'; ?>">Kelola Jadwal</a>
            </nav>
        </div>

        <?php if ($success_message): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 px-4 py-3 rounded-lg mb-6 shadow" role="alert"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <div class="flex flex-col lg:flex-row lg:space-x-8">
            <div class="w-full lg:w-2/5 xl:w-1/3 flex-shrink-0 mb-8 lg:mb-0">
                <div class="bg-white p-6 rounded-lg shadow-lg">
                <?php if ($view === 'film'): ?>
                    <?php
                        // [MODIFIKASI] Siapkan data untuk checkbox
                        $available_genres = ['Horor', 'Comedy', 'Action', 'Romance', 'Thriller', 'Sci-Fi', 'Adventure', 'Drama'];
                        $selected_genres = [];
                        if (!empty($form_data['genre'])) {
                            if (is_array($form_data['genre'])) {
                                $selected_genres = $form_data['genre']; // Dari POST gagal
                            } else {
                                $selected_genres = array_map('trim', explode(',', $form_data['genre'])); // Dari DB
                            }
                        }
                    ?>
                    <h3 class="text-xl font-bold mb-4 text-gray-800"><?php echo $action === 'edit' ? 'Edit Film' : 'Tambah Film Baru'; ?></h3>
                    <form method="POST" action="admin.php?view=film" class="space-y-4" enctype="multipart/form-data">
                        <input type="hidden" name="form_type" value="film"><input type="hidden" name="id_film" value="<?php echo $form_data['id_film'] ?? 0; ?>">
                        <input type="hidden" name="existing_poster" value="<?php echo htmlspecialchars($form_data['poster'] ?? ''); ?>">
                        <input type="hidden" name="existing_gambar_latar" value="<?php echo htmlspecialchars($form_data['gambar_latar'] ?? ''); ?>">

                        <div><label class="block text-sm font-medium text-gray-700">Judul Film</label><input type="text" name="judul" value="<?php echo htmlspecialchars($form_data['judul'] ?? ''); ?>" class="form-input px-3 py-2 mt-1 block w-full rounded-md shadow <?php echo isset($errors['judul']) ? 'border-red-500' : 'border-gray-300'; ?>">
                        <?php if(isset($errors['judul'])): ?><p class="text-red-600 text-xs mt-1"><?php echo $errors['judul']; ?></p><?php endif; ?></div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Genre</label>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 p-3 rounded-md bg-gray-50 border <?php echo isset($errors['genre']) ? 'border-red-500' : 'border-gray-200'; ?>">
                                <?php foreach ($available_genres as $g): ?>
                                    <label class="flex items-center space-x-2 text-sm text-gray-800 font-medium">
                                        <input type="checkbox" name="genre[]" value="<?php echo htmlspecialchars($g); ?>"
                                               class="rounded border-gray-400 text-blue-600 shadow-sm focus:border-blue-400 focus:ring focus:ring-offset-0 focus:ring-blue-300 focus:ring-opacity-50"
                                               <?php if (in_array($g, $selected_genres)) echo 'checked'; ?>>
                                        <span><?php echo htmlspecialchars($g); ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                            <?php if(isset($errors['genre'])): ?><p class="text-red-600 text-xs mt-1"><?php echo $errors['genre']; ?></p><?php endif; ?>
                        </div>
                        
                        <div><label class="block text-sm font-medium text-gray-700">Durasi (menit)</label><input type="number" name="durasi" value="<?php echo htmlspecialchars($form_data['durasi'] ?? ''); ?>" class="form-input px-3 py-2 mt-1 block w-full rounded-md shadow <?php echo isset($errors['durasi']) ? 'border-red-500' : 'border-gray-300'; ?>">
                        <?php if(isset($errors['durasi'])): ?><p class="text-red-600 text-xs mt-1"><?php echo $errors['durasi']; ?></p><?php endif; ?></div>
                        
                        <div><label class="block text-sm font-medium text-gray-700">Poster</label>
                        <?php if($action === 'edit' && !empty($form_data['poster'])): ?><img src="<?php echo htmlspecialchars($form_data['poster']); ?>" class="w-20 h-auto my-2 rounded"><?php endif; ?>
                        <input type="file" name="poster" class="form-input mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        <?php if(isset($errors['poster'])): ?><p class="text-red-600 text-xs mt-1"><?php echo $errors['poster']; ?></p><?php endif; ?></div>
                        
                        <div><label class="block text-sm font-medium text-gray-700">Gambar Latar (Opsional)</label>
                        <?php if($action === 'edit' && !empty($form_data['gambar_latar'])): ?><img src="<?php echo htmlspecialchars($form_data['gambar_latar']); ?>" class="w-32 h-auto my-2 rounded"><?php endif; ?>
                        <input type="file" name="gambar_latar" class="form-input mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        <?php if(isset($errors['gambar_latar'])): ?><p class="text-red-600 text-xs mt-1"><?php echo $errors['gambar_latar']; ?></p><?php endif; ?></div>
                        
                        <div><label class="block text-sm font-medium text-gray-700">Sinopsis</label><textarea name="sinopsis" rows="4" class="form-input px-3 py-2 mt-1 block w-full rounded-md shadow <?php echo isset($errors['sinopsis']) ? 'border-red-500' : 'border-gray-300'; ?>"><?php echo htmlspecialchars($form_data['sinopsis'] ?? ''); ?></textarea></div>
                <?php elseif ($view === 'studio'): ?>
                    <h3 class="text-xl font-bold mb-4 text-gray-800"><?php echo $action === 'edit' ? 'Edit Studio' : 'Tambah Studio Baru'; ?></h3>
                    <form method="POST" action="admin.php?view=studio" class="space-y-4">
                        <input type="hidden" name="form_type" value="studio"><input type="hidden" name="id_studio" value="<?php echo $form_data['id_studio'] ?? 0; ?>">
                        <div><label class="block text-sm font-medium text-gray-700">Nama Studio</label><input type="text" name="nama_studio" value="<?php echo htmlspecialchars($form_data['nama_studio'] ?? ''); ?>" class="form-input px-3 py-2 mt-1 block w-full rounded-md shadow <?php echo isset($errors['nama_studio']) ? 'border-red-500' : 'border-gray-300'; ?>">
                        <?php if(isset($errors['nama_studio'])): ?><p class="text-red-600 text-xs mt-1"><?php echo $errors['nama_studio']; ?></p><?php endif; ?></div>
                        <div><label class="block text-sm font-medium text-gray-700">Kapasitas</label><input type="number" name="kapasitas" value="<?php echo htmlspecialchars($form_data['kapasitas'] ?? ''); ?>" class="form-input px-3 py-2 mt-1 block w-full rounded-md shadow <?php echo isset($errors['kapasitas']) ? 'border-red-500' : 'border-gray-300'; ?>">
                        <?php if(isset($errors['kapasitas'])): ?><p class="text-red-600 text-xs mt-1"><?php echo $errors['kapasitas']; ?></p><?php endif; ?></div>
                <?php elseif ($view === 'jadwal'): 
                        $films = $conn->query("SELECT id_film, judul FROM film ORDER BY judul"); $studios = $conn->query("SELECT id_studio, nama_studio FROM studio ORDER BY nama_studio"); ?>
                    <h3 class="text-xl font-bold mb-4 text-gray-800"><?php echo $action === 'edit' ? 'Edit Jadwal' : 'Tambah Jadwal Baru'; ?></h3>
                     <form method="POST" action="admin.php?view=jadwal" class="space-y-4">
                        <input type="hidden" name="form_type" value="jadwal"><input type="hidden" name="id_jadwal" value="<?php echo $form_data['id_jadwal'] ?? 0; ?>">
                        <div><label class="block text-sm font-medium text-gray-700">Film</label><select name="id_film" class="form-input px-3 py-2 mt-1 block w-full rounded-md shadow <?php echo isset($errors['id_film']) ? 'border-red-500' : 'border-gray-300'; ?>"><option value="">Pilih Film</option><?php while($film = $films->fetch_assoc()): ?><option value="<?php echo $film['id_film']; ?>" <?php if(isset($form_data['id_film']) && $form_data['id_film'] == $film['id_film']) echo 'selected'; ?>><?php echo htmlspecialchars($film['judul']); ?></option><?php endwhile; ?></select>
                        <?php if(isset($errors['id_film'])): ?><p class="text-red-600 text-xs mt-1"><?php echo $errors['id_film']; ?></p><?php endif; ?></div>
                        <div><label class="block text-sm font-medium text-gray-700">Studio</label><select name="id_studio" class="form-input px-3 py-2 mt-1 block w-full rounded-md shadow <?php echo isset($errors['id_studio']) ? 'border-red-500' : 'border-gray-300'; ?>"><option value="">Pilih Studio</option><?php while($studio = $studios->fetch_assoc()): ?><option value="<?php echo $studio['id_studio']; ?>" <?php if(isset($form_data['id_studio']) && $form_data['id_studio'] == $studio['id_studio']) echo 'selected'; ?>><?php echo htmlspecialchars($studio['nama_studio']); ?></option><?php endwhile; ?></select>
                        <?php if(isset($errors['id_studio'])): ?><p class="text-red-600 text-xs mt-1"><?php echo $errors['id_studio']; ?></p><?php endif; ?></div>
                        <div><label class="block text-sm font-medium text-gray-700">Tanggal</label><input type="date" name="tanggal" value="<?php echo htmlspecialchars($form_data['tanggal'] ?? ''); ?>" class="form-input px-3 py-2 mt-1 block w-full rounded-md shadow <?php echo isset($errors['tanggal']) ? 'border-red-500' : 'border-gray-300'; ?>">
                        <?php if(isset($errors['tanggal'])): ?><p class="text-red-600 text-xs mt-1"><?php echo $errors['tanggal']; ?></p><?php endif; ?></div>
                        <div><label class="block text-sm font-medium text-gray-700">Jam</label><input type="time" name="jam" value="<?php echo htmlspecialchars($form_data['jam'] ?? ''); ?>" class="form-input px-3 py-2 mt-1 block w-full rounded-md shadow <?php echo isset($errors['jam']) ? 'border-red-500' : 'border-gray-300'; ?>">
                        <?php if(isset($errors['jam'])): ?><p class="text-red-600 text-xs mt-1"><?php echo $errors['jam']; ?></p><?php endif; ?></div>
                        <div><label class="block text-sm font-medium text-gray-700">Harga</label><input type="number" name="harga" placeholder="Contoh: 50000" value="<?php echo htmlspecialchars($form_data['harga'] ?? ''); ?>" class="form-input px-3 py-2 mt-1 block w-full rounded-md shadow <?php echo isset($errors['harga']) ? 'border-red-500' : 'border-gray-300'; ?>">
                        <?php if(isset($errors['harga'])): ?><p class="text-red-600 text-xs mt-1"><?php echo $errors['harga']; ?></p><?php endif; ?></div>
                <?php endif; ?>
                        <button type="submit" class="btn-primary w-full bg-blue-600 text-white py-2.5 px-4 rounded-md font-semibold hover:bg-blue-700 flex items-center justify-center space-x-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?php echo $action === 'edit' ? 'M5 13l4 4L19 7' : 'M12 6v6m0 0v6m0-6h6m-6 0H6'; ?>" /></svg>
                            <span><?php echo $action === 'edit' ? 'Update Data' : 'Tambah Data'; ?></span>
                        </button>
                        <?php if ($action === 'edit'): ?><a href="?view=<?php echo $view; ?>" class="block w-full text-center bg-gray-200 text-gray-800 py-2 px-4 rounded-md font-semibold hover:bg-gray-300 mt-2">Batal Edit</a><?php endif; ?>
                    </form>
                </div>
            </div>

            <div class="w-full lg:w-3/5 xl:w-2/3">
                <div class="bg-white p-6 rounded-lg shadow-lg">
                    <h3 class="text-xl font-bold mb-4 text-gray-800">Daftar <?php echo ucfirst($view); ?></h3>
                    
                    <?php if ($view === 'film'): 
                        $result = $conn->query("SELECT * FROM film ORDER BY id_film DESC"); ?>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 xl:grid-cols-3 gap-5">
                            <?php if ($result && $result->num_rows > 0): while ($row = $result->fetch_assoc()): ?>
                            <div class="list-item p-4 border rounded-lg transition-all duration-200 flex flex-col">
                                <div class="flex items-start space-x-4">
                                    <img src="<?php echo htmlspecialchars($row['poster']); ?>" class="w-16 h-24 object-cover rounded flex-shrink-0">
                                    <div class="min-w-0 flex-1">
                                        <p class="font-semibold text-gray-800 truncate"><?php echo htmlspecialchars($row['judul']); ?></p>
                                        <p class="text-sm text-gray-500"><?php echo htmlspecialchars($row['genre']); ?> - <?php echo $row['durasi']; ?> min</p>
                                    </div>
                                </div>
                                <div class="flex space-x-3 self-start mt-3">
                                    <a href="?view=film&action=edit&id=<?php echo $row['id_film']; ?>" class="text-gray-500 hover:text-blue-600 p-1 rounded-full transition-colors"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.5L16.732 3.732z" /></svg></a>
                                    <a href="?view=film&action=delete&id=<?php echo $row['id_film']; ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus film ini?');" class="text-gray-500 hover:text-red-600 p-1 rounded-full transition-colors"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></a>
                                </div>
                            </div>
                            <?php endwhile; else: ?>
                                <div class="text-center py-10 px-6 bg-gray-50 rounded-lg sm:col-span-2 xl:col-span-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" /></svg>
                                    <h3 class="mt-2 text-sm font-semibold text-gray-900">Data Kosong</h3>
                                    <p class="mt-1 text-sm text-gray-500">Belum ada film. Silakan tambahkan menggunakan form di sebelah kiri.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="<?php echo ($view === 'jadwal') ? 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4' : 'space-y-3'; ?>">
                            <?php 
                            $result = null;
                            if ($view === 'studio') $result = $conn->query("SELECT * FROM studio ORDER BY nama_studio");
                            if ($view === 'jadwal') $result = $conn->query("SELECT j.*, f.judul, s.nama_studio FROM jadwal j JOIN film f ON j.id_film = f.id_film JOIN studio s ON j.id_studio = s.id_studio ORDER BY j.tanggal DESC, j.jam DESC");

                            if ($result && $result->num_rows > 0):
                                if ($view === 'studio'): while ($row = $result->fetch_assoc()): ?>
                                <div class="list-item flex items-center justify-between p-4 border rounded-lg transition-all duration-200">
                                    <div>
                                        <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($row['nama_studio']); ?></p>
                                        <p class="text-sm text-gray-500"><?php echo $row['kapasitas']; ?> Kursi</p>
                                    </div>
                                    <div class="flex space-x-3 flex-shrink-0">
                                        <a href="?view=studio&action=edit&id=<?php echo $row['id_studio']; ?>" class="text-gray-500 hover:text-blue-600 p-1 rounded-full transition-colors"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.5L16.732 3.732z" /></svg></a>
                                        <a href="?view=studio&action=delete&id=<?php echo $row['id_studio']; ?>" onclick="return confirm('Yakin ingin menghapus studio ini?');" class="text-gray-500 hover:text-red-600 p-1 rounded-full transition-colors"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></a>
                                    </div>
                                </div>
                                <?php endwhile; endif;
                                if ($view === 'jadwal'): while ($row = $result->fetch_assoc()): ?>
                                <div class="list-item p-4 border rounded-lg transition-all duration-200 flex flex-col justify-between">
                                    <div>
                                        <p class="font-semibold text-gray-800 truncate"><?php echo htmlspecialchars($row['judul']); ?></p>
                                        <p class="text-sm text-gray-500"><?php echo htmlspecialchars($row['nama_studio']); ?> | <?php echo date('d M Y, H:i', strtotime($row['tanggal'] . ' ' . $row['jam'])); ?> | Rp <?php echo number_format($row['harga']); ?></p>
                                    </div>
                                    <div class="flex space-x-3 self-start mt-3">
                                        <a href="?view=jadwal&action=edit&id=<?php echo $row['id_jadwal']; ?>" class="text-gray-500 hover:text-blue-600 p-1 rounded-full transition-colors"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.5L16.732 3.732z" /></svg></a>
                                        <a href="?view=jadwal&action=delete&id=<?php echo $row['id_jadwal']; ?>" onclick="return confirm('Yakin ingin menghapus jadwal ini?');" class="text-gray-500 hover:text-red-600 p-1 rounded-full transition-colors"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></a>
                                    </div>
                                </div>
                            <?php endwhile; endif;
                            else: ?>
                                <div class="text-center py-10 px-6 bg-gray-50 rounded-lg <?php echo ($view === 'jadwal') ? 'sm:col-span-2 lg:col-span-3 xl:col-span-4' : ''; ?>">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" /></svg>
                                    <h3 class="mt-2 text-sm font-semibold text-gray-900">Data Kosong</h3>
                                    <p class="mt-1 text-sm text-gray-500">Belum ada data <?php echo $view; ?>. Silakan tambahkan menggunakan form di sebelah kiri.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
<?php
if (isset($conn) && $conn) {
    $conn->close();
}
?>