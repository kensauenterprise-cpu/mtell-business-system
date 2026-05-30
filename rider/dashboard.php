php
 ==========================
 🔐 SECURITY (AUTH + ROLE)
 ==========================
require_once $_SERVER['DOCUMENT_ROOT'].'infinityauth.php';
require_once $_SERVER['DOCUMENT_ROOT'].'infinityrole.php';

requireRole('rider');  🚴 Only riders allowed

 ==========================
 🧠 SESSION DATA
 ==========================
$rider_id = $_SESSION['user_id'];
$branch_id = $_SESSION['branch_id']  0;

 ==========================
 🔗 DB CONNECTION
 ==========================
require_once $_SERVER['DOCUMENT_ROOT'].'infinityadminincludesdb.php';

 ==========================
 📦 FETCH RIDER ORDERS
 ==========================
$stmt = $conn-prepare(
    SELECT o., c.name AS customer_name
    FROM orders o
    LEFT JOIN customers c ON o.customer_id = c.id
    WHERE o.rider_id = 
    ORDER BY o.id DESC
);

$stmt-bind_param(i, $rider_id);
$stmt-execute();
$result = $stmt-get_result();


!DOCTYPE html
html lang=en
head
meta charset=UTF-8
titleRider Dashboardtitle

style
body {
    font-family Arial;
    background#f4f4f4;
    padding20px;
}
.container {
    max-width1000px;
    marginauto;
}
h2 {
    text-aligncenter;
}
table {
    width100%;
    border-collapsecollapse;
    margin-top20px;
    background#fff;
}
th, td {
    padding10px;
    border1px solid #ddd;
    text-aligncenter;
}
th {
    background#007bff;
    color#fff;
}
.status {
    padding5px 10px;
    border-radius5px;
    colorwhite;
}
.pending { backgroundorange; }
.paid { backgroundgreen; }
.partial { background#17a2b8; }

button {
    padding6px 10px;
    bordernone;
    background#28a745;
    colorwhite;
    cursorpointer;
    border-radius4px;
}
.logout {
    floatright;
    text-decorationnone;
    colorred;
}
style
head

body

div class=container

a href=infinitylogout.php class=logout🚪 Logouta

h2🚴 Rider Dashboardh2

table
tr
    thOrder IDth
    thCustomerth
    thTotalth
    thStatusth
    thActionth
tr

php while($row = $result-fetch_assoc()) 
tr
    td#= $row['id']; td
    td= htmlspecialchars($row['customer_name']); td
    tdKES = number_format($row['total'], 2); td
    td
        span class=status = $row['status']; 
            = $row['status']; 
        span
    td
    td
        php if($row['status'] !== 'delivered') 
            form method=post action=update_status.php
                input type=hidden name=order_id value== $row['id']; 
                button type=submitMark Deliveredbutton
            form
        php else 
            ✅ Done
        php endif; 
    td
tr
php endwhile; 

table

div

body
html