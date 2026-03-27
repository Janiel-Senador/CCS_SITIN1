<?php
include 'includes/header.php';
include 'db_connect.php';
$message = "";
$step = isset($_POST['step']) ? (int)$_POST['step'] : 1;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['step1'])) {
        $_SESSION['reg_data'] = [
            'id_number' => $_POST['id_number'],
            'last_name' => $_POST['last_name'],
            'first_name' => $_POST['first_name'],
            'middle_name' => $_POST['middle_name']
        ];
        $step = 2;
    } elseif (isset($_POST['step2'])) {
        $_SESSION['reg_data']['course'] = $_POST['course'];
        $_SESSION['reg_data']['course_level'] = $_POST['course_level'];
        $_SESSION['reg_data']['email'] = $_POST['email'];
        $step = 3;
    } elseif (isset($_POST['step3'])) {
        $_SESSION['reg_data']['address'] = $_POST['address'];
        $_SESSION['reg_data']['password'] = $_POST['password'];
        $step = 4;
    } elseif (isset($_POST['submit'])) {
        $data = $_SESSION['reg_data'];
        $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (id_number, last_name, first_name, middle_name, course, course_level, email, address, password)
                VALUES ('{$data['id_number']}', '{$data['last_name']}', '{$data['first_name']}', '{$data['middle_name']}', 
                        '{$data['course']}', '{$data['course_level']}', '{$data['email']}', '{$data['address']}', '$hashed_password')";
        
        if (mysqli_query($conn, $sql)) {
            unset($_SESSION['reg_data']);
            $message = "<div class='alert alert-success'>Registration successful! <a href='login.php'>Login here</a></div>";
            $step = 1;
        } else {
            $message = "<div class='alert alert-danger'>Error: " . mysqli_error($conn) . "</div>";
        }
    }
}

$reg_data = isset($_SESSION['reg_data']) ? $_SESSION['reg_data'] : [];
?>

