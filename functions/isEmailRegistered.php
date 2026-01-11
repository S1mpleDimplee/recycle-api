<?php
function isEmailRegistered($email, $conn)
{
    // Use parameterized query to prevent SQL injection
    $sql = "SELECT * FROM users WHERE email = $1";
    $result = mysqli_query_params($conn, $sql, array($email));

    if (!$result) {
        return false; // or handle error appropriately
    }

    return mysqli_num_rows($result) > 0;
}
?>