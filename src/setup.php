<?php
// ONE-TIME SETUP PAGE. Open it once in the browser, then DELETE this file.
require_once __DIR__ . '/db.php';

$exists = $conn->query("SHOW TABLES LIKE 'repair'")->num_rows > 0;
if ($exists) {
    die("Tables already exist. Nothing to do. Please delete setup.php now.");
}

$sql = <<<'SQL'
-- Mobile Repair Shop schema + sample data
-- Runs automatically the FIRST time the db volume is created.

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

SQL;

try {
    $conn->multi_query($sql);
    while ($conn->more_results() && $conn->next_result()) { /* run all statements */ }
    echo "Done! Tables created. Now DELETE setup.php and open <a href='repair.php'>repair.php</a>.";
} catch (mysqli_sql_exception $ex) {
    echo "Error: " . htmlspecialchars($ex->getMessage());
}
