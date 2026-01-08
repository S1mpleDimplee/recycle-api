<?php
function fetchinvoices($data, $connection)
{
    // Fetch all invoices regardless of userid
    $sql = "SELECT invoice.*, 
            IF(car.carnickname = '' OR car.carnickname IS NULL, 
               CONCAT(car.brand, ' ', car.model), 
               car.carnickname) AS carnickname, 
            car.brand 
        FROM invoice 
        LEFT JOIN car ON car.carid = invoice.carid 
        ORDER BY invoice.date DESC";

    $result = mysqli_query($connection, $sql);

    if (!$result) {
        echo json_encode([
            "success" => false,
            "message" => "Fout bij het ophalen van facturen: " . mysqli_error($connection)
        ]);
        return;
    }

    $invoices = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $invoices[] = $row;
    }

    echo json_encode([
        "success" => true,
        "message" => "Factures succesvol opgehaald",
        "data" => $invoices
    ]);
}
