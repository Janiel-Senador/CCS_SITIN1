<?php
include 'includes/header.php';
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : "";
$sql = "SELECT * FROM users WHERE role = 'student' AND (first_name LIKE '%$search%' OR last_name LIKE '%$search%' OR id_number LIKE '%$search%')";
$result = mysqli_query($conn, $sql);

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM users WHERE id = '$id'");
    header("Location: admin_students.php");
    exit();
}
?>

<div class="rules-card glass" style="grid-column: span 2; max-width: 1200px; margin: 40px auto;">
    <h3 style="margin-bottom: 20px;">Student List Management</h3>
    
    <div class="search-bar">
        <form action="admin_students.php" method="GET" style="display: flex; gap: 10px; width: 100%;">
            <input type="text" name="search" class="form-control" placeholder="Search by name or ID number..." value="<?php echo $search; ?>">
            <button type="submit" class="btn" style="width: 150px;">Search</button>
        </form>
    </div>

    <div class="table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID Number</th>
                    <th>Name</th>
                    <th>Course & Year</th>
                    <th>Email</th>
                    <th>Sessions</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?php echo $row['id_number']; ?></td>
                        <td><?php echo $row['first_name'] . " " . $row['last_name']; ?></td>
                        <td><?php echo $row['course'] . " - " . $row['course_level']; ?></td>
                        <td><?php echo $row['email']; ?></td>
                        <td><?php echo $row['sessions_remaining']; ?></td>
                        <td>
                            <a href="edit_student.php?id=<?php echo $row['id']; ?>" class="action-btn btn-edit"><i class="fas fa-edit"></i> Edit</a>
                            <a href="admin_students.php?delete=<?php echo $row['id']; ?>" class="action-btn btn-delete" onclick="return confirm('Are you sure you want to delete this student?')"><i class="fas fa-trash"></i> Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
