<?php
/**
 * Performance Contract (PC) Module
 * Ministry of Health — State Department for Medical Services (PP-SDOMS)
 * Native PHP 8.1+ & MySQL (No Node.js)
 *
 * Data source: table `kpis_and_sub_kpis` (imported from the "KPIs and Sub KPIs"
 * sheet of PC_Summary.xlsx). Optional extras that are picked up automatically
 * when present (see database/migrations/add_pc_year_quarter.sql):
 *   - kpis_and_sub_kpis.financial_year   → drives the Year filter per row
 *   - kpi_quarterly_scores               → recorded Q1–Q4 scores for the Gantt chart
 */
require_once __DIR__ . '/../config/db.php';
require_auth();

$page_title = 'Performance Contract — State Department for Medical Services';
$active_tab = 'pc';

const PC_DEFAULT_FY = '2025/2026';
// Kenyan financial year runs July–June.
const PC_QUARTERS = [
    1 => ['label' => 'Q1', 'months' => 'Jul–Sep', 'color' => '#0ea5e9'],
    2 => ['label' => 'Q2', 'months' => 'Oct–Dec', 'color' => '#2563eb'],
    3 => ['label' => 'Q3', 'months' => 'Jan–Mar', 'color' => '#f59e0b'],
    4 => ['label' => 'Q4', 'months' => 'Apr–Jun', 'color' => '#059669'],
];
// Phasing applied to the reviewed achievement when no quarterly score has been
// recorded yet (same phasing used by the original Performance Gantt chart).
const PC_ESTIMATE_FACTORS = [1 => 0.85, 2 => 0.92, 3 => 0.98, 4 => 1.0];

$e = static fn($value): string => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
$num = static fn($value, int $decimals = 2): string => ($value === null || $value === '') ? '—' : number_format((float)$value, $decimals);
$text = static fn($value): string => ($value === null || trim((string)$value) === '') ? '—' : (string)$value;

// ---------------------------------------------------------------- load data
$rows = [];
$recorded_scores = [];       // [serial_no][financial_year][quarter] => pct
$has_year_column = false;
$table_missing = false;
$load_error = false;
if ($db_connected && $pdo) {
    try {
        $rows = $pdo->query('SELECT * FROM kpis_and_sub_kpis ORDER BY id ASC')->fetchAll(PDO::FETCH_ASSOC);
        $has_year_column = $rows ? array_key_exists('financial_year', $rows[0]) : false;
        if (!$rows) {
            $has_year_column = (bool)$pdo->query("SHOW COLUMNS FROM kpis_and_sub_kpis LIKE 'financial_year'")->fetch();
        }
    } catch (Throwable $exception) {
        $table_missing = stripos($exception->getMessage(), "doesn't exist") !== false || stripos($exception->getMessage(), 'Base table') !== false;
        $load_error = !$table_missing;
        error_log('Performance contract load failed: ' . $exception->getMessage());
    }
    if ($rows) {
        try {
            foreach ($pdo->query('SELECT serial_no, financial_year, quarter, achievement_pct FROM kpi_quarterly_scores')->fetchAll(PDO::FETCH_ASSOC) as $score) {
                $recorded_scores[(string)$score['serial_no']][(string)$score['financial_year']][(int)$score['quarter']] = (float)$score['achievement_pct'];
            }
        } catch (Throwable $exception) {
            $recorded_scores = []; // optional table not installed
        }
    }
}

// ------------------------------------------------------------------ filters
$year_options = [];
foreach ($rows as $row) {
    $year_options[trim((string)($row['financial_year'] ?? '')) ?: PC_DEFAULT_FY] = true;
}
foreach ($recorded_scores as $by_year) {
    foreach (array_keys($by_year) as $year_key) $year_options[$year_key] = true;
}
if (!$year_options) $year_options[PC_DEFAULT_FY] = true;
$year_options = array_keys($year_options);
rsort($year_options);

$selected_year = trim((string)($_GET['year'] ?? ''));
if (!in_array($selected_year, $year_options, true)) $selected_year = $year_options[0];
$selected_quarter = (int)($_GET['quarter'] ?? 0);
if (!isset(PC_QUARTERS[$selected_quarter])) $selected_quarter = 0; // 0 = all quarters
$selected_category = strtoupper(trim((string)($_GET['category'] ?? '')));

// --------------------------------------------------------- shape the dataset
$year_rows = array_values(array_filter($rows, static fn($row) => (trim((string)($row['financial_year'] ?? '')) ?: PC_DEFAULT_FY) === $selected_year));

$categories = [];   // letter => ['row' => category row, 'subs' => [...], 'weight' => float]
$total_row = null;
foreach ($year_rows as $row) {
    $serial = trim((string)($row['serial_no'] ?? ''));
    if ($serial === '') { $total_row = $row; continue; }
    $letter = strtoupper($serial[0]);
    $categories[$letter] ??= ['row' => null, 'subs' => [], 'weight' => 0.0];
    if (preg_match('/^[A-Za-z]$/', $serial)) {
        $categories[$letter]['row'] = $row;
    } else {
        $categories[$letter]['subs'][] = $row;
        $categories[$letter]['weight'] += (float)($row['weight_percent'] ?? 0);
    }
}

/** Percentage score of an indicator: weight achieved ÷ weight. */
$score_pct = static function (?float $weight_achieved, ?float $weight): ?float {
    if ($weight === null || $weight <= 0 || $weight_achieved === null) return null;
    return max(0.0, $weight_achieved / $weight * 100);
};
/** Q1–Q4 values for one indicator: recorded when available, otherwise phased from the reviewed score. */
$quarter_values = static function (string $serial, ?float $pct) use ($recorded_scores, $selected_year): array {
    $out = [];
    foreach (PC_QUARTERS as $q => $meta) {
        $recorded = $recorded_scores[$serial][$selected_year][$q] ?? null;
        if ($recorded !== null) { $out[$q] = ['pct' => $recorded, 'estimated' => false]; continue; }
        $out[$q] = ['pct' => $pct === null ? null : min(100.0, round($pct * PC_ESTIMATE_FACTORS[$q], 1)), 'estimated' => true];
    }
    return $out;
};

$sub_meta = [];   // serial => ['pct' => ..., 'quarters' => ...]
$cat_summary = []; // letter => weight, achieved, quarters
$grand_weight = 0.0;
$grand_achieved = 0.0;
foreach ($categories as $letter => &$cat) {
    $cat_weight = $cat['weight'];
    $cat_achieved = 0.0;
    $cat_q = array_fill_keys(array_keys(PC_QUARTERS), ['sum' => 0.0, 'weight' => 0.0, 'estimated' => false]);
    foreach ($cat['subs'] as $sub) {
        $serial = (string)$sub['serial_no'];
        $weight = (float)($sub['weight_percent'] ?? 0);
        $achieved = $sub['weight_achieved_percent'] === null ? null : (float)$sub['weight_achieved_percent'];
        $pct = $score_pct($achieved, $weight);
        $quarters = $quarter_values($serial, $pct);
        $sub_meta[$serial] = ['pct' => $pct, 'quarters' => $quarters];
        $cat_achieved += (float)$achieved;
        foreach ($quarters as $q => $value) {
            if ($value['pct'] === null) continue;
            $cat_q[$q]['sum'] += $value['pct'] * $weight;
            $cat_q[$q]['weight'] += $weight;
            $cat_q[$q]['estimated'] = $cat_q[$q]['estimated'] || $value['estimated'];
        }
    }
    // Prefer the achieved weight stored on the category row itself.
    if ($cat['row'] && $cat['row']['weight_achieved_percent'] !== null) $cat_achieved = (float)$cat['row']['weight_achieved_percent'];
    $cat_quarters = [];
    foreach ($cat_q as $q => $acc) {
        $cat_quarters[$q] = ['pct' => $acc['weight'] > 0 ? round($acc['sum'] / $acc['weight'], 1) : null, 'estimated' => $acc['estimated'] || $acc['weight'] <= 0];
    }
    $cat_pct = $score_pct($cat_achieved, $cat_weight);
    $cat_summary[$letter] = ['weight' => $cat_weight, 'achieved' => $cat_achieved, 'pct' => $cat_pct, 'quarters' => $cat_quarters];
    $grand_weight += $cat_weight;
    $grand_achieved += $cat_achieved;
}
unset($cat);

