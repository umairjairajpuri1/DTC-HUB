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
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
        }
        .section-title {
            color: #007bff;
            font-weight: bold;
        }
        .notes-box {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 2rem;
        }
        .notes-box a {
            display: block;
            text-decoration: none;
            color: white;
            padding: 10px;
            border-radius: 8px;
            text-align: center;
            flex: 1 0 22%;
            min-width: 150px;
        }
        .notes-box a:hover {
            opacity: 0.9;
        }
        .practical-group, .exam-group {
            margin-bottom: 1rem;
            padding: 0.5rem;
            border: 1px solid #dee2e6;
            border-radius: 8px;
        }
        .coming-soon {
            font-size: 1.5rem;
            color: #dc3545;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }
        .half-section {
            display: flex;
            flex-wrap: wrap;
        }
        .half-section .col {
            flex: 1;
            padding: 15px;
        }
        .dynamic-row {
            color: white;
        }
        @media (max-width: 768px) {
            .half-section {
                flex-direction: column;
            }
            .notes-box a {
                flex: 1 0 45%; /* For tablets and small screens */
            }
        }
        @media (max-width: 576px) {
            .notes-box a {
                flex: 1 0 100%; /* For mobile screens */
            }
        }
    </style>
</head>
<body>
<div class="container mt-5">
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

    <!-- Result Section -->
    <div class="row mt-5">
        <div class="col-md-12 text-center">
            <h3 class="section-title">Result</h3>
            <p class="coming-soon">Coming Soon</p>
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
</script>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<p style="text-align: center; font-family: Arial, sans-serif; margin-top: 20px;">
    Made with ❤️ by Umair Jairajpuri
</p>
</body>
</html>
