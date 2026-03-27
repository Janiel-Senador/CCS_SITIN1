<?php
include 'db_connect.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 🔐 Admin Access Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// 🔹 Handle Sit-in End (MUST BE BEFORE header.php include!)
if (isset($_GET['end_sitin'])) {
    $rid = mysqli_real_escape_string($conn, $_GET['end_sitin']);
    
    // Get student_id before updating
    $record_sql = "SELECT student_id FROM sitin_records WHERE id = '$rid'";
    $record = mysqli_fetch_assoc(mysqli_query($conn, $record_sql));
    
    if ($record) {
        $sid = $record['student_id'];
        
        // Deduct one session
        mysqli_query($conn, "UPDATE users SET sessions_remaining = sessions_remaining - 1 WHERE id = '$sid'");
        
        // Close the session
        mysqli_query($conn, "UPDATE sitin_records SET status = 'completed', time_out = CURRENT_TIMESTAMP WHERE id = '$rid'");
    }
    
    // ✅ Redirect BEFORE any HTML output
    header("Location: admin_sitin_form.php");
    exit();
}

// ✅ Now include header (after all redirect logic)
include 'includes/header.php';

$message = "";
$student_id = "";
$student_name = "";
$sessions_remaining = "";

// Search Student for Sit-in
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['search_student'])) {
    $id_number = mysqli_real_escape_string($conn, $_POST['id_number']);
    $sql = "SELECT * FROM users WHERE id_number = '$id_number' AND role = 'student'";
    $result = mysqli_query($conn, $sql);
    if ($row = mysqli_fetch_assoc($result)) {
        $student_id = $row['id'];
        $student_name = $row['first_name'] . " " . $row['last_name'];
        $sessions_remaining = $row['sessions_remaining'];
    } else {
        $message = "<div class='alert alert-danger'>Student ID not found!</div>";
    }
}

// Handle Sit-in Start
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['start_sitin'])) {
    $sid = mysqli_real_escape_string($conn, $_POST['student_id']);
    $id_num = mysqli_real_escape_string($conn, $_POST['id_num']);
    $s_name = mysqli_real_escape_string($conn, $_POST['s_name']);
    $purpose = mysqli_real_escape_string($conn, $_POST['purpose']);
    $lab = mysqli_real_escape_string($conn, $_POST['lab']);
    
    // Check if student is already in a session
    $check_sql = "SELECT * FROM sitin_records WHERE student_id = '$sid' AND status = 'active'";
    if (mysqli_num_rows(mysqli_query($conn, $check_sql)) > 0) {
        $message = "<div class='alert alert-danger'>Student already has an active session!</div>";
    } else {
        $sql = "INSERT INTO sitin_records (student_id, id_number, student_name, purpose, lab)
                VALUES ('$sid', '$id_num', '$s_name', '$purpose', '$lab')";
        if (mysqli_query($conn, $sql)) {
            $message = "<div class='alert alert-success'>Sit-in session started for $s_name!</div>";
            $student_id = $student_name = $sessions_remaining = "";
        }
    }
}

// Fetch active sessions
$active_sessions = mysqli_query($conn, "SELECT * FROM sitin_records WHERE status = 'active'");
?>

<div class="dashboard-grid">
    <!-- Search and Start Sit-in Card -->
    <div class="info-card glass">
        <h3>Sit-in Entry Form</h3>
        <?php echo $message; ?>
        
        <form action="admin_sitin_form.php" method="POST" class="admin-form">
            <div class="form-group">
                <label for="id_number">ID Number:</label>
                <div style="display: flex; gap: 10px;">
                    <input type="text" name="id_number" id="id_number" class="form-control" placeholder="Enter ID Number..." required>
                    <button type="submit" name="search_student" class="btn" style="width: 100px;">Find</button>
                </div>
            </div>
        </form>
        
        <?php if ($student_id): ?>
        <form action="admin_sitin_form.php" method="POST" class="admin-form">
            <input type="hidden" name="student_id" value="<?php echo $student_id; ?>">
            <input type="hidden" name="id_num" value="<?php echo htmlspecialchars($_POST['id_number']); ?>">
            <input type="hidden" name="s_name" value="<?php echo htmlspecialchars($student_name); ?>">
            
            <div class="form-group">
                <label>Student Name:</label>
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($student_name); ?>" readonly>
            </div>
            
            <div class="form-group">
                <label>Remaining Session:</label>
                <input type="text" class="form-control" value="<?php echo $sessions_remaining; ?>" readonly>
            </div>
            
            <div class="form-row" style="display: flex; gap: 10px;">
                <div class="form-group" style="flex: 1;">
                    <label for="purpose">Purpose:</label>
                    <select name="purpose" id="purpose" class="form-control" required>
                        <option value="C">C</option>
                        <option value="C#">C#</option>
                        <option value="JAVA">JAVA</option>
                        <option value="PHP">PHP</option>
                        <option value="PYTHON">PYTHON</option>
                    </select>
                </div>
                <div class="form-group" style="flex: 1;">
                    <label for="lab">Lab:</label>
                    <select name="lab" id="lab" class="form-control" required>
                        <option value="524">524</option>
                        <option value="526">526</option>
                        <option value="528">528</option>
                    </select>
                </div>
            </div>
            
            <button type="submit" name="start_sitin" class="btn btn-sit-in">Start Sit-in Session</button>
        </form>
        <?php endif; ?>
    </div>
    
    <!-- Active Sessions Display -->
    <div class="announcement-card glass">
        <h3>Currently Sit-in</h3>
        <div class="table-container" style="max-height: 500px; overflow-y: auto;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID Number</th>
                        <th>Student Name</th>
                        <th>Lab</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($active_sessions)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['id_number']); ?></td>
                        <td><?php echo htmlspecialchars($row['student_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['lab']); ?></td>
                        <td>
                            <a href="admin_sitin_form.php?end_sitin=<?php echo $row['id']; ?>" 
                               class="action-btn btn-delete"
                               onclick="return confirm('End this sit-in session?')">
                                <i class="fas fa-sign-out-alt"></i> End
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.alert {
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 5px;
    font-weight: 500;
}
.alert-danger {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}
.alert-success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}
</style>

<?php include 'includes/footer.php'; ?>