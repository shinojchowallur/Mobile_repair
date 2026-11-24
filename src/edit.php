<?php
require_once "db.php";

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$st = mysqli_prepare($conn, "SELECT * FROM repair WHERE repair_id = ?");
mysqli_stmt_bind_param($st, "i", $id);
mysqli_stmt_execute($st);
$rep = mysqli_fetch_assoc(mysqli_stmt_get_result($st));
if (!$rep) { flash('error', 'Repair not found.'); redirect('report.php'); }

$statuses = ['Pending', 'In Progress', 'Completed'];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $cid = (int)$_POST['customer_id']; $tid = (int)$_POST['tech_id'];
    $dev = trim($_POST['device_model']); $prob = trim($_POST['problem']);
    $date = $_POST['received_date']; $stat = $_POST['status'];

    if (!$cid || !$tid || !$dev || !$prob || !in_array($stat, $statuses) || !DateTime::createFromFormat('Y-m-d', $date)) {
        $error = "Please fill in all fields correctly.";
        $rep = array_merge($rep, $_POST);
    } else {
        $st = mysqli_prepare($conn, "UPDATE repair SET customer_id=?, tech_id=?, device_model=?, problem=?, received_date=?, status=? WHERE repair_id=?");
        mysqli_stmt_bind_param($st, "iissssi", $cid, $tid, $dev, $prob, $date, $stat, $id);
        mysqli_stmt_execute($st);
        flash('success', "Repair #$id updated.");
        redirect('report.php');
    }
}

$customers   = mysqli_fetch_all(mysqli_query($conn, "SELECT customer_id, name FROM customer ORDER BY name"), MYSQLI_ASSOC);
$technicians = mysqli_fetch_all(mysqli_query($conn, "SELECT tech_id, name FROM technician ORDER BY name"), MYSQLI_ASSOC);

$page = 'report'; $title = 'Edit Repair';
include "includes/header.php";
?>
<section class="hero slim"><div class="wrap"><h1 class="h-sm"><i class="fa-solid fa-pen-to-square"></i> Edit Repair #<?= $id ?></h1><p>Update details or move the repair to the next status.</p></div></section>
<main class="wrap pull-up">
  <form method="POST" class="card">
    <?= csrf_field() ?><input type="hidden" name="id" value="<?= $id ?>">
    <?php if ($error): ?><div class="alert"><i class="fa-solid fa-circle-exclamation"></i> <?= e($error) ?></div><?php endif; ?>
    <div class="row r3">
      <div class="field"><label>Customer</label><select name="customer_id"><?php foreach ($customers as $c): ?><option value="<?= $c['customer_id'] ?>" <?= $rep['customer_id']==$c['customer_id']?'selected':'' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select></div>
      <div class="field"><label>Technician</label><select name="tech_id"><?php foreach ($technicians as $t): ?><option value="<?= $t['tech_id'] ?>" <?= $rep['tech_id']==$t['tech_id']?'selected':'' ?>><?= e($t['name']) ?></option><?php endforeach; ?></select></div>
      <div class="field"><label>Status</label>
        <div class="seg"><?php foreach ($statuses as $s): ?><label><input type="radio" name="status" value="<?= $s ?>" <?= $rep['status']===$s?'checked':'' ?>><span><?= $s ?></span></label><?php endforeach; ?></div>
      </div>
    </div>
    <div class="row r3">
      <div class="field"><label>Device Model</label><input name="device_model" required maxlength="100" value="<?= e($rep['device_model']) ?>"></div>
      <div class="field"><label>Problem</label><input name="problem" required maxlength="255" value="<?= e($rep['problem']) ?>"></div>
      <div class="field"><label>Received Date</label><input type="date" name="received_date" required value="<?= e($rep['received_date']) ?>"></div>
    </div>
    <div class="actions">
      <button class="btn primary" type="submit"><i class="fa-solid fa-floppy-disk"></i> Save changes</button>
      <a class="btn ghost" href="report.php">Cancel</a>
    </div>
  </form>
</main>
<?php include "includes/footer.php"; ?>
