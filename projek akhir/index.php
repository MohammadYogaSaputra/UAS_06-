<?php
// Selalu mulai session di baris paling atas
session_start();
require_once 'koneksi.php';

// --- LOGIKA PENCARIAN & FILTER ---
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$genre_filter = isset($_GET['genre']) ? trim($_GET['genre']) : '';
$is_search_or_filter_active = !empty($search_query) || !empty($genre_filter);
$page_title = "Bioskop Online";
$page_heading = "";
$search_results = [];

if ($is_search_or_filter_active) {
    if (!empty($search_query)) {
        $page_title = "Cari: " . htmlspecialchars($search_query);
        $page_heading = "Hasil Pencarian untuk: '" . htmlspecialchars($search_query) . "'";
        $stmt = $conn->prepare("SELECT id_film, judul, poster FROM film WHERE judul LIKE ?");
        $like_query = "%{$search_query}%";
        $stmt->bind_param("s", $like_query);
    } elseif (!empty($genre_filter)) {
        $page_title = "Genre: " . htmlspecialchars($genre_filter);
        $page_heading = "Menampilkan Film Genre: " . htmlspecialchars($genre_filter);
        $stmt = $conn->prepare("SELECT id_film, judul, poster FROM film WHERE genre LIKE ?");
        $like_query = "%{$genre_filter}%";
        $stmt->bind_param("s", $like_query);
    }
    $stmt->execute();
    $search_results = $stmt->get_result();
    $stmt->close();
}


// --- MINI API UNTUK JAVASCRIPT ---
if (isset($_GET['action']) && $_GET['action'] == 'get_movie_details') {
    header('Content-Type: application/json');
    $id_film = isset($_GET['id_film']) ? intval($_GET['id_film']) : 0;
    
    if ($id_film > 0) {
        $stmt_detail = $conn->prepare("SELECT id_film, judul, poster, genre, durasi, sinopsis FROM film WHERE id_film = ?");
        $stmt_detail->bind_param("i", $id_film);
        $stmt_detail->execute();
        $result = $stmt_detail->get_result();
        $movie_details = $result->fetch_assoc();
        $stmt_detail->close();
        echo json_encode($movie_details);
    } else {
        echo json_encode(['error' => 'ID Film tidak valid.']);
    }
    $conn->close();
    exit(); 
}


// --- PENGAMBILAN DATA UNTUK TAMPILAN DEFAULT ---
$result_hero = $conn->query("SELECT id_film, judul, gambar_latar, poster, genre, LEFT(sinopsis, 150) as sinopsis_singkat FROM film WHERE gambar_latar IS NOT NULL AND gambar_latar != '' ORDER BY id_film DESC LIMIT 5");
$result_terbaru = $conn->query("SELECT id_film, judul, poster FROM film ORDER BY id_film DESC LIMIT 10");

$sql_favorit = "SELECT f.id_film, f.judul, f.poster, SUM(p.jumlah_tiket) AS total_tiket
                FROM film f
                LEFT JOIN jadwal j ON f.id_film = j.id_film
                LEFT JOIN pemesanan p ON j.id_jadwal = p.id_jadwal
                GROUP BY f.id_film, f.judul, f.poster
                ORDER BY total_tiket DESC, f.id_film DESC
                LIMIT 10";
$result_favorit = $conn->query($sql_favorit);

// --- PENGAMBILAN DATA GENRE ---
$genre_horor = 'Horor';
$stmt_horor = $conn->prepare("SELECT id_film, judul, poster FROM film WHERE genre LIKE ? ORDER BY RAND() LIMIT 10");
$param_horor = "%{$genre_horor}%";
$stmt_horor->bind_param("s", $param_horor);
$stmt_horor->execute();
$result_horor = $stmt_horor->get_result();

$genre_action = 'Action';
$stmt_action = $conn->prepare("SELECT id_film, judul, poster FROM film WHERE genre LIKE ? ORDER BY RAND() LIMIT 10");
$param_action = "%{$genre_action}%";
$stmt_action->bind_param("s", $param_action);
$stmt_action->execute();
$result_action = $stmt_action->get_result();

$genre_romance = 'Romance';
$stmt_romance = $conn->prepare("SELECT id_film, judul, poster FROM film WHERE genre LIKE ? ORDER BY RAND() LIMIT 10");
$param_romance = "%{$genre_romance}%";
$stmt_romance->bind_param("s", $param_romance);
$stmt_romance->execute();
$result_romance = $stmt_romance->get_result();


