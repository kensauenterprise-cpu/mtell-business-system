function loadProducts(category) {
  fetch('load_products.php?category=' + category)
    .then(response => response.text())
    .then(data => {
      document.getElementById('product-container').innerHTML = data;
    });
}
