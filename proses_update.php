<?php
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id_barang'];
    $nama = $_POST['nama_barang'];
    $deskripsi = $_POST['deskripsi_barang'];
    $harga = $_POST['harga_barang'];
    $stok = $_POST['stok_barang'];

    try {
        if (!empty($id)) {
            $sql = "UPDATE Tb_Barang SET Nama_barang=?, Deskripsi_barang=?, Harga_barang=?, Stok_barang=? WHERE Id_barang=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nama, $deskripsi, $harga, $stok, $id]);
            $status = "updated";
        } else {
            $sql = "INSERT INTO Tb_Barang (Nama_barang, Deskripsi_barang, Harga_barang, Stok_barang) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nama, $deskripsi, $harga, $stok]);
            $status = "added";
        }
        header("Location: list_barang.php?status=" . $status);
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}
?>