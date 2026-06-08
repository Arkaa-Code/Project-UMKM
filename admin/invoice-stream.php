<?php
// ============================================================
// Invoice Stream — Admin: Preview / Re-generate PDF
// File: admin/invoice-stream.php
// ============================================================
session_start();
require_once '../src/config/database.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(403);
    exit('Forbidden');
}

$reservation_id = (int)($_GET['id'] ?? 0);
if ($reservation_id <= 0) {
    http_response_code(400);
    exit('Invalid reservation ID');
}

$db = getDB();
$stmt = $db->prepare("SELECT * FROM reservations WHERE reservation_id = ?");
$stmt->execute([$reservation_id]);
$res = $stmt->fetch();

if (!$res) {
    http_response_code(404);
    exit('Reservation not found');
}

require_once '../src/includes/invoice-generator.php';

// ?regen=1 → save file to disk first (update DB), then stream
if (!empty($_GET['regen'])) {
    $inv_path = generateInvoicePDF($reservation_id, 'F');
    if ($inv_path) {
        $upd = $db->prepare("UPDATE reservations SET invoice_path = ? WHERE reservation_id = ?");
        $upd->execute([$inv_path, $reservation_id]);
    }
}

// Stream inline to browser
generateInvoicePDF($reservation_id, 'I');
