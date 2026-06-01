<?php

function UpdateProduct($data, $conn)
{
    $id           = $data['id']                   ?? '';
    $requesterId  = $data['userid']               ?? '';
    $name         = $data['product_name']         ?? '';
    $price        = $data['product_price']        ?? '';
    $img          = $data['product_img']          ?? '';
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
    $product = mysqli_fetch_assoc($checkResult);

    if (!$product) {
        echo json_encode(["success" => false, "message" => "Artikel niet gevonden"]);
        return;
    }

    if ($product['user_id'] != $requesterId && !isAdmin($requesterId, $conn)) {
        echo json_encode(["success" => false, "message" => "Geen toegang"]);
        return;
    }

    if (!empty($img)) {
        $imgBytes = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $img));
        $stmt = mysqli_prepare($conn,
            "UPDATE products SET product_name=?, product_price=?, product_img=?, product_description=?, product_availability=?, listing_type=?, bid_deadline=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, 'sssssssi', $name, $price, $imgBytes, $description, $availability, $listingType, $bidDeadline, $id);
    } else {
        $stmt = mysqli_prepare($conn,
            "UPDATE products SET product_name=?, product_price=?, product_description=?, product_availability=?, listing_type=?, bid_deadline=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, 'ssssssi', $name, $price, $description, $availability, $listingType, $bidDeadline, $id);
    }

    $result = mysqli_stmt_execute($stmt);

    if (!$result) {
        echo json_encode(["success" => false, "message" => "Fout bij bijwerken: " . mysqli_error($conn)]);
        return;
    }

    echo json_encode(["success" => true, "message" => "Artikel succesvol bijgewerkt"]);
}
