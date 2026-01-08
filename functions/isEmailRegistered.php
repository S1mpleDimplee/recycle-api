<?php
function isEmailRegistered($email, $conn)
{
    $sql = "SELECT * FROM user WHERE email='$email'";
    $result = mysqli_query($conn, $sql);
    return mysqli_num_rows($result) > 0;
}
?>