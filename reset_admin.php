<?php
include 'db_connect.php';

// Hardcoded hash for 'admin' to avoid any issues
$hashed_password = password_hash('admin', PASSWORD_DEFAULT);

$sql = "UPDATE users SET password = '$hashed_password', role = 'admin' WHERE id_number = 'admin'";

if (mysqli_query($conn, $sql)) {
    echo "<h1>Admin Password Reset Successful!</h1>";
    echo "<p>You can now log in with:</p>";
    echo "<ul><li>ID: admin</li><li>Password: admin</li></ul>";
    echo "<a href='login.php'>Go to Login</a>";
} else {
    echo "Error updating record: " . mysqli_error($conn);
}
?>
