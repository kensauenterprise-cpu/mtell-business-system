<h2>💾 Backup & Restore</h2>

<?php
if (isset($_POST['backup'])) {

    $file = "/infinity/backups/db_".date("Ymd_His").".sql";

    exec("mysqldump -u root -p your_db > ".$_SERVER['DOCUMENT_ROOT'].$file);

    echo "<p style='color:green'>Backup created: $file</p>";
}
?>

<form method="post">
<button name="backup">Create Backup</button>
</form>