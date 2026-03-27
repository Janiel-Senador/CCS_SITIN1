<?php
include 'includes/header.php';
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : "";
$search_results = array();

// 🔥 NEW: Search logic
if (!empty($search)) {
    $sql = "SELECT * FROM users WHERE role = 'student' AND 
            (id_number LIKE '%$search%' OR 
             first_name LIKE '%$search%' OR 
             last_name LIKE '%$search%' OR 
             CONCAT(first_name, ' ', last_name) LIKE '%$search%' OR
             email LIKE '%$search%')
            ORDER BY created_at DESC";
    $result = mysqli_query($conn, $sql);
    while($row = mysqli_fetch_assoc($result)) {
        $search_results[] = $row;
    }
}
?>

<div class="rules-card glass" style="grid-column: span 2; max-width: 1200px; margin: 40px auto;">
    <h3 style="margin-bottom: 20px;">
        <i class="fas fa-search"></i> Search Student Record
    </h3>
    
    <!-- Search Form -->
    <div class="search-bar">
        <form action="admin_search.php" method="GET" style="display: flex; gap: 10px; width: 100%;">
            <input type="text" name="search" class="form-control" 
                   placeholder="Search by ID number, name, or email..." 
                   value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="btn" style="width: 150px;">
                <i class="fas fa-search"></i> Search
            </button>
            <?php if (!empty($search)): ?>
                <a href="admin_search.php" class="btn" style="width: 100px; background: #6c757d;">
                    <i class="fas fa-times"></i> Clear
                </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Search Results -->
    <?php if (!empty($search)): ?>
        <div style="margin-top: 20px;">
            <p style="color: #666; margin-bottom: 15px;">
                <strong><?php echo count($search_results); ?></strong> result(s) found for "<?php echo htmlspecialchars($search); ?>"
            </p>
            
            <?php if (count($search_results) > 0): ?>
                <div class="table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID Number</th>
                                <th>Name</th>
                                <th>Course & Year</th>
                                <th>Email</th>
                                <th>Sessions</th>
                                <th>Date Registered</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($search_results as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['id_number']); ?></td>
                                <td><?php echo htmlspecialchars($row['first_name'] . " " . $row['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['course'] . " - " . $row['course_level'] . " Year"); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td>
                                    <span class="<?php echo $row['sessions_remaining'] <= 5 ? 'alert-danger' : 'alert-success'; ?>" 
                                          style="padding: 3px 8px; border-radius: 5px; font-size: 0.85rem;">
                                        <?php echo $row['sessions_remaining']; ?>
                                    </span>
                                </td>
                                <td><?php echo date('Y-m-d', strtotime($row['created_at'])); ?></td>
                                <td>
                                    <a href="edit_student.php?id=<?php echo $row['id']; ?>" 
                                       class="action-btn btn-edit">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="admin_sitin_form.php?student_id=<?php echo $row['id']; ?>" 
                                       class="action-btn btn-sit-in">
                                        <i class="fas fa-desktop"></i> Sit-in
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-warning" style="padding: 20px; text-align: center; background: #fff3cd; border: 1px solid #ffc107; border-radius: 10px; margin-top: 20px;">
                    <i class="fas fa-exclamation-triangle"></i> 
                    <strong>No students found</strong> matching "<?php echo htmlspecialchars($search); ?>"
                </div>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div style="text-align: center; padding: 40px; color: #666;">
            <i class="fas fa-search" style="font-size: 3rem; margin-bottom: 15px; opacity: 0.3;"></i>
            <p>Enter a student ID number, name, or email to search</p>
        </div>
    <?php endif; ?>
</div>

<style>
.alert-danger {
    background-color: #f8d7da;
    color: #721c24;
}
.alert-success {
    background-color: #d4edda;
    color: #155724;
}
</style>

<?php include 'includes/footer.php'; ?>