<div class="form-container glass">
    <h2 style="text-align: center; color: var(--primary-color); margin-bottom: 30px;">Student Registration</h2>
    <?php echo $message; ?>
    
    <!-- Progress Indicator -->
    <div class="progress-steps" style="display: flex; justify-content: space-between; margin-bottom: 30px; position: relative;">
        <div class="progress-line" style="position: absolute; top: 20px; left: 0; right: 0; height: 3px; background: #ddd; z-index: 0;"></div>
        <?php for($i = 1; $i <= 4; $i++): ?>
            <div class="step <?php echo $i <= $step ? 'active' : ''; ?>" 
                 style="position: relative; z-index: 1; width: 40px; height: 40px; border-radius: 50%; 
                        background: <?php echo $i <= $step ? 'var(--primary-color)' : '#ddd'; ?>; 
                        color: <?php echo $i <= $step ? '#fff' : '#999'; ?>; 
                        display: flex; align-items: center; justify-content: center; 
                        font-weight: 600; transition: all 0.3s;">
                <?php echo $i; ?>
            </div>
        <?php endfor; ?>
    </div>
    
    <form action="register.php" method="POST">
        <?php if ($step == 1): ?>
            <!-- Step 1: Personal Information -->
            <h3 style="margin-bottom: 20px;">Step 1: Personal Information</h3>
            <div class="form-group">
                <label for="id_number">ID Number *</label>
                <input type="text" name="id_number" id="id_number" class="form-control" 
                       value="<?php echo htmlspecialchars($reg_data['id_number'] ?? ''); ?>" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="last_name">Last Name *</label>
                    <input type="text" name="last_name" id="last_name" class="form-control" 
                           value="<?php echo htmlspecialchars($reg_data['last_name'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="first_name">First Name *</label>
                    <input type="text" name="first_name" id="first_name" class="form-control" 
                           value="<?php echo htmlspecialchars($reg_data['first_name'] ?? ''); ?>" required>
                </div>
            </div>
            <div class="form-group">
                <label for="middle_name">Middle Name</label>
                <input type="text" name="middle_name" id="middle_name" class="form-control" 
                       value="<?php echo htmlspecialchars($reg_data['middle_name'] ?? ''); ?>">
            </div>
            <button type="submit" name="step1" class="btn btn-primary">Next: Academic Info →</button>
            
        <?php elseif ($step == 2): ?>
            <!-- Step 2: Academic Information -->
            <h3 style="margin-bottom: 20px;">Step 2: Academic Information</h3>
            <div class="form-row">
                <div class="form-group">
                    <label for="course">Course *</label>
                    <select name="course" id="course" class="form-control" required>
                        <option value="">Select Course</option>
                        <option value="BSCS" <?php echo ($reg_data['course'] ?? '') == 'BSCS' ? 'selected' : ''; ?>>BSCS</option>
                        <option value="BSIT" <?php echo ($reg_data['course'] ?? '') == 'BSIT' ? 'selected' : ''; ?>>BSIT</option>
                        <option value="BSIS" <?php echo ($reg_data['course'] ?? '') == 'BSIS' ? 'selected' : ''; ?>>BSIS</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="course_level">Year Level *</label>
                    <select name="course_level" id="course_level" class="form-control" required>
                        <option value="">Select Level</option>
                        <option value="1" <?php echo ($reg_data['course_level'] ?? '') == '1' ? 'selected' : ''; ?>>1st Year</option>
                        <option value="2" <?php echo ($reg_data['course_level'] ?? '') == '2' ? 'selected' : ''; ?>>2nd Year</option>
                        <option value="3" <?php echo ($reg_data['course_level'] ?? '') == '3' ? 'selected' : ''; ?>>3rd Year</option>
                        <option value="4" <?php echo ($reg_data['course_level'] ?? '') == '4' ? 'selected' : ''; ?>>4th Year</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label for="email">Email Address *</label>
                <input type="email" name="email" id="email" class="form-control" 
                       value="<?php echo htmlspecialchars($reg_data['email'] ?? ''); ?>" required>
            </div>
            <input type="hidden" name="id_number" value="<?php echo htmlspecialchars($reg_data['id_number']); ?>">
            <input type="hidden" name="last_name" value="<?php echo htmlspecialchars($reg_data['last_name']); ?>">
            <input type="hidden" name="first_name" value="<?php echo htmlspecialchars($reg_data['first_name']); ?>">
            <input type="hidden" name="middle_name" value="<?php echo htmlspecialchars($reg_data['middle_name']); ?>">
            <div style="display: flex; gap: 10px;">
                <button type="button" onclick="history.back()" class="btn btn-secondary">← Back</button>
                <button type="submit" name="step2" class="btn btn-primary">Next: Contact Info →</button>
            </div>
            
        <?php elseif ($step == 3): ?>
            <!-- Step 3: Contact & Password -->
            <h3 style="margin-bottom: 20px;">Step 3: Contact & Security</h3>
            <div class="form-group">
                <label for="address">Address *</label>
                <textarea name="address" id="address" class="form-control" rows="3" required><?php echo htmlspecialchars($reg_data['address'] ?? ''); ?></textarea>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="password">Password *</label>
                    <input type="password" name="password" id="password" class="form-control" required>
                    <small style="color: #666; font-size: 0.85rem;">Minimum 8 characters</small>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password *</label>
                    <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
                </div>
            </div>
            <input type="hidden" name="id_number" value="<?php echo htmlspecialchars($reg_data['id_number']); ?>">
            <input type="hidden" name="last_name" value="<?php echo htmlspecialchars($reg_data['last_name']); ?>">
            <input type="hidden" name="first_name" value="<?php echo htmlspecialchars($reg_data['first_name']); ?>">
            <input type="hidden" name="middle_name" value="<?php echo htmlspecialchars($reg_data['middle_name']); ?>">
            <input type="hidden" name="course" value="<?php echo htmlspecialchars($reg_data['course']); ?>">
            <input type="hidden" name="course_level" value="<?php echo htmlspecialchars($reg_data['course_level']); ?>">
            <input type="hidden" name="email" value="<?php echo htmlspecialchars($reg_data['email']); ?>">
            <div style="display: flex; gap: 10px;">
                <button type="button" onclick="history.back()" class="btn btn-secondary">← Back</button>
                <button type="submit" name="step3" class="btn btn-primary">Review →</button>
            </div>
            
        <?php elseif ($step == 4): ?>
            <!-- Step 4: Review -->
            <h3 style="margin-bottom: 20px;">Step 4: Review Information</h3>
            <div class="review-section" style="background: rgba(255,255,255,0.5); padding: 20px; border-radius: 10px; margin-bottom: 20px;">
                <h4 style="color: var(--primary-color); margin-bottom: 10px;">Personal Information</h4>
                <p><strong>ID:</strong> <?php echo htmlspecialchars($reg_data['id_number']); ?></p>
                <p><strong>Name:</strong> <?php echo htmlspecialchars($reg_data['first_name'] . ' ' . $reg_data['last_name']); ?></p>
                
                <h4 style="color: var(--primary-color); margin: 20px 0 10px;">Academic Information</h4>
                <p><strong>Course:</strong> <?php echo htmlspecialchars($reg_data['course']); ?> - Year <?php echo htmlspecialchars($reg_data['course_level']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($reg_data['email']); ?></p>
                
                <h4 style="color: var(--primary-color); margin: 20px 0 10px;">Contact</h4>
                <p><strong>Address:</strong> <?php echo htmlspecialchars($reg_data['address']); ?></p>
            </div>
            <input type="hidden" name="id_number" value="<?php echo htmlspecialchars($reg_data['id_number']); ?>">
            <input type="hidden" name="last_name" value="<?php echo htmlspecialchars($reg_data['last_name']); ?>">
            <input type="hidden" name="first_name" value="<?php echo htmlspecialchars($reg_data['first_name']); ?>">
            <input type="hidden" name="middle_name" value="<?php echo htmlspecialchars($reg_data['middle_name']); ?>">
            <input type="hidden" name="course" value="<?php echo htmlspecialchars($reg_data['course']); ?>">
            <input type="hidden" name="course_level" value="<?php echo htmlspecialchars($reg_data['course_level']); ?>">
            <input type="hidden" name="email" value="<?php echo htmlspecialchars($reg_data['email']); ?>">
            <input type="hidden" name="address" value="<?php echo htmlspecialchars($reg_data['address']); ?>">
            <input type="hidden" name="password" value="<?php echo htmlspecialchars($reg_data['password']); ?>">
            <div style="display: flex; gap: 10px;">
                <button type="button" onclick="history.back()" class="btn btn-secondary">← Back</button>
                <button type="submit" name="submit" class="btn btn-primary">✓ Complete Registration</button>
            </div>
        <?php endif; ?>
    </form>
</div>