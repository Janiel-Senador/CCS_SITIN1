<?php
include 'includes/header.php';
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

// Fetch current user data
$sql = "SELECT * FROM users WHERE id = '$user_id'";
$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($result);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
    $middle_name = mysqli_real_escape_string($conn, $_POST['middle_name']);
    $course = mysqli_real_escape_string($conn, $_POST['course']);
    $course_level = mysqli_real_escape_string($conn, $_POST['course_level']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    
    // Handle Profile Picture Upload
    $profile_pic = $user['profile_picture'];
    if (!empty($_FILES['profile_pic']['name'])) {
        $target_dir = "assets/images/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_extension = pathinfo($_FILES["profile_pic"]["name"], PATHINFO_EXTENSION);
        $new_filename = "profile_" . $user_id . "_" . time() . "." . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        if (move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $target_file)) {
            $profile_pic = $new_filename;
        } else {
            $upload_error = $_FILES['profile_pic']['error'];
            $message = "<div class='alert alert-danger'>Sorry, there was an error uploading your file. Error code: $upload_error</div>";
        }
    }

    $update_sql = "UPDATE users SET 
                   first_name = '$first_name', 
                   last_name = '$last_name', 
                   middle_name = '$middle_name', 
                   course = '$course', 
                   course_level = '$course_level', 
                   email = '$email', 
                   address = '$address', 
                   profile_picture = '$profile_pic' 
                   WHERE id = '$user_id'";
    
    if (mysqli_query($conn, $update_sql)) {
        $message = "<div class='alert alert-success'>Profile updated successfully!</div>";
        // Refresh user data
        $sql = "SELECT * FROM users WHERE id = '$user_id'";
        $result = mysqli_query($conn, $sql);
        $user = mysqli_fetch_assoc($result);
    } else {
        $message = "<div class='alert alert-danger'>Error updating profile: " . mysqli_error($conn) . "</div>";
    }
}
?>

<div class="form-container glass">
    <h2 style="text-align: center; color: var(--primary-color); margin-bottom: 30px;">Edit Profile</h2>
    <?php echo $message; ?>
    
    <form action="edit_profile.php" method="POST" enctype="multipart/form-data">
        <!-- Profile Picture Section -->
        <div style="text-align: center; margin-bottom: 30px;">
            <div class="profile-pic-container">
                <img src="assets/images/<?php echo !empty($user['profile_picture']) ? $user['profile_picture'] : 'default_profile.png'; ?>" alt="Profile Picture" class="profile-pic" id="preview-pic" onerror="this.src='https://via.placeholder.com/150?text=Profile'">
            </div>
            <div style="margin-top: 15px;">
                <label for="profile_pic" class="btn" style="width: auto; padding: 10px 20px; font-size: 0.9rem; cursor: pointer;">
                    <i class="fas fa-upload"></i> Choose Photo
                </label>
                <input type="file" name="profile_pic" id="profile_pic" style="display: none;" onchange="previewImage(this)">
                <p style="font-size: 0.8rem; color: #666; margin-top: 5px;">Supported: JPG, PNG, GIF</p>
            </div>
        </div>

        <div class="form-row" style="display: flex; gap: 15px;">
            <div class="form-group" style="flex: 1;">
                <label for="first_name">First Name</label>
                <input type="text" name="first_name" id="first_name" class="form-control" value="<?php echo $user['first_name']; ?>" required>
            </div>
            <div class="form-group" style="flex: 1;">
                <label for="last_name">Last Name</label>
                <input type="text" name="last_name" id="last_name" class="form-control" value="<?php echo $user['last_name']; ?>" required>
            </div>
        </div>

        <div class="form-group">
            <label for="middle_name">Middle Name</label>
            <input type="text" name="middle_name" id="middle_name" class="form-control" value="<?php echo $user['middle_name']; ?>">
        </div>

        <div class="form-row" style="display: flex; gap: 15px;">
            <div class="form-group" style="flex: 1;">
                <label for="course">Course</label>
                <select name="course" id="course" class="form-control" required>
                    <option value="BSCS" <?php echo $user['course'] == 'BSCS' ? 'selected' : ''; ?>>BSCS</option>
                    <option value="BSIT" <?php echo $user['course'] == 'BSIT' ? 'selected' : ''; ?>>BSIT</option>
                    <option value="BSIS" <?php echo $user['course'] == 'BSIS' ? 'selected' : ''; ?>>BSIS</option>
                </select>
            </div>
            <div class="form-group" style="flex: 1;">
                <label for="course_level">Year Level</label>
                <select name="course_level" id="course_level" class="form-control" required>
                    <option value="1" <?php echo $user['course_level'] == '1' ? 'selected' : ''; ?>>1st Year</option>
                    <option value="2" <?php echo $user['course_level'] == '2' ? 'selected' : ''; ?>>2nd Year</option>
                    <option value="3" <?php echo $user['course_level'] == '3' ? 'selected' : ''; ?>>3rd Year</option>
                    <option value="4" <?php echo $user['course_level'] == '4' ? 'selected' : ''; ?>>4th Year</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" class="form-control" value="<?php echo $user['email']; ?>" required>
        </div>

        <div class="form-group">
            <label for="address">Address</label>
            <textarea name="address" id="address" class="form-control" rows="3" required><?php echo $user['address']; ?></textarea>
        </div>

        <button type="submit" class="btn">Update Profile</button>
    </form>
</div>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview-pic').src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php include 'includes/footer.php'; ?>
