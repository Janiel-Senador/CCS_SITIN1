<?php
include 'includes/header.php';
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// 🔹 Fetch Statistics
$total_registered_sql = "SELECT COUNT(*) as total FROM users WHERE role = 'student'";
$total_registered_res = mysqli_fetch_assoc(mysqli_query($conn, $total_registered_sql));

$currently_sitin_sql = "SELECT COUNT(*) as total FROM sitin_records WHERE status = 'active'";
$currently_sitin_res = mysqli_fetch_assoc(mysqli_query($conn, $currently_sitin_sql));

$total_sitin_sql = "SELECT COUNT(*) as total FROM sitin_records";
$total_sitin_res = mysqli_fetch_assoc(mysqli_query($conn, $total_sitin_sql));

// 🔹 Fetch Graph Data: Sit-in Trends (Last 7 Days)
$trend_sql = "SELECT DATE(time_in) as date, COUNT(*) as count 
              FROM sitin_records 
              WHERE time_in >= DATE_SUB(NOW(), INTERVAL 7 DAY)
              GROUP BY DATE(time_in) 
              ORDER BY date ASC";
$trend_result = mysqli_query($conn, $trend_sql);
$trend_labels = [];
$trend_data = [];
while($row = mysqli_fetch_assoc($trend_result)) {
    $trend_labels[] = date('M d', strtotime($row['date']));
    $trend_data[] = (int)$row['count'];
}

// 🔹 Fetch Graph Data: Lab Usage Distribution
$lab_sql = "SELECT lab, COUNT(*) as count FROM sitin_records GROUP BY lab ORDER BY count DESC";
$lab_result = mysqli_query($conn, $lab_sql);
$lab_labels = [];
$lab_data = [];
while($row = mysqli_fetch_assoc($lab_result)) {
    $lab_labels[] = "Lab " . $row['lab'];
    $lab_data[] = (int)$row['count'];
}

// 🔹 Fetch Graph Data: Hourly Activity Pattern
$hourly_sql = "SELECT HOUR(time_in) as hour, COUNT(*) as count 
               FROM sitin_records 
               GROUP BY HOUR(time_in) 
               ORDER BY hour ASC";
$hourly_result = mysqli_query($conn, $hourly_sql);
$hourly_labels = [];
$hourly_data = [];
while($row = mysqli_fetch_assoc($hourly_result)) {
    $hourly_labels[] = sprintf("%02d:00", $row['hour']);
    $hourly_data[] = (int)$row['count'];
}

// 🔹 Handle New Announcement
$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['post_announcement'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $content = mysqli_real_escape_string($conn, $_POST['content']);
    $sql = "INSERT INTO announcements (title, content) VALUES ('$title', '$content')";
    if (mysqli_query($conn, $sql)) {
        $message = "<div class='alert alert-success'>Announcement posted successfully!</div>";
    }
}

// 🔹 Fetch Announcements
$announcements_sql = "SELECT * FROM announcements ORDER BY created_at DESC LIMIT 5";
$announcements_res = mysqli_query($conn, $announcements_sql);
?>

