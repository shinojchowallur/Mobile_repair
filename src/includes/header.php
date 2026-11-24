<?php
require_once __DIR__ . '/../db.php';
$flash = get_flash();
$page  = $page  ?? '';
$title = $title ?? 'Mobile Repair Shop';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?> | Mobile Repair Shop</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link rel="stylesheet" href="assets/style.css">
<script>try{var t=localStorage.getItem('theme');if(t)document.documentElement.dataset.theme=t;}catch(e){}</script>
</head>
<body>
<header class="topbar">
  <div class="wrap nav">
    <a class="brand" href="repair.php"><i class="fa-solid fa-screwdriver-wrench"></i><span>Mobile <b>Repair</b> Shop</span></a>
    <nav id="menu" class="links">
      <a href="repair.php" class="<?= $page==='home' ? 'active' : '' ?>"><i class="fa-solid fa-house"></i> Home</a>
      <a href="repair.php#form" class="<?= $page==='register' ? 'active' : '' ?>"><i class="fa-solid fa-wrench"></i> Register Repair</a>
      <a href="report.php" class="<?= $page==='report' ? 'active' : '' ?>"><i class="fa-solid fa-chart-simple"></i> View Report</a>
    </nav>
    <div class="nav-actions">
      <button class="icon-btn" id="themeBtn" aria-label="Toggle dark mode"><i class="fa-solid fa-moon"></i></button>
      <button class="icon-btn burger" id="burger" aria-label="Menu" aria-expanded="false"><i class="fa-solid fa-bars"></i></button>
    </div>
  </div>
</header>
<?php if ($flash): ?>
<div class="toast <?= e($flash[0]) ?>" role="status"><i class="fa-solid <?= $flash[0]==='error' ? 'fa-circle-exclamation' : 'fa-circle-check' ?>"></i> <?= e($flash[1]) ?></div>
<?php endif; ?>
