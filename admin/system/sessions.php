<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

?>

<div style="
    background:white;
    padding:20px;
    border-radius:10px;
    box-shadow:0 0 10px rgba(0,0,0,0.1);
">

<h2>👥 Active Session</h2>

<table
border="1"
cellpadding="10"
cellspacing="0"
width="100%"
style="border-collapse:collapse;"
>

<tr style="background:#0f172a;color:white;">

<th>Session Variable</th>
<th>Value</th>

</tr>

<?php foreach ($_SESSION as $key => $value): ?>

<tr>

<td>
    <?= htmlspecialchars($key) ?>
</td>

<td>
    <?= htmlspecialchars(print_r($value, true)) ?>
</td>

</tr>

<?php endforeach; ?>

</table>

</div>