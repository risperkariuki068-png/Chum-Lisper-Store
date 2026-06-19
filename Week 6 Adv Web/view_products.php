<?php
// Include your database connection file
require_once 'db_connect.php';

try {
    // Fetch all products from your database table
    $stmt = $pdo->query("SELECT * FROM products");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Query failed: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>View Products</title>
    <style>
        /* This styles the table to look exactly like the one in your assignment example */
        table {
            border-collapse: collapse;
            width: 50%;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
    </style>
</head>
<body>

    <h2>Product Records</h2>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Product Name</th>
                <th>Price</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($products)): ?>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($product['id']); ?></td>
                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                        <td>$<?php echo htmlspecialchars($product['price']); ?></td>
                        <td>
                        <a href="#">Edit</a> | 
    <a href="#" onclick="alert('Are you sure you want to delete this product?'); return false;" style="color: red;">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4">No product records found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

</body>
</html>