<?php 
include 'db_connect.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_number = $_POST['id_number'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE id_number = '$id_number'";
    $result = mysqli_query($conn, $sql);

    if ($row = mysqli_fetch_assoc($result)) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['first_name'] = $row['first_name'];
            $_SESSION['role'] = $row['role']; // Store role in session
            
            if ($row['role'] == 'admin') {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: dashboard.php");
            }
            exit();
        } else {
            $message = "<div class='alert alert-danger'>Invalid password!</div>";
        }
    } else {
        $message = "<div class='alert alert-danger'>ID Number not found!</div>";
    }
}

include 'includes/header.php'; 
?>

<div class="form-container glass" style="max-width: 450px;">
    <h2 style="text-align: center; color: var(--primary-color); margin-bottom: 20px;">Student Login</h2>
    <?php echo $message; ?>
    <form action="login.php" method="POST">
        <div class="form-group">
            <label for="id_number">ID Number</label>
            <input type="text" name="id_number" id="id_number" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" name="password" id="password" class="form-control" required>
        </div>

        <div class="form-group" style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <input type="checkbox" name="remember" id="remember">
                <label for="remember" style="display: inline; font-weight: normal;">Remember Me</label>
            </div>
            <a href="#" style="color: var(--primary-color); text-decoration: none; font-size: 0.9rem;">Forgot Password?</a>
        </div>

        <button type="submit" class="btn">Login</button>
        
        <p style="text-align: center; margin-top: 15px;">
            Don't have an account? <a href="register.php" style="color: var(--primary-color); text-decoration: none; font-weight: 600;">Register</a>
        </p>
    </form>
</div>

<style>
.alert {
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 5px;
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
