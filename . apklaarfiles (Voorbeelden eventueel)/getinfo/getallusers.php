<?php
function getAllUsers($conn) {
    $sql = "
        SELECT 
            userid,
            email,
            firstname,
            lastname,
            phonenumber,
            role,
            isverified,
            created_at
        FROM user
        ORDER BY created_at DESC
    ";

    $result = mysqli_query($conn, $sql);

    if (!$result) {
        echo json_encode([
            'success' => false,
            'message' => mysqli_error($conn)
        ]);
        return;
    }

    echo json_encode([
        'success' => true,
        'data' => mysqli_fetch_all($result, MYSQLI_ASSOC)
    ]);
}

