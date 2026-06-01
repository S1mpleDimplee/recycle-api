<?php

function GetAllProducts($conn)
{
    $sql = "SELECT p.id, p.user_id, p.product_name, p.product_price, p.product_description,
                   p.product_availability, p.listing_type, p.bid_deadline, p.created_at,
                   u.name AS seller_name, u.username AS seller_username,
                   (p.product_img IS NOT NULL AND LENGTH(p.product_img) > 0) AS has_legacy_img,
                   COALESCE((SELECT MAX(b.amount) FROM bids b WHERE b.product_id = p.id AND b.status = 'pending'), 0) AS highest_bid,
                   (SELECT COUNT(DISTINCT b.bidder_id) FROM bids b WHERE b.product_id = p.id AND b.status = 'pending') AS bid_count
            FROM products p
            LEFT JOIN users u ON u.id = p.user_id
            ORDER BY p.id DESC";
    $result = mysqli_query($conn, $sql);

    // Collect rows keyed by product id
    $products = [];
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
