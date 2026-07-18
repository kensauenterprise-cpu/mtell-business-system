<?php
session_start();

ini_set('display_errors',1);
error_reporting(E_ALL);

// remove item
if(isset($_GET['remove'])){
    $removeId = $_GET['remove'];
    unset($_SESSION['cart'][$removeId]);
    header("Location: /infinity/cart/cart.php");
    exit;
}

// update quantity
if($_SERVER['REQUEST_METHOD']=="POST" && isset($_POST['update_quantity'])){

    $id  = $_POST['product_id'];
    $qty = max(1,intval($_POST['quantity']));

    $_SESSION['cart'][$id]['quantity'] = $qty;

    header("Location: /infinity/cart/cart.php");
    exit;
}

// empty cart check
if(empty($_SESSION['cart'])){
    echo "<h2>?? Your Cart is Empty</h2>
    <p><a href='/index.php'>?? Return to Homepage</a></p>";
    exit;
}

// calculate total
$total = 0;

foreach($_SESSION['cart'] as $item){
    $total += $item['price'] * $item['quantity'];
}

$_SESSION['cart_total'] = $total;

?>

<!DOCTYPE html>
<html>
<head>

<meta charset="UTF-8">

<title>Your Cart - Mtell Kenya</title>

<link rel="stylesheet" href="/infinity/assets/css/styles.css">

<style>

body{
font-family:sans-serif;
background:#f5f5f5;
}

.cart-container{
max-width:900px;
margin:40px auto;
padding:20px;
background:#fff;
border-radius:8px;
}

.cart-table{
width:100%;
border-collapse:collapse;
}

.cart-table th,
.cart-table td{
padding:10px;
border-bottom:1px solid #ddd;
}

.cart-table th{
background:#007bff;
color:#fff;
}

.cart-table img{
width:90px;
border-radius:6px;
}

.total-row td{
font-weight:bold;
font-size:18px;
color:#28a745;
}

.remove-link{
color:red;
text-decoration:none;
font-weight:bold;
}

.back-button{
display:inline-block;
margin-top:20px;
background:#007bff;
color:#fff;
padding:8px 16px;
border-radius:6px;
text-decoration:none;
}

.checkout-button{
margin-top:20px;
}

.checkout-button button{
padding:12px 22px;
background:#28a745;
color:#fff;
border:none;
border-radius:6px;
font-weight:bold;
cursor:pointer;
}

.checkout-login-btn{
    display:inline-block;
    padding:12px 22px;
    background:#28a745;
    color:white;
    text-decoration:none;
    border-radius:6px;
    font-weight:bold;
}

input[type="number"]{
width:60px;
}

</style>

</head>

<body>

<div class="cart-container">

<h2>?? Your Cart</h2>

<table class="cart-table">

<thead>

<tr>

<th>Image</th>
<th>Product</th>
<th>Price</th>
<th>Quantity</th>
<th>Subtotal</th>
<th>Action</th>

</tr>

</thead>

<tbody>

<?php foreach($_SESSION['cart'] as $id=>$item): ?>

<tr>

<td>
<img src="/infinity/uploads/<?php echo htmlspecialchars($item['image']); ?>">
</td>

<td>
<?php echo htmlspecialchars($item['name']); ?>
</td>

<td>
Ksh <?php echo number_format($item['price'],2); ?>
</td>

<td>

<form method="POST">

<input type="hidden" name="product_id" value="<?php echo $id; ?>">

<input type="number" name="quantity"
value="<?php echo $item['quantity']; ?>" min="1">

<button name="update_quantity">??</button>

</form>

</td>

<td>

Ksh <?php
echo number_format($item['price']*$item['quantity'],2);
?>

</td>

<td>

<a class="remove-link"
href="?remove=<?php echo $id; ?>">
? Remove
</a>

</td>

</tr>

<?php endforeach; ?>

<tr class="total-row">

<td colspan="4">Total</td>

<td colspan="2">
Ksh <?php echo number_format($total,2); ?>
</td>

</tr>

</tbody>

</table>

<a href="/index.php" class="back-button">
?? Continue Shopping
</a>

<!-- checkout -->

<div class="checkout-button">

<?php if(isset($_SESSION['customer_id'])): ?>

<form action="/infinity/checkout.php" method="POST">

    <input
    type="hidden"
    name="amount"
    value="<?php echo $total; ?>"
    >

    <button type="submit">
        ?? Proceed to Checkout
    </button>

</form>

<?php else: ?>

<a href="/infinity/customer_login.php" class="checkout-login-btn">
    ?? Login To Checkout
</a>

<?php endif; ?>

</div>

</div>

</body>
</html>