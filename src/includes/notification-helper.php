<?php
// ============================================================
// Notification Helper
// Kirim WhatsApp via Fonnte API & Email via PHPMailer SMTP
// ============================================================

require_once __DIR__ . '/../config/database.php';

// ── Ambil konfigurasi notifikasi dari DB ──────────────────────
function getNotifConfig(): array {
    $db = getDB();
    try {
        $row = $db->query("SELECT * FROM notification_settings WHERE id = 1 LIMIT 1")->fetch();
        return $row ?: [];
    } catch (\Throwable $e) {
        return [];
    }
}

// ── Kirim WhatsApp via Fonnte ─────────────────────────────────
function sendWhatsApp(string $phone, string $message): array {
    $cfg = getNotifConfig();
    $token = $cfg['fonnte_token'] ?? '';

    if (empty($token)) {
        return ['success' => false, 'error' => 'Fonnte token belum dikonfigurasi.'];
    }

    // Normalisasi nomor: 08xx → 628xx
    $phone = preg_replace('/[^0-9]/', '', $phone);
    if (str_starts_with($phone, '0')) {
        $phone = '62' . substr($phone, 1);
    }

    $ch = curl_init('https://api.fonnte.com/send');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => ['Authorization: ' . $token],
        CURLOPT_POSTFIELDS     => [
            'target'  => $phone,
            'message' => $message,
        ],
        CURLOPT_TIMEOUT        => 15,
    ]);
    $response = curl_exec($ch);
    $err      = curl_error($ch);
    curl_close($ch);

    if ($err) {
        return ['success' => false, 'error' => 'CURL error: ' . $err];
    }

    $data = json_decode($response, true);
    if (!empty($data['status'])) {
        return ['success' => true, 'response' => $data];
    }
    return ['success' => false, 'error' => $response];
}

