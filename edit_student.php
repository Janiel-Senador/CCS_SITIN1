<?php
include 'includes/header.php';
include 'db_connect.php';

// 🔐 Admin Access Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// 🔐 Validate Student ID Parameter
if (!isset($_GET['id']) || empty($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: admin_students.php");
    exit();
}

$student_id = mysqli_real_escape_string($conn, $_GET['id']);
$message = "";

// 🔍 Fetch Current Student Data
$sql = "SELECT * FROM users WHERE id = '$student_id' AND role = 'student'";
$result = mysqli_query($conn, $sql);
$student = mysqli_fetch_assoc($result);

if (!$student) {
    $message = "<div class='alert alert-danger'>Student not found!</div>";
}

// 💾 Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && $student) {
    $id_number = mysqli_real_escape_string($conn, $_POST['id_number']);
    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
    $middle_name = mysqli_real_escape_string($conn, $_POST['middle_name']);
    $course = mysqli_real_escape_string($conn, $_POST['course']);
    $course_level = mysqli_real_escape_string($conn, $_POST['course_level']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $sessions_remaining = mysqli_real_escape_string($conn, $_POST['sessions_remaining']);
    
    // 🔐 Check if ID Number is being changed and if it already exists
    if ($id_number !== $student['id_number']) {
        $check_sql = "SELECT id FROM users WHERE id_number = '$id_number' AND id != '$student_id'";
        $check_result = mysqli_query($conn, $check_sql);
        if (mysqli_num_rows($check_result) > 0) {
            $message = "<div class='alert alert-danger'>ID Number already exists! Please use a unique ID.</div>";
        } else {
            // ✅ Update with new ID number
            $update_sql = "UPDATE users SET 
                id_number = '$id_number',
                first_name = '$first_name',
                last_name = '$last_name',
                middle_name = '$middle_name',
                course = '$course',
                course_level = '$course_level',
                email = '$email',
                address = '$address',
                sessions_remaining = '$sessions_remaining'
                WHERE id = '$student_id'";
            
            if (mysqli_query($conn, $update_sql)) {
                $message = "<div class='alert alert-success'>Student updated successfully!</div>";
                // Refresh student data
                $student = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id = '$student_id'"));
            } else {
                $message = "<div class='alert alert-danger'>Error updating student: " . mysqli_error($conn) . "</div>";
            }
        }
    } else {
        // ✅ Update without changing ID number
        $update_sql = "UPDATE users SET 
            first_name = '$first_name',
            last_name = '$last_name',
            middle_name = '$middle_name',
            course = '$course',
            course_level = '$course_level',
            email = '$email',
            address = '$address',
            sessions_remaining = '$sessions_remaining'
            WHERE id = '$student_id'";
        
        if (mysqli_query($conn, $update_sql)) {
            $message = "<div class='alert alert-success'>Student updated successfully!</div>";
            // Refresh student data
            $student = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id = '$student_id'"));
        } else {
            $message = "<div class='alert alert-danger'>Error updating student: " . mysqli_error($conn) . "</div>";
        }
    }
}
?>

<div class="form-container glass" style="max-width: 700px; margin: 40px auto;">
    <h2 style="text-align: center; color: var(--primary-color); margin-bottom: 30px;">
        <i class="fas fa-user-edit"></i> Edit Student Profile
    </h2>
    
    <?php echo $message; ?>
    
    <?php if ($student): ?>
    <form action="edit_student.php?id=<?php echo $student['id']; ?>" method="POST">
        
        <!-- ID Number (Unique Field) -->
        <div class="form-group">
            <label for="id_number">ID Number *</label>
            <input type="text" name="id_number" id="id_number" class="form-control" 
                   value="<?php echo htmlspecialchars($student['id_number']); ?>" required>
            <small style="color: #666; display: block; margin-top: 5px;">
                ⚠️ Changing ID Number must be unique across all students
            </small>
        </div>
        
        <!-- First & Last Name -->
        <div class="form-row" style="display: flex; gap: 15px;">
            <div class="form-group" style="flex: 1;">
                <label for="first_name">First Name *</label>
                <input type="text" name="first_name" id="first_name" class="form-control" 
                       value="<?php echo htmlspecialchars($student['first_name']); ?>" required>
            </div>
            <div class="form-group" style="flex: 1;">
                <label for="last_name">Last Name *</label>
                <input type="text" name="last_name" id="last_name" class="form-control" 
                       value="<?php echo htmlspecialchars($student['last_name']); ?>" required>
            </div>
        </div>
        
        <!-- Middle Name -->
        <div class="form-group">
            <label for="middle_name">Middle Name</label>
            <input type="text" name="middle_name" id="middle_name" class="form-control" 
                   value="<?php echo htmlspecialchars($student['middle_name']); ?>">
        </div>
        
        <!-- Course & Year Level -->
        <div class="form-row" style="display: flex; gap: 15px;">
            <div class="form-group" style="flex: 1;">
                <label for="course">Course *</label>
                <select name="course" id="course" class="form-control" required>
                    <option value="">Select Course</option>
                    <option value="BSCS" <?php echo $student['course'] == 'BSCS' ? 'selected' : ''; ?>>BSCS</option>
                    <option value="BSIT" <?php echo $student['course'] == 'BSIT' ? 'selected' : ''; ?>>BSIT</option>
                    <option value="BSIS" <?php echo $student['course'] == 'BSIS' ? 'selected' : ''; ?>>BSIS</option>
                </select>
            </div>
            <div class="form-group" style="flex: 1;">
                <label for="course_level">Year Level *</label>
                <select name="course_level" id="course_level" class="form-control" required>
                    <option value="">Select Year</option>
                    <option value="1" <?php echo $student['course_level'] == '1' ? 'selected' : ''; ?>>1st Year</option>
                    <option value="2" <?php echo $student['course_level'] == '2' ? 'selected' : ''; ?>>2nd Year</option>
                    <option value="3" <?php echo $student['course_level'] == '3' ? 'selected' : ''; ?>>3rd Year</option>
                    <option value="4" <?php echo $student['course_level'] == '4' ? 'selected' : ''; ?>>4th Year</option>
                </select>
            </div>
        </div>
        
        <!-- Email -->
        <div class="form-group">
            <label for="email">Email Address *</label>
            <input type="email" name="email" id="email" class="form-control" 
                   value="<?php echo htmlspecialchars($student['email']); ?>" required>
        </div>
        
        <!-- Address -->
        <div class="form-group">
            <label for="address">Address *</label>
            <textarea name="address" id="address" class="form-control" rows="3" required><?php echo htmlspecialchars($student['address']); ?></textarea>
        </div>
        
        <!-- Sessions Remaining -->
        <div class="form-group">
            <label for="sessions_remaining">Sessions Remaining *</label>
            <input type="number" name="sessions_remaining" id="sessions_remaining" class="form-control" 
                   value="<?php echo htmlspecialchars($student['sessions_remaining']); ?>" min="0" max="100" required>
            <small style="color: #666; display: block; margin-top: 5px;">
                Default: 30 sessions per semester
            </small>
        </div>
        
        <!-- Action Buttons -->
        <div style="display: flex; gap: 10px; margin-top: 30px;">
            <button type="submit" class="btn" style="flex: 1;">
                <i class="fas fa-save"></i> Save Changes
            </button>
            <a href="admin_students.php" class="btn" style="flex: 1; background: #6c757d;">
                <i class="fas fa-times"></i> Cancel
            </a>
        </div>
    </form>
    <?php endif; ?>
</div>

<!-- Custom Alert Styles -->
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
</style>

<?php include 'includes/footer.php'; ?>