// Weight achieved shown on the cards: the reviewed figure, or the selected quarter's phasing.
$card_achieved = static function (string $letter) use ($cat_summary, $categories, $sub_meta, $selected_quarter): float {
    if ($selected_quarter === 0) return $cat_summary[$letter]['achieved'];
    $sum = 0.0;
    foreach ($categories[$letter]['subs'] as $sub) {
        $q = $sub_meta[(string)$sub['serial_no']]['quarters'][$selected_quarter]['pct'] ?? null;
        if ($q !== null) $sum += (float)$sub['weight_percent'] * $q / 100;
    }
    return $sum;
};
$grand_card_achieved = 0.0;
foreach (array_keys($categories) as $letter) $grand_card_achieved += $card_achieved($letter);
if ($selected_quarter === 0 && $total_row && $total_row['weight_achieved_percent'] !== null) $grand_card_achieved = (float)$total_row['weight_achieved_percent'];

if ($selected_category !== '' && !isset($categories[$selected_category])) $selected_category = '';
$visible_categories = $selected_category === '' ? $categories : [$selected_category => $categories[$selected_category]];

$category_names = [];
foreach ($categories as $letter => $cat) $category_names[$letter] = trim((string)($cat['row']['performance_criteria'] ?? '')) ?: ('Category ' . $letter);

$category_colors = ['A' => '#4778c4', 'B' => '#ed8734', 'C' => '#6eae49', 'D' => '#f0c400', 'E' => '#8458b6', 'F' => '#64748b'];
$status_style = static function (?string $status): string {
    return match (strtolower(trim((string)$status))) {
        'approved' => 'background:#dbeafe;color:#1d4ed8;',
        'reviewed' => 'background:#d1fae5;color:#047857;',
        'submitted' => 'background:#fef3c7;color:#92400e;',
        'draft' => 'background:#e2e8f0;color:#475569;',
        default => 'background:#f1f5f9;color:#334155;',
    };
};
$pct_color = static fn(?float $pct): string => $pct === null ? '#94a3b8' : ($pct >= 90 ? '#059669' : ($pct >= 75 ? '#2563eb' : '#e11d48'));
$link = static function (array $override = []) use ($selected_year, $selected_quarter, $selected_category): string {
    $query = array_merge(['year' => $selected_year, 'quarter' => $selected_quarter ?: null, 'category' => $selected_category ?: null], $override);
    return '/modules/pc.php?' . http_build_query(array_filter($query, static fn($v) => $v !== null && $v !== ''));
};
$visible_quarters = $selected_quarter ? [$selected_quarter => PC_QUARTERS[$selected_quarter]] : PC_QUARTERS;
$any_estimated = false;

// ======================================================= uploads & downloads
$can_download_pdf = !empty(get_current_user_data()['can_download']) || can_edit();
$can_upload_reports = !empty(get_current_user_data()['can_upload']) || can_edit();
$tab = (($_GET['tab'] ?? '') === 'upload') ? 'upload' : 'overview';
$upload_error = '';
$upload_dir = __DIR__ . '/../uploads/pc_reports';
$allowed_upload_ext = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'csv', 'png', 'jpg', 'jpeg'];
$max_upload_bytes = 10 * 1024 * 1024;
$can_edit_kpis = can_edit();
$kpi_statuses = ['Draft', 'Submitted', 'Reviewed', 'Approved'];
$edit_error = '';

// Edit one KPI / Sub KPI row.
if ($db_connected && $pdo && !$table_missing && $_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form_action'] ?? '') === 'edit_kpi') {
    $decimal = static function ($value, float $max = 999.99): array {
        $value = trim((string)$value);
        if ($value === '') return [true, null];
        if (!is_numeric($value) || abs((float)$value) > $max) return [false, null];
        return [true, round((float)$value, 2)];
    };
    $short = static fn($value, int $limit): ?string => ($value = trim((string)$value)) === '' ? null : mb_substr($value, 0, $limit);
    if (!$can_edit_kpis) {
        $edit_error = 'You do not have permission to edit performance contract records.';
    } elseif (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $edit_error = 'Your session expired. Refresh the page and try again.';
    } else {
        [$weight_ok, $weight_value] = $decimal($_POST['weight_percent'] ?? '');
        [$achieved_ok, $achieved_value] = $decimal($_POST['weight_achieved_percent'] ?? '');
        $criteria = trim((string)($_POST['performance_criteria'] ?? ''));
        $status_value = trim((string)($_POST['status'] ?? ''));
        $edit_id = (int)($_POST['id'] ?? 0);
        if ($edit_id <= 0 || $criteria === '') {
            $edit_error = 'The performance criteria is required.';
        } elseif (!$weight_ok || !$achieved_ok) {
            $edit_error = 'Weight and weight achieved must be numbers below 1000.';
        } elseif ($status_value !== '' && !in_array($status_value, $kpi_statuses, true)) {
            $edit_error = 'Choose a valid status.';
        } else {
            $before = $pdo->prepare('SELECT * FROM kpis_and_sub_kpis WHERE id = ?');
            $before->execute([$edit_id]);
            if (!$before->fetch()) {
                $edit_error = 'That record no longer exists.';
            } else {
                $pdo->prepare('UPDATE kpis_and_sub_kpis SET performance_criteria=?, unit_of_measure=?, weight_percent=?, target=?, achievement_before_review=?, achievement_after_review=?, weight_achieved_percent=?, status=?, reports=?, actions=? WHERE id=?')
                    ->execute([mb_substr($criteria, 0, 65000), $short($_POST['unit_of_measure'] ?? '', 50), $weight_value, $short($_POST['target'] ?? '', 50), $short($_POST['achievement_before_review'] ?? '', 50), $short($_POST['achievement_after_review'] ?? '', 50), $achieved_value, $status_value !== '' ? $status_value : null, $short($_POST['reports'] ?? '', 255), $short($_POST['actions'] ?? '', 255), $edit_id]);
                log_audit_trail('UPDATE', 'Performance Contract', $edit_id, 'Edited KPI record #' . $edit_id);
                header('Location: ' . $link(['msg' => 'kpi_saved']) . '#pcKpiTable');
                exit;
            }
        }
    }
}

$report_uploads = [];
$uploads_by_serial = [];
$uploads_ready = false;
if ($db_connected && $pdo && !$table_missing) {
    $create_uploads_sql = "CREATE TABLE IF NOT EXISTS pc_report_uploads (
        id INT AUTO_INCREMENT PRIMARY KEY,
        serial_no VARCHAR(20) NULL,
        financial_year VARCHAR(9) NOT NULL,
        quarter TINYINT NOT NULL DEFAULT 0,
        title VARCHAR(255) NOT NULL,
        original_name VARCHAR(255) NOT NULL,
        stored_name VARCHAR(255) NOT NULL,
        file_size INT NOT NULL DEFAULT 0,
        uploaded_by VARCHAR(150) NULL,
        created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
    )";
    try { $pdo->exec($create_uploads_sql); $uploads_ready = true; } catch (Throwable $exception) { error_log('pc_report_uploads unavailable: ' . $exception->getMessage()); }
}

// Download a stored report (authenticated; files are not web-accessible).
if ($uploads_ready && isset($_GET['download'])) {
    $stmt = $pdo->prepare('SELECT * FROM pc_report_uploads WHERE id = ?');
    $stmt->execute([(int)$_GET['download']]);
    $file_row = $stmt->fetch(PDO::FETCH_ASSOC);
    $file_path = $file_row ? $upload_dir . '/' . basename((string)$file_row['stored_name']) : '';
    if (!$file_row || !is_file($file_path)) { http_response_code(404); exit('Report file not found.'); }
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . str_replace(['"', "\r", "\n"], '', (string)$file_row['original_name']) . '"');
    header('Content-Length: ' . filesize($file_path));
    header('X-Content-Type-Options: nosniff');
    readfile($file_path);
    exit;
}