<div class="dashboard-grid">
    <!-- Statistics Card -->
    <div class="rules-card glass" style="grid-column: span 2;">
        <h3>📊 CCS Admin Statistics</h3>
        <div class="stats-grid">
            <div class="stat-card glass">
                <i class="fas fa-users" style="color: #4a148c;"></i>
                <div class="stat-number"><?php echo $total_registered_res['total']; ?></div>
                <div class="stat-label">Students Registered</div>
            </div>
            <div class="stat-card glass">
                <i class="fas fa-desktop" style="color: #2196F3;"></i>
                <div class="stat-number"><?php echo $currently_sitin_res['total']; ?></div>
                <div class="stat-label">Currently Sit-in</div>
            </div>
            <div class="stat-card glass">
                <i class="fas fa-history" style="color: #ff9800;"></i>
                <div class="stat-number"><?php echo $total_sitin_res['total']; ?></div>
                <div class="stat-label">Total Sit-in Sessions</div>
            </div>
            <div class="stat-card glass" style="cursor: pointer;" onclick="toggleGraphView()">
                <i class="fas fa-chart-line" style="color: #4CAF50;"></i>
                <div class="stat-number">View</div>
                <div class="stat-label">Graph Analytics</div>
            </div>
        </div>
    </div>

    <!-- 🔹 MODERN GRAPH VISUALIZATION CARD (2026 Design) -->
    <div class="info-card glass" style="grid-column: span 2; position: relative;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="margin: 0;">
                <i class="fas fa-chart-area"></i> Sit-in Analytics Dashboard
            </h3>
            <div class="graph-controls" style="display: flex; gap: 8px;">
                <button class="graph-btn active" data-view="trend" onclick="switchGraph('trend')">
                    <i class="fas fa-wave-square"></i> Trend
                </button>
                <button class="graph-btn" data-view="lab" onclick="switchGraph('lab')">
                    <i class="fas fa-building"></i> Lab Usage
                </button>
                <button class="graph-btn" data-view="hourly" onclick="switchGraph('hourly')">
                    <i class="fas fa-clock"></i> Hourly
                </button>
            </div>
        </div>
        
        <!-- Graph Canvas Container with Glass Effect -->
        <div class="graph-container glass" style="background: rgba(255,255,255,0.4); padding: 20px; border-radius: 15px;">
            <canvas id="analyticsChart" height="120"></canvas>
        </div>
        
        <!-- Graph Legend & Stats -->
        <div style="display: flex; justify-content: space-around; margin-top: 20px; flex-wrap: wrap; gap: 10px;">
            <div class="graph-stat">
                <span class="stat-value" id="peakValue">--</span>
                <span class="stat-label">Peak Day</span>
            </div>
            <div class="graph-stat">
                <span class="stat-value" id="avgValue">--</span>
                <span class="stat-label">Avg/Day</span>
            </div>
            <div class="graph-stat">
                <span class="stat-value" id="topLab">--</span>
                <span class="stat-label">Top Lab</span>
            </div>
            <div class="graph-stat">
                <span class="stat-value" id="trendDir">--</span>
                <span class="stat-label">Trend</span>
            </div>
        </div>
    </div>

    <!-- Post Announcement Form -->
    <div class="info-card glass">
        <h3>📢 Post New Announcement</h3>
        <?php echo $message; ?>
        <form action="admin_dashboard.php" method="POST" class="admin-form">
            <div class="form-group">
                <label for="title">Title:</label>
                <input type="text" name="title" id="title" class="form-control" placeholder="Enter announcement title..." required>
            </div>
            <div class="form-group">
                <label for="content">Content:</label>
                <textarea name="content" id="content" class="form-control" rows="4" placeholder="Write your announcement..." required></textarea>
            </div>
            <button type="submit" name="post_announcement" class="btn">
                <i class="fas fa-paper-plane"></i> Post Announcement
            </button>
        </form>
    </div>

    <!-- Announcements Display -->
    <div class="announcement-card glass">
        <h3>🔔 Current Announcements</h3>
        <div style="max-height: 400px; overflow-y: auto;">
            <?php if (mysqli_num_rows($announcements_res) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($announcements_res)): ?>
                <div class="announcement-item">
                    <div class="announcement-header">
                        <i class="fas fa-bullhorn" style="margin-right: 5px;"></i>
                        CCS Admin | <?php echo date('M d, Y', strtotime($row['created_at'])); ?>
                    </div>
                    <p><strong><?php echo htmlspecialchars($row['title']); ?></strong></p>
                    <p><?php echo nl2br(htmlspecialchars($row['content'])); ?></p>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="text-align: center; color: #666; padding: 20px;">No announcements yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- 🔹 Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0"></script>

<!-- 🔹 Modern Graph Script (2026 Design) -->
<script>
// Global chart instance
let analyticsChart = null;

// Data from PHP
const trendLabels = <?php echo json_encode($trend_labels); ?>;
const trendData = <?php echo json_encode($trend_data); ?>;
const labLabels = <?php echo json_encode($lab_labels); ?>;
const labData = <?php echo json_encode($lab_data); ?>;
const hourlyLabels = <?php echo json_encode($hourly_labels); ?>;
const hourlyData = <?php echo json_encode($hourly_data); ?>;

