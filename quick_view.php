<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$id = intval($_GET['id'] ?? 0);
$product = getProductById($pdo, $id);

if (!$product) {
    echo "<p>Product not found.</p>";
    exit;
}
?>
<h2><?php echo htmlspecialchars($product['name']); ?></h2>
<img 
    src="images/products/<?php echo htmlspecialchars($product['main_image']); ?>" 
    alt="<?php echo htmlspecialchars($product['name']); ?>" 
    style="width:200px; height:200px; object-fit:cover; display:block; margin:0 auto;"
>
<p><?php echo htmlspecialchars($product['description']); ?></p>
<p>Price: $<?php echo number_format($product['sale_price'], 2); ?></p>
<?php if ($product['stock_quantity'] > 0): ?>
    <p>In Stock: <?php echo $product['stock_quantity']; ?></p>
<?php else: ?>
    <p style="color:red;">Out of Stock</p>
<?php endif; ?>