if ($uploads_ready && $_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form_action'] ?? '') !== 'edit_kpi') {
    $tab = 'upload';
    $post_action = (string)($_POST['form_action'] ?? 'upload');
    if (!$can_upload_reports) {
        $upload_error = 'You do not have permission to upload or remove reports.';
    } elseif (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $upload_error = 'Your session expired. Refresh the page and try again.';
    } elseif ($post_action === 'delete') {
        $stmt = $pdo->prepare('SELECT * FROM pc_report_uploads WHERE id = ?');
        $stmt->execute([(int)($_POST['id'] ?? 0)]);
        if ($old = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $pdo->prepare('DELETE FROM pc_report_uploads WHERE id = ?')->execute([$old['id']]);
            @unlink($upload_dir . '/' . basename((string)$old['stored_name']));
            log_audit_trail('DELETE', 'Performance Contract', (int)$old['id'], 'Removed report ' . $old['original_name']);
        }
        header('Location: ' . $link(['tab' => 'upload', 'msg' => 'deleted']));
        exit;
    } else {
        $serials = array_filter(array_map(static fn($r) => trim((string)($r['serial_no'] ?? '')), $rows));
        $post_serial = trim((string)($_POST['serial_no'] ?? ''));
        $post_year = trim((string)($_POST['financial_year'] ?? ''));
        $post_quarter = (int)($_POST['quarter'] ?? 0);
        $post_title = trim((string)($_POST['title'] ?? ''));
        $file = $_FILES['report_file'] ?? null;
        $extension = $file ? strtolower(pathinfo((string)$file['name'], PATHINFO_EXTENSION)) : '';
        if ($post_serial !== '' && !in_array($post_serial, $serials, true)) {
            $upload_error = 'Choose a valid indicator.';
        } elseif (!in_array($post_year, $year_options, true) || ($post_quarter !== 0 && !isset(PC_QUARTERS[$post_quarter]))) {
            $upload_error = 'Choose a valid year and quarter.';
        } elseif (!$file || $file['error'] === UPLOAD_ERR_NO_FILE) {
            $upload_error = 'Choose a file to upload.';
        } elseif ($file['error'] !== UPLOAD_ERR_OK || $file['size'] > $max_upload_bytes) {
            $upload_error = 'The file could not be uploaded. The maximum size is 10 MB.';
        } elseif (!in_array($extension, $allowed_upload_ext, true)) {
            $upload_error = 'Allowed file types: ' . strtoupper(implode(', ', $allowed_upload_ext)) . '.';
        } else {
            if (!is_dir($upload_dir)) { @mkdir($upload_dir, 0755, true); }
            @file_put_contents($upload_dir . '/.htaccess', "Require all denied\nDeny from all\n");
            $stored = bin2hex(random_bytes(16)) . '.' . $extension;
            $original = preg_replace('/[^\w.\- ()]+/u', '_', basename((string)$file['name']));
            if (!move_uploaded_file($file['tmp_name'], $upload_dir . '/' . $stored)) {
                $upload_error = 'The server could not save the file. Check that the uploads folder is writable.';
            } else {
                $user = get_current_user_data();
                $pdo->prepare('INSERT INTO pc_report_uploads (serial_no, financial_year, quarter, title, original_name, stored_name, file_size, uploaded_by) VALUES (?,?,?,?,?,?,?,?)')
                    ->execute([$post_serial !== '' ? $post_serial : null, $post_year, $post_quarter, $post_title !== '' ? $post_title : $original, $original, $stored, (int)$file['size'], $user['full_name'] ?? null]);
                log_audit_trail('CREATE', 'Performance Contract', (int)$pdo->lastInsertId(), 'Uploaded report ' . $original);
                header('Location: ' . $link(['tab' => 'upload', 'msg' => 'uploaded', 'year' => $post_year]));
                exit;
            }
        }
    }
}
if ($uploads_ready) {
    try {
        $report_uploads = $pdo->query('SELECT * FROM pc_report_uploads ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC);
        foreach ($report_uploads as $upload) {
            if ($upload['serial_no'] !== null && $upload['financial_year'] === $selected_year) $uploads_by_serial[(string)$upload['serial_no']][] = $upload;
        }
    } catch (Throwable $exception) { $report_uploads = []; }
}
/** Reports cell: a Download button for this indicator plus links to any uploaded report files. */
$reports_cell = static function (string $serial, $stored_text) use ($uploads_by_serial, $e, $link, $can_download_pdf): string {
    $html = '';
    if ($can_download_pdf) {
        $html .= '<a class="pcx-dl" href="' . $e($link(['export' => 'pdf', 'serial' => $serial, 'tab' => null])) . '" title="Download the PDF for ' . $e($serial) . '">⬇ Download</a>';
    }
    foreach ($uploads_by_serial[$serial] ?? [] as $upload) {
        $html .= '<div class="pcx-file"><a href="/modules/pc.php?download=' . (int)$upload['id'] . '">📎 ' . $e($upload['title']) . '</a></div>';
    }
    if (trim((string)$stored_text) !== '') $html .= '<div class="pcx-file">' . $e($stored_text) . '</div>';
    return $html !== '' ? $html : '—';
};
/** Actions cell: existing action text plus an Edit button for users who may edit. */
$actions_cell = static function (?array $row) use ($e, $can_edit_kpis): string {
    $html = '';
    if ($row && trim((string)($row['actions'] ?? '')) !== '') $html .= '<div class="pcx-file" style="margin:0 0 6px;">' . $e($row['actions']) . '</div>';
    if ($row && $can_edit_kpis && isset($row['id'])) {
        $payload = $e(json_encode($row, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?: '{}');
        $html .= '<button type="button" class="pcx-edit" data-row="' . $payload . '">✎ Edit</button>';
    }
    return $html !== '' ? $html : '—';
};

// ============================================================== PDF download
if (($_GET['export'] ?? '') === 'pdf') {
    if (!$can_download_pdf) { http_response_code(403); exit('PDF download permission is required.'); }
    $pdf_rows = [];   // [type, cells]
    $only = trim((string)($_GET['serial'] ?? ''));   // one indicator (or a whole category letter)
    foreach ($visible_categories as $letter => $cat) {
        if ($only !== '' && strlen($only) === 1 && strtoupper($only) !== $letter) continue;
        if ($only !== '' && strlen($only) > 1 && !in_array($only, array_column($cat['subs'], 'serial_no'), true)) continue;
        $cr = $cat['row'] ?? [];
        if ($only === '' || strlen($only) === 1) $pdf_rows[] = ['cat', [$letter, $category_names[$letter], (string)($cr['unit_of_measure'] ?? ''), number_format($cat_summary[$letter]['weight'], 2), (string)($cr['target'] ?? ''), (string)($cr['achievement_before_review'] ?? ''), (string)($cr['achievement_after_review'] ?? ''), $num($cr['weight_achieved_percent'] ?? $cat_summary[$letter]['achieved']), (string)($cr['status'] ?? ''), (string)($cr['reports'] ?? ''), (string)($cr['actions'] ?? '')]];
        foreach ($cat['subs'] as $sub) {
            if ($only !== '' && strlen($only) > 1 && (string)$sub['serial_no'] !== $only) continue;
            $pdf_rows[] = ['row', [(string)$sub['serial_no'], (string)$sub['performance_criteria'], (string)$sub['unit_of_measure'], $num($sub['weight_percent']), (string)$sub['target'], (string)$sub['achievement_before_review'], (string)$sub['achievement_after_review'], $num($sub['weight_achieved_percent']), (string)$sub['status'], trim(implode('; ', array_column($uploads_by_serial[(string)$sub['serial_no']] ?? [], 'title')) . ' ' . (string)$sub['reports']), (string)$sub['actions']]];
        }
    }
    if ($total_row && $selected_category === '' && $only === '') {
        $pdf_rows[] = ['total', ['', (string)$total_row['performance_criteria'], '', '', '', '', '', $num($total_row['weight_achieved_percent']), (string)$total_row['status'], (string)$total_row['reports'], (string)$total_row['actions']]];
    }
    $subtitle = 'FY ' . $selected_year . ($selected_quarter ? ' - ' . PC_QUARTERS[$selected_quarter]['label'] . ' (' . PC_QUARTERS[$selected_quarter]['months'] . ')' : ' - Q1-Q4') . ($selected_category !== '' ? ' - Category ' . $selected_category : '') . ($only !== '' ? ' - Indicator ' . $only : '');
    pc_stream_pdf($pdf_rows, $subtitle);
}

/** Minimal dependency-free PDF writer: landscape A4 table of the KPIs and Sub KPIs. */
function pc_pdf_safe(string $value): string {
    $converted = function_exists('iconv') ? @iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $value) : $value;
    $converted = preg_replace('/[^\x20-\x7E]/', '?', $converted === false ? $value : $converted) ?? '';
    return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $converted);
}
function pc_stream_pdf(array $rows, string $subtitle): never {
    $headers = ['S/No', 'Performance Criteria', 'Unit', 'Weight %', 'Target', 'Before Review', 'After Review', 'Wt Achieved %', 'Status', 'Reports', 'Actions'];
    $widths = [34, 232, 38, 46, 50, 58, 58, 62, 52, 90, 82];       // points, total 802
    $chars  = [6, 52, 8, 9, 10, 12, 12, 12, 11, 20, 18];            // wrap width per column at 6.5pt
    $x0 = 20; $top = 520; $bottom = 40; $line = 8.5;
    $pages = []; $content = ''; $y = $top;
    $new_page = function () use (&$content, &$y, &$pages, $headers, $widths, $x0, $subtitle, $top) {
        if ($content !== '') $pages[] = $content;
        $content = "BT /F2 14 Tf 20 565 Td (Performance Contract - KPIs and Sub KPIs) Tj ET\n";
        $content .= "BT /F1 8 Tf 20 551 Td (State Department for Medical Services | " . pc_pdf_safe($subtitle) . " | Exported " . date('d M Y') . ") Tj ET\n";
        $content .= "0.05 0.28 0.63 rg 20 " . ($top - 10) . " 802 20 re f 1 1 1 rg\n";
        $x = $x0;
        foreach ($headers as $i => $h) { $content .= "BT /F2 6.5 Tf " . ($x + 3) . " " . ($top - 3) . " Td (" . pc_pdf_safe($h) . ") Tj ET\n"; $x += $widths[$i]; }
        $content .= "0 g\n";
        $y = $top - 22;
    };
    $new_page();
    foreach ($rows as [$type, $cells]) {
        $wrapped = []; $height = 1;
        foreach ($cells as $i => $cell) {
            $wrapped[$i] = explode("\n", wordwrap(trim((string)$cell) === '' ? '-' : trim((string)$cell), $chars[$i], "\n", true));
            $height = max($height, count($wrapped[$i]));
        }
        $row_h = $height * $line + 5;
        if ($y - $row_h < $bottom) $new_page();
        if ($type === 'cat') $content .= "0.93 0.96 1 rg $x0 " . ($y - $row_h + 3) . " 802 $row_h re f 0 g\n";
        if ($type === 'total') $content .= "0.06 0.09 0.16 rg $x0 " . ($y - $row_h + 3) . " 802 $row_h re f 1 g\n";
        $font = $type === 'row' ? 'F1' : 'F2';
        $x = $x0;
        foreach ($wrapped as $i => $lines) {
            foreach ($lines as $n => $text_line) $content .= "BT /$font 6.5 Tf " . ($x + 3) . " " . ($y - $n * $line - 5) . " Td (" . pc_pdf_safe($text_line) . ") Tj ET\n";
            $x += $widths[$i];
        }
        if ($type === 'total') $content .= "0 g\n";
        $content .= "0.88 0.91 0.95 RG $x0 " . ($y - $row_h + 3) . " m " . ($x0 + 802) . " " . ($y - $row_h + 3) . " l S\n";
        $y -= $row_h;
    }
    $pages[] = $content;
    $objects = []; $kids = []; $next = 5;
    foreach ($pages as $n => $stream) {
        $stream .= "BT /F1 7 Tf 400 20 Td (Page " . ($n + 1) . " of " . count($pages) . ") Tj ET\n";
        $page_id = $next++; $content_id = $next++; $kids[] = "$page_id 0 R";
        $objects[$page_id] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 842 595] /Resources << /Font << /F1 3 0 R /F2 4 0 R >> >> /Contents $content_id 0 R >>";
        $objects[$content_id] = "<< /Length " . strlen($stream) . " >>\nstream\n{$stream}endstream";
    }
    $objects[1] = '<< /Type /Catalog /Pages 2 0 R >>';
    $objects[2] = '<< /Type /Pages /Kids [' . implode(' ', $kids) . '] /Count ' . count($kids) . ' >>';
    $objects[3] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>';
    $objects[4] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>';
    ksort($objects);
    $pdf = "%PDF-1.4\n"; $offsets = [];
    foreach ($objects as $id => $object) { $offsets[$id] = strlen($pdf); $pdf .= "$id 0 obj\n$object\nendobj\n"; }
    $xref = strlen($pdf); $max = max(array_keys($objects));
    $pdf .= "xref\n0 " . ($max + 1) . "\n0000000000 65535 f \n";
    for ($id = 1; $id <= $max; $id++) $pdf .= sprintf("%010d 00000 n \n", $offsets[$id] ?? 0);
    $pdf .= "trailer\n<< /Size " . ($max + 1) . " /Root 1 0 R >>\nstartxref\n$xref\n%%EOF";
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="performance-contract-' . date('Y-m-d') . '.pdf"');
    header('Content-Length: ' . strlen($pdf));
    echo $pdf;
    exit;
}

