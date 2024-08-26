<?php
// Koneksi Database
$server = "localhost";
$user = "root";
$pass = "";
$database = "inventaris";

// Buat koneksi
$koneksi = mysqli_connect($server, $user, $pass, $database);

// Cek koneksi
if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Pesan error
$error = '';

// Jika tombol simpan diklik
if (isset($_POST['bsimpan'])) {
    // Validasi form
    if (empty($_POST['tkode']) || empty($_POST['tnama']) || empty($_POST['tasal']) || empty($_POST['tjumlah']) || empty($_POST['tsatuan']) || empty($_POST['ttanggal_diterima'])) {
        $error = "Semua kolom harus diisi!";
    } else {
        // Pengujian apakah data akan diedit atau disimpan baru
        if (isset($_GET['hal']) && $_GET['hal'] == "edit") {
            // Data akan diedit
            $stmt = $koneksi->prepare("UPDATE tbarang SET nama=?, asal=?, jumlah=?, satuan=?, tanggal_diterima=? WHERE id_barang=?");
            $stmt->bind_param("ssissi", $_POST['tnama'], $_POST['tasal'], $_POST['tjumlah'], $_POST['tsatuan'], $_POST['ttanggal_diterima'], $_GET['id']);

            // Uji jika edit data sukses
            if ($stmt->execute()) {
                echo "<script>
                    alert('Edit Data Sukses');
                    document.location='index.php';
                </script>";
            } else {
                echo "<script>
                    alert('Edit Data Gagal');
                    document.location='index.php';
                </script>";
            }
            $stmt->close();
        } else {
            // Data akan disimpan baru
            $stmt = $koneksi->prepare("INSERT INTO tbarang (kode, nama, asal, jumlah, satuan, tanggal_diterima) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $_POST['tkode'], $_POST['tnama'], $_POST['tasal'], $_POST['tjumlah'], $_POST['tsatuan'], $_POST['ttanggal_diterima']);

            // Uji jika simpan data sukses
            if ($stmt->execute()) {
                echo "<script>
                    alert('Simpan Data Sukses');
                    document.location='index.php';
                </script>";
            } else {
                echo "<script>
                    alert('Simpan Data Gagal');
                    document.location='index.php';
                </script>";
            }
            $stmt->close();
        }
    }
}

// Deklarasi variabel untuk menampung data yang akan diedit
$vkode = "";
$vnama = "";
$vasal = "";
$vjumlah = "";
$vsatuan = "";
$vtanggal_diterima = "";

// Pengujian jika tombol edit atau hapus diklik
if (isset($_GET['hal'])) {
    // Pengujian jika edit data
    if ($_GET['hal'] == "edit") {
        // Tampilkan data yang akan diedit
        $stmt = $koneksi->prepare("SELECT * FROM tbarang WHERE id_barang = ?");
        $stmt->bind_param("i", $_GET['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();

        if ($data) {
            // Jika data ditemukan, maka data ditampung dalam variabel
            $vkode = $data['kode'];
            $vnama = $data['nama'];
            $vasal = $data['asal'];
            $vjumlah = $data['jumlah'];
            $vsatuan = $data['satuan'];
            $vtanggal_diterima = $data['tanggal_diterima'];
        }
        $stmt->close();
    } else if ($_GET['hal'] == "hapus") {
        // Persiapan hapus data
        $stmt = $koneksi->prepare("DELETE FROM tbarang WHERE id_barang = ?");
        $stmt->bind_param("i", $_GET['id']);

        // Uji jika hapus data sukses
        if ($stmt->execute()) {
            echo "<script>
                alert('Hapus Data Sukses');
                document.location='index.php';
            </script>";
        } else {
            echo "<script>
                alert('Hapus Data Gagal');
                document.location='index.php';
            </script>";
        }
        $stmt->close();
    }
}

// Penanganan pencarian
$keyword = isset($_POST['keyword']) ? $_POST['keyword'] : '';

// Query untuk mendapatkan data barang
$sql = "SELECT * FROM tbarang WHERE nama LIKE ? OR kode LIKE ? ORDER BY id_barang DESC";
$stmt = $koneksi->prepare($sql);
$searchTerm = "%$keyword%";
$stmt->bind_param("ss", $searchTerm, $searchTerm);
$stmt->execute();
$result = $stmt->get_result();
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CRUD</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="style.css" rel="stylesheet" type="text/css">
    <style>
        /* Tambahkan gaya kustom jika diperlukan */
        .footer {
            background-color: #D7CDC6;
            color: #000;
        }
    </style>
</head>

<body>
    <!-- Awal kontainer -->
    <div class="container mt-5">
        <!-- <h3 class="text-center">DATA INVENTARIS</h3> -->
        <h1 class="text-center">PT. AWINATAX</h1>

        <!-- Bagian input barang -->
        <div class="row">
            <div class="col-md-12 col-lg-8 mx-auto">
                <div class="card mt-3">
                    <div class="card-header bg-secondary text-light">
                        Form Input Data Barang
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <?= $error ?>
                            </div>
                        <?php endif; ?>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Kode Barang</label>
                                <input type="text" class="form-control" placeholder="Masukkan Kode Barang" name="tkode" value="<?= htmlspecialchars($vkode) ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Nama Barang</label>
                                <input type="text" class="form-control" placeholder="Masukkan Nama Barang" name="tnama" value="<?= htmlspecialchars($vnama) ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Asal Barang</label>
                                <select class="form-select" name="tasal">
                                    <option value="Pembelian" <?= $vasal == 'Pembelian' ? 'selected' : '' ?>>Pembelian</option>
                                    <option value="Hibah" <?= $vasal == 'Hibah' ? 'selected' : '' ?>>Hibah</option>
                                    <option value="Sumbangan" <?= $vasal == 'Sumbangan' ? 'selected' : '' ?>>Sumbangan</option>
                                    <option value="Bantuan" <?= $vasal == 'Bantuan' ? 'selected' : '' ?>>Bantuan</option>
                                </select>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Jumlah Barang</label>
                                        <input type="number" class="form-control" placeholder="Masukkan Jumlah Barang" name="tjumlah" value="<?= htmlspecialchars($vjumlah) ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Satuan</label>
                                        <select class="form-select" name="tsatuan">
                                            <option value="Unit" <?= $vsatuan == 'Unit' ? 'selected' : '' ?>>Unit</option>
                                            <option value="Kotak" <?= $vsatuan == 'Kotak' ? 'selected' : '' ?>>Kotak</option>
                                            <option value="Pcs" <?= $vsatuan == 'Pcs' ? 'selected' : '' ?>>Pcs</option>
                                            <option value="Box" <?= $vsatuan == 'Box' ? 'selected' : '' ?>>Box</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Tanggal diterima</label>
                                        <input type="date" class="form-control" name="ttanggal_diterima" value="<?= htmlspecialchars($vtanggal_diterima) ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="text-center">
                                <hr>
                                <button class="btn btn-primary" name="bsimpan" type="submit">Simpan</button>
                                <button class="btn btn-danger" name="bkosong" type="reset">Kosongkan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- Akhir Form -->

        <!-- Bagian pencarian -->
        <div class="row mt-3">
            <div class="col-md-12 col-lg-11 mx-auto">
                <div class="card">
                    <div class="card-header bg-secondary text-light">
                        Pencarian Data Barang
                    </div>
                    <div class="card-body">
                        <form method="POST" class="d-flex">
                            <input type="text" class="form-control me-2" placeholder="Cari berdasarkan kode atau nama" name="keyword" value="<?= htmlspecialchars($keyword) ?>">
                            <button class="btn btn-primary" type="submit">Cari</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- Akhir Pencarian -->

        <!-- Bagian data barang -->
        <div class="row mt-3">
            <div class="col-md-12 col-lg-11 mx-auto">
                <div class="card">
                    <div class="card-header bg-secondary text-light">
                        Data Barang
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Kode Barang</th>
                                    <th>Nama Barang</th>
                                    <th>Asal Barang</th>
                                    <th>Jumlah Barang</th>
                                    <th>Satuan Barang</th>
                                    <th>Tanggal diterima</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                while ($data = $result->fetch_assoc()) :
                                ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><?= htmlspecialchars($data['kode']) ?></td>
                                        <td><?= htmlspecialchars($data['nama']) ?></td>
                                        <td><?= htmlspecialchars($data['asal']) ?></td>
                                        <td><?= htmlspecialchars($data['jumlah']) ?></td>
                                        <td><?= htmlspecialchars($data['satuan']) ?></td>
                                        <td><?= htmlspecialchars($data['tanggal_diterima']) ?></td>
                                        <td>
                                            <a href="index.php?hal=edit&id=<?= $data['id_barang'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                            <a href="index.php?hal=hapus&id=<?= $data['id_barang'] ?>" onclick="return confirm('Apakah yakin ingin menghapus data ini?')" class="btn btn-danger btn-sm">Hapus</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Akhir kontainer -->

    <!-- Awal footer -->
    <footer class="footer text-dark mt-5 py-2 text-center">
        <div class="container">
            <p>&copy; 2024 AWINATAX. All rights reserved.</p>
        </div>
    </footer>
    <!-- Akhir footer -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-O7VH+ax5f3FFInzUpfVeAIQrtbAPFnDD4tXkzFbh3srBCThMY4fRb3BrtR/eNq1s" crossorigin="anonymous"></script>
</body>

</html>
