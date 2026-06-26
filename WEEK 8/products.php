<?php
session_start();
require_once 'db_connect.php';

try {
    // Fetch your actual 4 items from your products table
    $stmt = $pdo->prepare("SELECT * FROM products LIMIT 4"); 
    $stmt->execute();
    $store_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database query error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Showcase - Chum-Lisper-Store</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }

        .header-title {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }

        /* ========================================================
           REQUIREMENT: MOBILE-FIRST DESIGN (Default Styles)
           By default, the layout handles exactly 1 column on mobile view.
           ======================================================== */
        .product-grid {
            display: grid; /* REQUIREMENT: CSS Grid Layout */
            grid-template-columns: 1fr; 
            gap: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .product-card {
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.05);
            padding: 20px;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        /* REQUIREMENT: Responsive Product Images */
        .product-image {
            max-width: 100%;
            height: 180px;
            object-fit: cover;
            border-radius: 6px;
            margin-bottom: 15px;
        }

        .product-name {
            font-size: 1.25rem;
            color: #222;
            margin: 10px 0;
            font-weight: 600;
        }

        .product-desc {
            font-size: 0.9rem;
            color: #666;
            line-height: 1.4;
            margin-bottom: 15px;
        }

        .product-price {
            font-size: 1.3rem;
            color: #0066cc;
            font-weight: bold;
        }

        /* ========================================================
           REQUIREMENT: AT LEAST TWO MEDIA QUERY BREAKPOINTS
           ======================================================== */

        /* Breakpoint 1: Tablet View (Widths 600px and up) */
        @media (min-width: 600px) {
            .product-grid {
                grid-template-columns: repeat(2, 1fr); /* 2 items per row */
            }
        }

        /* Breakpoint 2: Desktop View (Widths 950px and up) */
        @media (min-width: 950px) {
            .product-grid {
                grid-template-columns: repeat(4, 1fr); /* 4 items side-by-side */
            }
        }
    </style>
</head>
<body>

    <h1 class="header-title">🏪 Chum-Lisper-Store - Live Product Showcase</h1>

    <div class="product-grid">
        <?php if (!empty($store_products)): ?>
            <?php foreach ($store_products as $product): ?>
                <div class="product-card">
                    <?php 
                        $img_src = trim($product['image_url'] ?? '');
                        
                        // Strict check: if it doesn't begin with http:// or https://, or is cut off, use fallback link
                        if (strpos($img_src, 'http://') === 0 || strpos($img_src, 'https://') === 0) {
                            // If it's a full web URL link, check if it's missing the ending parameters or cut off
                            if (strlen($img_src) < 30) { 
                                $img_src = 'https://picsum.photos/300/200';
                            }
                        } elseif (!empty($img_src) && $img_src !== 'placeholder.jpg') {
                            // Local file saved inside uploads
                            $img_src = 'uploads/' . $img_src;
                        } else {
                            // Default Fallback placeholder image link
                            $img_src = 'https://picsum.photos/300/200';
                        }
                    ?>
                    
                    <img src="<?php echo htmlspecialchars($img_src); ?>" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>" 
                         class="product-image">
                         
                    <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
                    
                    <p class="product-desc"><?php echo htmlspecialchars($product['description'] ?? 'No description available.'); ?></p>
                    
                    <span class="product-price">Ksh <?php echo number_format($product['price'], 2); ?></span>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align: center; grid-column: 1 / -1;">No products found in the database list.</p>
        <?php endif; ?>
    </div>

</body>
</html>