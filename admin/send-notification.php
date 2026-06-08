<?php
// ============================================================
// admin/send-notification.php
// AJAX handler — kirim WA atau Email server-side
// ============================================================

// Buffer semua output agar PHP warning/error tidak merusak JSON
ob_start();

// Tangkap semua error sebagai exception agar tidak keluar sebagai HTML
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
});

// Pastikan response selalu JSON, bahkan saat fatal error
register_shutdown_function(function() {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'error' => 'Fatal PHP error: ' . $err['message']]);
    }
});

try {
    session_start();
    if (empty($_SESSION['admin_id'])) {
        ob_clean();
        http_response_code(403);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'error' => 'Unauthorized']);
        exit;
    }

    header('Content-Type: application/json; charset=utf-8');

    require_once '../src/config/database.php';
    require_once '../src/includes/notification-helper.php';

    $db    = getDB();
    $resId = (int)($_POST['reservation_id'] ?? 0);
    $type  = $_POST['type'] ?? '';   // 'wa' atau 'email'

    if (!$resId || !in_array($type, ['wa', 'email'], true)) {
        ob_clean();
        echo json_encode(['success' => false, 'error' => 'Parameter tidak valid.']);
        exit;
    }

    // Ambil data reservasi — semua kolom pelanggan langsung di tabel reservations
    $res = null;
    try {
        $stmt = $db->prepare(
            "SELECT r.*,
                    COALESCE(i.company_name, 'RZ Equipment Rental') AS company_name
             FROM reservations r
             LEFT JOIN invoice_settings i ON i.id = 1
             WHERE r.reservation_id = :id AND r.status = 'approved'
             LIMIT 1"
        );
        $stmt->execute([':id' => $resId]);
        $res = $stmt->fetch();
    } catch (\Throwable $e) {
        // Fallback: tanpa join invoice_settings
        $stmt = $db->prepare(
            "SELECT * FROM reservations
             WHERE reservation_id = :id AND status = 'approved'
             LIMIT 1"
        );
        $stmt->execute([':id' => $resId]);
        $res = $stmt->fetch();
    }

    if (!$res) {
        ob_clean();
        echo json_encode(['success' => false, 'error' => 'Reservasi tidak ditemukan atau status bukan approved.']);
        exit;
    }

    // Kirim sesuai tipe
    $result = ['success' => false, 'error' => 'Tipe tidak dikenali.'];

    if ($type === 'wa') {
        $phone = $res['phone'] ?? '';
        if (empty($phone)) {
            ob_clean();
            echo json_encode(['success' => false, 'error' => 'Nomor telepon pelanggan tidak tersedia.']);
            exit;
        }
        $message = buildWAApprovalMessage($res);
        $result  = sendWhatsApp($phone, $message);

    } elseif ($type === 'email') {
        $email = $res['email'] ?? '';
        if (empty($email)) {
            ob_clean();
            echo json_encode(['success' => false, 'error' => 'Email pelanggan tidak tersedia di data reservasi.']);
            exit;
        }
        $invNo   = 'INV-' . str_pad($res['reservation_id'], 5, '0', STR_PAD_LEFT);
        $subject = 'Konfirmasi Reservasi ' . $invNo . ' - ' . ($res['company_name'] ?? 'RZ Equipment Rental');
        $html    = buildEmailApprovalHTML($res);
        $result  = sendEmail($email, $res['customer_name'], $subject, $html);
    }

    // Log hasil pengiriman (abaikan jika tabel belum ada)
    try {
        $db->prepare(
            "INSERT INTO notification_log (reservation_id, type, status, message, created_at)
             VALUES (:rid, :type, :status, :msg, NOW())"
        )->execute([
            ':rid'    => $resId,
            ':type'   => $type,
            ':status' => $result['success'] ? 'success' : 'failed',
            ':msg'    => $result['success'] ? 'OK' : ($result['error'] ?? 'Unknown error'),
        ]);
    } catch (\Throwable) { /* tabel belum ada, skip */ }

    ob_clean();
    echo json_encode($result);

} catch (\Throwable $e) {
    ob_clean();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}