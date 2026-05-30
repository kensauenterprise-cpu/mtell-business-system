<?php
// ==========================
// 🔐 SECURITY
// ==========================
require_once $_SERVER['DOCUMENT_ROOT'].'/infinity/admin/includes/init.php';

?>

<h2>💳 Transactions</h2>

<div style="margin-bottom:15px;">
    <label>Filter by Method:</label>
    <select id="methodFilter">
        <option value="">All</option>
        <option value="mpesa">MPESA</option>
        <option value="cash">Cash</option>
        <option value="invoice">Invoice</option>
    </select>

    <label style="margin-left:10px;">From:</label>
    <input type="date" id="dateFrom">

    <label>To:</label>
    <input type="date" id="dateTo">

    <button onclick="loadTransactions()">🔍 Filter</button>
</div>

<table border="1" cellpadding="8" id="transactionsTable" style="width:100%; background:white;">
    <thead>
        <tr>
            <th>ID</th>
            <th>Order</th>
            <th>Method</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Date</th>
        </tr>
    </thead>
    <tbody></tbody>
</table>

<script>
function loadTransactions(){

    let method = document.getElementById("methodFilter").value;
    let from = document.getElementById("dateFrom").value;
    let to = document.getElementById("dateTo").value;

    let url = "/infinity/admin/api/transactions.php?";
    
    if(method) url += "method=" + method + "&";
    if(from) url += "from=" + from + "&";
    if(to) url += "to=" + to;

    fetch(url)
    .then(res => res.json())
    .then(data => {

        let tbody = document.querySelector("#transactionsTable tbody");
        tbody.innerHTML = "";

        if(data.length === 0){
            tbody.innerHTML = "<tr><td colspan='6'>No transactions found</td></tr>";
            return;
        }

        data.forEach(t => {

            let statusColor = t.status === "paid" ? "green" : "red";

            tbody.innerHTML += `
            <tr>
                <td>${t.id}</td>
                <td>#${t.order_id}</td>
                <td>${t.method}</td>
                <td>KES ${t.amount}</td>
                <td style="color:${statusColor}">${t.status}</td>
                <td>${t.date}</td>
            </tr>`;
        });

    });
}

// AUTO LOAD
loadTransactions();
</script>