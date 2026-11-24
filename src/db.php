<?php
// ===== 1. DATABASE SETTINGS =====
// On Railway these come from environment variables. The fallbacks below match your local docker-compose.
$host = getenv('MYSQLHOST')     ?: 'db';
$port = getenv('MYSQLPORT')     ?: '3306';
$user = getenv('MYSQLUSER')     ?: 'root';
$pass = getenv('MYSQLPASSWORD') ?: 'root123';
$name = getenv('MYSQLDATABASE') ?: 'repair_db';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $conn = new mysqli($host, $user, $pass, $name, (int)$port);
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $ex) {
    die("Connection failed: " . htmlspecialchars($ex->getMessage()));
}

// ===== 2. HELPERS =====
if (session_status() === PHP_SESSION_NONE) session_start();

// escape output
function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

// redirect and stop
function redirect($url) { header("Location: $url"); exit; }

// flash messages (shown once as a toast)
function flash($type, $msg) { $_SESSION['flash'] = [$type, $msg]; }
function get_flash() {
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

// CSRF protection
function csrf_field() {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return '<input type="hidden" name="csrf" value="' . e($_SESSION['csrf']) . '">';
}
function check_csrf() {
    $ok = isset($_POST['csrf'], $_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $_POST['csrf']);
    if (!$ok) { http_response_code(403); die('Invalid request. Go back and try again.'); }
}

// status -> CSS class for the badge
function status_class($s) {
    return 's-' . strtolower(str_replace(' ', '-', $s));
}
