<?php
// ============================================================
// Invoice Generator Helper
// File: src/includes/invoice-generator.php
//
// Requires FPDF 1.86 → src/includes/fpdf/fpdf.php
// ============================================================

require_once __DIR__ . '/fpdf/fpdf.php';

define('INV_R', 37);
define('INV_G', 99);
define('INV_B', 235); // #2563EB

// ── Custom FPDF class with proper Footer on every page ──────
class InvoicePDF extends FPDF
{
    public string $companyName = '';

    public function Footer(): void
    {
        $this->SetY(-12);
        $this->SetFont('Arial', '', 7);
        $this->SetTextColor(160, 160, 160);
        $this->Cell(0, 5,
            'Dokumen ini digenerate otomatis oleh sistem. | ' . $this->companyName,
            0, 0, 'C'
        );
    }
}

/**
 * generateInvoicePDF($reservation_id, $output_type)
 *
 * @param int    $reservation_id
 * @param string $output_type  'F' = save file, 'I' = inline stream, 'D' = force download
 * @return string|bool  filepath when 'F', true when streaming, false on error
 */
function generateInvoicePDF(int $reservation_id, string $output_type = 'F'): string|bool
{
    require_once __DIR__ . '/../config/database.php';
    $db = getDB();

    // ── Data fetching ──────────────────────────────────────
    $stmt = $db->prepare("SELECT * FROM reservations WHERE reservation_id = ?");
    $stmt->execute([$reservation_id]);
    $res = $stmt->fetch();
    if (!$res) return false;

    $istmt = $db->prepare("
        SELECT ri.*, e.equipment_name, c.category_name
        FROM reservation_items ri
        JOIN equipment e  ON e.equipment_id = ri.equipment_id
        JOIN categories c ON c.category_id  = e.category_id
        WHERE ri.reservation_id = ?
        ORDER BY c.category_name, e.equipment_name
    ");
    $istmt->execute([$reservation_id]);
    $items = $istmt->fetchAll();

    try {
        $cfg = $db->query("SELECT * FROM invoice_settings WHERE id = 1")->fetch();
    } catch (Exception $e) {
        $cfg = false;
    }
    if (!$cfg) {
        $cfg = [
            'company_name'         => 'RZ Equipment Rental',
            'company_address'      => '',
            'company_phone'        => '',
            'company_email'        => '',
            'payment_instructions' => '',
            'invoice_notes'        => '',
            'logo_path'            => null,
        ];
    }

    // ── Dates ──────────────────────────────────────────────
    $d1   = new DateTime($res['start_date']);
    $d2   = new DateTime($res['end_date']);
    $days = max(1, (int)$d1->diff($d2)->days);

    $invoice_no   = 'INV-' . date('Ymd') . '-' . str_pad($reservation_id, 5, '0', STR_PAD_LEFT);
    $invoice_date = date('d M Y');

    // ── Init PDF ───────────────────────────────────────────
    $pdf = new InvoicePDF('P', 'mm', 'A4');
    $pdf->companyName = $cfg['company_name'];
    $pdf->SetAutoPageBreak(true, 16); // 16mm bottom = room for footer
    $pdf->SetMargins(15, 15, 15);
    $pdf->AddPage();

    $pageW   = 210;
    $usableW = 180; // 210 - 30

    // ══════════════════════════════════════════════════════
    // SECTION 1 — HEADER BAND (compact: 32mm tall)
    // ══════════════════════════════════════════════════════
    $pdf->SetFillColor(INV_R, INV_G, INV_B);
    $pdf->Rect(0, 0, $pageW, 32, 'F');

    // Logo
    $logoDrawn = false;
    if (!empty($cfg['logo_path'])) {
        $logoAbs = __DIR__ . '/../../' . ltrim($cfg['logo_path'], '/');
        if (file_exists($logoAbs)) {
            try {
                $pdf->Image($logoAbs, 15, 6, 20, 20);
                $logoDrawn = true;
            } catch (Exception $e) {}
        }
    }

    // Header: left column (company info) | right column (INVOICE label)
    // Left col: X=nameX, width=104mm — right col: X=125, width=70mm
    // Strict boundary ensures no overlap regardless of text length
    $leftColX = $logoDrawn ? 39 : 15;
    $leftColW = 104;
    $rightColX = 125;
    $rightColW = $pageW - $rightColX - 5;

    $pdf->SetTextColor(255, 255, 255);

    // LEFT: Company name (bold, truncated to leftColW)
    $pdf->SetFont('Arial', 'B', 13);
    $pdf->SetXY($leftColX, 7);
    $pdf->Cell($leftColW, 6, _utf($cfg['company_name']), 0, 1, 'L');

    // LEFT: Address (MultiCell so long addresses wrap instead of overflow)
    $addrY = 14;
    if (!empty($cfg['company_address'])) {
        $pdf->SetFont('Arial', '', 7.5);
        $pdf->SetXY($leftColX, $addrY);
        $pdf->MultiCell($leftColW, 4, _utf($cfg['company_address']), 0, 'L');
        $addrY = $pdf->GetY();
    }

    // LEFT: Telp | Email
    $contactParts = array_filter([
        !empty($cfg['company_phone']) ? 'Telp: ' . $cfg['company_phone'] : '',
        !empty($cfg['company_email']) ? $cfg['company_email'] : '',
    ]);
    if ($contactParts) {
        $pdf->SetFont('Arial', '', 7.5);
        $pdf->SetXY($leftColX, $addrY);
        $pdf->Cell($leftColW, 4, implode('  |  ', $contactParts), 0, 0, 'L');
    }

    // RIGHT: "INVOICE" big label — positioned in right column only
    $pdf->SetFont('Arial', 'B', 22);
    $pdf->SetXY($rightColX, 6);
    $pdf->Cell($rightColW, 10, 'INVOICE', 0, 1, 'R');

    // RIGHT: Invoice number
    $pdf->SetFont('Arial', '', 8);
    $pdf->SetXY($rightColX, 17);
    $pdf->Cell($rightColW, 5, $invoice_no, 0, 1, 'R');

    // ══════════════════════════════════════════════════════
    // SECTION 2 — META: Tagihan Kepada + Detail Invoice
    // ══════════════════════════════════════════════════════
    $pdf->SetTextColor(40, 40, 40);
    $metaTop = 37; // 5mm gap after header band

    // Sub-headers
    $pdf->SetFillColor(INV_R, INV_G, INV_B);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetXY(15, $metaTop);
    $pdf->Cell(88, 5, 'TAGIHAN KEPADA', 'LTR', 0, 'L', true);
    $pdf->Cell(2, 5, '', 0);           // gap between columns
    $pdf->Cell(88, 5, 'DETAIL INVOICE', 'LTR', 1, 'L', true);

    $pdf->SetTextColor(40, 40, 40);
    $pdf->SetFont('Arial', '', 8);

    $rowH  = 5;
    $leftY = $metaTop + 5;

    // Left column — customer
    $custRows = [
        ['Nama',        $res['customer_name']],
        ['Telepon',     $res['phone']],
        ['Email',       $res['email'] ?: '-'],
    ];
    foreach ($custRows as [$lbl, $val]) {
        $pdf->SetXY(15, $leftY);
        $pdf->SetFont('Arial', 'B', 7.5);
        $pdf->Cell(22, $rowH, $lbl . ':', 'LR');
        $pdf->SetFont('Arial', '', 7.5);
        $pdf->Cell(66, $rowH, _utf($val), 'R');
        $leftY += $rowH;
    }
    // Bottom border left column
    $pdf->SetXY(15, $leftY);
    $pdf->Cell(88, 0, '', 'T');

    // Right column — invoice detail
    $rightY = $metaTop + 5;
    $invoiceRows = [
        ['No. Invoice', $invoice_no],
        ['Tanggal',     $invoice_date],
        ['Periode',     date('d M Y', strtotime($res['start_date'])) . ' - ' . date('d M Y', strtotime($res['end_date']))],
        ['Durasi',      $days . ' hari'],
        ['Status',      strtoupper($res['status'])],
    ];
    foreach ($invoiceRows as [$lbl, $val]) {
        $pdf->SetXY(105, $rightY);
        $pdf->SetFont('Arial', 'B', 7.5);
        $pdf->Cell(22, $rowH, $lbl . ':', 'LR');
        $pdf->SetFont('Arial', '', 7.5);
        $pdf->Cell(66, $rowH, _utf($val), 'R');
        $rightY += $rowH;
    }
    // Bottom border right column
    $pdf->SetXY(105, $rightY);
    $pdf->Cell(88, 0, '', 'T');

    $pdf->SetY(max($leftY, $rightY) + 5);

    // ══════════════════════════════════════════════════════
    // SECTION 3 — ITEMS TABLE
    // ══════════════════════════════════════════════════════
    $colWidths = [74, 14, 32, 14, 30]; // Equipment, Qty, Harga/Hari, Hari, Subtotal — total 164+16margin=180
    $colHeaders = ['Equipment / Item', 'Qty', 'Harga/Hari', 'Hari', 'Subtotal'];

    // Table header
    $pdf->SetFillColor(INV_R, INV_G, INV_B);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetX(15);
    foreach ($colHeaders as $i => $h) {
        $pdf->Cell($colWidths[$i], 6, $h, 1, 0, $i === 0 ? 'L' : 'C', true);
    }
    $pdf->Ln();

    // Table rows
    $pdf->SetTextColor(40, 40, 40);
    $fill = false;
    foreach ($items as $row) {
        $pdf->SetFillColor($fill ? 243 : 255, $fill ? 246 : 255, $fill ? 252 : 255);
        $pdf->SetFont('Arial', '', 8);
        $pdf->SetX(15);
        $name = _utf($row['equipment_name']);
        $cat  = _utf($row['category_name']);
        $pdf->Cell($colWidths[0], 5.5, $name . ' (' . $cat . ')', 'LR', 0, 'L', $fill);
        $pdf->Cell($colWidths[1], 5.5, $row['quantity'],                             'LR', 0, 'C', $fill);
        $pdf->Cell($colWidths[2], 5.5, 'Rp ' . number_format($row['rental_price'], 0, ',', '.'), 'LR', 0, 'R', $fill);
        $pdf->Cell($colWidths[3], 5.5, $days,                                        'LR', 0, 'C', $fill);
        $pdf->Cell($colWidths[4], 5.5, 'Rp ' . number_format($row['subtotal'],      0, ',', '.'), 'LR', 0, 'R', $fill);
        $pdf->Ln();
        $fill = !$fill;
    }
    // Close table border
    $pdf->SetX(15);
    $pdf->Cell(array_sum($colWidths), 0, '', 'T');
    $pdf->Ln(3);

    // ── TOTAL row ─────────────────────────────────────────
    $totalW = $colWidths[2] + $colWidths[3] + $colWidths[4]; // 76
    $labelW = array_sum($colWidths) - $totalW;               // 88
    $pdf->SetX(15);
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetFillColor(INV_R, INV_G, INV_B);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell($labelW, 7, 'TOTAL PEMBAYARAN', 1, 0, 'R', true);
    $pdf->Cell($totalW, 7, 'Rp ' . number_format($res['total_amount'], 0, ',', '.'), 1, 1, 'R', true);
    $pdf->SetTextColor(40, 40, 40);
    $pdf->Ln(4);

    // ══════════════════════════════════════════════════════
    // SECTION 4 — PAYMENT INSTRUCTIONS (compact)
    // ══════════════════════════════════════════════════════
    if (!empty($cfg['payment_instructions'])) {
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetFillColor(INV_R, INV_G, INV_B);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetX(15);
        $pdf->Cell($usableW, 5, 'INSTRUKSI PEMBAYARAN', 1, 1, 'L', true);
        $pdf->SetTextColor(50, 50, 50);
        $pdf->SetFont('Arial', '', 8);
        $pdf->SetX(15);
        $pdf->MultiCell($usableW, 4.5, _utf($cfg['payment_instructions']), 'LBR');
        $pdf->Ln(3);
    }

    // ══════════════════════════════════════════════════════
    // SECTION 5 — NOTES (compact)
    // ══════════════════════════════════════════════════════
    if (!empty($cfg['invoice_notes'])) {
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetFillColor(235, 240, 255);
        $pdf->SetTextColor(50, 70, 120);
        $pdf->SetX(15);
        $pdf->Cell($usableW, 5, 'CATATAN', 1, 1, 'L', true);
        $pdf->SetFont('Arial', 'I', 7.5);
        $pdf->SetTextColor(80, 80, 100);
        $pdf->SetX(15);
        $pdf->MultiCell($usableW, 4.5, _utf($cfg['invoice_notes']), 'LBR');
    }

    // ══════════════════════════════════════════════════════
    // OUTPUT
    // ══════════════════════════════════════════════════════
    if ($output_type === 'F') {
        $projectRoot = dirname(__DIR__, 2);
        $invoiceDir  = $projectRoot . '/assets/invoices';
        if (!is_dir($invoiceDir)) {
            mkdir($invoiceDir, 0775, true);
        }
        $filename = 'invoice_' . $reservation_id . '_' . date('Ymd') . '.pdf';
        $filepath = $invoiceDir . '/' . $filename;
        $pdf->Output('F', $filepath);
        return 'assets/invoices/' . $filename;
    }

    $pdf->Output($output_type, 'Invoice-' . $invoice_no . '.pdf');
    return true;
}

/**
 * UTF-8 → windows-1252 for FPDF core fonts
 */
function _utf(string $str): string
{
    return iconv('UTF-8', 'windows-1252//TRANSLIT//IGNORE', $str) ?: $str;
}