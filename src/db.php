<?php
session_start();
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = mysqli_connect(
        getenv('DB_HOST') ?: 'db',
        getenv('DB_USER') ?: 'root',
        getenv('DB_PASS') ?: 'root123',
        getenv('DB_NAME') ?: 'repair_db',
        (int)(getenv('DB_PORT') ?: 3306)
    );
    mysqli_set_charset($conn, 'utf8mb4');
} catch (mysqli_sql_exception $ex) {
    http_response_code(500);
    die("Database connection failed: " . htmlspecialchars($ex->getMessage()));
}

function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function redirect($url) { header("Location: $url"); exit; }
function flash($type, $msg) { $_SESSION['flash'] = [$type, $msg]; }
function get_flash() { $f = $_SESSION['flash'] ?? null; unset($_SESSION['flash']); return $f; }
function csrf_token() {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return $_SESSION['csrf'];
}
function csrf_field() { return '<input type="hidden" name="csrf" value="' . csrf_token() . '">'; }
function check_csrf() {
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(400);
        die("Invalid request token. Go back and try again.");
    }
}
function status_class($s) { return 's-' . strtolower(str_replace(' ', '-', $s)); }
