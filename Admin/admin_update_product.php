    <?php
    require_once('../config/db_connection.php');

    header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
        $productID = mysqli_real_escape_string($connect, $_POST['product_id']);
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        $markupPercentage = isset($_POST['markup_percentage']) && $_POST['markup_percentage'] !== ''
            ? (float) $_POST['markup_percentage']
            : 0.00;
        $saleQuantity = isset($_POST['sale_quantity']) && $_POST['sale_quantity'] !== ''
            ? (int) $_POST['sale_quantity']
            : 0;

        // Make sure sale quantity does not exceed stock
        $queryStock = "SELECT Stock FROM producttb WHERE ProductID = '$productID'";
        $resultStock = $connect->query($queryStock);
        $rowStock = $resultStock->fetch_assoc();
        $stock = (int) $rowStock['Stock'];
        if ($saleQuantity > $stock) {
            $saleQuantity = $stock;
        }

        // Update product table
        $query = "UPDATE producttb 
                SET IsActive = '$isActive', 
                    MarkupPercentage = '$markupPercentage', 
                    SaleQuantity = '$saleQuantity'
                WHERE ProductID = '$productID'";

        if (mysqli_query($connect, $query)) {
            // Get updated product data for response
            $queryProduct = "SELECT * FROM producttb WHERE ProductID = '$productID'";
            $resultProduct = $connect->query($queryProduct);
            $product = $resultProduct->fetch_assoc();

            // Correctly calculate profit and selling price from updated product
            $basePrice = $product['Price'];
            $updatedMarkup = $product['MarkupPercentage'];
            $finalPrice = $basePrice + ($basePrice * ($updatedMarkup / 100));
            $profit = $basePrice * ($updatedMarkup / 100);
            $availableStock = $product['Stock'] - $product['SaleQuantity'];

            // Count all products that are active/on sale
            $queryAllProductCount = "SELECT COUNT(*) as count FROM producttb WHERE IsActive = 1";
            $resultAllProductCount = $connect->query($queryAllProductCount);
            $rowAllProductCount = $resultAllProductCount->fetch_assoc();
            $allProductCount = $rowAllProductCount['count'];

            echo json_encode([
                'success' => true,
                'finalPrice' => number_format($finalPrice, 2),
                'profit' => number_format($profit, 2),
                'markupPercentage' => (floor($updatedMarkup) == $updatedMarkup) ? (int)$updatedMarkup : number_format($updatedMarkup, 2),
                'availableStock' => $availableStock,
                'saleQuantity' => $product['SaleQuantity'],
                'isActive' => (bool)$product['IsActive'],
                'allProductCount' => $allProductCount
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Database update failed: ' . mysqli_error($connect)
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid request'
        ]);
    }
