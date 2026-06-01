<?php

function GetUserProducts($data, $conn)
{
    $userId = $data['userid'] ?? '';

    if (empty($userId)) {
        echo json_encode(["success" => false, "message" => "Gebruiker ID is verplicht"]);
        return;
    }

    $stmt = mysqli_prepare($conn,
        "SELECT id, user_id, product_name, product_price, product_description,
                product_availability, listing_type, bid_deadline, created_at,
                (product_img IS NOT NULL AND LENGTH(product_img) > 0) AS has_legacy_img
         FROM products WHERE user_id = ? ORDER BY id DESC");
    mysqli_stmt_bind_param($stmt, 'i', $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    // Collect rows keyed by product id
    $products   = [];
    $orderedIds = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $products[$row['id']] = $row;
        $orderedIds[]         = (int)$row['id'];
    }

    // Fetch all image positions in one query
    $imageMap = [];
    if (!empty($orderedIds)) {
        $safeIds   = implode(',', $orderedIds);
        $imgResult = mysqli_query($conn,
            "SELECT product_id, position FROM product_images
             WHERE product_id IN ($safeIds)
             ORDER BY product_id, position ASC");
        while ($imgRow = mysqli_fetch_assoc($imgResult)) {
            $imageMap[(int)$imgRow['product_id']][] = (int)$imgRow['position'];
        }
    }

    // Build output
    $output = [];
    foreach ($orderedIds as $pid) {
        $row    = $products[$pid];
        $images = [];

        if (!empty($imageMap[$pid])) {
            foreach ($imageMap[$pid] as $pos) {
                $images[] = serveImageUrl($pid, $pos);
            }
        } elseif ($row['has_legacy_img']) {
            $images[] = serveImageUrl($pid, 1);
        }

        $row['images']      = $images;
        $row['product_img'] = $images[0] ?? null; // backward compat
        unset($row['has_legacy_img']);
        $output[] = $row;
    }

    echo json_encode(["success" => true, "data" => $output]);
}
