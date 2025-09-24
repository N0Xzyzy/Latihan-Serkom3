<?php
session_start();
$conn = new mysqli("localhost","root","","laundry"); //<-Koneksi
if ($conn->connect_error){
    die("Koneksi Gagal". $conn->connect_error);
}

$res = $conn->query("SELECT t.*, p.nama, j.jenis, j.harga
                                FROM transaksi t
                                JOIN pelanggan p ON t.id_pelanggan = p.id
                                JOIN jenis j ON t.id_jenis = j.id"); //<-Pilih data untuk tampil ke tabel

$edit = null;
if(isset($_GET["edit"])){
    $id = (int)$_GET["edit"];
    $edit = $conn->query("SELECT t.*, j.harga
                                    FROM transaksi t
                                    JOIN pelanggan p ON t.id_pelanggan = p.id
                                    JOIN jenis j ON t.id_jenis = j.id
                                    WHERE t.id = $id")->fetch_assoc(); //<-Untuk fungsi edit dan dropdown
}

function getOptions($conn, $table, $valField, $labelField, $selected=null, $extraField=null) {
    $opt = '';
    $q = $conn->query("SELECT * FROM $table");
    while ($r = $q->fetch_assoc()) {
        $sel = $r[$valField]==$selected ? 'selected' : '';
        $extra = $extraField ? 'data-harga="'.$r[$extraField].'"' : '';
        $opt .= "<option value='{$r[$valField]}' $sel $extra>".htmlspecialchars($r[$labelField])."</option>";
    }
    return $opt;
} //<-Fungsi dropdown

if (isset($_POST["simpan"])){
    $id_pelanggan = mysqli_real_escape_string($conn, $_POST["id_pelanggan"]);
    $id_jenis = mysqli_real_escape_string($conn, $_POST["id_jenis"]);
    $jumlah = (int)$_POST["jumlah"];
    $total = (int)$_POST["total"];
    $tgl_masuk = date("Y-m-d");
    $tgl_keluar = date("Y-m-d", strtotime("+3 days"));
    if (!empty($_POST["id_edit"])){
        $id = (int)$_POST["id_edit"];
        $sql = "UPDATE transaksi SET
                id_pelanggan = '$id_pelanggan',
                tanggal_masuk = '$tgl_masuk',
                tanggal_keluar = '$tgl_keluar',
                id_jenis = '$id_jenis',
                jumlah = '$jumlah',
                total = '$total'
                WHERE id = '$id'";
    }else{
        $sql = "INSERT INTO transaksi (id_pelanggan, tanggal_masuk, tanggal_keluar, id_jenis, jumlah, total)
                VALUES ('$id_pelanggan', '$tgl_masuk', '$tgl_keluar', '$id_jenis', '$jumlah', '$total')";
    }
    $conn->query($sql);
    header("Location: index.php");
    exit;
} //<-Fungsi simpan

if (isset($_GET["hapus"])){
    $id = (int)$_GET["hapus"];
    $conn->query("DELETE FROM transaksi WHERE id = $id");
    header("Location: index.php");
    exit;
} //<-Fungsi Hapus
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD Laundry</title>
    <link rel="stylesheet" href="css/bootstrap.css">
</head>
<body>
    <h2 class="display-1">CRUD Laundry</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Nama</th>
            <th>Tanggal Masuk</th>
            <th>Tanggal Keluar</th>
            <th>Jenis</th>
            <th>Harga</th>
            <th>Jumlah</th>
            <th>Total</th>
            <th>Aksi</th>
        </tr>
        <?php while($row = $res->fetch_assoc()): ?>
        <tr>
            <td><?= $row['id']; ?></td>
            <td><?= htmlspecialchars($row['nama']); ?></td>
            <td><?= $row['tanggal_masuk']; ?></td>
            <td><?= $row['tanggal_keluar']; ?></td>
            <td><?= htmlspecialchars($row['jenis']); ?></td>
            <td><?= $row['harga']; ?></td>
            <td><?= $row['jumlah']; ?></td>
            <td><?= $row['total']; ?></td>
            <td>
                <a href="index.php?edit=<?php echo $row['id']; ?>">Edit</a>
                <a href="index.php?hapus=<?php echo $row['id']; ?>" onclick="return confirm('Hapus data?')">Hapus</a>
            </td>
        <?php endwhile; ?>
    </table>

    <form method="POST">
        <?php if($edit): ?><input type="hidden" name="id_edit" value="<?= $edit['id'] ?>"><?php endif; ?>
        <label for="id_pelanggan">Nama Pelanggan:</label>
        <select name="id_pelanggan" id="id_pelanggan" required>
            <option value="">--Pilih Opsi--</option>
            <?php echo getOptions($conn, 'pelanggan', 'id', 'nama', $edit['id_pelanggan'] ?? ''); ?>
        </select>

        <label for="id_jenis">Jenis:</label>
        <select name="id_jenis" id="id_jenis" onchange="updateHarga()">
            <option value="">--Pilih Opsi--</option>
            <?php echo getOptions($conn, 'jenis', 'id', 'jenis', $edit['id_jenis'] ?? null, 'harga');  ?>
        </select>

        <label for="harga">Harga:</label>
        <input type="number" name="harga" id="harga" value="<?php echo $edit['harga'] ?? '';?>" readonly>

        <label for="jumlah">Jumlah Barang</label>
        <input type="number" name="jumlah" id="jumlah" 
            value="<?= $edit['jumlah'] ?? '';?>" 
            required min="1" oninput="updateTotal()">

        <label for="total">Total Harga</label>
        <input type="number" name="total" id="total" 
            value="<?= $edit['total'] ?? '';?>" readonly>

        <button type="submit" name="simpan">Simpan</button>
        <?php if($edit):?>
            <a href="index.php">Batal</a>
        <?php endif;?>
    </form>

    <script>
        function updateHarga(){
            let sel=document.getElementById('id_jenis');
            let harga=sel.options[sel.selectedIndex]?.dataset.harga||0;
            document.getElementById('harga').value=harga;
            updateTotal();
        }
        function updateTotal(){
            let h = parseInt(document.getElementById('harga').value)||0;
            let j = parseInt(document.getElementById('jumlah').value)||0;
            document.getElementById('total').value=h*j;
        }
        window.onload = updateHarga;
    </script>
</body>
</html>