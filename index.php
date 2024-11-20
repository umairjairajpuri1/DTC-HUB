<?php 
session_start();
include 'db.php'; // Include the database connection file

// Redirect to login if session is not set
if (!isset($_SESSION['name']) || !isset($_SESSION['year'])) {
    header("Location: login.php");
    exit();
}

// Fetch user details from session
$name = $_SESSION['name'];
$year = $_SESSION['year']; // User's academic year

// Establish database connection
$conn = db_connect();
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Fetch notes for the user's year
$notes_sql = $conn->prepare("SELECT subject_code, notes_link FROM notes WHERE year = ?");
$notes_sql->bind_param("i", $year);
$notes_sql->execute();
$notes_result = $notes_sql->get_result();

// Fetch practical schedule
$practical_sql = $conn->prepare("
    SELECT subject_code, schedule_date, teacher_name, roll_range 
    FROM new_practical 
    WHERE year = ?
");
$practical_sql->bind_param("i", $year);
$practical_sql->execute();
$practical_result = $practical_sql->get_result();

// Fetch exam schedule
$datesheet_sql = $conn->prepare("SELECT subject_code, exam_date FROM new_exam WHERE year = ?");
$datesheet_sql->bind_param("i", $year);
$datesheet_sql->execute();
$datesheet_result = $datesheet_sql->get_result();

// Fetch student counts by year for the graph
$student_count_sql = "SELECT year, COUNT(*) AS student_count FROM students GROUP BY year";
$student_count_result = $conn->query($student_count_sql);

$student_labels = [];
$student_data = [];
while ($row = $student_count_result->fetch_assoc()) {
    $student_labels[] = "Year " . $row['year'];
    $student_data[] = $row['student_count'];
}

// Generate dynamic colors
$colors = ['#007bff', '#28a745', '#ffc107', '#dc3545', '#6f42c1'];
function getColor($index, $colors) {
    return $colors[$index % count($colors)];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="lib/style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.0.2/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="dark-mode">
    <!-- Navbar -->
    <nav class="navbar">
        <img src="lib/dtc.webp" alt="DTC Logo" class="navbar-logo">
        <ul class="navbar-menu">
            <li><a href="#">Home</a></li>
            <li><a href="logout.php">Logout</a></li>
            <li>
                <label class="switch">
                    <input type="checkbox" id="themeToggle">
                    <span class="slider round"></span>
                </label>
            </li>
        </ul>
    </nav>

    <div class="container">
        <!-- Header -->
        <div class="text-center mb-4">
            <h1 class="text-primary">Welcome <?php echo htmlspecialchars($name); ?>!</h1>
            <p>Your academic year: <strong><?php echo htmlspecialchars($year); ?></strong></p>
        </div>

        <!-- Number of Students Graph -->
        <div class="row mb-5">
            <div class="col-md-12">
                <h3 class="section-title text-center">Number of Students by Year</h3>
                <canvas id="studentsChart"></canvas>
            </div>
        </div>

        <!-- Practical and Exam Section -->
        <div class="half-section">
            <!-- Practical Schedule -->
            <div class="col">
                <h3 class="section-title">Practical Schedule</h3>
                <?php if ($practical_result->num_rows > 0): ?>
                    <table class="table table-bordered table-striped">
                        <thead class="thead-dark">
                            <tr>
                                <th>Subject</th>
                                <th>Date</th>
                                <th>Teacher</th>
                                <th>Roll Number</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 0; ?>
                            <?php while ($row = $practical_result->fetch_assoc()): ?>
                                <tr class="dynamic-row" style="background-color: <?php echo getColor($i++, $colors); ?>;">
                                    <td><?php echo htmlspecialchars($row['subject_code']); ?></td>
                                    <td><?php echo htmlspecialchars($row['schedule_date']); ?></td>
                                    <td><?php echo htmlspecialchars($row['teacher_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['roll_range']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No practical schedule found for your year.</p>
                <?php endif; ?>
            </div>

            <!-- Exam Schedule -->
            <div class="col">
                <h3 class="section-title">Exam Schedule</h3>
                <?php if ($datesheet_result->num_rows > 0): ?>
                    <table class="table table-bordered table-striped">
                        <thead class="thead-dark">
                            <tr>
                                <th>Subject</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 0; ?>
                            <?php while ($row = $datesheet_result->fetch_assoc()): ?>
                                <tr class="dynamic-row" style="background-color: <?php echo getColor($i++, $colors); ?>;">
                                    <td><?php echo htmlspecialchars($row['subject_code']); ?></td>
                                    <td><?php echo htmlspecialchars($row['exam_date']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No exam schedule found for your year.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Notes Section -->
        <div class="row mt-5">
            <div class="col-md-12">
                <h3 class="section-title">Notes Section</h3>
                <div class="notes-box">
                    <?php $i = 0; ?>
                    <?php if ($notes_result->num_rows > 0): ?>
                        <?php while ($row = $notes_result->fetch_assoc()): ?>
                            <a href="<?php echo htmlspecialchars($row['notes_link']); ?>" target="_blank" style="background-color: <?php echo getColor($i++, $colors); ?>;">
                                <?php echo htmlspecialchars($row['subject_code']); ?>
                            </a>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p>No notes available for your year.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="text-center mt-5">
            <p>&copy; 2024 DTC Hub. All rights reserved.</p>
        </footer>
    </div>

    <!-- Chart Scripts -->
    <script>
        const studentData = {
            labels: <?php echo json_encode($student_labels); ?>,
            datasets: [{
                label: 'Number of Students',
                data: <?php echo json_encode($student_data); ?>,
                backgroundColor: ['#007bff', '#28a745', '#ffc107', '#dc3545'],
                borderWidth: 1
            }]
        };

        const studentCtx = document.getElementById('studentsChart').getContext('2d');
        new Chart(studentCtx, {
            type: 'bar',
            data: studentData,
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Number of Students'
                        }
                    }
                }
            }
        });

        // Theme Toggle Script
        const body = document.body;
        const themeToggle = document.getElementById('themeToggle');
        themeToggle.addEventListener('click', () => {
            body.classList.toggle('dark-mode');
            body.classList.toggle('light-mode');
        });
    </script>
</body>
</html>
