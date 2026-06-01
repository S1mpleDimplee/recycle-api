<?php

function AddProduct($data, $conn)
{
    $userId       = $data['userid']               ?? '';
    $name         = $data['product_name']         ?? '';
    $price        = $data['product_price']        ?? '';
    $description  = $data['product_description']  ?? '';
    $availability = $data['product_availability'] ?? 'available';
    $rawDeadline  = $data['bid_deadline']          ?? null;
    $bid_deadline = $rawDeadline ? date('Y-m-d H:i:s', strtotime($rawDeadline)) : null;
    $listing_type = in_array($data['listing_type'] ?? '', ['bid', 'buy']) ? $data['listing_type'] : 'bid';

    if ($listing_type === 'buy') $bid_deadline = null;

    // Accept product_images array (up to 4) or legacy single product_img
    $rawImages = [];
    if (!empty($data['product_images']) && is_array($data['product_images'])) {
        $rawImages = array_slice($data['product_images'], 0, 4);
    } elseif (!empty($data['product_img'])) {
        $rawImages = [$data['product_img']];
    }

    // Decode first image for legacy product_img column (keeps existing queries working)
    $img = !empty($rawImages[0])
        ? base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $rawImages[0]))
        : null;

    if (empty($userId) || empty($name) || $price === '') {
        echo json_encode(["success" => false, "message" => "Naam, prijs en gebruiker zijn verplicht"]);
        return;
    }

    $stmt = mysqli_prepare($conn,
        "INSERT INTO products (user_id, product_name, product_price, product_img, product_description, product_availability, bid_deadline, listing_type)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'isssssss', $userId, $name, $price, $img, $description, $availability, $bid_deadline, $listing_type);
    $result = mysqli_stmt_execute($stmt);

    if (!$result) {
        echo json_encode(["success" => false, "message" => "Fout bij toevoegen: " . mysqli_error($conn)]);
        return;
    }

    $productId = mysqli_insert_id($conn);

    // Store all images in product_images table
    foreach ($rawImages as $index => $rawImg) {
        if (empty($rawImg)) continue;
        $imgBytes = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $rawImg));
        if (empty($imgBytes)) continue;
        $pos      = $index + 1;
        $imgStmt  = mysqli_prepare($conn,
            "INSERT INTO product_images (product_id, image_data, position) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($imgStmt, 'isi', $productId, $imgBytes, $pos);
        mysqli_stmt_execute($imgStmt);
    }

    echo json_encode([
        "success" => true,
        "message" => "Artikel succesvol toegevoegd",
        "data"    => ["id" => $productId]
    ]);
}