// Gradient generator for modern look
function createGradient(ctx, chartArea, colorStart, colorEnd) {
    const gradient = ctx.createLinearGradient(0, chartArea.bottom, 0, chartArea.top);
    gradient.addColorStop(0, colorStart);
    gradient.addColorStop(1, colorEnd);
    return gradient;
}

// Initialize Trend Chart (Default View)
function initTrendChart() {
    const ctx = document.getElementById('analyticsChart').getContext('2d');
    
    // Destroy existing chart if any
    if (analyticsChart) analyticsChart.destroy();
    
    analyticsChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: trendLabels,
            datasets: [{
                label: 'Sit-in Sessions',
                data: trendData,
                borderColor: '#4a148c',
                backgroundColor: function(context) {
                    const chart = context.chart;
                    const {ctx, chartArea} = chart;
                    if (!chartArea) return null;
                    return createGradient(ctx, chartArea, 'rgba(74, 20, 140, 0.3)', 'rgba(255, 235, 59, 0.1)');
                },
                borderWidth: 3,
                pointBackgroundColor: '#ffeb3b',
                pointBorderColor: '#fff',
                pointBorderWidth: 3,
                pointRadius: 6,
                pointHoverRadius: 8,
                fill: true,
                tension: 0.4, // Smooth curves
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(74, 20, 140, 0.9)',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    padding: 12,
                    cornerRadius: 10,
                    displayColors: false,
                    callbacks: {
                        label: function(context) {
                            return `Sessions: ${context.parsed.y}`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.05)' },
                    ticks: { color: '#666', font: { family: 'Poppins' } }
                },
                x: {
                    grid: { display: false },
                    ticks: { color: '#666', font: { family: 'Poppins' } }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            }
        }
    });
    
    // Update stats
    updateGraphStats('trend');
}

// Initialize Lab Usage Chart (Doughnut)
function initLabChart() {
    const ctx = document.getElementById('analyticsChart').getContext('2d');
    if (analyticsChart) analyticsChart.destroy();
    
    const colors = ['#4a148c', '#7b1fa2', '#9c27b0', '#ba68c8', '#ce93d8'];
    
    analyticsChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labLabels,
            datasets: [{
                data: labData,
                backgroundColor: colors,
                borderColor: '#fff',
                borderWidth: 3,
                hoverOffset: 15
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '65%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        font: { family: 'Poppins', size: 11 },
                        padding: 15,
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(74, 20, 140, 0.9)',
                    padding: 12,
                    cornerRadius: 10,
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a,b) => a+b, 0);
                            const pct = ((context.parsed / total) * 100).toFixed(1);
                            return `${context.label}: ${context.parsed} sessions (${pct}%)`;
                        }
                    }
                }
            }
        }
    });
    
    updateGraphStats('lab');
}

// Initialize Hourly Activity Chart (Bar with Gradient)
function initHourlyChart() {
    const ctx = document.getElementById('analyticsChart').getContext('2d');
    if (analyticsChart) analyticsChart.destroy();
    
    // Create gradient for bars
    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(74, 20, 140, 0.8)');
    gradient.addColorStop(0.5, 'rgba(123, 31, 162, 0.6)');
    gradient.addColorStop(1, 'rgba(255, 235, 59, 0.3)');
    
    analyticsChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: hourlyLabels,
            datasets: [{
                label: 'Sessions Started',
                data: hourlyData,
                backgroundColor: gradient,
                borderColor: '#4a148c',
                borderWidth: 1,
                borderRadius: 8,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(74, 20, 140, 0.9)',
                    padding: 12,
                    cornerRadius: 10,
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.05)' },
                    ticks: { color: '#666', font: { family: 'Poppins' } }
                },
                x: {
                    grid: { display: false },
                    ticks: { 
                        color: '#666', 
                        font: { family: 'Poppins', size: 9 },
                        maxRotation: 45,
                        minRotation: 45
                    }
                }
            }
        }
    });
    
    updateGraphStats('hourly');
}

