<?php
require_once "db.php";

$errors = [];
$old = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $old = $_POST;
    $customer_id = (int)($_POST['customer_id'] ?? 0);
    $tech_id     = (int)($_POST['tech_id'] ?? 0);
    $device      = trim($_POST['device_model'] ?? '');
    $problem     = trim($_POST['problem'] ?? '');
    $date        = $_POST['received_date'] ?? '';
    $nc = trim($_POST['new_cust_name'] ?? '');
    $nt = trim($_POST['new_tech_name'] ?? '');

    if (!$nc && !$customer_id) $errors[] = "Select an existing customer or enter a new customer name.";
    if (!$nt && !$tech_id)     $errors[] = "Select an existing technician or enter a new technician name.";
    if (!$device || !$problem) $errors[] = "Device model and problem are required.";
    if (!DateTime::createFromFormat('Y-m-d', $date)) $errors[] = "Enter a valid received date.";

    if (!$errors) {
        try {
            mysqli_begin_transaction($conn);

            if ($nc) {
                $st = mysqli_prepare($conn, "INSERT INTO customer (name, address, phone) VALUES (?, ?, ?)");
                $a = trim($_POST['new_cust_address'] ?? ''); $p = trim($_POST['new_cust_phone'] ?? '');
                mysqli_stmt_bind_param($st, "sss", $nc, $a, $p);
                mysqli_stmt_execute($st);
                $customer_id = mysqli_insert_id($conn);
            }
            if ($nt) {
                $st = mysqli_prepare($conn, "INSERT INTO technician (name, skill, phone) VALUES (?, ?, ?)");
                $s = trim($_POST['new_tech_skill'] ?? ''); $p = trim($_POST['new_tech_phone'] ?? '');
                mysqli_stmt_bind_param($st, "sss", $nt, $s, $p);
                mysqli_stmt_execute($st);
                $tech_id = mysqli_insert_id($conn);
            }

            $st = mysqli_prepare($conn, "INSERT INTO repair (customer_id, tech_id, device_model, problem, received_date) VALUES (?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($st, "iisss", $customer_id, $tech_id, $device, $problem, $date);
            mysqli_stmt_execute($st);
            mysqli_commit($conn);

            flash('success', "Repair registered for $device.");
            redirect('report.php');
        } catch (mysqli_sql_exception $ex) {
            mysqli_rollback($conn);
            $errors[] = "Could not save the repair. Please try again.";
        }
    }
}

$customers   = mysqli_fetch_all(mysqli_query($conn, "SELECT customer_id, name, phone FROM customer ORDER BY name"), MYSQLI_ASSOC);
$technicians = mysqli_fetch_all(mysqli_query($conn, "SELECT tech_id, name, skill FROM technician ORDER BY name"), MYSQLI_ASSOC);
$v = fn($k) => e($old[$k] ?? '');

$page = 'home'; $title = 'Register Repair';
include "includes/header.php";
?>
<section class="hero">
  <div class="wrap hero-in">
    <div>
      <h1>Repair Made <span>Easy</span></h1>
      <p>Register your device for repair and track the progress.</p>
    </div>
    <div class="hero-art" aria-hidden="true"><i class="fa-solid fa-mobile-screen-button"></i><i class="fa-solid fa-gear g1"></i><i class="fa-solid fa-gear g2"></i></div>
  </div>
</section>

<main class="wrap pull-up" id="form">
  <form method="POST" class="card" id="repairForm" novalidate>
    <?= csrf_field() ?>
    <h2 class="card-title"><i class="fa-solid fa-wrench"></i> Repair Registration Form</h2>

    <?php if ($errors): ?>
      <div class="alert"><?php foreach ($errors as $er) echo "<div><i class='fa-solid fa-circle-exclamation'></i> " . e($er) . "</div>"; ?></div>
    <?php endif; ?>

    <div class="row" data-group="cust">
      <div class="field">
        <label for="customer_id"><i class="fa-solid fa-user"></i> Existing Customer</label>
        <select name="customer_id" id="customer_id">
          <option value="">-- Select Customer --</option>
          <?php foreach ($customers as $c): ?>
            <option value="<?= $c['customer_id'] ?>" <?= ($old['customer_id'] ?? '') == $c['customer_id'] ? 'selected' : '' ?>><?= e($c['name']) ?> (<?= e($c['phone']) ?>)</option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="or">OR</div>
      <div class="field"><label><i class="fa-solid fa-user-plus"></i> New Customer Name</label><input name="new_cust_name" maxlength="100" placeholder="Customer Name" value="<?= $v('new_cust_name') ?>"></div>
      <div class="field"><label><i class="fa-solid fa-location-dot"></i> Address</label><input name="new_cust_address" maxlength="255" placeholder="Address" value="<?= $v('new_cust_address') ?>"></div>
      <div class="field"><label><i class="fa-solid fa-phone"></i> Phone</label><input name="new_cust_phone" maxlength="20" inputmode="tel" placeholder="Phone" value="<?= $v('new_cust_phone') ?>"></div>
    </div>

    <div class="row" data-group="tech">
      <div class="field">
        <label for="tech_id"><i class="fa-solid fa-user-gear"></i> Existing Technician</label>
        <select name="tech_id" id="tech_id">
          <option value="">-- Select Technician --</option>
          <?php foreach ($technicians as $t): ?>
            <option value="<?= $t['tech_id'] ?>" <?= ($old['tech_id'] ?? '') == $t['tech_id'] ? 'selected' : '' ?>><?= e($t['name']) ?> - <?= e($t['skill']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="or">OR</div>
      <div class="field"><label><i class="fa-solid fa-helmet-safety"></i> New Technician Name</label><input name="new_tech_name" maxlength="100" placeholder="Technician Name" value="<?= $v('new_tech_name') ?>"></div>
      <div class="field"><label><i class="fa-solid fa-microchip"></i> Skill</label><input name="new_tech_skill" maxlength="100" placeholder="Skill" value="<?= $v('new_tech_skill') ?>"></div>
      <div class="field"><label><i class="fa-solid fa-phone"></i> Phone</label><input name="new_tech_phone" maxlength="20" inputmode="tel" placeholder="Phone" value="<?= $v('new_tech_phone') ?>"></div>
    </div>

    <div class="row r3">
      <div class="field"><label><i class="fa-solid fa-mobile-screen"></i> Device Model</label><input name="device_model" required maxlength="100" placeholder="Enter Device Model" value="<?= $v('device_model') ?>"></div>
      <div class="field"><label><i class="fa-solid fa-triangle-exclamation"></i> Problem</label><input name="problem" required maxlength="255" placeholder="Describe the problem..." value="<?= $v('problem') ?>"></div>
      <div class="field"><label><i class="fa-regular fa-calendar"></i> Received Date</label><input type="date" name="received_date" required value="<?= $old ? $v('received_date') : date('Y-m-d') ?>"></div>
    </div>

    <div class="actions">
      <button class="btn primary" type="submit"><i class="fa-solid fa-paper-plane"></i> Register Repair</button>
      <button class="btn ghost" type="reset" id="resetBtn"><i class="fa-solid fa-rotate"></i> Reset</button>
    </div>
    <a class="strip" href="report.php"><i class="fa-solid fa-arrow-up-right-from-square"></i> View Repair Report <i class="fa-solid fa-arrow-right"></i></a>
  </form>
</main>
<?php include "includes/footer.php"; ?>
