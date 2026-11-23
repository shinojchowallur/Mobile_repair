<?php
require_once "db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    check_csrf();
    $id = (int)$_POST['delete_id'];
    $st = mysqli_prepare($conn, "DELETE FROM repair WHERE repair_id = ?");
    mysqli_stmt_bind_param($st, "i", $id);
    mysqli_stmt_execute($st);
    flash('success', 'Repair record deleted.');
    redirect('report.php');
}

$rows = mysqli_fetch_all(mysqli_query($conn,
  "SELECT r.repair_id, c.name AS customer, c.phone AS cphone, c.address AS caddress,
          r.device_model, r.problem, t.name AS tech, t.skill AS tskill, r.received_date, r.status
   FROM repair r
   INNER JOIN customer c   ON r.customer_id = c.customer_id
   INNER JOIN technician t ON r.tech_id = t.tech_id
   ORDER BY r.received_date DESC, r.repair_id DESC"), MYSQLI_ASSOC);

$count = ['Pending' => 0, 'In Progress' => 0, 'Completed' => 0];
foreach ($rows as $r) $count[$r['status']]++;

$page = 'report'; $title = 'Repair Report';
include "includes/header.php";
?>
<section class="hero slim">
  <div class="wrap">
    <h1 class="h-sm"><i class="fa-solid fa-chart-simple"></i> Repair Report</h1>
    <p>List of all repair registrations</p>
  </div>
</section>

<main class="wrap pull-up">
  <div class="stats">
    <div class="stat"><i class="fa-solid fa-layer-group"></i><b data-count="<?= count($rows) ?>">0</b><span>Total repairs</span></div>
    <div class="stat"><i class="fa-solid fa-hourglass-half"></i><b data-count="<?= $count['Pending'] ?>">0</b><span>Pending</span></div>
    <div class="stat"><i class="fa-solid fa-screwdriver-wrench"></i><b data-count="<?= $count['In Progress'] ?>">0</b><span>In progress</span></div>
    <div class="stat"><i class="fa-solid fa-circle-check"></i><b data-count="<?= $count['Completed'] ?>">0</b><span>Completed</span></div>
  </div>

  <div class="card">
    <div class="toolbar">
      <div class="search"><i class="fa-solid fa-magnifying-glass"></i><input id="search" type="search" placeholder="Search by customer, device or technician..."></div>
      <select id="statusFilter" aria-label="Filter by status">
        <option value="">All statuses</option><option>Pending</option><option>In Progress</option><option>Completed</option>
      </select>
      <button class="btn primary" id="exportBtn"><i class="fa-solid fa-download"></i> Export PDF</button>
    </div>

    <div class="table-wrap">
    <table id="tbl">
      <thead><tr>
        <th data-sort="0">Customer Name <i class="fa-solid fa-sort"></i></th>
        <th data-sort="1">Device Model <i class="fa-solid fa-sort"></i></th>
        <th data-sort="2">Problem <i class="fa-solid fa-sort"></i></th>
        <th data-sort="3">Technician Name <i class="fa-solid fa-sort"></i></th>
        <th data-sort="4">Received Date <i class="fa-solid fa-sort"></i></th>
        <th data-sort="5">Status <i class="fa-solid fa-sort"></i></th>
        <th class="no-print">Actions</th>
      </tr></thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr data-status="<?= e($r['status']) ?>"
            data-customer="<?= e($r['customer']) ?>" data-phone="<?= e($r['cphone']) ?>" data-address="<?= e($r['caddress']) ?>"
            data-device="<?= e($r['device_model']) ?>" data-problem="<?= e($r['problem']) ?>"
            data-tech="<?= e($r['tech']) ?>" data-skill="<?= e($r['tskill']) ?>" data-date="<?= e($r['received_date']) ?>">
          <td data-label="Customer"><?= e($r['customer']) ?></td>
          <td data-label="Device"><?= e($r['device_model']) ?></td>
          <td data-label="Problem"><?= e($r['problem']) ?></td>
          <td data-label="Technician"><?= e($r['tech']) ?></td>
          <td data-label="Received"><?= e($r['received_date']) ?></td>
          <td data-label="Status"><span class="badge <?= status_class($r['status']) ?>"><?= e($r['status']) ?></span></td>
          <td class="no-print act">
            <button class="btn sm blue view-btn"><i class="fa-solid fa-eye"></i> View</button>
            <a class="btn sm green" href="edit.php?id=<?= $r['repair_id'] ?>"><i class="fa-solid fa-pen-to-square"></i> Edit</a>
            <form method="POST" class="del-form"><?= csrf_field() ?><input type="hidden" name="delete_id" value="<?= $r['repair_id'] ?>"><button class="btn sm red" type="submit" aria-label="Delete"><i class="fa-solid fa-trash"></i></button></form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    </div>
    <div class="empty" id="empty" hidden><i class="fa-regular fa-folder-open"></i><p>No repairs match your search. <a href="repair.php">Register a repair</a></p></div>

    <div class="pager no-print">
      <span id="info"></span>
      <div id="pages"></div>
    </div>
  </div>
  <p><a class="back" href="repair.php"><i class="fa-solid fa-arrow-left"></i> Back to Repair Registration</a></p>
</main>

<div class="modal" id="modal" hidden>
  <div class="dialog" role="dialog" aria-modal="true" aria-labelledby="mTitle">
    <button class="icon-btn close" id="mClose" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
    <h3 id="mTitle">Repair details</h3>
    <dl id="mBody"></dl>
  </div>
</div>
<?php include "includes/footer.php"; ?>
