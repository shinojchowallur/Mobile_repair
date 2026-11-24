<?php
// TEMPORARY: shows whether the database variables reach PHP (no secret values shown). DELETE after use.
header('Content-Type: text/plain');
foreach (['MYSQLHOST','MYSQLPORT','MYSQLUSER','MYSQLPASSWORD','MYSQLDATABASE'] as $k) {
    $v = getenv($k);
    if ($v === false)      echo "$k: NOT SET\n";
    elseif ($v === '')     echo "$k: EMPTY\n";
    else {
        $show = ($k === 'MYSQLPASSWORD') ? '(hidden)' : $v;
        echo "$k: OK, length " . strlen($v) . ", value $show\n";
    }
}
