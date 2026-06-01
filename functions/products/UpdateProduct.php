<?php

function UpdateProduct($data, $conn)
{
    $id           = $data['id']                   ?? '';
    $requesterId  = $data['userid']               ?? '';
    $name         = $data['product_name']         ?? '';
    $price        = $data['product_price']        ?? '';
    $description  = $data['product_description']  ?? '';
    $availability = $data['product_availability'] ?? 'available';
    $listingType  = $data['listing_type']         ?? '';
    $bidDeadline  = $data['bid_deadline']         ?? null;

    if (empty($id) || empty($requesterId)) {
        echo json_encode(["success" => false, "message" => "Artikel ID en gebruiker ID zijn verplicht"]);
        return;
    }

    $check = mysqli_prepare($conn, "SELECT user_id FROM products p WHERE id = ?");
    mysqli_stmt_bind_param($check, 'i', $id);
    mysqli_stmt_execute($check);
    $checkResult = mysqli_stmt_get_result($check);
    $product     = mysqli_fetch_assoc($checkResult);

    if (!$product) {
        echo json_encode(["success" => false, "message" => "Artikel niet gevonden"]);
        return;
    }

    if ($product['user_id'] != $requesterId && !isAdmin($requesterId, $conn)) {
        echo json_encode(["success" => false, "message" => "Geen toegang"]);
        return;
    }

    // ── images ────────────────────────────────────────────────────────────────
    $rawImages = [];
    if (!empty($data['product_images']) && is_array($data['product_images'])) {
        $rawImages = array_slice($data['product_images'], 0, 4);
    } elseif (!empty($data['product_img'])) {
        $rawImages = [$data['product_img']];
    }

    $newImgBytes = null; // for legacy product_img column

    if (!empty($rawImages)) {
        // Replace all images
        $del = mysqli_prepare($conn, "DELETE FROM product_images WHERE product_id = ?");
        mysqli_stmt_bind_param($del, 'i', $id);
        mysqli_stmt_execute($del);

        foreach ($rawImages as $index => $rawImg) {
            if (empty($rawImg)) continue;
            $imgBytes = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $rawImg));
            if (empty($imgBytes)) continue;
            $pos     = $index + 1;
            $imgStmt = mysqli_prepare($conn,
                "INSERT INTO product_images (product_id, image_data, position) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($imgStmt, 'isi', $id, $imgBytes, $pos);
            mysqli_stmt_execute($imgStmt);
            if ($index === 0) $newImgBytes = $imgBytes;
        }
    }

    // ── update products row ───────────────────────────────────────────────────
    if ($newImgBytes !== null) {
        $stmt = mysqli_prepare($conn,
            "UPDATE products SET product_name=?, product_price=?, product_img=?, product_description=?, product_availability=?, listing_type=?, bid_deadline=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, 'sssssssi',
            $name, $price, $newImgBytes, $description, $availability, $listingType, $bidDeadline, $id);
    } else {
        $stmt = mysqli_prepare($conn,
            "UPDATE products SET product_name=?, product_price=?, product_description=?, product_availability=?, listing_type=?, bid_deadline=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, 'ssssssi',
            $name, $price, $description, $availability, $listingType, $bidDeadline, $id);
    }

    if (!mysqli_stmt_execute($stmt)) {
        echo json_encode(["success" => false, "message" => "Fout bij bijwerken: " . mysqli_error($conn)]);
        return;
    }

    echo json_encode(["success" => true, "message" => "Artikel succesvol bijgewerkt"]);
}
