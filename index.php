<?php
session_start();
$conn = new mysqli("localhost","root","","laundry");
if ($conn->connect_error){
    die("Koneksi Gagal". $conn->connect_error);
}

$result = $conn->query("SELECT t.*, p.nama, j.jenis, j.harga
                                FROM transaksi t
                                JOIN pelanggan p ON t.id_pelanggan = p.id
                                JOIN jenis j ON t.id_jenis = j.id");

function getOptions($conn, $table, $valueField, $textField, $selected = null){
    $option = "";
    $result = mysqli_query($conn, "SELECT $valueField, $textField FROM $table");
    if ($result && mysqli_num_rows($result) > 0){
        while ($row = mysqli_fetch_assoc($result)){
            $isSelected = ($row[$valueField] == $selected) ? "selected" : "";
            $option .= '<option value="'.$row[$valueField].'" '.$isSelected.'>'.$row[$textField].'</option>';
        }
    }return $option;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD Laundry</title>
</head>
<body>
    <h2>CRUD Laundry</h2>
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
        <?php while($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $row['id']; ?></td>
            <td><?= htmlspecialchars($row['nama']); ?></td>
            <td><?= $row['tgl_masuk']; ?></td>
            <td><?= $row['tgl_keluar']; ?></td>
            <td><?= htmlspecialchars($row['jenis']); ?></td>
            <td><?= $row['harga']; ?></td>
            <td><?= $row['jumlah']; ?></td>
            <td><?= $row['total']; ?></td>
            <td>
                <a href="edit.php?id=<?php echo $row['id']; ?>">Edit</a>
                <a href="delete.php?id=<?php echo $row['id']; ?>">Delete</a>
            </td>
        <?php endwhile; ?>
    </table>

    <form action="POST">
        <label for="id_pelanggan">Nama Pelanggan:</label>
        <select name="id_pelanggan" id="id_pelanggan" required>
            <option value="">--Pilih Opsi--</option>
            <?php echo getOptions($conn, 'pelanggan', 'id', 'nama', $edit['nama_pelanggan']) ?? '' ?>
        </select>
        <label for="id_jenis">Jenis:</label>
        <select name="id_jenis" id="id_jenis">
            <option value="">--Pilih Opsi--</option>
            <?php echo getOptions($conn, 'jenis', 'id', 'jenis', $edit['jenis']) ?? '' ?>
        </select>
    </form>
</body>
</html>