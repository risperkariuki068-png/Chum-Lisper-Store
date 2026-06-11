<?php
session_start();
require 'db_connect.php';

$stmt = $pdo->prepare("SELECT * FROM products");
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$is_logged_in = isset($_SESSION['user_id']);
$is_admin = $is_logged_in && $_SESSION['user_role'] === 'admin';
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chum & Lisper Store</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 text-slate-800">

    <nav class="bg-white shadow-sm p-4 relative z-50">
        <div class="max-w-7xl mx-auto flex flex-wrap justify-between items-center px-4">
            <h1 class="text-2xl font-bold text-indigo-600 tracking-tight"><a href="index.php">Chum & Lisper Store</a></h1>
            <div class="space-x-4 flex items-center mt-4 sm:mt-0">
                <a href="index.php" class="text-slate-600 hover:text-indigo-600 font-medium transition">Home</a>
                <a href="cart.php" class="text-slate-600 hover:text-indigo-600 font-medium transition relative">
                    Cart 
                    <?php if(!empty($_SESSION['cart'])): ?>
                        <span class="absolute -top-2 -right-3 bg-red-500 text-white text-xs font-bold px-1.5 py-0.5 rounded-full"><?= array_sum($_SESSION['cart']) ?></span>
                    <?php endif; ?>
                </a>
                
                <?php if ($is_logged_in): ?>
                    <?php if ($is_admin): ?>
                        <a href="admin.php" class="text-amber-600 hover:text-amber-700 font-medium transition">Admin Panel</a>
                    <?php endif; ?>
                    <a href="logout.php" class="bg-slate-900 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-slate-800 transition">Logout (<?= htmlspecialchars($_SESSION['user_name']) ?>)</a>
                <?php else: ?>
                    <a href="auth.php" class="bg-indigo-600 text-white px-5 py-2 rounded-md font-medium hover:bg-indigo-700 transition shadow-sm">Login / Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <header class="relative bg-cover bg-center h-[500px] flex items-center" 
            style="background-image: url('https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?ixlib=rb-4.0.3&auto=format&fit=crop&w=2000&q=80');">
        <div class="absolute inset-0 bg-slate-900/70"></div> 
        
        <div class="relative z-10 max-w-7xl mx-auto px-8 w-full">
            <div class="max-w-2xl text-white">
                <span class="text-indigo-400 font-bold tracking-wider uppercase text-sm mb-4 block">Welcome to</span>
                <h2 class="text-5xl md:text-6xl font-extrabold mb-6 leading-tight">Chum & Lisper Store.</h2>
                <p class="text-lg md:text-xl text-slate-200 mb-8">Discover our curated selection of premium products tailored just for you. Quality guaranteed.</p>
                <a href="#product-grid" class="inline-block bg-indigo-600 text-white font-bold px-8 py-4 rounded-md hover:bg-indigo-700 hover:-translate-y-1 transform transition-all duration-200 shadow-lg">
                    Start Shopping
                </a>
            </div>
        </div>
    </header>

    <main id="product-grid" class="max-w-7xl mx-auto px-4 py-16">
        <div class="flex justify-between items-end mb-10">
            <div>
                <h2 class="text-3xl font-bold text-slate-900">Featured Products</h2>
            </div>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
            <?php foreach ($products as $product): ?>
                <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-xl hover:-translate-y-1 transform transition-all duration-300 flex flex-col">
                    <div class="relative h-56 overflow-hidden">
                        <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="w-full h-full object-cover">
                    </div>
                    <div class="p-6 flex flex-col flex-grow">
                        <h3 class="text-lg font-bold text-slate-800 mb-2 truncate"><?= htmlspecialchars($product['name']) ?></h3>
                        <p class="text-slate-500 text-sm mb-6 line-clamp-2 flex-grow"><?= htmlspecialchars($product['description']) ?></p>
                        
                        <div class="flex justify-between items-center mt-auto border-t border-slate-100 pt-4">
                            <span class="text-2xl font-extrabold text-slate-900">KSh <?= number_format($product['price'], 2) ?></span>
                            <form action="cart.php" method="POST">
                                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                <button type="submit" name="add_to_cart" class="bg-slate-900 text-white px-4 py-2 rounded-md text-sm font-bold hover:bg-slate-800 transition">Add +</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <footer class="bg-slate-900 text-slate-400 py-8 text-center mt-auto">
        <p>&copy; <?= date('Y') ?> Chum & Lisper Store. All rights reserved.</p>
    </footer>

</body>
</html>