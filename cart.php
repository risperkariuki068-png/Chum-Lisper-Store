<?php
session_start();
require 'db_connect.php';

// Initialize the cart session array if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// 1. Handle adding items to the cart (From Homepage)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $product_id = (int)$_POST['product_id'];
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id]++;
    } else {
        $_SESSION['cart'][$product_id] = 1;
    }
    header("Location: cart.php");
    exit;
}

// 2. NEW: Handle updating quantities directly inside the cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    if (isset($_POST['quantities']) && is_array($_POST['quantities'])) {
        foreach ($_POST['quantities'] as $id => $qty) {
            $qty = (int)$qty;
            if ($qty > 0) {
                $_SESSION['cart'][$id] = $qty; // Update to the new quantity
            } else {
                unset($_SESSION['cart'][$id]); // If they type 0 or less, remove the item
            }
        }
    }
    header("Location: cart.php");
    exit;
}

// 3. Handle removing items from the cart via the "Remove" link
if (isset($_GET['remove'])) {
    $remove_id = (int)$_GET['remove'];
    unset($_SESSION['cart'][$remove_id]);
    header("Location: cart.php");
    exit;
}

// Fetch cart product details from the database
$cart_items = [];
$total_price = 0;

if (!empty($_SESSION['cart'])) {
    $placeholders = implode(',', array_fill(0, count($_SESSION['cart']), '?'));
    $stmt = $pdo->prepare("SELECT id, name, price, image_url FROM products WHERE id IN ($placeholders)");
    $stmt->execute(array_keys($_SESSION['cart']));
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($products as $product) {
        $qty = $_SESSION['cart'][$product['id']];
        $subtotal = $product['price'] * $qty;
        $total_price += $subtotal;
        $product['quantity'] = $qty;
        $product['subtotal'] = $subtotal;
        $cart_items[] = $product;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart - Chum & Lisper Store</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 text-slate-800 h-screen flex flex-col">

    <nav class="bg-white shadow-sm p-4 mb-8">
        <div class="max-w-6xl mx-auto flex justify-between items-center">
            <a href="index.php" class="text-2xl font-bold text-indigo-600">Chum & Lisper</a>
            <div class="space-x-4">
                <a href="index.php" class="text-slate-600 hover:text-indigo-600 font-medium">Continue Shopping</a>
            </div>
        </div>
    </nav>

    <main class="max-w-4xl mx-auto px-4 flex-grow w-full">
        <h2 class="text-3xl font-bold mb-8">Shopping Cart</h2>
        
        <?php if (empty($cart_items)): ?>
            <div class="bg-white p-8 rounded-xl shadow-sm border border-slate-100 text-center">
                <p class="text-slate-500 mb-4">Your cart is currently empty.</p>
                <a href="index.php" class="inline-block bg-indigo-600 text-white px-6 py-2 rounded-md hover:bg-indigo-700 transition">Browse Products</a>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
                <form action="cart.php" method="POST">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200">
                                <th class="p-4 font-semibold text-slate-600">Product</th>
                                <th class="p-4 font-semibold text-slate-600">Price</th>
                                <th class="p-4 font-semibold text-slate-600">Qty</th>
                                <th class="p-4 font-semibold text-slate-600">Subtotal</th>
                                <th class="p-4 font-semibold text-slate-600"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cart_items as $item): ?>
                                <tr class="border-b border-slate-100">
                                    <td class="p-4 flex items-center space-x-4">
                                        <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="Product" class="w-16 h-16 object-cover rounded">
                                        <span class="font-medium"><?= htmlspecialchars($item['name']) ?></span>
                                    </td>
                                    <td class="p-4 text-slate-600">KSh <?= number_format($item['price'], 2) ?></td>
                                    <td class="p-4">
                                        <input type="number" 
                                               name="quantities[<?= $item['id'] ?>]" 
                                               value="<?= $item['quantity'] ?>" 
                                               min="1" 
                                               class="w-16 border border-slate-300 rounded-md p-1.5 text-center outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                                    </td>
                                    <td class="p-4 font-semibold text-slate-900">KSh <?= number_format($item['subtotal'], 2) ?></td>
                                    <td class="p-4 text-right">
                                        <a href="cart.php?remove=<?= $item['id'] ?>" class="text-red-500 hover:text-red-700 text-sm font-medium">Remove</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <div class="p-6 bg-slate-50 border-t border-slate-200 flex justify-between items-center flex-wrap gap-4">
                        <div class="flex items-center space-x-6">
                            <div>
                                <p class="text-sm text-slate-500 mb-1">Total Amount</p>
                                <p class="text-2xl font-bold text-slate-900">KSh <?= number_format($total_price, 2) ?></p>
                            </div>
                            <button type="submit" name="update_cart" class="text-indigo-600 text-sm font-semibold hover:underline mt-4">
                                ↺ Update Quantities
                            </button>
                        </div>
                        <a href="checkout.php" class="bg-indigo-600 text-white px-8 py-3 rounded-md font-medium hover:bg-indigo-700 transition">
                            Proceed to Checkout
                        </a>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>