// ── Kirim Email via PHPMailer SMTP ────────────────────────────
function sendEmail(string $toEmail, string $toName, string $subject, string $htmlBody): array {
    $cfg = getNotifConfig();

    $smtpHost = $cfg['smtp_host']     ?? '';
    $smtpUser = $cfg['smtp_user']     ?? '';
    $smtpPass = $cfg['smtp_pass']     ?? '';
    $smtpPort = (int)($cfg['smtp_port'] ?? 587);
    $fromName = $cfg['smtp_from_name'] ?? 'RZ Equipment Rental';

    if (empty($smtpHost) || empty($smtpUser) || empty($smtpPass)) {
        return ['success' => false, 'error' => 'Konfigurasi SMTP belum lengkap.'];
    }

    // Cek apakah PHPMailer tersedia
    $pmAutoload = __DIR__ . '/phpmailer/Exception.php';
    $pmMailer   = __DIR__ . '/phpmailer/PHPMailer.php';
    $pmSMTP     = __DIR__ . '/phpmailer/SMTP.php';

    if (!file_exists($pmAutoload)) {
        return ['success' => false, 'error' => 'PHPMailer tidak ditemukan di src/includes/phpmailer/.'];
    }

    require_once $pmAutoload;
    require_once $pmMailer;
    require_once $pmSMTP;

    try {
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = $smtpHost;
        $mail->SMTPAuth   = true;
        $mail->Username   = $smtpUser;
        $mail->Password   = $smtpPass;
        $mail->SMTPSecure = ($smtpPort === 465)
            ? PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS
            : PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $smtpPort;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom($smtpUser, $fromName);
        $mail->addAddress($toEmail, $toName);
        $mail->Subject = $subject;
        $mail->isHTML(true);
        $mail->Body    = $htmlBody;
        $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $htmlBody));

        $mail->send();
        return ['success' => true];
    } catch (\Throwable $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

// ── Template Pesan WA Approval ───────────────────────────────
function buildWAApprovalMessage(array $res): string {
    $cfg         = getNotifConfig();
    $companyName = $cfg['company_name_notif'] ?? 'RZ Equipment Rental';
    $invNo       = 'INV-' . str_pad($res['reservation_id'], 5, '0', STR_PAD_LEFT);
    $start       = date('d M Y', strtotime($res['start_date']));
    $end         = date('d M Y', strtotime($res['end_date']));
    $total       = 'Rp ' . number_format($res['total_amount'], 0, ',', '.');

    return "Halo *{$res['customer_name']}*,\n\n" .
           "✅ *Reservasi Anda telah DISETUJUI*\n\n" .
           "🏢 *{$companyName}*\n" .
           "📄 No. Invoice: *{$invNo}*\n" .
           "📅 Periode: {$start} s/d {$end}\n" .
           "💰 Total: *{$total}*\n\n" .
           "Silakan lakukan pembayaran sesuai instruksi yang tertera pada invoice.\n" .
           "Terima kasih telah menggunakan layanan kami! 🙏";
}

// ── Template Email HTML Approval ─────────────────────────────
function buildEmailApprovalHTML(array $res): string {
    $cfg         = getNotifConfig();
    $companyName = $cfg['company_name_notif'] ?? 'RZ Equipment Rental';
    $invNo       = 'INV-' . str_pad($res['reservation_id'], 5, '0', STR_PAD_LEFT);
    $start       = date('d M Y', strtotime($res['start_date']));
    $end         = date('d M Y', strtotime($res['end_date']));
    $total       = 'Rp ' . number_format($res['total_amount'], 0, ',', '.');
    $name        = htmlspecialchars($res['customer_name']);

    return <<<HTML
    <!DOCTYPE html>
    <html lang="id">
    <head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
    <body style="margin:0;padding:0;background:#F1F5F9;font-family:Arial,sans-serif">
      <div style="max-width:560px;margin:40px auto;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,.08)">

        <!-- Header -->
        <div style="background:#1E3A8A;padding:28px 32px;text-align:center">
          <h1 style="color:#fff;margin:0;font-size:22px;letter-spacing:.5px">{$companyName}</h1>
          <p style="color:#93C5FD;margin:6px 0 0;font-size:13px">Konfirmasi Reservasi</p>
        </div>

        <!-- Body -->
        <div style="padding:32px">
          <p style="color:#1E293B;font-size:16px;margin:0 0 20px">Halo <strong>{$name}</strong>,</p>
          <div style="background:#ECFDF5;border-left:4px solid #22C55E;padding:14px 18px;border-radius:6px;margin-bottom:24px">
            <p style="margin:0;color:#15803D;font-weight:700;font-size:16px">✅ Reservasi Anda telah DISETUJUI</p>
          </div>

          <table style="width:100%;border-collapse:collapse;font-size:14px;color:#334155">
            <tr>
              <td style="padding:10px 0;border-bottom:1px solid #E2E8F0;width:45%">No. Invoice</td>
              <td style="padding:10px 0;border-bottom:1px solid #E2E8F0;font-weight:700">{$invNo}</td>
            </tr>
            <tr>
              <td style="padding:10px 0;border-bottom:1px solid #E2E8F0">Periode Sewa</td>
              <td style="padding:10px 0;border-bottom:1px solid #E2E8F0">{$start} s/d {$end}</td>
            </tr>
            <tr>
              <td style="padding:10px 0">Total Pembayaran</td>
              <td style="padding:10px 0;font-weight:700;color:#1E3A8A;font-size:16px">{$total}</td>
            </tr>
          </table>

          <p style="margin:24px 0 8px;color:#64748B;font-size:13px">
            Silakan lakukan pembayaran sesuai instruksi yang tertera pada invoice yang terlampir.
            Jika ada pertanyaan, hubungi kami segera.
          </p>
        </div>

        <!-- Footer -->
        <div style="background:#F8FAFC;padding:16px 32px;text-align:center;border-top:1px solid #E2E8F0">
          <p style="margin:0;color:#94A3B8;font-size:12px">
            Email ini dikirim otomatis oleh sistem {$companyName}. Jangan balas email ini.
          </p>
        </div>
      </div>
    </body>
    </html>
    HTML;
}
