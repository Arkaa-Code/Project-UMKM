<?php
// Dedicated save endpoint untuk notification settings
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php'); exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: settings.php?tab=notification'); exit;
}

require_once '../src/config/database.php';
$db = getDB();

$n_fonnte   = trim($_POST['fonnte_token']       ?? '');
$n_host     = trim($_POST['smtp_host']          ?? 'smtp.gmail.com');
$n_port     = (int)($_POST['smtp_port']         ?? 587);
$n_user     = trim($_POST['smtp_user']          ?? '');
$n_pass     = trim($_POST['smtp_pass']          ?? '');
$n_fromname = trim($_POST['smtp_from_name']     ?? 'RZ Equipment Rental');
$n_company  = trim($_POST['company_name_notif'] ?? 'RZ Equipment Rental');
$n_wa_en    = isset($_POST['wa_enabled'])    ? 1 : 0;
$n_email_en = isset($_POST['email_enabled']) ? 1 : 0;

// Ambil password lama jika field dikosongkan
if ($n_pass === '') {
    try {
        $old = $db->query("SELECT smtp_pass FROM notification_settings WHERE id=1")->fetchColumn();
        $n_pass = $old ?: '';
    } catch (\Throwable $e) { $n_pass = ''; }
}

try {
    $db->prepare(
        "INSERT INTO notification_settings
            (id, fonnte_token, smtp_host, smtp_port, smtp_user, smtp_pass, smtp_from_name, company_name_notif, wa_enabled, email_enabled)
         VALUES (1, :ft, :sh, :sp, :su, :spw, :sfn, :cn, :we, :ee)
         ON DUPLICATE KEY UPDATE
            fonnte_token       = VALUES(fonnte_token),
            smtp_host          = VALUES(smtp_host),
            smtp_port          = VALUES(smtp_port),
            smtp_user          = VALUES(smtp_user),
            smtp_pass          = VALUES(smtp_pass),
            smtp_from_name     = VALUES(smtp_from_name),
            company_name_notif = VALUES(company_name_notif),
            wa_enabled         = VALUES(wa_enabled),
            email_enabled      = VALUES(email_enabled)"
    )->execute([
        ':ft'  => $n_fonnte  !== '' ? $n_fonnte  : null,
        ':sh'  => $n_host,
        ':sp'  => $n_port,
        ':su'  => $n_user    !== '' ? $n_user    : null,
        ':spw' => $n_pass    !== '' ? $n_pass    : null,
        ':sfn' => $n_fromname,
        ':cn'  => $n_company,
        ':we'  => $n_wa_en,
        ':ee'  => $n_email_en,
    ]);
    header('Location: settings.php?tab=notification&saved=1');
} catch (\Throwable $e) {
    header('Location: settings.php?tab=notification&error=' . urlencode($e->getMessage()));
}
exit;
