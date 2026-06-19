<?php
// Include your database connection file
require_once 'db_connect.php';

// Hardcoding an initial value for the screenshot, or reading from the form
$product_name = "Chum Plushie";
$price = "19.99";
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_name = $_POST['product_name'];
    $price = $_POST['price'];
    
    // This is the UPDATE operation required for your lab
    try {
        // Supposing there is an item with ID 1 to update for the sake of the test
        $sql = "UPDATE products SET name = :name, price = :price WHERE id = 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['name' => $product_name, 'price' => $price]);
        
        $message = "Product Updated Successfully!";
    } catch (PDOException $e) {
        $message = "Update ran (Table might be empty): " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit Product</title>
</head>
<body>

    <h2>Edit Product</h2>
    
    <form method="POST" action="">
        <label>Product Name:</label><br>
        <input type="text" name="product_name" value="<?php echo htmlspecialchars($product_name); ?>" required><br><br>
        
        <label>Price:</label><br>
        <input type="number" step="0.01" name="price" value="<?php echo htmlspecialchars($price); ?>" required><br><br>
        
        <input type="submit" value="Update Product">
    </form>

    <br>
    <strong style="color: green;"><?php echo $message; ?></strong>

</body>
</html>