// Update Stats Below Graph
function updateGraphStats(view) {
    if (view === 'trend' && trendData.length > 0) {
        const max = Math.max(...trendData);
        const avg = (trendData.reduce((a,b) => a+b, 0) / trendData.length).toFixed(1);
        const peakIdx = trendData.indexOf(max);
        const isUp = trendData[trendData.length-1] > trendData[0];
        
        document.getElementById('peakValue').textContent = trendLabels[peakIdx] || '--';
        document.getElementById('avgValue').textContent = avg;
        document.getElementById('topLab').textContent = '--';
        document.getElementById('trendDir').innerHTML = isUp ? 
            '<span style="color:#4CAF50">↑ Rising</span>' : 
            '<span style="color:#f44336">↓ Falling</span>';
    }
    else if (view === 'lab' && labData.length > 0) {
        const maxIdx = labData.indexOf(Math.max(...labData));
        document.getElementById('peakValue').textContent = '--';
        document.getElementById('avgValue').textContent = '--';
        document.getElementById('topLab').textContent = labLabels[maxIdx] || '--';
        document.getElementById('trendDir').textContent = '--';
    }
    else if (view === 'hourly' && hourlyData.length > 0) {
        const maxIdx = hourlyData.indexOf(Math.max(...hourlyData));
        document.getElementById('peakValue').textContent = hourlyLabels[maxIdx] || '--';
        document.getElementById('avgValue').textContent = '--';
        document.getElementById('topLab').textContent = '--';
        document.getElementById('trendDir').textContent = '--';
    }
}

// Switch Graph View
function switchGraph(view) {
    // Update button states
    document.querySelectorAll('.graph-btn').forEach(btn => btn.classList.remove('active'));
    document.querySelector(`.graph-btn[data-view="${view}"]`).classList.add('active');
    
    // Initialize appropriate chart
    if (view === 'trend') initTrendChart();
    else if (view === 'lab') initLabChart();
    else if (view === 'hourly') initHourlyChart();
}

// Toggle Graph View (from stat card click)
function toggleGraphView() {
    document.querySelector('.info-card.glass').scrollIntoView({ behavior: 'smooth' });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    initTrendChart(); // Default view
    
    // Add subtle animation to stat cards on hover
    document.querySelectorAll('.stat-card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
            this.style.transition = 'transform 0.3s ease';
        });
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
});
</script>

<!-- 🔹 Modern Graph Styles -->
<style>
/* Graph Button Styles */
.graph-btn {
    padding: 8px 16px;
    background: rgba(255,255,255,0.5);
    border: 2px solid transparent;
    border-radius: 20px;
    color: var(--text-color);
    font-size: 0.85rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 6px;
}

.graph-btn:hover,
.graph-btn.active {
    background: var(--primary-color);
    color: #fff;
    border-color: var(--secondary-color);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(74, 20, 140, 0.3);
}

.graph-btn i {
    font-size: 0.9rem;
}

/* Graph Stats */
.graph-stat {
    text-align: center;
    padding: 10px 20px;
    background: rgba(255,255,255,0.3);
    border-radius: 10px;
    min-width: 100px;
}

.stat-value {
    display: block;
    font-size: 1.3rem;
    font-weight: 700;
    color: var(--primary-color);
    margin-bottom: 3px;
}

.stat-label {
    font-size: 0.8rem;
    color: #666;
    font-weight: 500;
}

/* Chart Container Animation */
.graph-container {
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
}

.graph-container::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,235,59,0.1) 0%, transparent 70%);
    animation: pulse 4s infinite;
    pointer-events: none;
    z-index: 0;
}

.graph-container canvas {
    position: relative;
    z-index: 1;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); opacity: 0.5; }
    50% { transform: scale(1.1); opacity: 0.8; }
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .graph-controls {
        flex-wrap: wrap;
        justify-content: center;
    }
    
    .graph-btn {
        padding: 6px 12px;
        font-size: 0.8rem;
    }
    
    .graph-stat {
        min-width: 80px;
        padding: 8px 12px;
    }
    
    .stat-value {
        font-size: 1.1rem;
    }
}
</style>

<?php include 'includes/footer.php'; ?>