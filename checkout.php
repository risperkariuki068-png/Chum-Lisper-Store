<?php
session_start();
require 'db_connect.php';

if (empty($_SESSION['cart'])) {
    header("Location: index.php");
    exit;
}

if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit;
}

$order_placed = false;
$total_price = 0;

// Calculate total price for display and insertion
$placeholders = implode(',', array_fill(0, count($_SESSION['cart']), '?'));
$stmt = $pdo->prepare("SELECT id, price FROM products WHERE id IN ($placeholders)");
$stmt->execute(array_keys($_SESSION['cart']));
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($products as $product) {
    $qty = $_SESSION['cart'][$product['id']];
    $total_price += ($product['price'] * $qty);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $user_id = $_SESSION['user_id']; 
    
    // In a real app, you would process the M-Pesa API response here before saving
    $insert_stmt = $pdo->prepare("INSERT INTO orders (user_id, total_price, status) VALUES (?, ?, 'completed')");
    
    if ($insert_stmt->execute([$user_id, $total_price])) {
        unset($_SESSION['cart']); 
        $order_placed = true;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Chum & Lisper Store</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 text-slate-800 h-screen flex flex-col">

    <nav class="bg-white shadow-sm p-4 mb-8">
        <div class="max-w-6xl mx-auto flex justify-between items-center px-4">
            <h1 class="text-2xl font-bold text-indigo-600"><a href="index.php">Chum & Lisper</a></h1>
            <a href="cart.php" class="text-slate-600 hover:text-indigo-600 font-medium">Back to Cart</a>
        </div>
    </nav>

    <main class="max-w-3xl mx-auto px-4 w-full">
        <?php if ($order_placed): ?>
            <div class="bg-white p-10 rounded-xl shadow-sm border border-slate-100 text-center">
                <div class="w-16 h-16 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl">✓</div>
                <h2 class="text-3xl font-bold mb-2">Payment Received, <?= htmlspecialchars($_SESSION['user_name']) ?>!</h2>
                <p class="text-slate-500 mb-8">Thank you for your purchase. Your M-Pesa transaction was successful and your order has been processed.</p>
                <a href="index.php" class="bg-indigo-600 text-white px-6 py-2 rounded-md hover:bg-indigo-700 transition">Return to Store</a>
            </div>
        <?php else: ?>
            <h2 class="text-3xl font-bold mb-8">Secure Checkout</h2>
            <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6 md:p-8">
                <form action="checkout.php" method="POST" class="space-y-6">
                    
                    <div>
                        <h3 class="text-lg font-semibold border-b pb-2 mb-4">Delivery Details</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-600 mb-1">Full Name</label>
                                <input type="text" required value="<?= htmlspecialchars($_SESSION['user_name']) ?>" class="w-full border border-slate-300 rounded-md p-2 outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-slate-600 mb-1">Delivery Address</label>
                                <input type="text" required class="w-full border border-slate-300 rounded-md p-2 outline-none focus:ring-2 focus:ring-indigo-500" placeholder="e.g., Moi Avenue, CBD">
                            </div>
                        </div>
                    </div>

                    <div class="mt-8">
                        <div class="flex items-center mb-4">
                            <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center text-white font-bold mr-3 shadow-sm">M</div>
                            <h3 class="text-lg font-semibold text-slate-800">M-Pesa Payment Simulation</h3>
                        </div>
                        
                        <div class="bg-green-50/50 p-5 rounded-lg border border-green-200">
                            <p class="text-sm text-slate-600 mb-4">
                                You are about to pay <strong class="text-green-700 text-base">KSh <?= number_format($total_price, 2) ?></strong>. Enter your Safaricom phone number below. A prompt will be sent to your phone to enter your M-Pesa PIN.
                            </p>
                            
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Safaricom Phone Number</label>
                                <div class="flex shadow-sm rounded-md">
                                    <span class="inline-flex items-center px-4 rounded-l-md border border-r-0 border-slate-300 bg-slate-100 text-slate-500 font-medium">
                                        +254
                                    </span>
                                    <input type="tel" required name="mpesa_number" pattern="[0-9]{9}" title="Please enter 9 digits after the country code" class="flex-1 min-w-0 block w-full px-3 py-2 rounded-none rounded-r-md border border-slate-300 focus:ring-green-500 focus:border-green-500 outline-none" placeholder="712 345 678">
                                </div>
                                <p class="text-xs text-slate-400 mt-2">Exclude the 0 at the beginning (e.g., 712345678)</p>
                            </div>
                        </div>
                    </div>

                    <button type="submit" name="place_order" class="w-full bg-green-600 text-white font-bold py-3 rounded-md hover:bg-green-700 shadow-md transform transition-all duration-200 mt-6 flex justify-center items-center">
                        Pay KSh <?= number_format($total_price, 2) ?> via M-Pesa
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>