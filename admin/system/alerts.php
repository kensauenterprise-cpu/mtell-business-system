<h2>🚨 System Alerts</h2>

<ul>
<li id="db">Database: Checking...</li>
<li id="api">API: Checking...</li>
</ul>

<script>
fetch('/infinity/admin/api/orders_count.php')
.then(()=>document.getElementById('api').innerText='API ✅ OK')
.catch(()=>document.getElementById('api').innerText='API ❌ DOWN');

fetch('/infinity/admin/api/db_check.php')
.then(()=>document.getElementById('db').innerText='DB ✅ OK')
.catch(()=>document.getElementById('db').innerText='DB ❌ DOWN');
</script>