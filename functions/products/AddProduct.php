<?php

function AddProduct($data, $conn)
{
    $userId       = $data['userid']               ?? '';
    $name         = $data['product_name']         ?? '';
    $price        = $data['product_price']        ?? '';
    $img          = $data['product_img']          ?? '';
    $description  = $data['product_description']  ?? '';
    $availability = $data['product_availability'] ?? 'available';
    $rawDeadline  = $data['bid_deadline']  ?? null;
    $bid_deadline = $rawDeadline ? date('Y-m-d H:i:s', strtotime($rawDeadline)) : null;
    $listing_type = in_array($data['listing_type'] ?? '', ['bid', 'buy']) ? $data['listing_type'] : 'bid';

    if ($listing_type === 'buy') $bid_deadline = null;

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

    echo json_encode([
        "success" => true,
        "message" => "Artikel succesvol toegevoegd",
        "data"    => ["id" => mysqli_insert_id($conn)]
    ]);
}
