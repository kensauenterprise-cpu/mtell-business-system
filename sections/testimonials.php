<?php
//==========================================
// TESTIMONIALS
//==========================================

$testimonials = [

    [
        "name" => "James Mwangi",
        "location" => "Nairobi",
        "image" => "/infinity/assets/images/user1.jpg",
        "rating" => 5,
        "message" => "Excellent customer service and very fast delivery. My Samsung phone arrived the next day exactly as described."
    ],

    [
        "name" => "Mercy Akinyi",
        "location" => "Kisumu",
        "image" => "/infinity/assets/images/user2.jpg",
        "rating" => 5,
        "message" => "I ordered a Tecno smartphone and the whole process was smooth. Secure payment and genuine products."
    ],

    [
        "name" => "David Kiptoo",
        "location" => "Eldoret",
        "image" => "/infinity/assets/images/user3.jpg",
        "rating" => 5,
        "message" => "Mtell Online Shopping offers competitive prices and excellent customer support. Highly recommended."
    ]

];
?>

<section class="testimonials">

    <div class="container">

        <div class="flex-between mb-40">

            <h2 class="section-title">
                What Our Customers Say
            </h2>

        </div>

        <div class="testimonial-grid">

            <?php foreach($testimonials as $review): ?>

            <div class="testimonial-card fade-in">

                <img
                    src="<?= $review['image']; ?>"
                    alt="<?= htmlspecialchars($review['name']); ?>">

                <div class="product-rating">

                    <?php
                    for($i=1;$i<=5;$i++){
                        echo ($i<=$review['rating']) ? "★" : "☆";
                    }
                    ?>

                </div>

                <p>

                    "<?= htmlspecialchars($review['message']); ?>"

                </p>

                <h4>

                    <?= htmlspecialchars($review['name']); ?>

                </h4>

                <small>

                    <?= htmlspecialchars($review['location']); ?>

                </small>

            </div>

            <?php endforeach; ?>

        </div>

    </div>

</section>