$genre_options = ['Horor', 'Comedy', 'Action', 'Romance', 'Thriller', 'Sci-Fi', 'Adventure', 'Drama'];

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Bioskop Online</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <script>
        const isUserLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
        const isAdminLoggedIn = <?php echo isset($_SESSION['admin_username']) ? 'true' : 'false'; ?>;
    </script>
    <style>
        html, body { max-width: 100%; overflow-x: hidden; }
        body { 
            font-family: 'Inter', sans-serif; 
            background-color: #e5e7eb; /* Abu-abu 200 */
            color: #1f2937; /* Abu-abu 800 */
        }
        .movie-poster-card { transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out; }
        .movie-poster-card:hover { transform: scale(1.05); box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2); }
        .hero-slider .swiper-slide {
            height: 85vh;
            max-height: 800px;
            background-size: cover;
            background-position: center;
        }
        .hero-slider .slide-overlay { background: linear-gradient(to right, rgba(0,0,0,0.8) 20%, rgba(0,0,0,0.2) 60%, transparent 100%); }

        @media (max-width: 767px) {
            .hero-slider .slide-overlay { background: linear-gradient(to top, rgba(0,0,0,0.8) 20%, transparent 80%); }
        }
        .modal { display: none; }
        .modal.active { display: flex; }

        .swiper-button-next, .swiper-button-prev { display: none; }
        .movie-slider-container {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        .movie-slider-container::-webkit-scrollbar {
            display: none;
        }
    </style>
</head>
<body>
    
    <header class="bg-gray-100/80 backdrop-blur-sm w-full z-20 py-4 border-b border-gray-200 sticky top-0">
        <nav class="container mx-auto px-4 sm:px-6 flex justify-between items-center">
            <a href="index.php" class="text-2xl font-bold text-gray-800 tracking-wider">BIOSKOP<span class="text-blue-600">ONLINE</span></a>
            
            <div class="hidden md:flex items-center space-x-8">
                <a href="index.php" class="text-gray-600 hover:text-blue-600 font-medium transition">Beranda</a>
                <a href="tiket_saya.php" class="text-gray-600 hover:text-blue-600 font-medium transition">Film Saya</a>
            </div>

            <div class="flex items-center space-x-4">
                <div class="hidden md:flex items-center space-x-5">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <span class="text-gray-800">Halo, <span class="font-semibold"><?php echo htmlspecialchars($_SESSION['user_nama']); ?></span>!</span>
                        <form action="logout.php" method="POST">
                            <button type="submit" class="bg-blue-500 text-white px-5 py-2 rounded-lg hover:bg-blue-600 text-sm font-semibold transition">Logout</button>
                        </form>
                    <?php elseif (isset($_SESSION['admin_username'])): ?>
                         <a href="admin.php" class="font-semibold text-yellow-500 hover:underline">Dasbor Admin</a>
                         <form action="logout.php" method="POST">
                            <button type="submit" class="bg-red-600 text-white px-5 py-2 rounded-lg hover:bg-red-700 text-sm font-semibold transition">Logout</button>
                         </form>
                    <?php else: ?>
                        <a href="user_register.php" class="text-gray-800 font-semibold px-5 py-2 rounded-lg border border-gray-400 hover:bg-gray-200 transition">Daftar</a>
                        <a href="login.php" class="bg-blue-500 text-white px-5 py-2 rounded-lg hover:bg-blue-600 font-semibold transition">Masuk</a>
                    <?php endif; ?>
                </div>

                <button id="hamburger-btn" class="md:hidden text-gray-800 focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>
            </div>
        </nav>
        
        <div id="mobile-menu" class="hidden md:hidden bg-gray-100 container mx-auto px-4 sm:px-6 py-4">
            <div class="flex flex-col space-y-4">
                <a href="index.php" class="text-gray-600 hover:text-blue-600 font-medium transition">Beranda</a>
                <a href="tiket_saya.php" class="text-gray-600 hover:text-blue-600 font-medium transition">Film Saya</a>
                <div class="border-t border-gray-200 pt-4 flex flex-col space-y-4">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <span class="text-gray-800">Halo, <span class="font-semibold"><?php echo htmlspecialchars($_SESSION['user_nama']); ?></span>!</span>
                    <form action="logout.php" method="POST" class="w-full">
                        <button type="submit" class="w-full text-center bg-blue-500 text-white px-5 py-2 rounded-lg hover:bg-blue-600 text-sm font-semibold transition">Logout</button>
                    </form>
                <?php elseif (isset($_SESSION['admin_username'])): ?>
                     <a href="admin.php" class="font-semibold text-yellow-500 hover:underline">Dasbor Admin</a>
                     <form action="logout.php" method="POST" class="w-full">
                        <button type="submit" class="w-full text-center bg-red-600 text-white px-5 py-2 rounded-lg hover:bg-red-700 text-sm font-semibold transition">Logout</button>
                     </form>
                <?php else: ?>
                    <a href="user_register.php" class="w-full text-center text-gray-800 font-semibold px-5 py-2 rounded-lg border border-gray-400 hover:bg-gray-200 transition">Daftar</a>
                    <a href="login.php" class="w-full text-center bg-blue-500 text-white px-5 py-2 rounded-lg hover:bg-blue-600 font-semibold transition">Masuk</a>
                <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <?php if (!$is_search_or_filter_active): ?>
    <section class="w-full">
         <div class="swiper-container hero-slider">
            <div class="swiper-wrapper">
                <?php if ($result_hero && $result_hero->num_rows > 0): ?>
                    <?php while($row_hero = $result_hero->fetch_assoc()): ?>
                    <div class="swiper-slide movie-details-trigger cursor-pointer" data-id_film="<?php echo $row_hero['id_film']; ?>" style="background-image: url('<?php echo htmlspecialchars($row_hero['gambar_latar'] ?: $row_hero['poster']); ?>');">
                        <div class="slide-overlay absolute inset-0"></div>
                        <div class="relative h-full flex items-end md:items-center p-4 sm:p-8">
                            <div class="w-full md:w-1/2 lg:w-2/5 text-center md:text-left">
                                <h2 class="text-3xl sm:text-4xl md:text-5xl font-extrabold text-white mb-4 drop-shadow-lg"><?php echo htmlspecialchars($row_hero['judul']); ?></h2>
                                <p class="text-base text-slate-200 mb-6 line-clamp-3 mx-auto md:mx-0 max-w-md drop-shadow-md"><?php echo htmlspecialchars($row_hero['sinopsis_singkat']); ?>...</p>
                                <div class="inline-block bg-blue-500 text-white font-bold text-lg px-8 py-3 rounded-lg hover:bg-blue-600 transition">Lihat Detail</div>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php endif; ?>
            </div>
            <div class="swiper-pagination"></div>
        </div>
    </section>
    <?php endif; ?>

    <main class="container mx-auto px-4 sm:px-6 py-12">
        
        <section class="bg-gray-50 p-4 sm:p-6 rounded-xl mb-12 shadow-lg">
            <form action="index.php" method="GET" class="flex flex-col md:flex-row items-center gap-4">
                <div class="flex-grow w-full md:w-auto">
                    <label for="search" class="sr-only">Cari Film</label>
                    <input type="text" name="search" id="search" placeholder="Cari judul film..." value="<?php echo htmlspecialchars($search_query); ?>" class="w-full px-4 py-2.5 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white text-gray-800 border-gray-300">
                </div>
                <div class="w-full md:w-auto">
                    <label for="genre" class="sr-only">Pilih Genre</label>
                    <select name="genre" id="genre" class="w-full px-4 py-2.5 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white text-gray-800 border-gray-300">
                        <option value="">Semua Genre</option>
                        <?php foreach($genre_options as $genre): ?>
                            <option value="<?php echo $genre; ?>" <?php if($genre_filter == $genre) echo 'selected'; ?>><?php echo $genre; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="w-full md:w-auto flex gap-2">
                    <button type="submit" class="w-full bg-blue-500 text-white font-semibold px-6 py-2.5 rounded-lg hover:bg-blue-600 transition">Cari</button>
                    <a href="index.php" class="w-full text-center bg-gray-500 text-white font-semibold px-6 py-2.5 rounded-lg hover:bg-gray-600 transition">Reset</a>
                </div>
            </form>
        </section>

        <?php if (isset($_SESSION['pesanan_sukses'])): ?>
            <div class="p-4 mb-6 rounded-lg bg-green-100 text-green-800 border border-green-400">
                <?php echo $_SESSION['pesanan_sukses']; unset($_SESSION['pesanan_sukses']); ?>
            </div>
        <?php endif; ?>

        <?php if ($is_search_or_filter_active): ?>
            <section>
                <h2 class="text-3xl font-bold text-gray-800 mb-6"><?php echo $page_heading; ?></h2>
                <?php if($search_results && $search_results->num_rows > 0): ?>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-6">
                    <?php while($row = $search_results->fetch_assoc()): ?>
                        <div class="movie-details-trigger group cursor-pointer" data-id_film="<?php echo $row['id_film']; ?>">
                            <div class="aspect-[2/3] bg-gray-300 rounded-lg overflow-hidden movie-poster-card">
                                <img src="<?php echo htmlspecialchars($row['poster'] ?: 'https://placehold.co/400x600/e2e8f0/475569?text=N/A'); ?>" alt="Poster <?php echo htmlspecialchars($row['judul']); ?>" class="w-full h-full object-cover">
                            </div>
                            <h3 class="mt-2 text-sm font-semibold text-gray-700 group-hover:text-blue-600 transition truncate"><?php echo htmlspecialchars($row['judul']); ?></h3>
                        </div>
                    <?php endwhile; ?>
                </div>
                <?php else: ?>
                    <div class="text-center py-16 bg-gray-50 rounded-lg shadow-lg">
                        <p class="text-gray-500 text-lg">Film yang Anda cari tidak ditemukan.</p>
                        <a href="index.php" class="mt-4 inline-block bg-blue-500 text-white px-6 py-2 rounded-lg hover:bg-blue-600 font-semibold transition">Kembali ke Beranda</a>
                    </div>
                <?php endif; ?>
            </section>
        <?php else: ?>
            <div class="space-y-16">
                <?php 
                function create_movie_slider($title, $result, $slider_id) { if ($result && $result->num_rows > 0): ?>
                <section>
                    <h2 class="text-2xl md:text-3xl font-bold text-gray-800 mb-6"><?php echo $title; ?></h2>
                    <div class="swiper-container movie-slider-container movie-slider-<?php echo $slider_id; ?>">
                        <div class="swiper-wrapper">
                            <?php mysqli_data_seek($result, 0); while($row = $result->fetch_assoc()): ?>
                            <div class="swiper-slide">
                                <div class="movie-details-trigger cursor-pointer" data-id_film="<?php echo $row['id_film']; ?>">
                                    <div class="aspect-[2/3] bg-gray-300 rounded-lg overflow-hidden movie-poster-card">
                                        <img src="<?php echo htmlspecialchars($row['poster'] ?: 'https://placehold.co/400x600/e2e8f0/475569?text=N/A'); ?>" alt="Poster <?php echo htmlspecialchars($row['judul']); ?>" class="w-full h-full object-cover">
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                        </div>
                </section>
                <?php endif; } 
                create_movie_slider('Film Terfavorit', $result_favorit, 'favorit');
                create_movie_slider('Terbaru Untukmu', $result_terbaru, 'terbaru');
                create_movie_slider('Pilihan Genre: ' . $genre_horor, $result_horor, 'genre-horor');
                create_movie_slider('Pilihan Genre: ' . $genre_action, $result_action, 'genre-action');
                create_movie_slider('Pilihan Genre: ' . $genre_romance, $result_romance, 'genre-romance');
                ?>
            </div>
        <?php endif; ?>
    </main>
    
    <div id="modal" class="modal fixed inset-0 bg-black/80 items-center justify-center p-4 z-50">
        <div class="bg-gray-50 rounded-lg shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-y-auto relative">
            <button id="close-modal" class="absolute top-4 right-4 text-gray-500 hover:text-gray-900 z-20"><svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
            <div id="modal-content-wrapper" class="w-full opacity-0 transition-opacity duration-300"></div>
        </div>
    </div>

    <footer class="border-t border-gray-300 mt-8 py-8"><div class="container mx-auto px-6 text-center text-gray-500"><p>&copy; <?php echo date("Y"); ?> Bioskop Online.</p></div></footer>

    <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Hamburger Menu
            const hamburgerBtn = document.getElementById('hamburger-btn');
            const mobileMenu = document.getElementById('mobile-menu');
            hamburgerBtn.addEventListener('click', () => {
                mobileMenu.classList.toggle('hidden');
            });

            // Hero Slider
            if (document.querySelector('.hero-slider')) {
                new Swiper('.hero-slider', { loop: true, autoplay: { delay: 5000 }, pagination: { el: '.swiper-pagination', clickable: true }, effect: 'fade', fadeEffect: { crossFade: true }});
            }
            
            // Movie Sliders
            function initMovieSlider(selector) {
                if (document.querySelector(selector)) {
                    new Swiper(selector, { 
                        slidesPerView: 2.5, 
                        spaceBetween: 15, 
                        freeMode: true,
                        grabCursor: true,
                        breakpoints: { 
                            640:{slidesPerView:3.5, spaceBetween: 20}, 
                            768:{slidesPerView:4.5, spaceBetween: 20}, 
                            1024:{slidesPerView:5.5, spaceBetween: 20}, 
                            1280:{slidesPerView:6.5,spaceBetween:30}
                        }
                    });
                }
            }
            initMovieSlider('.movie-slider-favorit');
            initMovieSlider('.movie-slider-terbaru');
            initMovieSlider('.movie-slider-genre-horor');
            initMovieSlider('.movie-slider-genre-action');
            initMovieSlider('.movie-slider-genre-romance');

            // Modal Logic
            const modal = document.getElementById('modal');
            const closeModalBtn = document.getElementById('close-modal');
            const modalWrapper = document.getElementById('modal-content-wrapper');

            document.querySelectorAll('.movie-details-trigger').forEach(trigger => {
                trigger.addEventListener('click', function(e) {
                    e.preventDefault();
                    const idFilm = this.dataset.id_film;
                    modal.classList.add('active');
                    modalWrapper.classList.remove('opacity-100');
                    modalWrapper.innerHTML = '<p class="text-center p-10">Memuat detail film...</p>';
                    
                    fetch(`index.php?action=get_movie_details&id_film=${idFilm}`)
                        .then(response => response.json())
                        .then(data => {
                            if(data && !data.error) {
                                let buttonHtml = '';
                                if (isAdminLoggedIn) {
                                    buttonHtml = `<button disabled class="w-full text-center block bg-gray-500 text-white font-bold py-3 px-4 rounded-lg cursor-not-allowed">Admin Tidak Bisa Memesan</button>`;
                                } else {
                                    buttonHtml = `<a href="pesan_tiket.php?id_film=${data.id_film}" id="pesan-tiket-btn" class="w-full text-center block bg-blue-500 text-white font-bold py-3 px-4 rounded-lg hover:bg-blue-600 transition duration-300">Pesan Tiket Sekarang</a>`;
                                }

                                modalWrapper.innerHTML = `
                                    <div class="md:flex">
                                        <img src="${data.poster || 'https://placehold.co/400x600?text=N/A'}" alt="Poster Film" class="w-full md:w-1/3 h-auto object-cover rounded-l-lg self-start">
                                        <div class="p-6 md:p-8 flex-1">
                                            <h2 class="text-3xl lg:text-4xl font-bold mb-2 text-gray-800">${data.judul}</h2>
                                            <div class="flex items-center space-x-4 mb-4 text-sm text-gray-500">
                                                <span>${data.genre}</span><span>|</span><span>${data.durasi} min</span>
                                            </div>
                                            <p class="mb-6 text-gray-600 leading-relaxed">${data.sinopsis}</p>
                                            <div class="mt-6">${buttonHtml}</div>
                                        </div>
                                    </div>`;
                                modalWrapper.classList.add('opacity-100');
                                
                                const pesanTiketBtn = document.getElementById('pesan-tiket-btn');
                                if (pesanTiketBtn) {
                                    pesanTiketBtn.addEventListener('click', function(e) {
                                        if (!isUserLoggedIn) {
                                            e.preventDefault();
                                            alert('Anda harus login terlebih dahulu untuk memesan tiket.');
                                            window.location.href = 'login.php';
                                        }
                                    });
                                 }
                            } else {
                                modalWrapper.innerHTML = '<p class="text-center p-10 text-red-500">Gagal memuat detail film.</p>';
                            }
                        });
                });
            });
            
            const closeModal = () => modal.classList.remove('active');
            closeModalBtn.addEventListener('click', closeModal);
            modal.addEventListener('click', (event) => { if (event.target === modal) closeModal(); });
            document.addEventListener('keydown', (event) => { if (event.key === 'Escape') closeModal(); });
        });
    </script>
</body>
</html>
<?php
if ($conn) {
    $conn->close();
}
?>