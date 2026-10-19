<?php
/**
 * Database connection (PDO/MySQL).
 * Update the credentials below to match your server.
 */

define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'perfmon');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $opts = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $opts);
        } catch (PDOException $e) {
            http_response_code(500);
            die('Database connection failed: ' . htmlspecialchars($e->getMessage()));
        }
    }
    return $pdo;
}

/** Escape for HTML output. */
function e(?string $v): string
{
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

/** Format a date for display (YYYY-MM-DD -> DD Mon YYYY). */
function fmt_date(?string $d): string
{
    if (!$d || $d === '0000-00-00') return '—';
    $ts = strtotime($d);
    return $ts ? date('d M Y', $ts) : '—';
}

/** Format a decimal, trimming trailing zeros. */
function fmt_num($n): string
{
    if ($n === null || $n === '') return '0';
    return rtrim(rtrim(number_format((float)$n, 2, '.', ','), '0'), '.');
}
