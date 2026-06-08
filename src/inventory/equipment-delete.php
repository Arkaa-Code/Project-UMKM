<?php
session_start();
require_once '../config/database.php';

$equipment_id = (int)($_GET['id'] ?? 0);

if ($equipment_id <= 0) {
    $_SESSION['error_message'] = 'ID equipment tidak valid';
    header('Location: equipment-list.php');
    exit;
}

try {
    $db = getDB();
    
    // Delete equipment
    $stmt = $db->prepare("DELETE FROM equipment WHERE equipment_id = ?");
    $stmt->execute([$equipment_id]);
    $_SESSION['success_message'] = 'Equipment berhasil dihapus';
} catch (PDOException $e) {
    $_SESSION['error_message'] = 'Gagal menghapus equipment: ' . $e->getMessage();
}

header('Location: equipment-list.php');
exit;
