-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 12 Jun 2025 pada 20.33
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `movie_booking`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `admin`
--

CREATE TABLE `admin` (
  `id_admin` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `admin`
--

INSERT INTO `admin` (`id_admin`, `username`, `password`) VALUES
(3, 'admin', '$2y$10$cLZgzcxhNq2TqObGfT8kl.fd4LBscdEUMZC72TJyuhnellIjlWMNG'),
(4, 'yoga', '$2y$10$YTPeGa6B0PccXkoyJ1mVEOsByVCZ2cCSDJEMRlbGNiG9ZHmn.3yBW'),
(5, 'yoga1', '$2y$10$SqMHmxlTqZIs3lDxrzBN1OTCdGzryV5l4qHTfrKpejqcJ.ZrwbXE2');

-- --------------------------------------------------------

--
-- Struktur dari tabel `film`
--

CREATE TABLE `film` (
  `id_film` int(11) NOT NULL,
  `judul` varchar(100) NOT NULL,
  `genre` varchar(50) DEFAULT NULL,
  `durasi` smallint(50) NOT NULL,
  `sinopsis` text DEFAULT NULL,
  `poster` varchar(255) DEFAULT NULL,
  `gambar_latar` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `film`
--

INSERT INTO `film` (`id_film`, `judul`, `genre`, `durasi`, `sinopsis`, `poster`, `gambar_latar`) VALUES
(16, 'DASIM', 'Horor', 99, '\"Dasim\" adalah film horor Indonesia yang diangkat dari kisah nyata, mengisahkan pasangan muda yang baru menikah namun diteror oleh makhluk gaib bernama Dasim — jin pemecah rumah tangga dalam kepercayaan lama. Gangguan tak kasat mata mulai merusak hubungan mereka, memunculkan konflik, kecurigaan, dan ketakutan. Dengan latar adat dan budaya lokal, film ini mengajak penonton menyelami teror yang lahir dari dalam rumah sendiri.', 'posters/1749704717_684a600dd4c99_Gambar_WhatsApp_2025-06-12_pukul_11.40.56_d56a7167.jpg', 'posters/1749704717_684a600dd5a02_Gambar_WhatsApp_2025-06-12_pukul_11.40.56_d56a7167.jpg'),
(17, 'Tak Ingin Usai di Sini', 'Romance', 108, '\"Tak Ingin Usai di Sini\" adalah film drama romantis yang mengangkat kisah tentang cinta muda, kenangan, dan perasaan yang sulit dilepaskan. Film ini berfokus pada dua tokoh utama yang sedang berada dalam fase penting hubungan mereka — antara mempertahankan atau melepaskan.\r\n\r\nDiwarnai dengan kehangatan, nostalgia, dan obrolan-obrolan sederhana yang sarat makna, keduanya mencoba mencari arti dari hubungan yang mereka jalani. Apakah cinta cukup untuk bertahan, atau justru waktu yang memaksa mereka untuk berpisah?\r\n\r\nFilm ini menyentuh perasaan banyak penonton lewat dialog yang jujur, adegan-adegan intim nan sederhana, serta chemistry yang kuat antara karakter.', 'posters/1749705018_684a613ae3e68_Gambar_WhatsApp_2025-06-12_pukul_11.40.58_8cec8d7d.jpg', 'posters/1749705018_684a613ae4563_Gambar_WhatsApp_2025-06-12_pukul_11.40.58_8cec8d7d.jpg'),
(18, 'JALAN PULANG', 'Horor, Thriller', 97, 'Film ini berkisah tentang sekelompok orang yang tanpa sengaja masuk ke sebuah desa terkutuk yang tidak tercatat di peta dan dihuni oleh sosok-sosok misterius serta makhluk gaib haus darah. Di tengah usaha mereka untuk menemukan jalan pulang, satu per satu dari mereka menghilang secara misterius, terjebak dalam ritual gelap dan kutukan yang telah berlangsung turun-temurun.\r\n\r\nSosok menyeramkan dalam sangkar dan mayat bergelimpangan menjadi pertanda bahwa mereka tidak hanya harus melarikan diri, tapi juga mengungkap rahasia kelam desa tersebut sebelum semuanya terlambat.', 'posters/1749705294_684a624e52a8d_Gambar_WhatsApp_2025-06-12_pukul_11.40.58_c9fd1f2f.jpg', 'posters/1749705294_684a624e5343c_Gambar_WhatsApp_2025-06-12_pukul_11.40.58_c9fd1f2f.jpg'),
(19, 'TENUNG', 'Horor', 107, 'Tenung mengisahkan seorang wanita muda yang tanpa sengaja membuka masa lalu kelam keluarganya, ketika ia menemukan sebuah kain misterius peninggalan neneknya. Kain itu ternyata bukan sembarang kain, melainkan hasil dari ritual tenung—sebuah ilmu hitam yang menggunakan kekuatan magis untuk mencelakai orang lain.\r\n\r\nSejak kain itu muncul, berbagai kejadian mengerikan mulai menghantui rumahnya. Sosok perempuan tua berpakaian hitam, suara-suara gaib, dan gangguan supranatural tak kunjung berhenti. Ia harus mencari tahu siapa yang memulai tenung itu, dan bagaimana menghentikannya, sebelum semua orang yang ia cintai menjadi korban.', 'posters/1749705526_684a63369a357_Gambar_WhatsApp_2025-06-12_pukul_11.41.00_87630ae6.jpg', 'posters/1749705526_684a63369aa76_Gambar_WhatsApp_2025-06-12_pukul_11.41.00_87630ae6.jpg'),
(20, 'Keluarga Super Irit', 'Comedy', 116, 'Keluarga Sukaharta—ayah Toni (Dwi Sasono), ibu Linda (Widi Mulia), dan ketiga anaknya Sally, Billy, serta Kenny—dulu hidup boros dan terlilit utang. Saat Toni kehilangan pekerjaan dan mengalami pemotongan gaji besar, mereka pun menerapkan kehidupan super hemat alias frugal living: menumpang Wi‑Fi tetangga, makan gratis di resepsi, menjatah air, bahkan menagih uang parkir di kantor kecamatan. Namun ketika harus pindah ke tempat tinggal sempit bekas kandang burung di atas ruko, tekanan kehidupan makin berat. Ditambah sanak saudara yang mulai menumpang, keluarga ini diuji ketahanannya. Dengan jurus hemat keluarga (TRIK), mereka berusaha bertahan, menjaga keharmonisan, dan mencari jalan untuk kembali ke kehidupan semula ', 'posters/1749705866_684a648acd27f_Gambar_WhatsApp_2025-06-12_pukul_11.40.56_767ea196.jpg', 'posters/1749705866_684a648acdca2_Gambar_WhatsApp_2025-06-12_pukul_11.40.56_767ea196.jpg'),
(21, 'Tabbayun', 'Drama', 112, '\"Tabayyun\" mengisahkan tentang perjalanan seorang pria dan wanita yang dipertemukan dalam situasi tak terduga, membawa serta anak kecil yang menjadi penghubung di antara mereka. Di tengah konflik keluarga dan tekanan sosial, mereka berusaha mencari kebenaran, memahami satu sama lain, dan menyembuhkan luka masa lalu.\r\n\r\nDiadaptasi dari novel karya Ilyas Bacthiar, film ini mengangkat tema klarifikasi, maaf, dan kejujuran dalam menghadapi fitnah dan prasangka. Dengan latar suasana religius dan nilai-nilai moral yang kuat, Tabayyun menyoroti pentingnya mencari kebenaran sebelum menghakimi, demi menjaga keharmonisan keluarga dan harga diri.', 'posters/1749706490_684a66fa19456_Gambar_WhatsApp_2025-06-12_pukul_11.41.00_b5215fc4.jpg', 'posters/1749706490_684a66fa19c79_Gambar_WhatsApp_2025-06-12_pukul_11.41.00_b5215fc4.jpg'),
(22, 'Kitab Sujjin & Illiyyin', 'Horor, Thriller', 110, 'Dalam ajaran spiritual, Sujjin dan Illiyyin adalah dua kitab yang mencatat segala amal perbuatan manusia—baik maupun buruk. Namun, dalam film ini, konsep tersebut diangkat menjadi mimpi buruk yang nyata.\r\n\r\n\"Kitab Sujjin & Illiyyin\" menceritakan tentang sebuah keluarga yang terjebak dalam kutukan mengerikan setelah salah satu anggotanya melakukan dosa besar yang mengundang murka gaib. Mereka mulai mengalami kejadian-kejadian horor yang mengungkap rahasia masa lalu keluarga dan catatan kelam perbuatan mereka yang selama ini tersembunyi.\r\n\r\nSatu per satu anggota keluarga menghadapi teror berdarah dari makhluk-makhluk yang muncul sebagai konsekuensi dari perbuatan mereka. Ketegangan memuncak ketika mereka menyadari bahwa jalan keluar hanya bisa ditemukan dengan menebus dosa dan mengungkap kebenaran yang selama ini dikubur.', 'posters/1749706664_684a67a8e2ffb_Gambar_WhatsApp_2025-06-12_pukul_11.41.00_b085d182.jpg', 'posters/1749706664_684a67a8e37ac_Gambar_WhatsApp_2025-06-12_pukul_11.41.00_b085d182.jpg'),
(23, 'Jodoh 3 Bujang', 'Comedy, Romance, Drama', 100, 'Tiga pria bujang yang sudah cukup umur tapi masih betah melajang, mendadak harus menghadapi kenyataan pahit: mereka diancam akan dijodohkan oleh keluarga masing-masing! Keadaan makin kacau ketika keluarga mereka saling bersaing untuk \"menyodorkan\" calon pasangan yang menurut mereka paling cocok.\r\n\r\nKisah menjadi semakin rumit dan lucu ketika ketiganya justru terlibat dalam hubungan yang tidak terduga dengan wanita-wanita yang memiliki kepribadian unik dan latar belakang berbeda. Dari momen canggung hingga kesalahpahaman besar, ketiganya belajar tentang cinta, komitmen, dan makna jodoh yang sesungguhnya.\r\n\r\nFilm ini diangkat dari kisah nyata dan menyajikan cerita yang menghibur namun menyentuh, cocok untuk ditonton segala usia.', 'posters/1749706811_684a683b6be49_Gambar_WhatsApp_2025-06-12_pukul_11.40.59_24c54164.jpg', 'posters/1749706811_684a683b6c539_Gambar_WhatsApp_2025-06-12_pukul_11.40.59_24c54164.jpg'),
(24, 'Legenda Kelam Malin Kundang', 'Horor, Thriller', 105, 'Film ini merupakan reinterpretasi kelam dari legenda rakyat Indonesia, Malin Kundang, dalam versi yang penuh teror psikologis dan misteri mendalam. Dikisahkan bahwa sebuah batu yang selama ini dianggap sebagai peninggalan kutukan, ternyata menyimpan ingatan dan dendam dari masa lalu yang tak pernah selesai.\r\n\r\nSeorang pria bernama Rio (diperankan oleh Rio Dewanto) tanpa sengaja memicu bangkitnya arwah masa lalu saat kembali ke kampung halamannya. Bersama seorang wanita yang misterius dan memiliki hubungan gelap dengan kisah Malin Kundang, mereka mulai dihantui oleh kejadian-kejadian mengerikan yang berkaitan dengan kutukan yang belum berakhir.\r\n\r\nApakah Malin benar-benar dikutuk karena durhaka, atau ada rahasia kelam yang tersembunyi di balik legenda itu?\r\n\r\n', 'posters/1749706953_684a68c9a90a0_Gambar_WhatsApp_2025-06-12_pukul_11.40.54_9042adb0.jpg', 'posters/1749706953_684a68c9a978a_Gambar_WhatsApp_2025-06-12_pukul_11.40.54_9042adb0.jpg'),
(25, 'GJLS Ibuku ibu-ibu', 'Comedy, Drama', 95, 'Empat sahabat, satu buku, dan kisah ibu-ibu yang tidak biasa.\r\n\r\nFilm ini menceritakan petualangan kocak dari tiga sahabat konyol: Rigen, Rispo, dan Hifdzi, yang tanpa sengaja menemukan sebuah buku harian milik ibu-ibu di lingkungan mereka. Awalnya mereka berniat iseng membaca untuk lucu-lucuan, tapi ternyata isi buku itu penuh dengan rahasia kehidupan, drama masa muda, dan kisah cinta tak terduga dari para ibu yang mereka kenal sejak kecil.\r\n\r\nDalam pencarian pemilik buku, mereka justru semakin dekat dengan para ibu-ibu itu—termasuk ibu mereka sendiri. Dari situ, banyak momen lucu, menyentuh, dan membekas yang mengajarkan mereka soal cinta, pengorbanan, dan pentingnya menghargai orang tua, terutama ibu.', 'posters/1749707097_684a695932689_Gambar_WhatsApp_2025-06-12_pukul_11.40.58_090775bf.jpg', 'posters/1749707097_684a69593333b_Gambar_WhatsApp_2025-06-12_pukul_11.40.58_090775bf.jpg'),
(26, 'Gundik', 'Horor, Comedy', 115, 'Empat sahabat, satu buku, dan kisah ibu-ibu yang tidak biasa.\r\n\r\nFilm ini menceritakan petualangan kocak dari tiga sahabat konyol: Rigen, Rispo, dan Hifdzi, yang tanpa sengaja menemukan sebuah buku harian milik ibu-ibu di lingkungan mereka. Awalnya mereka berniat iseng membaca untuk lucu-lucuan, tapi ternyata isi buku itu penuh dengan rahasia kehidupan, drama masa muda, dan kisah cinta tak terduga dari para ibu yang mereka kenal sejak kecil.\r\n\r\nDalam pencarian pemilik buku, mereka justru semakin dekat dengan para ibu-ibu itu—termasuk ibu mereka sendiri. Dari situ, banyak momen lucu, menyentuh, dan membekas yang mengajarkan mereka soal cinta, pengorbanan, dan pentingnya menghargai orang tua, terutama ibu.', 'posters/1749707295_684a6a1f54640_Gambar_WhatsApp_2025-06-12_pukul_11.40.57_02fbae84.jpg', 'posters/1749707295_684a6a1f54cb3_Gambar_WhatsApp_2025-06-12_pukul_11.40.57_02fbae84.jpg'),
(27, 'How to Train Your Dragon', 'Adventure, Drama', 98, '\"How to Train Your Dragon\" adalah film animasi petualangan yang mengikuti kisah Hiccup, seorang remaja Viking yang tinggal di pulau Berk, tempat di mana berburu naga adalah tradisi turun-temurun. Namun, hidup Hiccup berubah ketika ia berhasil menangkap seekor Night Fury—naga paling langka dan ditakuti—tetapi tidak mampu membunuhnya. Sebaliknya, ia menjalin persahabatan yang tak terduga dengan naga tersebut yang ia beri nama Toothless. Persahabatan ini membawa Hiccup pada pemahaman baru bahwa naga sebenarnya bukan musuh umat manusia. Bersama Toothless, Hiccup mencoba mengubah cara pandang masyarakatnya terhadap naga, meskipun harus menghadapi konflik dengan ayahnya sendiri, Stoick, kepala suku Berk.', 'posters/1749707585_684a6b4128cf5_Gambar_WhatsApp_2025-06-12_pukul_11.40.57_83ddc7fa.jpg', 'posters/1749707585_684a6b4129503_Gambar_WhatsApp_2025-06-12_pukul_11.40.57_83ddc7fa.jpg'),
(28, 'Dendam Malam Kelam', 'Thriller', 100, 'Seorang pria terbangun di sebuah rumah asing, tanpa ingatan akan kejadian sebelumnya, hanya untuk menemukan seorang wanita yang telah meninggal di kamar mandi. Dalam usahanya membuktikan bahwa ia tidak bersalah, pria ini justru terperangkap dalam jaringan rahasia, dendam masa lalu, dan kebohongan yang kelam. Film ini menyuguhkan misteri yang penuh ketegangan, di mana setiap petunjuk membawa kita semakin dekat pada kebenaran yang mengerikan.', 'posters/1749707707_684a6bbbb4c82_Gambar_WhatsApp_2025-06-12_pukul_11.40.57_c7af2e91.jpg', 'posters/1749707707_684a6bbbb571e_Gambar_WhatsApp_2025-06-12_pukul_11.40.57_c7af2e91.jpg'),
(29, 'Hi-Five', 'Comedy, Action', 112, 'Hi-Five adalah film aksi-komedi fantasi asal Korea Selatan yang menceritakan kisah lima orang biasa yang tiba-tiba mendapatkan kekuatan super setelah menerima transplantasi organ dari makhluk luar angkasa. Mereka harus belajar mengendalikan kekuatan tersebut, menghadapi kekacauan yang ditimbulkan, dan bekerja sama sebagai tim untuk melawan ancaman besar yang membahayakan umat manusia. Misi mereka tidak hanya menyelamatkan dunia, tetapi juga mencari jati diri masing-masing melalui persahabatan dan kerja sama.', 'posters/1749707837_684a6c3dcc573_Gambar_WhatsApp_2025-06-12_pukul_11.40.59_64186e98.jpg', 'posters/1749707837_684a6c3dccbdc_Gambar_WhatsApp_2025-06-12_pukul_11.40.59_64186e98.jpg');

-- --------------------------------------------------------

--
-- Struktur dari tabel `jadwal`
--

CREATE TABLE `jadwal` (
  `id_jadwal` int(11) NOT NULL,
  `id_film` int(11) DEFAULT NULL,
  `id_studio` int(11) DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `jam` time DEFAULT NULL,
  `harga` int(11) NOT NULL DEFAULT 35000
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `jadwal`
--

INSERT INTO `jadwal` (`id_jadwal`, `id_film`, `id_studio`, `tanggal`, `jam`, `harga`) VALUES
(24, 16, 7, '2025-11-11', '12:12:00', 500000),
(30, 16, 7, '2025-12-15', '20:00:00', 40000),
(31, 28, 5, '2025-05-13', '20:00:00', 50000),
(32, 26, 5, '2025-09-04', '09:00:00', 50000),
(33, 25, 7, '2025-09-06', '09:00:00', 50000),
(34, 25, 7, '2025-04-12', '12:00:00', 40000),
(35, 29, 5, '2025-02-10', '12:30:00', 70000),
(36, 26, 7, '2025-10-11', '04:00:00', 40000),
(37, 27, 5, '2025-02-10', '22:00:00', 35000),
(38, 27, 5, '2025-12-12', '13:20:00', 30000),
(39, 18, 5, '2025-09-20', '15:00:00', 40000),
(40, 28, 7, '2026-09-22', '00:00:00', 50000),
(41, 23, 5, '2025-07-20', '20:00:00', 30000),
(42, 23, 5, '2025-05-20', '05:00:00', 40000),
(43, 20, 5, '2025-12-12', '16:00:00', 45000),
(44, 20, 5, '2025-08-20', '17:00:00', 40000),
(45, 22, 5, '2025-06-20', '05:00:00', 50000),
(46, 22, 5, '2025-10-30', '19:00:00', 40000),
(47, 22, 5, '2025-12-20', '15:20:00', 50000),
(48, 22, 5, '2025-05-20', '10:20:00', 50000),
(49, 24, 7, '2025-04-20', '16:00:00', 55000),
(50, 24, 7, '2025-12-02', '16:00:00', 45000),
(51, 21, 5, '2025-05-12', '20:00:00', 50000),
(52, 21, 5, '2025-11-30', '09:00:00', 50000),
(53, 21, 5, '2025-06-05', '18:00:00', 50000),
(54, 17, 7, '2025-09-12', '07:00:00', 55000),
(55, 17, 7, '2025-05-20', '19:00:00', 30000),
(56, 19, 7, '2025-02-20', '14:00:00', 35000),
(57, 19, 7, '2025-08-20', '13:50:00', 50000),
(58, 19, 7, '2025-05-20', '19:00:00', 40000),
(59, 28, 5, '2025-12-11', '20:00:00', 50000);

-- --------------------------------------------------------

--
-- Struktur dari tabel `kursi_terpesan`
--

CREATE TABLE `kursi_terpesan` (
  `id_kursi` int(11) NOT NULL,
  `id_jadwal` int(11) DEFAULT NULL,
  `id_pemesanan` int(11) NOT NULL,
  `nomor_kursi` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kursi_terpesan`
--

INSERT INTO `kursi_terpesan` (`id_kursi`, `id_jadwal`, `id_pemesanan`, `nomor_kursi`) VALUES
(40, 38, 17, 'A3'),
(41, 38, 17, 'A4'),
(42, 38, 17, 'A5'),
(43, 41, 18, 'B5'),
(44, 41, 18, 'B6'),
(45, 59, 19, 'C2');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pemesanan`
--

CREATE TABLE `pemesanan` (
  `id_pemesanan` int(11) NOT NULL,
  `id_jadwal` int(11) DEFAULT NULL,
  `id_user` int(11) DEFAULT NULL,
  `jumlah_tiket` int(11) DEFAULT NULL,
  `total_harga` int(11) NOT NULL,
  `kode_booking` varchar(50) NOT NULL,
  `tgl_pemesanan` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pemesanan`
--

INSERT INTO `pemesanan` (`id_pemesanan`, `id_jadwal`, `id_user`, `jumlah_tiket`, `total_harga`, `kode_booking`, `tgl_pemesanan`) VALUES
(17, 38, 2, 3, 90000, 'INV-1749746094419', '2025-06-12 23:34:54'),
(18, 41, 2, 2, 60000, 'INV-1749751375953', '2025-06-13 01:02:55'),
(19, 59, 2, 1, 50000, 'INV-1749753180539', '2025-06-13 01:33:00');

-- --------------------------------------------------------

--
-- Struktur dari tabel `studio`
--

CREATE TABLE `studio` (
  `id_studio` int(11) NOT NULL,
  `nama_studio` varchar(50) DEFAULT NULL,
  `kapasitas` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `studio`
--

INSERT INTO `studio` (`id_studio`, `nama_studio`, `kapasitas`) VALUES
(5, 'studio1', 30),
(7, 'studio2', 40);

-- --------------------------------------------------------

--
-- Struktur dari tabel `user`
--

CREATE TABLE `user` (
  `id_user` int(11) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `tanggal_daftar` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `user`
--

INSERT INTO `user` (`id_user`, `nama_lengkap`, `email`, `password`, `no_hp`, `tanggal_daftar`) VALUES
(1, 'mohammad yoga saputra', 'yogawoy8@gmail.com', '$2y$10$EIh5YFXNT//.FyvmcIDrSOzszpat5J3QjM42J2KdoN8dc1W4srmvi', '08798', '2025-06-08 09:41:55'),
(2, 'mohammad yoga saputra', 'yogawoy08@gmail.com', '$2y$10$PWrOKkzfSglMIXgSpnoWjO0AUPkzLeZ2T2uKL89lK5bLqRROOmQDi', '623587', '2025-06-08 09:43:34'),
(3, 'mohammad yoga saputra', 'yogaaduhai@gamil.com', '$2y$10$14zUaFD2NS05.ySNS/nbT.BMc9QxoosBsMXcxVOdgl//DGJC8f6Za', '62941691877', '2025-06-08 12:51:55'),
(4, 'zaky', 'zaky@gmail.com', '$2y$10$ixSdIy9lXaGKiDu229Kr..7RJ2GyE.hPIxb0QKu2CFtJVLu7Nc.y.', '', '2025-06-09 10:40:43'),
(5, 'yoga adugai', 'yogawoy1@gmail.com', '$2y$10$HNaVD.8tN0v8gQME71lQWOAQ8Au5b640FqQP3Lk15I5.dtQ64JHdG', '08562317', '2025-06-10 14:59:00'),
(6, 'adit tot', 'adit@gmail.com', '$2y$10$DLyvkuB8fQZdw7X9N.vEHO11n.rSxPiHvUXVC7gGA4r6wqu1mhasS', '08734767', '2025-06-12 18:11:03'),
(7, 'ieqfguyfe', 'ygqef@gmai.com', '$2y$10$IV2TCso7418nSCL5wQyxGecSKQVxNAdjvxWCSfLdUM1bsxTrrt6qa', '0884', '2025-06-12 18:11:33');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id_admin`);

--
-- Indeks untuk tabel `film`
--
ALTER TABLE `film`
  ADD PRIMARY KEY (`id_film`);

--
-- Indeks untuk tabel `jadwal`
--
ALTER TABLE `jadwal`
  ADD PRIMARY KEY (`id_jadwal`),
  ADD KEY `id_film` (`id_film`),
  ADD KEY `id_studio` (`id_studio`);

--
-- Indeks untuk tabel `kursi_terpesan`
--
ALTER TABLE `kursi_terpesan`
  ADD PRIMARY KEY (`id_kursi`),
  ADD KEY `id_jadwal` (`id_jadwal`),
  ADD KEY `fk_pemesanan_kursi` (`id_pemesanan`);

--
-- Indeks untuk tabel `pemesanan`
--
ALTER TABLE `pemesanan`
  ADD PRIMARY KEY (`id_pemesanan`),
  ADD UNIQUE KEY `kode_booking` (`kode_booking`),
  ADD KEY `id_jadwal` (`id_jadwal`),
  ADD KEY `id_user` (`id_user`);

--
-- Indeks untuk tabel `studio`
--
ALTER TABLE `studio`
  ADD PRIMARY KEY (`id_studio`);

--
-- Indeks untuk tabel `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `admin`
--
ALTER TABLE `admin`
  MODIFY `id_admin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `film`
--
ALTER TABLE `film`
  MODIFY `id_film` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT untuk tabel `jadwal`
--
ALTER TABLE `jadwal`
  MODIFY `id_jadwal` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT untuk tabel `kursi_terpesan`
--
ALTER TABLE `kursi_terpesan`
  MODIFY `id_kursi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT untuk tabel `pemesanan`
--
ALTER TABLE `pemesanan`
  MODIFY `id_pemesanan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT untuk tabel `studio`
--
ALTER TABLE `studio`
  MODIFY `id_studio` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `user`
--
ALTER TABLE `user`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `jadwal`
--
ALTER TABLE `jadwal`
  ADD CONSTRAINT `jadwal_ibfk_1` FOREIGN KEY (`id_film`) REFERENCES `film` (`id_film`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `jadwal_ibfk_2` FOREIGN KEY (`id_studio`) REFERENCES `studio` (`id_studio`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `kursi_terpesan`
--
ALTER TABLE `kursi_terpesan`
  ADD CONSTRAINT `fk_pemesanan_kursi` FOREIGN KEY (`id_pemesanan`) REFERENCES `pemesanan` (`id_pemesanan`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `kursi_terpesan_ibfk_1` FOREIGN KEY (`id_jadwal`) REFERENCES `jadwal` (`id_jadwal`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `pemesanan`
--
ALTER TABLE `pemesanan`
  ADD CONSTRAINT `pemesanan_ibfk_1` FOREIGN KEY (`id_jadwal`) REFERENCES `jadwal` (`id_jadwal`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `pemesanan_ibfk_2` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
