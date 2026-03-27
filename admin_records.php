<?php
include 'includes/header.php';
include 'db_connect.php';

// 🔐 Admin Access Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

$message = "";

// 🔹 Handle Delete Record
if (isset($_GET['delete_record'])) {
    $record_id = mysqli_real_escape_string($conn, $_GET['delete_record']);
    $delete_sql = "DELETE FROM sitin_records WHERE id = '$record_id'";
    if (mysqli_query($conn, $delete_sql)) {
        $message = "<div class='alert alert-success'>Record deleted successfully!</div>";
    } else {
        $message = "<div class='alert alert-danger'>Error deleting record: " . mysqli_error($conn) . "</div>";
    }
}

// 🔹 Handle Reset All Sessions
if (isset($_POST['reset_all_sessions'])) {
    $reset_sql = "UPDATE users SET sessions_remaining = 30 WHERE role = 'student'";
    if (mysqli_query($conn, $reset_sql)) {
        $message = "<div class='alert alert-success'>All student sessions have been reset to 30!</div>";
    } else {
        $message = "<div class='alert alert-danger'>Error resetting sessions: " . mysqli_error($conn) . "</div>";
    }
}

// 🔹 Search Functionality
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : "";
$filter_status = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : "";
$filter_lab = isset($_GET['lab']) ? mysqli_real_escape_string($conn, $_GET['lab']) : "";

// 🔹 Build Search Query
$where_clauses = [];
if (!empty($search)) {
    $where_clauses[] = "(sr.id_number LIKE '%$search%' OR 
                         sr.student_name LIKE '%$search%' OR 
                         sr.purpose LIKE '%$search%' OR 
                         sr.lab LIKE '%$search%' OR
                         u.course_level LIKE '%$search%')";
}
if (!empty($filter_status) && $filter_status != 'all') {
    $where_clauses[] = "sr.status = '$filter_status'";
}
if (!empty($filter_lab) && $filter_lab != 'all') {
    $where_clauses[] = "sr.lab = '$filter_lab'";
}

$where_sql = !empty($where_clauses) ? "WHERE " . implode(" AND ", $where_clauses) : "";

// 🔹 Fetch Records with Year Level (JOIN with users table)
$records_sql = "SELECT sr.*, u.course_level as year_level 
                FROM sitin_records sr 
                LEFT JOIN users u ON sr.student_id = u.id 
                $where_sql
                ORDER BY sr.time_in DESC LIMIT 100";
$records = mysqli_query($conn, $records_sql);

// 🔹 Get filter options for dropdowns
$labs = ['524', '526', '528', '530'];
?>

