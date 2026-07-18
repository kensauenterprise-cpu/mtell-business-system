<?php
//==================================================
// TOP BRANDS
//==================================================

$brands = [];

// Check whether brand column exists
$check = $conn->query("SHOW COLUMNS FROM products LIKE 'brand'");

if($check && $check->num_rows > 0){

    $sql = "
        SELECT DISTINCT brand
        FROM products
        WHERE brand IS NOT NULL
        AND brand <> ''
        ORDER BY brand ASC
    ";

    $result = $conn->query($sql);

    if($result){

        while($row = $result->fetch_assoc()){

            $brands[] = $row['brand'];

        }

    }

}

// Fallback brands
if(empty($brands)){

    $brands = [

        "Samsung",

        "Apple",

        "Tecno",

        "Infinix",

        "Xiaomi",

        "Oppo",

        "Vivo",

        "Nokia",

        "Realme",

        "Huawei",

        "itel",

        "Google"

    ];

}
?>

<section class="brands">

<div class="container">

<div class="flex-between mb-40">

<h2 class="section-title">

Top Brands

</h2>

<a href="/infinity/shop.php" class="btn">

View Products

</a>

</div>

<div class="brand-grid">

<?php foreach($brands as $brand): ?>

<a
href="/infinity/shop.php?brand=<?= urlencode($brand) ?>"
class="brand-card">

<div class="brand-logo">

<?php

$letter = strtoupper(substr($brand,0,1));

?>

<div class="brand-circle">

<?= $letter ?>

</div>

</div>

<h4>

<?= htmlspecialchars($brand) ?>

</h4>

</a>

<?php endforeach; ?>

</div>

</div>

</section>