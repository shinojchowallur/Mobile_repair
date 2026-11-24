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

    // Auto-create tables and sample data if not yet initialized
    $check = mysqli_query($conn, "SHOW TABLES LIKE 'customer'");
    if (mysqli_num_rows($check) === 0) {
        $setupSql = "
        CREATE TABLE IF NOT EXISTS customer (
          customer_id INT AUTO_INCREMENT PRIMARY KEY,
          name    VARCHAR(100) NOT NULL,
          address VARCHAR(255),
          phone   VARCHAR(20)
        );
        CREATE TABLE IF NOT EXISTS technician (
          tech_id INT AUTO_INCREMENT PRIMARY KEY,
          name  VARCHAR(100) NOT NULL,
          skill VARCHAR(100),
          phone VARCHAR(20)
        );
        CREATE TABLE IF NOT EXISTS repair (
          repair_id INT AUTO_INCREMENT PRIMARY KEY,
          customer_id INT NOT NULL,
          tech_id INT NOT NULL,
          device_model VARCHAR(100) NOT NULL,
          problem VARCHAR(255) NOT NULL,
          received_date DATE NOT NULL,
          status ENUM('Pending','In Progress','Completed') NOT NULL DEFAULT 'Pending',
          FOREIGN KEY (customer_id) REFERENCES customer(customer_id) ON DELETE CASCADE,
          FOREIGN KEY (tech_id)     REFERENCES technician(tech_id)   ON DELETE CASCADE
        );
        INSERT INTO customer (name, address, phone) VALUES
         ('Arun Kumar','Kakkanad, Kochi','9876543210'),
         ('Rahul Das','Edappally, Kochi','9876501234'),
         ('Meera Joseph','Thrippunithura','9847012345');
        INSERT INTO technician (name, skill, phone) VALUES
         ('Sanjay','Display & Hardware','9000000001'),
         ('Neeraj','Battery & Software','9000000002');
        INSERT INTO repair (customer_id, tech_id, device_model, problem, received_date, status) VALUES
         (1,1,'iPhone 13 Pro','display','2026-10-11','Completed'),
         (2,2,'iPhone 13','battery','2026-11-05','In Progress'),
         (1,1,'iPhone 12','software','2026-11-03','Pending'),
         (3,2,'Samsung S23','display','2026-10-15','Completed');
        ";
        mysqli_multi_query($conn, $setupSql);
        while (mysqli_next_result($conn)) {;}
    }
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
