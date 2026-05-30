<h2>🧠 System Metrics</h2>

<ul>
<li>Server: <?= php_uname() ?></li>
<li>PHP Version: <?= phpversion() ?></li>
<li>Memory Usage: <?= round(memory_get_usage()/1024/1024,2) ?> MB</li>
<li>Disk Free: <?= round(disk_free_space("/")/1024/1024/1024,2) ?> GB</li>
</ul>