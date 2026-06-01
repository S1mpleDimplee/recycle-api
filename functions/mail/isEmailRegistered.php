<?php
function isEmailRegistered($email, $conn)
{
    // Use parameterized query to prevent SQL injection
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (!$result) {
        return false; // or handle error appropriately
    }

    return mysqli_num_rows($result) > 0;
}
?>