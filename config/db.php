<?php
/**
 * Database connection (PDO/MySQL) for PP-SDOMS.
 *
 * Exposes the globals expected by the original modules:
 *   $db_connected (bool) and $pdo (PDO|null).
 *
 * If includes/header.php (the real auth layer) is present it will define the
 * auth helpers; otherwise this file provides safe fallbacks so the pages still
 * run for local deployment / testing.
 */

define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'chasonet_mohdemo');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

global $pdo, $db_connected;
$db_connected = false;
$pdo = null;

$dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
$opts = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];
try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $opts);
    $db_connected = true;
} catch (PDOException $e) {
    $pdo = null;
    $db_connected = false;
    error_log('DB connection failed: ' . $e->getMessage());
}

/* ----------------------------------------------------------------------- */
/*  Auth + security helpers                                                */
/*  (the real implementations live in includes/header.php when deployed;   */
/*   these fallbacks make the modules runnable standalone.)                */
/* ----------------------------------------------------------------------- */

if (!function_exists('require_auth')) {
    function require_auth(): void { /* no-op fallback */ }
}
if (!function_exists('get_current_user_data')) {
    function get_current_user_data(): array {
        return ['full_name' => 'Local User', 'email' => 'local@localhost',
                'can_upload' => 1, 'can_download' => 1, 'can_edit' => 1,
                'is_admin' => 1, 'role' => 'Administrator'];
    }
}
if (!function_exists('can_edit')) {
    function can_edit(): bool { return true; }
}
if (!function_exists('get_csrf_token')) {
    function get_csrf_token(): string {
        if (session_status() !== PHP_SESSION_ACTIVE) @session_start();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}
if (!function_exists('verify_csrf_token')) {
    function verify_csrf_token(string $token): bool {
        if (session_status() !== PHP_SESSION_ACTIVE) @session_start();
        return !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}
if (!function_exists('log_audit_trail')) {
    function log_audit_trail(string $action, string $module, ?int $record_id, string $details): void {
        global $pdo, $db_connected;
        if (!$db_connected || !$pdo) return;
        try {
            $u = get_current_user_data();
            $pdo->prepare('INSERT INTO audit_logs (user_name, user_email, action, module, record_id, details, ip_address) VALUES (?,?,?,?,?,?,?)')
                ->execute([$u['full_name'] ?? 'System', $u['email'] ?? '', $action, $module, $record_id, $details, $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1']);
        } catch (Throwable $e) { /* ignore */ }
    }
}
