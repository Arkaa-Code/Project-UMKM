<?php
session_start();
require_once '../config/database.php';

$category_id = (int)($_GET['id'] ?? 0);

if ($category_id <= 0) {
    $_SESSION['error_message'] = 'ID kategori tidak valid';
    header('Location: category-list.php');
    exit;
}

try {
    $db = getDB();
    
    // Check if category has equipment
    $stmt = $db->prepare("SELECT COUNT(*) FROM equipment WHERE category_id = ?");
    $stmt->execute([$category_id]);
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
        $_SESSION['error_message'] = 'Tidak dapat menghapus kategori yang masih memiliki equipment';
    } else {
        // Delete category
        $stmt = $db->prepare("DELETE FROM categories WHERE category_id = ?");
        $stmt->execute([$category_id]);
        $_SESSION['success_message'] = 'Kategori berhasil dihapus';
    }
} catch (PDOException $e) {
    $_SESSION['error_message'] = 'Gagal menghapus kategori: ' . $e->getMessage();
}

header('Location: category-list.php');
exit;
