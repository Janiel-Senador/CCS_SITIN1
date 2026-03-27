<?php
include 'includes/header.php';
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}
?>

<div class="rules-card glass" style="grid-column: span 2; max-width: 1200px; margin: 40px auto;">
    <h3 style="margin-bottom: 20px;">Sit-in Reports</h3>
    <p style="text-align: center; color: #666;">Sit-in reporting and filtering will be implemented here.</p>
</div>

<?php include 'includes/footer.php'; ?>