require_once __DIR__ . '/../includes/header.php';
?>

<style>
    .pcx-page { display:flex; flex-direction:column; gap:20px; }
    .pcx-head { display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; }
    .pcx-head h2 { font-size:1.25rem; font-weight:800; color:#0f172a; margin:0; }
    .pcx-head span.pcx-sub { font-size:.8rem; color:#64748b; }
    .pcx-filter { display:flex; flex-wrap:wrap; align-items:flex-end; gap:12px; padding:14px 18px; background:#fff; border:1px solid #e2e8f0; border-radius:8px; }
    .pcx-filter label { display:flex; flex-direction:column; gap:4px; font-size:.72rem; font-weight:800; color:#64748b; text-transform:uppercase; }
    .pcx-filter select { min-width:150px; padding:8px 10px; border:1px solid #cbd5e1; border-radius:6px; background:#f8fafc; font:inherit; font-size:.82rem; color:#1e293b; }
    .pcx-btn { display:inline-block; padding:8px 14px; border:1px solid #cbd5e1; border-radius:6px; background:#fff; color:#334155; font-size:.78rem; font-weight:700; text-decoration:none; cursor:pointer; }
    .pcx-btn-primary { background:#0d47a1; border-color:#0d47a1; color:#fff; }
    .pcx-chip { display:inline-flex; align-items:center; gap:8px; padding:6px 12px; border-radius:999px; background:#eff6ff; border:1px solid #bfdbfe; color:#1e3a8a; font-size:.78rem; font-weight:700; }
    .pcx-chip a { color:#1d4ed8; text-decoration:none; font-weight:800; }

    .pcx-cards { display:grid; grid-template-columns:repeat(auto-fit,minmax(190px,1fr)); gap:14px; }
    .pcx-card { display:block; padding:15px 16px; background:#fff; border:1px solid #e2e8f0; border-top:4px solid var(--pcx-accent,#0d47a1); border-radius:8px; color:inherit; text-decoration:none; transition:transform .15s, box-shadow .15s; }
    .pcx-card:hover, .pcx-card:focus-visible { transform:translateY(-2px); box-shadow:0 6px 14px rgba(16,44,83,.12); outline:none; }
    .pcx-card.is-active { box-shadow:0 0 0 2px var(--pcx-accent,#0d47a1); }
    .pcx-card-label { font-size:.72rem; font-weight:800; color:#64748b; text-transform:uppercase; letter-spacing:.04em; min-height:2.1em; }
    .pcx-card-value { margin-top:6px; font-size:1.4rem; font-weight:800; color:#102c53; font-family:'JetBrains Mono',monospace; }
    .pcx-card-value small { font-size:.75rem; color:#64748b; font-weight:700; }
    .pcx-card-meta { margin-top:3px; font-size:.74rem; font-weight:700; }
    .pcx-card-hint { margin-top:7px; font-size:.7rem; font-weight:700; color:#2563eb; }

    .pcx-panel { background:#fff; border:1px solid #e2e8f0; border-radius:8px; overflow:hidden; box-shadow:0 2px 4px rgba(0,0,0,.02); }
    .pcx-panel-head { display:flex; flex-wrap:wrap; justify-content:space-between; align-items:center; gap:10px; padding:12px 18px; font-size:.85rem; font-weight:700; color:#fff; }
    .pcx-panel-head .pcx-badge { background:#2563eb; padding:2px 8px; border-radius:4px; font:700 .68rem 'JetBrains Mono',monospace; }

    /* Performance Gantt chart */
    .pcx-gantt-cols, .pcx-gantt-row { display:grid; grid-template-columns:minmax(240px,4fr) 1fr 1fr minmax(260px,5fr) 74px; gap:10px; align-items:center; }
    .pcx-gantt-colhead { padding:9px 16px; background:#f1f5f9; border-bottom:1px solid #e2e8f0; font:700 .68rem 'JetBrains Mono',monospace; color:#475569; }
    .pcx-gantt-colhead > div:not(:first-child) { text-align:center; }
    .pcx-gantt-cat { padding:9px 16px; background:#f8fafc; border-top:1px solid #e2e8f0; border-left:5px solid var(--pcx-accent,#0d47a1); }
    .pcx-gantt-cat .pcx-gantt-name { font-weight:800; color:#0f172a; }
    .pcx-gantt-row { padding:10px 16px; border-top:1px solid #f1f5f9; }
    .pcx-gantt-row:hover { background:#f8fafc; }
    .pcx-code { display:inline-block; padding:1px 6px; border-radius:4px; background:#1e3a8a; color:#fff; font:800 .66rem 'JetBrains Mono',monospace; }
    .pcx-weight { display:inline-block; padding:1px 6px; border-radius:4px; background:#fef3c7; color:#78350f; border:1px solid #fde68a; font-weight:700; font-size:.66rem; }
    .pcx-gantt-name { font-size:.78rem; font-weight:700; color:#0f172a; margin-top:4px; line-height:1.3; }
    .pcx-gantt-unit { font-size:.68rem; color:#64748b; }
    .pcx-gantt-val { text-align:center; font:700 .72rem 'JetBrains Mono',monospace; color:#334155; }
    .pcx-q-grid { display:grid; gap:8px; text-align:center; font-family:'JetBrains Mono',monospace; font-size:.66rem; }
    .pcx-q-label { color:#94a3b8; font-size:.6rem; margin-bottom:2px; }
    .pcx-q-track { height:13px; border-radius:99px; background:#f1f5f9; border:1px solid #e2e8f0; overflow:hidden; }
    .pcx-q-bar { height:100%; border-radius:99px; }
    .pcx-q-bar.is-estimated { background-image:repeating-linear-gradient(45deg,rgba(255,255,255,.45) 0 4px,transparent 4px 8px); }
    .pcx-q-num { font-weight:700; color:#475569; }
    .pcx-score { display:inline-block; padding:2px 8px; border-radius:4px; color:#fff; font:800 .68rem 'JetBrains Mono',monospace; }
    .pcx-m { display:none; }
    .pcx-legend { display:flex; flex-wrap:wrap; gap:14px; padding:10px 18px; border-top:1px solid #e2e8f0; background:#f8fafc; font-size:.72rem; color:#475569; }
    .pcx-legend i { display:inline-block; width:12px; height:12px; border-radius:3px; vertical-align:-2px; margin-right:4px; }

    /* KPI table */
    .pcx-table { width:100%; border-collapse:collapse; font-size:.8rem; text-align:left; }
    .pcx-table th { padding:11px 12px; background:#f8fafc; color:#475569; border-bottom:2px solid #e2e8f0; font-size:.68rem; font-weight:800; text-transform:uppercase; white-space:nowrap; vertical-align:bottom; }
    .pcx-table td { padding:11px 12px; border-bottom:1px solid #f1f5f9; vertical-align:top; color:#334155; }
    .pcx-table .pcx-num { text-align:right; font-family:'JetBrains Mono',monospace; white-space:nowrap; }
    .pcx-table tr.pcx-cat-row td { background:#eef4ff; font-weight:800; color:#0f172a; border-top:2px solid #c7d7f5; }
    .pcx-table tr.pcx-total-row td { background:#0f172a; color:#fff; font-weight:800; border-bottom:0; }
    .pcx-serial { font-family:'JetBrains Mono',monospace; font-weight:800; color:#0d47a1; white-space:nowrap; }
    .pcx-calc { font-style:italic; color:#64748b; }
    .pcx-status { display:inline-block; padding:3px 9px; border-radius:10px; font-size:.68rem; font-weight:800; }
    .pcx-notice { padding:14px 18px; border-radius:8px; background:#fff7ed; border:1px solid #fed7aa; color:#9a3412; font-size:.84rem; }
    .pcx-empty { padding:28px; text-align:center; color:#64748b; }
    @media (max-width:1000px) {
        .pcx-gantt-cols { display:none; }
        .pcx-m { display:inline; }
        .pcx-gantt-row { grid-template-columns:1fr 1fr; }
        .pcx-gantt-row > :first-child, .pcx-gantt-row > .pcx-q-grid { grid-column:1 / -1; }
    }
    .pcx-tabs { display:flex; flex-wrap:wrap; gap:6px; border-bottom:2px solid #e2e8f0; }
    .pcx-tab { padding:9px 16px; border:1px solid #e2e8f0; border-bottom:0; border-radius:8px 8px 0 0; background:#f8fafc; color:#334155; font-size:.8rem; font-weight:800; text-decoration:none; margin-bottom:-2px; }
    .pcx-tab:hover { background:#eef4ff; }
    .pcx-tab.is-active { background:#0d47a1; border-color:#0d47a1; color:#fff; }
    .pcx-dl { display:inline-block; padding:4px 10px; border-radius:6px; background:#059669; color:#fff !important; font-size:.72rem; font-weight:800; text-decoration:none; white-space:nowrap; }
    .pcx-dl:hover { background:#047857; }
    .pcx-edit { padding:4px 10px; border:1px solid #0d47a1; border-radius:6px; background:#fff; color:#0d47a1; font:inherit; font-size:.72rem; font-weight:800; cursor:pointer; }
    .pcx-edit:hover { background:#0d47a1; color:#fff; }
    .pcx-file { margin-top:5px; font-size:.7rem; color:#475569; } .pcx-file a { color:#0d47a1; font-weight:700; text-decoration:none; }
    .pcx-table tr.pcx-total-row .pcx-edit { background:transparent; border-color:#fff; color:#fff; }
    dialog.pcx-dialog { width:min(720px,calc(100% - 28px)); border:0; border-radius:12px; padding:0; box-shadow:0 20px 60px #0f172a55; }
    dialog.pcx-dialog::backdrop { background:rgba(15,23,42,.5); }
    .pcx-dialog-head { display:flex; justify-content:space-between; align-items:center; padding:14px 18px; background:#0d47a1; color:#fff; font-weight:800; }
    .pcx-dialog-head button { background:none; border:0; color:#fff; font-size:1.3rem; cursor:pointer; }
    .pcx-dialog form { display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:12px; padding:18px; }
    .pcx-dialog label { display:flex; flex-direction:column; gap:4px; font-size:.7rem; font-weight:800; color:#64748b; text-transform:uppercase; }
    .pcx-dialog input, .pcx-dialog select, .pcx-dialog textarea { padding:8px 10px; border:1px solid #cbd5e1; border-radius:6px; font:inherit; font-size:.82rem; color:#1e293b; text-transform:none; }
    .pcx-dialog .pcx-full { grid-column:1 / -1; }
    .pcx-tab-action { margin-left:auto; background:#059669; border-color:#059669; color:#fff; }
    .pcx-tab-action:hover { background:#047857; }
    .pcx-upload-form { display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:14px; padding:18px; }
    .pcx-upload-form label { display:flex; flex-direction:column; gap:4px; font-size:.72rem; font-weight:800; color:#64748b; text-transform:uppercase; }
    .pcx-upload-form select, .pcx-upload-form input { padding:8px 10px; border:1px solid #cbd5e1; border-radius:6px; background:#f8fafc; font:inherit; font-size:.82rem; color:#1e293b; text-transform:none; }
    .pcx-upload-form .pcx-full { grid-column:1 / -1; }
    .pcx-flash { padding:12px 16px; border-radius:8px; font-size:.84rem; font-weight:700; }
    .pcx-flash-ok { background:#ecfdf5; border:1px solid #10b981; color:#065f46; }
    .pcx-flash-err { background:#fef2f2; border:1px solid #fca5a5; color:#991b1b; }
    @media print { .pcx-tabs { display:none !important; } }
    @media print { .pcx-filter, .pcx-btn { display:none !important; } }
</style>

<div class="pcx-page">

    <div class="pcx-head">
        <div>
            <h2>🛡️ Performance Contract (PC) Management</h2>
            <span class="pcx-sub">FY <?= $e($selected_year) ?> &bull; KPIs &amp; Sub-KPIs, weights, reviewed achievements and quarterly progression</span>
        </div>
        <button type="button" class="pcx-btn" onclick="window.print();">🖨️ Print Evaluation Summary</button>
    </div>

    <nav class="pcx-tabs" aria-label="Performance contract sections">
        <a class="pcx-tab<?= $tab === 'overview' ? ' is-active' : '' ?>" href="<?= $e($link(['tab' => null])) ?>">📊 Performance Overview</a>
        <a class="pcx-tab<?= $tab === 'upload' ? ' is-active' : '' ?>" href="<?= $e($link(['tab' => 'upload'])) ?>">⬆ Upload Report</a>
    </nav>

    <?php if (!$db_connected): ?>
        <div class="pcx-notice" role="alert">The database connection is unavailable, so the Performance Contract cannot be loaded.</div>
    <?php elseif ($table_missing): ?>
        <div class="pcx-notice" role="alert">The <code>kpis_and_sub_kpis</code> table does not exist yet. Import <code>database/kpis_and_sub_kpis.sql</code> (or the updated <code>database/schema.sql</code>) into the database and reload this page.</div>
    <?php elseif ($load_error): ?>
        <div class="pcx-notice" role="alert">The Performance Contract data could not be loaded. Check the database schema and try again.</div>
    <?php endif; ?>

    <?php if ($tab === 'overview'): ?>
    <?php if (($_GET['msg'] ?? '') === 'kpi_saved'): ?><div class="pcx-flash pcx-flash-ok" role="status">The record was updated.</div><?php endif; ?>
    <?php if ($edit_error !== ''): ?><div class="pcx-flash pcx-flash-err" role="alert"><?= $e($edit_error) ?></div><?php endif; ?>
    <!-- Filters: Year and Quarter -->
    <form method="get" action="/modules/pc.php" class="pcx-filter" id="pcFilterForm" aria-label="Performance contract filters">
        <?php if ($selected_category !== ''): ?><input type="hidden" name="category" value="<?= $e($selected_category) ?>"><?php endif; ?>
        <label>Year
            <select name="year" aria-label="Filter by financial year">
                <?php foreach ($year_options as $year_option): ?><option value="<?= $e($year_option) ?>" <?= $year_option === $selected_year ? 'selected' : '' ?>>FY <?= $e($year_option) ?></option><?php endforeach; ?>
            </select>
        </label>
        <label>Quarter
            <select name="quarter" aria-label="Filter by quarter">
                <option value="0" <?= $selected_quarter === 0 ? 'selected' : '' ?>>All Quarters (Q1–Q4)</option>
                <?php foreach (PC_QUARTERS as $q => $meta): ?><option value="<?= $q ?>" <?= $q === $selected_quarter ? 'selected' : '' ?>><?= $e($meta['label']) ?> (<?= $e($meta['months']) ?>)</option><?php endforeach; ?>
            </select>
        </label>
        <button type="submit" class="pcx-btn pcx-btn-primary">Apply</button>
        <a class="pcx-btn" href="/modules/pc.php">Reset</a>
        <?php if ($selected_category !== ''): ?>
            <span class="pcx-chip">Showing: <?= $e($selected_category) ?> — <?= $e($category_names[$selected_category]) ?> <a href="<?= $e($link(['category' => null])) ?>" aria-label="Show all categories">✕</a></span>
        <?php endif; ?>
    </form>

    <?php if (!$year_rows && !$table_missing && !$load_error && $db_connected): ?>
        <div class="pcx-panel"><div class="pcx-empty">No performance contract records exist for FY <?= $e($selected_year) ?>.</div></div>
    <?php endif; ?>

    <?php if ($categories): ?>
    <!-- Key statistics: each card opens the table and Gantt chart for that category -->
    <div class="pcx-cards" aria-label="Key performance statistics">
        <?php foreach ($categories as $letter => $cat):
            $summary = $cat_summary[$letter];
            $achieved_for_card = $card_achieved($letter);
            $card_pct = $summary['weight'] > 0 ? $achieved_for_card / $summary['weight'] * 100 : null;
        ?>
            <a class="pcx-card<?= $selected_category === $letter ? ' is-active' : '' ?>" style="--pcx-accent:<?= $e($category_colors[$letter] ?? '#0d47a1') ?>" href="<?= $e($link(['category' => $letter]) . '#pcKpiTable') ?>" title="Show <?= $e($category_names[$letter]) ?> indicators">
                <div class="pcx-card-label"><?= $e($letter) ?> — <?= $e($category_names[$letter]) ?></div>
                <div class="pcx-card-value"><?= $e(number_format($achieved_for_card, 2)) ?> <small>/ <?= $e(number_format($summary['weight'], 1)) ?> pts</small></div>
                <div class="pcx-card-meta" style="color:<?= $pct_color($card_pct) ?>"><?= $card_pct === null ? '—' : $e(number_format($card_pct, 1)) . '% of weight achieved' ?><?= $selected_quarter ? ' (' . $e(PC_QUARTERS[$selected_quarter]['label']) . ')' : '' ?></div>
                <div class="pcx-card-hint">View <?= count($cat['subs']) ?> indicator<?= count($cat['subs']) === 1 ? '' : 's' ?> &rarr;</div>
            </a>
        <?php endforeach; ?>
        <a class="pcx-card<?= $selected_category === '' ? ' is-active' : '' ?>" style="--pcx-accent:#0f172a" href="<?= $e($link(['category' => null]) . '#pcKpiTable') ?>" title="Show all categories">
            <div class="pcx-card-label">Total weight achieved</div>
            <div class="pcx-card-value"><?= $e(number_format($grand_card_achieved, 2)) ?> <small>/ <?= $e(number_format($grand_weight, 1)) ?></small></div>
            <div class="pcx-card-meta" style="color:<?= $pct_color($grand_weight > 0 ? $grand_card_achieved / $grand_weight * 100 : null) ?>"><?= $grand_weight > 0 ? $e(number_format($grand_card_achieved / $grand_weight * 100, 1)) . '% composite score' : '—' ?><?= $selected_quarter ? ' (' . $e(PC_QUARTERS[$selected_quarter]['label']) . ')' : '' ?></div>
            <div class="pcx-card-hint">View all categories &rarr;</div>
        </a>
    </div>

    <!-- Performance Gantt chart (Q1–Q4) -->
    <section class="pcx-panel" id="pcGantt" aria-label="Performance Contract Gantt chart">
        <div class="pcx-panel-head" style="background:#0f172a;">
            <span>📊 Performance Contract Gantt Chart — Quarterly Progression &amp; Achievement Levels</span>
            <span class="pcx-badge">FY <?= $e($selected_year) ?><?= $selected_quarter ? ' · ' . $e(PC_QUARTERS[$selected_quarter]['label']) : ' · Q1–Q4' ?></span>
        </div>
        <div class="pcx-gantt-colhead pcx-gantt-cols">
            <div>Sub-KPI Code, Weight &amp; Indicator</div>
            <div>Target</div>
            <div>Achieved</div>
            <div>Quarterly Progression (<?= $selected_quarter ? $e(PC_QUARTERS[$selected_quarter]['label']) : 'Q1 • Q2 • Q3 • Q4' ?>)</div>
            <div style="text-align:right;">Score</div>
        </div>

        <?php foreach ($visible_categories as $letter => $cat):
            $summary = $cat_summary[$letter];
            $accent = $category_colors[$letter] ?? '#0d47a1';
        ?>
            <div class="pcx-gantt-cat pcx-gantt-row" style="--pcx-accent:<?= $e($accent) ?>">
                <div><span class="pcx-code" style="background:<?= $e($accent) ?>"><?= $e($letter) ?></span> <span class="pcx-weight">Weight: <?= $e(number_format($summary['weight'], 1)) ?>%</span>
                    <div class="pcx-gantt-name"><?= $e($category_names[$letter]) ?></div></div>
                <div class="pcx-gantt-val">—</div>
                <div class="pcx-gantt-val"><?= $e(number_format($summary['achieved'], 2)) ?> pts</div>
                <div class="pcx-q-grid" style="grid-template-columns:repeat(<?= count($visible_quarters) ?>,1fr)">
                    <?php foreach ($visible_quarters as $q => $meta): $qv = $summary['quarters'][$q]; if ($qv['estimated']) $any_estimated = true; ?>
                        <div><div class="pcx-q-label"><?= $e($meta['label']) ?> (<?= $e($meta['months']) ?>)</div>
                            <div class="pcx-q-track"><div class="pcx-q-bar<?= $qv['estimated'] ? ' is-estimated' : '' ?>" style="width:<?= $qv['pct'] === null ? 0 : min(100, $qv['pct']) ?>%;background-color:<?= $e($meta['color']) ?>"></div></div>
                            <span class="pcx-q-num"><?= $qv['pct'] === null ? '—' : $e(number_format($qv['pct'], 0)) . '%' ?></span></div>
                    <?php endforeach; ?>
                </div>
                <div style="text-align:right;"><span class="pcx-score" style="background:<?= $pct_color($summary['pct']) ?>"><?= $summary['pct'] === null ? '—' : $e(number_format($summary['pct'], 0)) . '%' ?></span></div>
            </div>
            <?php foreach ($cat['subs'] as $sub):
                $serial = (string)$sub['serial_no'];
                $meta_row = $sub_meta[$serial];
            ?>
                <div class="pcx-gantt-row">
                    <div>
                        <span class="pcx-code"><?= $e($serial) ?></span>
                        <span class="pcx-weight">Weight: <?= $e($num($sub['weight_percent'], 1)) ?>%</span>
                        <div class="pcx-gantt-name"><?= $e($sub['performance_criteria']) ?></div>
                        <div class="pcx-gantt-unit">Unit: <?= $e($text($sub['unit_of_measure'])) ?> &bull; <?= $e($text($sub['status'])) ?></div>
                    </div>
                    <div class="pcx-gantt-val"><span class="pcx-m" style="color:#94a3b8;font-weight:400;">Target: </span><?= $e($text($sub['target'])) ?></div>
                    <div class="pcx-gantt-val"><span class="pcx-m" style="color:#94a3b8;font-weight:400;">Achieved: </span><?= $e($text($sub['achievement_after_review'])) ?></div>
                    <div class="pcx-q-grid" style="grid-template-columns:repeat(<?= count($visible_quarters) ?>,1fr)">
                        <?php foreach ($visible_quarters as $q => $meta): $qv = $meta_row['quarters'][$q]; if ($qv['estimated'] && $qv['pct'] !== null) $any_estimated = true; ?>
                            <div><div class="pcx-q-label"><?= $e($meta['label']) ?> (<?= $e($meta['months']) ?>)</div>
                                <div class="pcx-q-track"><div class="pcx-q-bar<?= $qv['estimated'] ? ' is-estimated' : '' ?>" style="width:<?= $qv['pct'] === null ? 0 : min(100, $qv['pct']) ?>%;background-color:<?= $e($meta['color']) ?>"></div></div>
                                <span class="pcx-q-num"><?= $qv['pct'] === null ? '—' : $e(number_format($qv['pct'], 0)) . '%' ?></span></div>
                        <?php endforeach; ?>
                    </div>
                    <div style="text-align:right;"><span class="pcx-score" style="background:<?= $pct_color($meta_row['pct']) ?>"><?= $meta_row['pct'] === null ? '—' : $e(number_format($meta_row['pct'], 0)) . '%' ?></span></div>
                </div>
            <?php endforeach; ?>
        <?php endforeach; ?>

        <div class="pcx-legend">
            <?php foreach (PC_QUARTERS as $q => $meta): ?><span><i style="background:<?= $e($meta['color']) ?>"></i><?= $e($meta['label']) ?> <?= $e($meta['months']) ?></span><?php endforeach; ?>
            <span><i style="background:#059669"></i>Score ≥ 90%</span><span><i style="background:#2563eb"></i>75–89%</span><span><i style="background:#e11d48"></i>&lt; 75%</span>
            <?php if ($any_estimated): ?><span>Striped bars are indicative: they are phased from the reviewed score until quarterly scores are recorded in <code>kpi_quarterly_scores</code>.</span><?php endif; ?>
        </div>
    </section>

    <!-- KPIs and Sub-KPIs table: every column of kpis_and_sub_kpis -->
    <section class="pcx-panel" id="pcKpiTable" aria-label="KPIs and Sub KPIs">
        <div class="pcx-panel-head" style="background:#0d47a1;">
            <span>Performance Contract — KPIs and Sub KPIs<?= $selected_category !== '' ? ' · ' . $e($selected_category) . ' — ' . $e($category_names[$selected_category]) : '' ?></span>
            <span>Total Weight: <?= $e(number_format($grand_weight, 1)) ?>% &bull; Weight Achieved: <?= $e(number_format($grand_card_achieved, 2)) ?></span>
        </div>
        <div style="overflow-x:auto;">
            <table class="pcx-table">
                <thead>
                    <tr>
                        <th>S/No</th>
                        <th>Performance Criteria</th>
                        <th>Unit of Measure</th>
                        <th class="pcx-num">Weight (%)</th>
                        <th class="pcx-num">Target</th>
                        <th class="pcx-num">Achievement Before Review</th>
                        <th class="pcx-num">Achievement After Review</th>
                        <th class="pcx-num">Weight Achieved (%)</th>
                        <th>Status</th>
                        <th>Reports</th>
                        <th>Actions</th>
                        <?php if ($selected_quarter): ?><th class="pcx-num"><?= $e(PC_QUARTERS[$selected_quarter]['label']) ?> Progress (%)</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($visible_categories as $letter => $cat):
                        $summary = $cat_summary[$letter];
                        $category_row = $cat['row'] ?? [];
                        $category_weight_raw = $category_row['weight_percent'] ?? null;
                    ?>
                        <tr class="pcx-cat-row" id="cat-<?= $e($letter) ?>">
                            <td class="pcx-serial"><?= $e($letter) ?></td>
                            <td><?= $e($category_names[$letter]) ?></td>
                            <td><?= $e($text($category_row['unit_of_measure'] ?? null)) ?></td>
                            <td class="pcx-num"><?php if ($category_weight_raw !== null && (float)$category_weight_raw > 0): ?><?= $e($num($category_weight_raw)) ?><?php else: ?><span class="pcx-calc" title="Sum of the sub-KPI weights"><?= $e(number_format($summary['weight'], 2)) ?></span><?php endif; ?></td>
                            <td class="pcx-num"><?= $e($text($category_row['target'] ?? null)) ?></td>
                            <td class="pcx-num"><?= $e($text($category_row['achievement_before_review'] ?? null)) ?></td>
                            <td class="pcx-num"><?= $e($text($category_row['achievement_after_review'] ?? null)) ?></td>
                            <td class="pcx-num"><?= $e($num($category_row['weight_achieved_percent'] ?? $summary['achieved'])) ?></td>
                            <td><span class="pcx-status" style="<?= $status_style($category_row['status'] ?? null) ?>"><?= $e($text($category_row['status'] ?? null)) ?></span></td>
                            <td><?= $reports_cell($letter, $category_row['reports'] ?? null) ?></td>
                            <td><?= $actions_cell($category_row ?: null) ?></td>
                            <?php if ($selected_quarter): $cq = $summary['quarters'][$selected_quarter]; ?><td class="pcx-num"><?= $cq['pct'] === null ? '—' : $e(number_format($cq['pct'], 1)) ?><?= $cq['estimated'] ? '*' : '' ?></td><?php endif; ?>
                        </tr>
                        <?php foreach ($cat['subs'] as $sub): $serial = (string)$sub['serial_no']; ?>
                            <tr>
                                <td class="pcx-serial"><?= $e($serial) ?></td>
                                <td><?= $e($sub['performance_criteria']) ?></td>
                                <td><?= $e($text($sub['unit_of_measure'])) ?></td>
                                <td class="pcx-num"><?= $e($num($sub['weight_percent'])) ?></td>
                                <td class="pcx-num"><?= $e($text($sub['target'])) ?></td>
                                <td class="pcx-num"><?= $e($text($sub['achievement_before_review'])) ?></td>
                                <td class="pcx-num"><?= $e($text($sub['achievement_after_review'])) ?></td>
                                <td class="pcx-num"><?= $e($num($sub['weight_achieved_percent'])) ?></td>
                                <td><span class="pcx-status" style="<?= $status_style($sub['status']) ?>"><?= $e($text($sub['status'])) ?></span></td>
                                <td><?= $reports_cell($serial, $sub['reports']) ?></td>
                                <td><?= $actions_cell($sub) ?></td>
                                <?php if ($selected_quarter): $sq = $sub_meta[$serial]['quarters'][$selected_quarter]; ?><td class="pcx-num"><?= $sq['pct'] === null ? '—' : $e(number_format($sq['pct'], 1)) ?><?= $sq['estimated'] && $sq['pct'] !== null ? '*' : '' ?></td><?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                    <?php if ($total_row && $selected_category === ''): ?>
                        <tr class="pcx-total-row">
                            <td></td>
                            <td colspan="6"><?= $e($text($total_row['performance_criteria'])) ?></td>
                            <td class="pcx-num"><?= $e($num($selected_quarter ? $grand_card_achieved : $total_row['weight_achieved_percent'])) ?></td>
                            <td><?= $e($text($total_row['status'])) ?></td>
                            <td><?= $reports_cell('', $total_row['reports']) ?></td>
                            <td><?= $actions_cell($total_row) ?></td>
                            <?php if ($selected_quarter): ?><td></td><?php endif; ?>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if ($selected_quarter): ?><div class="pcx-legend">* indicative value phased from the reviewed score (no quarterly score recorded yet).</div><?php endif; ?>
    </section>
    <?php endif; /* categories */ ?>
    <?php endif; /* overview tab */ ?>

    <?php if ($tab === 'upload'): ?>
    <!-- Upload Report tab -->
    <?php if (($_GET['msg'] ?? '') === 'uploaded'): ?><div class="pcx-flash pcx-flash-ok" role="status">The report was uploaded successfully.</div><?php endif; ?>
    <?php if (($_GET['msg'] ?? '') === 'deleted'): ?><div class="pcx-flash pcx-flash-ok" role="status">The report was removed.</div><?php endif; ?>
    <?php if ($upload_error !== ''): ?><div class="pcx-flash pcx-flash-err" role="alert"><?= $e($upload_error) ?></div><?php endif; ?>
    <?php if (!$uploads_ready): ?><div class="pcx-notice" role="alert">Report uploads are unavailable: the database could not create the <code>pc_report_uploads</code> table.</div><?php endif; ?>

    <?php if ($uploads_ready && $can_upload_reports): ?>
    <section class="pcx-panel" aria-label="Upload a performance contract report">
        <div class="pcx-panel-head" style="background:#0d47a1;"><span>Upload Report</span><span>PDF, Word, Excel, PowerPoint, CSV or image &bull; max 10 MB</span></div>
        <form method="post" action="<?= $e($link(['tab' => 'upload'])) ?>" enctype="multipart/form-data" class="pcx-upload-form">
            <input type="hidden" name="csrf_token" value="<?= $e(get_csrf_token()) ?>">
            <input type="hidden" name="form_action" value="upload">
            <label>Indicator
                <select name="serial_no">
                    <option value="">General — whole performance contract</option>
                    <?php foreach ($categories as $letter => $cat): ?>
                        <optgroup label="<?= $e($letter . ' — ' . $category_names[$letter]) ?>">
                            <option value="<?= $e($letter) ?>"><?= $e($letter) ?> — <?= $e($category_names[$letter]) ?> (category)</option>
                            <?php foreach ($cat['subs'] as $sub): ?><option value="<?= $e($sub['serial_no']) ?>"><?= $e($sub['serial_no']) ?> — <?= $e($sub['performance_criteria']) ?></option><?php endforeach; ?>
                        </optgroup>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Year
                <select name="financial_year"><?php foreach ($year_options as $year_option): ?><option value="<?= $e($year_option) ?>" <?= $year_option === $selected_year ? 'selected' : '' ?>>FY <?= $e($year_option) ?></option><?php endforeach; ?></select>
            </label>
            <label>Quarter
                <select name="quarter"><option value="0">Annual (all quarters)</option><?php foreach (PC_QUARTERS as $q => $meta): ?><option value="<?= $q ?>" <?= $q === $selected_quarter ? 'selected' : '' ?>><?= $e($meta['label']) ?> (<?= $e($meta['months']) ?>)</option><?php endforeach; ?></select>
            </label>
            <label>Report title (optional)<input type="text" name="title" maxlength="255" placeholder="e.g. Q2 evidence for A1"></label>
            <label class="pcx-full">Report file *<input type="file" name="report_file" required accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.csv,.png,.jpg,.jpeg"></label>
            <div class="pcx-full"><button type="submit" class="pcx-btn pcx-btn-primary">⬆ Upload Report</button></div>
        </form>
    </section>
    <?php elseif ($uploads_ready): ?>
        <div class="pcx-notice">You do not have permission to upload reports. Ask an administrator to grant the Upload permission.</div>
    <?php endif; ?>

    <?php if ($uploads_ready): ?>
    <section class="pcx-panel" aria-label="Uploaded reports">
        <div class="pcx-panel-head" style="background:#0f172a;"><span>Uploaded Reports</span><span><?= count($report_uploads) ?> file<?= count($report_uploads) === 1 ? '' : 's' ?></span></div>
        <div style="overflow-x:auto;"><table class="pcx-table">
            <thead><tr><th>Title</th><th>Indicator</th><th>Year</th><th>Quarter</th><th>Uploaded by</th><th>Date</th><th class="pcx-num">Size</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($report_uploads as $upload): ?>
                <tr>
                    <td><?= $e($upload['title']) ?><div style="font-size:.7rem;color:#64748b;"><?= $e($upload['original_name']) ?></div></td>
                    <td><?= $upload['serial_no'] === null ? 'General' : '<span class="pcx-serial">' . $e($upload['serial_no']) . '</span>' ?></td>
                    <td>FY <?= $e($upload['financial_year']) ?></td>
                    <td><?= (int)$upload['quarter'] === 0 ? 'Annual' : $e(PC_QUARTERS[(int)$upload['quarter']]['label'] ?? '—') ?></td>
                    <td><?= $e($text($upload['uploaded_by'])) ?></td>
                    <td><?= $e(substr((string)$upload['created_at'], 0, 10)) ?></td>
                    <td class="pcx-num"><?= $e(number_format($upload['file_size'] / 1024, 0)) ?> KB</td>
                    <td style="white-space:nowrap;">
                        <a class="pcx-btn" href="/modules/pc.php?download=<?= (int)$upload['id'] ?>">⬇ Download</a>
                        <?php if ($can_upload_reports): ?>
                        <form method="post" action="<?= $e($link(['tab' => 'upload'])) ?>" style="display:inline;" onsubmit="return confirm('Remove this report? This cannot be undone.');">
                            <input type="hidden" name="csrf_token" value="<?= $e(get_csrf_token()) ?>"><input type="hidden" name="form_action" value="delete"><input type="hidden" name="id" value="<?= (int)$upload['id'] ?>">
                            <button type="submit" class="pcx-btn" style="color:#b91c1c;">Remove</button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$report_uploads): ?><tr><td colspan="8" class="pcx-empty">No reports have been uploaded yet.</td></tr><?php endif; ?>
            </tbody>
        </table></div>
    </section>
    <?php endif; ?>
    <?php endif; ?>

</div>

<?php if ($can_edit_kpis && $tab === 'overview'): ?>
<dialog class="pcx-dialog" id="pcEditDialog" aria-labelledby="pcEditTitle">
    <div class="pcx-dialog-head"><span id="pcEditTitle">Edit record</span><button type="button" id="pcEditClose" aria-label="Close">×</button></div>
    <form method="post" action="<?= $e($link([]) . '#pcKpiTable') ?>" id="pcEditForm">
        <input type="hidden" name="csrf_token" value="<?= $e(get_csrf_token()) ?>">
        <input type="hidden" name="form_action" value="edit_kpi">
        <input type="hidden" name="id" value="">
        <label class="pcx-full">Performance criteria *<textarea name="performance_criteria" rows="2" required></textarea></label>
        <label>Unit of measure<input name="unit_of_measure" maxlength="50"></label>
        <label>Weight (%)<input name="weight_percent" type="number" step="0.01" min="0" max="999.99"></label>
        <label>Target<input name="target" maxlength="50"></label>
        <label>Achievement before review<input name="achievement_before_review" maxlength="50"></label>
        <label>Achievement after review<input name="achievement_after_review" maxlength="50"></label>
        <label>Weight achieved (%)<input name="weight_achieved_percent" type="number" step="0.01" min="0" max="999.99"></label>
        <label>Status<select name="status"><option value="">—</option><?php foreach ($kpi_statuses as $status_option): ?><option value="<?= $e($status_option) ?>"><?= $e($status_option) ?></option><?php endforeach; ?></select></label>
        <label>Reports (note)<input name="reports" maxlength="255"></label>
        <label class="pcx-full">Actions<input name="actions" maxlength="255"></label>
        <div class="pcx-full" style="display:flex;justify-content:flex-end;gap:10px;">
            <button type="button" class="pcx-btn" id="pcEditCancel">Cancel</button>
            <button type="submit" class="pcx-btn pcx-btn-primary">Save changes</button>
        </div>
    </form>
</dialog>
<?php endif; ?>

<script>
(() => {
    const dialog = document.getElementById('pcEditDialog');
    if (dialog) {
        const form = document.getElementById('pcEditForm');
        document.querySelectorAll('.pcx-edit').forEach(button => button.addEventListener('click', () => {
            const row = JSON.parse(button.dataset.row || '{}');
            ['id','performance_criteria','unit_of_measure','weight_percent','target','achievement_before_review','achievement_after_review','weight_achieved_percent','status','reports','actions']
                .forEach(name => { if (form.elements[name]) form.elements[name].value = row[name] ?? ''; });
            document.getElementById('pcEditTitle').textContent = 'Edit ' + (row.serial_no ? row.serial_no + ' — ' : '') + (row.performance_criteria || 'record').slice(0, 70);
            dialog.showModal();
        }));
        const close = () => dialog.close();
        document.getElementById('pcEditClose').addEventListener('click', close);
        document.getElementById('pcEditCancel').addEventListener('click', close);
        dialog.addEventListener('click', event => { if (event.target === dialog) close(); });
    }
})();
(() => {
    // Year / Quarter filters apply as soon as they change.
    const form = document.getElementById('pcFilterForm');
    form?.querySelectorAll('select').forEach(select => select.addEventListener('change', () => form.requestSubmit()));
})();
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>