<div class="rules-card glass" style="grid-column: span 2; max-width: 1400px; margin: 40px auto;">
    <h3 style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
        <span><i class="fas fa-clipboard-list"></i> Sit-in Records Management</span>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <!-- 🔹 Add Student Button -->
            <a href="register.php" class="btn" style="width: auto; padding: 10px 20px; background: #2196F3;">
                <i class="fas fa-user-plus"></i> Add Student
            </a>
            <!-- 🔹 Reset All Sessions Button -->
            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to reset ALL student sessions to 30? This cannot be undone!');">
                <button type="submit" name="reset_all_sessions" class="btn" style="width: auto; padding: 10px 20px; background: #ff9800;">
                    <i class="fas fa-undo"></i> Reset All Sessions
                </button>
            </form>
        </div>
    </h3>
    
    <?php echo $message; ?>
    
    <!-- 🔹 Search & Filter Bar -->
    <div style="background: rgba(255,255,255,0.3); padding: 20px; border-radius: 10px; margin-bottom: 20px;">
        <form action="admin_records.php" method="GET" style="display: flex; gap: 10px; flex-wrap: wrap; align-items: end;">
            <div class="form-group" style="flex: 2; min-width: 200px;">
                <label for="search" style="font-size: 0.85rem; font-weight: 600; color: var(--primary-color);">Search</label>
                <input type="text" name="search" id="search" class="form-control" 
                       placeholder="Search by ID, Name, Purpose, Lab..." 
                       value="<?php echo htmlspecialchars($search); ?>" style="padding: 10px;">
            </div>
            <div class="form-group" style="flex: 1; min-width: 120px;">
                <label for="status" style="font-size: 0.85rem; font-weight: 600; color: var(--primary-color);">Status</label>
                <select name="status" id="status" class="form-control" style="padding: 10px;">
                    <option value="all" <?php echo $filter_status == 'all' ? 'selected' : ''; ?>>All</option>
                    <option value="active" <?php echo $filter_status == 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="completed" <?php echo $filter_status == 'completed' ? 'selected' : ''; ?>>Completed</option>
                </select>
            </div>
            <div class="form-group" style="flex: 1; min-width: 100px;">
                <label for="lab" style="font-size: 0.85rem; font-weight: 600; color: var(--primary-color);">Lab</label>
                <select name="lab" id="lab" class="form-control" style="padding: 10px;">
                    <option value="all" <?php echo $filter_lab == 'all' ? 'selected' : ''; ?>>All</option>
                    <?php foreach($labs as $lab): ?>
                        <option value="<?php echo $lab; ?>" <?php echo $filter_lab == $lab ? 'selected' : ''; ?>><?php echo $lab; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn" style="width: auto; padding: 10px 25px;">
                <i class="fas fa-search"></i> Search
            </button>
            <?php if (!empty($search) || !empty($filter_status) || !empty($filter_lab)): ?>
                <a href="admin_records.php" class="btn" style="width: auto; padding: 10px 20px; background: #6c757d;">
                    <i class="fas fa-times"></i> Clear
                </a>
            <?php endif; ?>
        </form>
        <?php if (!empty($search) || !empty($filter_status) || !empty($filter_lab)): ?>
            <p style="margin-top: 10px; font-size: 0.9rem; color: #666;">
                <i class="fas fa-filter"></i> 
                Filtering: 
                <?php if (!empty($search)): ?>"<?php echo htmlspecialchars($search); ?>" | <?php endif; ?>
                <?php if (!empty($filter_status) && $filter_status != 'all'): ?>Status: <?php echo ucfirst($filter_status); ?> | <?php endif; ?>
                <?php if (!empty($filter_lab) && $filter_lab != 'all'): ?>Lab: <?php echo $filter_lab; ?> | <?php endif; ?>
                <a href="admin_records.php" style="color: var(--primary-color);">Clear all</a>
            </p>
        <?php endif; ?>
    </div>
    
    <!-- 🔹 Records Table -->
    <div class="table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID Number</th>
                    <th>Student Name</th>
                    <th>Year Level</th>
                    <th>Purpose</th>
                    <th>Lab</th>
                    <th>Time In</th>
                    <th>Time Out</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($records) > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($records)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['id_number']); ?></td>
                        <td><?php echo htmlspecialchars($row['student_name']); ?></td>
                        <td>
                            <span class="badge" style="background: #4a148c; color: white; padding: 3px 10px; border-radius: 15px; font-size: 0.85rem;">
                                <?php 
                                    $year = $row['year_level'];
                                    if (empty($year)) {
                                        echo '<span style="color: #999;">N/A</span>';
                                    } else {
                                        $suffix = '';
                                        if ($year == 1) $suffix = 'st';
                                        elseif ($year == 2) $suffix = 'nd';
                                        elseif ($year == 3) $suffix = 'rd';
                                        else $suffix = 'th';
                                        echo $year . $suffix . ' Year';
                                    }
                                ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($row['purpose']); ?></td>
                        <td><?php echo htmlspecialchars($row['lab']); ?></td>
                        <td><?php echo date('Y-m-d H:i', strtotime($row['time_in'])); ?></td>
                        <td><?php echo $row['time_out'] ? date('Y-m-d H:i', strtotime($row['time_out'])) : '<span style="color: #999;">N/A</span>'; ?></td>
                        <td>
                            <span class="<?php echo $row['status'] == 'active' ? 'alert-success' : 'alert-secondary'; ?>" 
                                  style="padding: 4px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: 500;">
                                <?php echo ucfirst($row['status']); ?>
                            </span>
                        </td>
                        <td>
                            <!-- 🔹 Edit Button -->
                            <a href="edit_record.php?id=<?php echo $row['id']; ?>" 
                               class="action-btn btn-edit" 
                               title="Edit Record">
                                <i class="fas fa-edit"></i>
                            </a>
                            <!-- 🔹 Delete Button -->
                            <a href="admin_records.php?delete_record=<?php echo $row['id']; ?>" 
                               class="action-btn btn-delete" 
                               onclick="return confirm('Are you sure you want to delete this record?')"
                               title="Delete Record">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 40px; color: #666;">
                            <i class="fas fa-search" style="font-size: 3rem; margin-bottom: 15px; opacity: 0.3;"></i>
                            <p>No records found matching your search criteria.</p>
                            <a href="admin_records.php" class="btn" style="width: auto; margin-top: 10px; padding: 8px 20px;">
                                <i class="fas fa-undo"></i> Clear Filters
                            </a>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Info Box -->
    <div style="margin-top: 20px; padding: 15px; background: rgba(255,255,255,0.3); border-radius: 10px; font-size: 0.9rem;">
        <i class="fas fa-info-circle"></i> 
        <strong>Tip:</strong> Use the search bar to filter records by ID, name, purpose, or lab. Click "Add Student" to register new users. Click "Reset All Sessions" to restore all student session counts to 30.
    </div>
</div>

<!-- Custom Styles -->
<style>
.alert {
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 10px;
    font-weight: 500;
}
.alert-success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}
.alert-danger {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}
.alert-secondary {
    background-color: #e2e3e5;
    color: #383d41;
    border: 1px solid #d6d8db;
}
.badge {
    display: inline-block;
    font-weight: 600;
}
.action-btn {
    padding: 6px 12px;
    border-radius: 6px;
    border: none;
    cursor: pointer;
    margin-right: 5px;
    font-size: 0.85rem;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    transition: all 0.2s;
}
.action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 3px 10px rgba(0,0,0,0.2);
}
.btn-edit { 
    background: #2196F3; 
    color: #fff; 
}
.btn-delete { 
    background: #f44336; 
    color: #fff; 
}
.form-control {
    width: 100%;
    padding: 14px;
    background: rgba(255, 255, 255, 0.5);
    border: 1px solid var(--glass-border);
    border-radius: 10px;
    font-size: 1rem;
    transition: var(--transition);
}
.form-control:focus {
    outline: none;
    background: rgba(255, 255, 255, 0.8);
    border-color: var(--primary-color);
    box-shadow: 0 0 0 4px rgba(74, 20, 140, 0.1);
}
</style>

<?php include 'includes/footer.php'; ?>