<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php"); exit;
}
require '../config/db.php';
$stmt = $pdo->prepare("DELETE FROM detail_jadwal WHERE id_detail_jadwal=?");
$stmt->execute([intval($_GET['id'])]);
header("Location: dashboard.php");
exit;
