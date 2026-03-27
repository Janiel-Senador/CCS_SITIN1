<?php 
include 'includes/header.php'; 
include 'db_connect.php';

$user_id_num = "123456";
$user_name = "Jan v Senador";
$sessions = 30;

if (isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    $sql = "SELECT * FROM users WHERE id = '$uid'";
    $result = mysqli_query($conn, $sql);
    if ($row = mysqli_fetch_assoc($result)) {
        $user_id_num = $row['id_number'];
        $user_name = $row['first_name'] . " " . (isset($row['middle_name']) ? $row['middle_name'] . " " : "") . $row['last_name'];
        $sessions = $row['sessions_remaining'];
    }
}
?>

<div class="reservation-container glass">
    <h2 style="text-align: center; color: var(--primary-color); margin-bottom: 30px;">Make a Reservation</h2>
    
    <form action="reservation.php" method="POST">
        <div class="form-row" style="display: flex; gap: 20px; margin-bottom: 20px;">
            <div class="form-group" style="flex: 1;">
                <label for="id_number">ID Number:</label>
                <input type="text" id="id_number" value="<?php echo $user_id_num; ?>" class="form-control" readonly>
            </div>
            <div class="form-group" style="flex: 1;">
                <label for="student_name">Student Name:</label>
                <input type="text" id="student_name" value="<?php echo $user_name; ?>" class="form-control" readonly>
            </div>
        </div>

        <div class="form-row" style="display: flex; gap: 20px; margin-bottom: 20px;">
            <div class="form-group" style="flex: 1;">
                <label for="purpose">Purpose:</label>
                <select name="purpose" id="purpose" class="form-control" required>
                    <option value="">Select Purpose</option>
                    <option value="C Programming">C Programming</option>
                    <option value="Java Programming">Java Programming</option>
                    <option value="Web Development">Web Development</option>
                    <option value="Networking">Networking</option>
                    <option value="Database Management">Database Management</option>
                    <option value="Others">Others</option>
                </select>
            </div>
            <div class="form-group" style="flex: 1;">
                <label for="lab">Lab:</label>
                <select name="lab" id="lab" class="form-control" required>
                    <option value="">Select Lab</option>
                    <option value="524">524</option>
                    <option value="526">526</option>
                    <option value="528">528</option>
                    <option value="530">530</option>
                </select>
            </div>
        </div>

        <div class="form-row" style="display: flex; gap: 20px; margin-bottom: 20px;">
            <div class="form-group" style="flex: 1;">
                <label for="time_in">Time In:</label>
                <input type="time" name="time_in" id="time_in" class="form-control" required>
            </div>
            <div class="form-group" style="flex: 1;">
                <label for="date">Date:</label>
                <input type="date" name="date" id="date" class="form-control" required>
            </div>
        </div>

        <div class="form-group">
            <label for="remaining_sessions">Remaining Session:</label>
            <input type="text" id="remaining_sessions" value="<?php echo $sessions; ?>" class="form-control" readonly>
        </div>

        <button type="submit" class="btn">Confirm Reservation</button>
    </form>
</div>

<div class="personalization-badge">
    <i class="fas fa-calendar-check"></i> Reservation Assistant Active
</div>

<?php include 'includes/footer.php'; ?>
