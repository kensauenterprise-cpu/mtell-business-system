<h2>📡 Live Logs</h2>

<pre id="logBox" style="background:black;color:#0f0;height:400px;overflow:auto;"></pre>

<script>
function loadLogs(){
    fetch('/infinity/logs/error.log')
    .then(r=>r.text())
    .then(data=>{
        let box = document.getElementById("logBox");
        box.innerText = data;
        box.scrollTop = box.scrollHeight;
    });
}

setInterval(loadLogs, 3000);
loadLogs();
</script>