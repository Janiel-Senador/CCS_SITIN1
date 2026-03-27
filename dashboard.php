<?php
include 'includes/header.php';
include 'db_connect.php';

// User data fetching (existing code)
$user_name = "Jan v Senador";
$course = "BSIT";
$year = "2";
$email = "jan@gmail.com";
$address = "cebu";
$sessions = 30;
$profile_pic = "default_profile.png";

if (isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    $sql = "SELECT * FROM users WHERE id = '$uid'";
    $result = mysqli_query($conn, $sql);
    if ($row = mysqli_fetch_assoc($result)) {
        $user_name = $row['first_name'] . " " . (isset($row['middle_name']) ? $row['middle_name'] . " " : "") . $row['last_name'];
        $course = $row['course'];
        $year = $row['course_level'];
        $email = $row['email'];
        $address = $row['address'];
        $sessions = $row['sessions_remaining'];
        $profile_pic = !empty($row['profile_picture']) ? $row['profile_picture'] : "default_profile.png";
    }
}

// 🔥 NEW: Fetch announcements from database
$announcements_sql = "SELECT * FROM announcements ORDER BY created_at DESC LIMIT 10";
$announcements_result = mysqli_query($conn, $announcements_sql);
?>

<div class="dashboard-grid">
    <!-- Student Info Card -->
    <div class="info-card glass">
        <h3>Student Information</h3>
        <div class="profile-pic-container">
            <img src="assets/images/<?php echo $profile_pic; ?>" alt="Profile Picture" class="profile-pic" onerror="this.src='https://via.placeholder.com/150?text=Profile'">
        </div>
        <div class="info-item">
            <span class="info-label">Name:</span> <?php echo htmlspecialchars($user_name); ?>
        </div>
        <div class="info-item">
            <span class="info-label">Course:</span> <?php echo htmlspecialchars($course); ?>
        </div>
        <div class="info-item">
            <span class="info-label">Year:</span> <?php echo htmlspecialchars($year); ?>
        </div>
        <div class="info-item">
            <span class="info-label">Email:</span> <?php echo htmlspecialchars($email); ?>
        </div>
        <div class="info-item">
            <span class="info-label">Address:</span> <?php echo htmlspecialchars($address); ?>
        </div>
        <div class="info-item">
            <span class="info-label">Session:</span> <?php echo $sessions; ?>
        </div>
    </div>

    <!-- Announcement Card -->
    <div class="announcement-card glass">
        <h3>Announcements</h3>
        <?php 
        if (mysqli_num_rows($announcements_result) > 0):
            while($announcement = mysqli_fetch_assoc($announcements_result)): 
        ?>
            <div class="announcement-item">
                <div class="announcement-header">CCS Admin | <?php echo date('Y-M-d', strtotime($announcement['created_at'])); ?></div>
                <p><strong><?php echo htmlspecialchars($announcement['title']); ?></strong></p>
                <p><?php echo nl2br(htmlspecialchars($announcement['content'])); ?></p>
            </div>
        <?php 
            endwhile;
        else:
        ?>
            <div class="announcement-item">
                <p style="text-align: center; color: #666;">No announcements at this time.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Rules and Regulations Card (existing code) -->
    <div class="rules-card glass">
        <h3>Rules and Regulations</h3>
        <div class="rules-content">
            <p style="text-align: center; font-weight: bold;">University of Cebu<br>COLLEGE OF INFORMATION & COMPUTER STUDIES<br>LABORATORY RULES AND REGULATIONS</p>
            <p>To avoid embarrassment and maintain camaraderie with your friends and superiors at our laboratories, please observe the following:</p>
            <ol>
                <li>Maintain silence, proper decorum, and discipline inside the laboratory. Mobile phones, walkmans and other personal pieces of equipment must be switched off.</li>
                <li>Games are not allowed inside the lab. This includes computer-related games, card games and other games that may disturb the operation of the lab.</li>
                <li>Surfing the Internet is allowed only with the permission of the instructor. Downloading and installing of software are strictly prohibited.</li>
                <li>Getting access to other websites not related to the course (especially pornographic and illicit sites) is strictly prohibited.</li>
                <li>Deleting computer files and changing the set-up of the computer is a major offense.</li>
                <li>Observe computer time usage carefully. A fifteen-minute allowance is given for each use. Otherwise, the unit will be given to those who wish to "sit-in".</li>
                <li>Observe proper decorum while inside the laboratory.
                    <ul>
                        <li>Do not get inside the lab unless the instructor is present.</li>
                        <li>All bags, knapsacks, and the likes must be deposited at the counter.</li>
                        <li>Follow the seating arrangement of your instructor.</li>
                        <li>At the end of class, all software programs must be closed.</li>
                        <li>Return all chairs to their proper places after using.</li>
                    </ul>
                </li>
                <li>Chewing gum, eating, drinking, smoking, and other forms of vandalism are prohibited inside the lab.</li>
                <li>Anyone causing a continual disturbance will be asked to leave the lab. Acts or gestures offensive to the members of the community, including public display of physical intimacy, are not tolerated.</li>
                <li>Persons exhibiting hostile or threatening behavior such as yelling, swearing, or disregarding requests made by lab personnel will be asked to leave the lab.</li>
                <li>For serious offense, the lab personnel may call the Civil Security Office (CSU) for assistance.</li>
                <li>Any technical problem or difficulty must be addressed to the laboratory supervisor, student assistant or instructor immediately.</li>
            </ol>
            <p style="font-weight: bold; margin-top: 20px;">DISCIPLINARY ACTION</p>
            <p>First Offense - The Head or the Dean or OIC recommends to the Guidance Center for a suspension from classes for each offender.</p>
            <p>Second and Subsequent Offenses - A recommendation for a heavier sanction will be endorsed to the Guidance Center.</p>
        </div>
    </div>
</div>

<div class="personalization-badge">
    <i class="fas fa-user-circle"></i> Welcome, <?php echo htmlspecialchars(explode(' ', $user_name)[0]); ?>!
</div>

<?php include 'includes/footer.php'; ?>