<?php
function getProductsByType($connect, $type)
{
    $query = "SELECT p.*, 
                pt.ProductType, 
                pi_primary.ImageUserPath AS PrimaryImagePath, 
                pi_secondary.ImageUserPath AS SecondaryImagePath
            FROM producttb p
            INNER JOIN producttypetb pt 
                ON p.ProductTypeID = pt.ProductTypeID
            LEFT JOIN productimagetb pi_primary
                ON p.ProductID = pi_primary.ProductID AND pi_primary.PrimaryImage = 1
            LEFT JOIN productimagetb pi_secondary
                ON p.ProductID = pi_secondary.ProductID AND pi_secondary.SecondaryImage = 1
            WHERE pt.ProductType = ?
            AND p.isActive = 1
            GROUP BY p.ProductID";

    $stmt = $connect->prepare($query);
    $stmt->bind_param("s", $type);
    $stmt->execute();
    $result = $stmt->get_result();

    $products = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    }
    $stmt->close();
    return $products;
}
