<?php
session_start();

// SECURITY FIX: Prevent unauthorized access to the admin dashboard
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: auth.php");
    exit;
}

require 'db_connect.php';
$message = '';

// 1. DELETE OPERATION
if (isset($_GET['delete'])) {
    $delete_id = (int)$_GET['delete'];
    
    // Optional: Fetch the image path before deleting the record to delete the file from the server too
    $stmt_img = $pdo->prepare("SELECT image_url FROM products WHERE id = ?");
    $stmt_img->execute([$delete_id]);
    $img_to_delete = $stmt_img->fetchColumn();

    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    if ($stmt->execute([$delete_id])) {
        $message = "Product deleted successfully.";
        // Clean up the actual file if it exists in the uploads folder
        if ($img_to_delete && file_exists($img_to_delete) && strpos($img_to_delete, 'uploads/') === 0) {
            unlink($img_to_delete);
        }
    }
}

// 2. CREATE & UPDATE OPERATIONS
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_product'])) {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $category_id = $_POST['category_id'];
    $product_id = $_POST['product_id'];

    // Default to the existing image (if editing) or a placeholder (if new)
    $image_url = $_POST['existing_image_url'] ?? 'https://via.placeholder.com/300x200?text=Product';

    // Handle File Upload Logic
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        
        // Create the uploads directory automatically if it doesn't exist
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        // Clean the filename to remove spaces and special characters for safety, prepended with a timestamp
        $clean_filename = preg_replace("/[^a-zA-Z0-9.]/", "_", basename($_FILES['image_file']['name']));
        $file_name = time() . '_' . $clean_filename;
        $target_file = $upload_dir . $file_name;

        // Move the file from temporary storage to the uploads folder
        if (move_uploaded_file($_FILES['image_file']['tmp_name'], $target_file)) {
            $image_url = $target_file; // The database will now store "uploads/filename.jpg"
        } else {
            $message = "Error: Failed to move uploaded file.";
        }
    }

    if (empty($product_id)) {
        $stmt = $pdo->prepare("INSERT INTO products (name, price, description, image_url, category_id) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$name, $price, $description, $image_url, $category_id])) {
            $message = "Product added successfully.";
        }
    } else {
        $stmt = $pdo->prepare("UPDATE products SET name = ?, price = ?, description = ?, image_url = ?, category_id = ? WHERE id = ?");
        if ($stmt->execute([$name, $price, $description, $image_url, $category_id, $product_id])) {
            $message = "Product updated successfully.";
        }
    }
}

// 3. READ OPERATION (Edit Form)
$edit_product = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_product = $stmt->fetch(PDO::FETCH_ASSOC);
}

// 4. READ OPERATION (Data Table)
$stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC");
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$cat_stmt = $pdo->query("SELECT * FROM categories");
$categories = $cat_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Chum & Lisper Store</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 text-slate-800">

    <nav class="bg-slate-900 text-white p-4 mb-8">
        <div class="max-w-6xl mx-auto flex flex-wrap justify-between items-center">
            <h1 class="text-xl font-bold">Chum & Lisper Admin</h1>
            <div class="space-x-4">
                <a href="index.php" class="text-slate-300 hover:text-white">Live Store</a>
                <a href="logout.php" class="text-red-400 hover:text-red-300">Logout</a>
            </div>
        </div>
    </nav>

    <main class="max-w-6xl mx-auto px-4 pb-12 grid grid-cols-1 md:grid-cols-3 gap-8">
        <div class="md:col-span-1">
            <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-100 sticky top-4">
                <h2 class="text-xl font-bold mb-4"><?= $edit_product ? 'Edit Product' : 'Add New Product' ?></h2>
                <?php if ($message): ?>
                    <div class="bg-green-100 text-green-700 p-3 rounded-md mb-4 text-sm"><?= htmlspecialchars($message) ?></div>
                <?php endif; ?>
                
                <form action="admin.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                    <input type="hidden" name="product_id" value="<?= $edit_product['id'] ?? '' ?>">
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1">Product Name</label>
                        <input type="text" name="name" required value="<?= htmlspecialchars($edit_product['name'] ?? '') ?>" class="w-full border rounded-md p-2">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1">Price (KSh)</label>
                        <input type="number" step="0.01" name="price" required value="<?= $edit_product['price'] ?? '' ?>" class="w-full border rounded-md p-2">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1">Category</label>
                        <select name="category_id" required class="w-full border rounded-md p-2 bg-white">
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= (isset($edit_product['category_id']) && $edit_product['category_id'] == $cat['id']) ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1">Product Image</label>
                        
                        <?php if ($edit_product && !empty($edit_product['image_url'])): ?>
                            <div class="mb-2">
                                <img src="<?= htmlspecialchars($edit_product['image_url']) ?>" alt="Current Image" class="w-full h-32 object-cover rounded border border-slate-200">
                            </div>
                        <?php endif; ?>
                        
                        <input type="hidden" name="existing_image_url" value="<?= htmlspecialchars($edit_product['image_url'] ?? '') ?>">
                        
                        <input type="file" name="image_file" accept="image/*" class="w-full border border-slate-300 rounded-md p-2 bg-white text-sm file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        
                        <?php if ($edit_product): ?>
                            <p class="text-xs text-slate-500 mt-1">Leave blank to keep the current image.</p>
                        <?php endif; ?>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1">Description</label>
                        <textarea name="description" rows="3" class="w-full border rounded-md p-2"><?= htmlspecialchars($edit_product['description'] ?? '') ?></textarea>
                    </div>
                    
                    <button type="submit" name="save_product" class="w-full bg-indigo-600 text-white py-2 rounded-md hover:bg-indigo-700 transition"><?= $edit_product ? 'Update Product' : 'Save Product' ?></button>
                    
                    <?php if ($edit_product): ?>
                        <a href="admin.php" class="block text-center text-slate-500 hover:text-slate-800 text-sm mt-2">Cancel Edit</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <div class="md:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm min-w-[500px]">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200">
                            <th class="p-4 font-semibold text-slate-600 w-16">Image</th>
                            <th class="p-4 font-semibold text-slate-600">Product Details</th>
                            <th class="p-4 font-semibold text-slate-600">Price</th>
                            <th class="p-4 font-semibold text-slate-600 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr class="border-b border-slate-100 hover:bg-slate-50">
                                <td class="p-4">
                                    <img src="<?= htmlspecialchars($product['image_url']) ?>" class="w-12 h-12 object-cover rounded border border-slate-200" alt="Thumb">
                                </td>
                                <td class="p-4">
                                    <div class="font-medium text-slate-800"><?= htmlspecialchars($product['name']) ?></div>
                                    <div class="text-xs text-slate-500">ID: #<?= $product['id'] ?> | <?= htmlspecialchars($product['category_name']) ?></div>
                                </td>
                                <td class="p-4 text-slate-600 font-medium">KSh <?= number_format($product['price'], 2) ?></td>
                                <td class="p-4 text-right space-x-3">
                                    <a href="admin.php?edit=<?= $product['id'] ?>" class="text-indigo-600 hover:text-indigo-800 font-medium">Edit</a>
                                    <a href="admin.php?delete=<?= $product['id'] ?>" onclick="return confirm('Delete this product permanently?');" class="text-red-500 hover:text-red-700 font-medium">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>