<?php
require_once('../config/db_connection.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $productID = mysqli_real_escape_string($connect, $_POST['product_id']);
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    $markupPercentage = isset($_POST['markup_percentage']) && $_POST['markup_percentage'] !== ''
        ? (float) $_POST['markup_percentage']
        : 0.00;
    $newSaleQuantity = isset($_POST['sale_quantity']) && $_POST['sale_quantity'] !== ''
        ? (int) $_POST['sale_quantity']
        : 0;

    // Get current stock and sale quantity
    $queryStock = "SELECT Stock, SaleQuantity FROM producttb WHERE ProductID = '$productID'";
    $resultStock = $connect->query($queryStock);
    if (!$resultStock || $resultStock->num_rows === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Product not found'
        ]);
        exit;
    }

    $rowStock = $resultStock->fetch_assoc();
    $currentStock = (int) $rowStock['Stock'];
    $currentSaleQuantity = (int) $rowStock['SaleQuantity'];

    // Calculate the change in sale quantity
    $difference = $newSaleQuantity - $currentSaleQuantity;

    // Adjust stock based on the difference
    $updatedStock = $currentStock - $difference;

    // Ensure stock does not go negative
    if ($updatedStock < 0) {
        $difference = $currentStock; // only sell remaining stock
        $newSaleQuantity = $currentSaleQuantity + $difference;
        $updatedStock = 0;
    }

    // Update product table with new stock and sale quantity
    $query = "UPDATE producttb 
            SET IsActive = '$isActive', 
                MarkupPercentage = '$markupPercentage', 
                SaleQuantity = '$newSaleQuantity',
                Stock = '$updatedStock'
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
