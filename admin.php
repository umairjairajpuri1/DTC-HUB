<?php
session_start();

// Admin login
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['password'])) {
    if ($_POST['password'] === '2112') {
        $_SESSION['admin_logged_in'] = true;
        header("Location: admin.php");
        exit();
    } else {
        $error = "Invalid password.";
    }
}

// Admin functionalities
include 'db.php';
$conn = db_connect();
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Handle adding/updating notes
if ($_SESSION['admin_logged_in'] && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['notes_link'])) {
    $year = $_POST['year'];
    $subject_code = $_POST['subject_code'];
    $notes_link = $_POST['notes_link'];

    $stmt = $conn->prepare("INSERT INTO notes (year, subject_code, notes_link) VALUES (?, ?, ?) 
                            ON DUPLICATE KEY UPDATE notes_link = VALUES(notes_link)");
    $stmt->bind_param("iss", $year, $subject_code, $notes_link);
    $stmt->execute();
    $success = "Notes successfully added/updated!";
}

// Fetch years and subjects from new_exam
$years_sql = "SELECT DISTINCT year FROM new_exam";
$years_result = $conn->query($years_sql);

// Handle AJAX request for subjects based on year
if (isset($_GET['year'])) {
    $selected_year = intval($_GET['year']);
    $subjects_sql = $conn->prepare("SELECT DISTINCT subject_code FROM new_exam WHERE year = ?");
    $subjects_sql->bind_param("i", $selected_year);
    $subjects_sql->execute();
    $subjects_result = $subjects_sql->get_result();
    $subjects = [];
    while ($row = $subjects_result->fetch_assoc()) {
        $subjects[] = $row['subject_code'];
    }
    echo json_encode($subjects);
    exit();
}

if (!isset($_SESSION['admin_logged_in'])): ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Login</title>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    </head>
    <body>
    <div class="container mt-5">
        <h1 class="text-center">Admin Login</h1>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Login</button>
        </form>
    </div>
    </body>
    </html>
<?php else: ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Panel</title>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    </head>
    <body>
    <div class="container mt-5">
        <h1 class="text-center">Admin Panel</h1>
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <form method="POST" id="admin-form">
            <div class="form-group">
                <label for="year">Year:</label>
                <select id="year" name="year" class="form-control" required>
                    <option value="">Select Year</option>
                    <?php while ($row = $years_result->fetch_assoc()): ?>
                        <option value="<?php echo $row['year']; ?>"><?php echo $row['year']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="subject_code">Subject:</label>
                <select id="subject_code" name="subject_code" class="form-control" required>
                    <option value="">Select Subject</option>
                </select>
            </div>
            <div class="form-group">
                <label for="notes_link">Notes Link:</label>
                <input type="url" id="notes_link" name="notes_link" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Add/Update Notes</button>
        </form>
    </div>
    <script>
        $(document).ready(function () {
            $('#year').change(function () {
                const year = $(this).val();
                if (year) {
                    $.ajax({
                        url: 'admin.php',
                        type: 'GET',
                        data: { year: year },
                        success: function (response) {
                            const subjects = JSON.parse(response);
                            let options = '<option value="">Select Subject</option>';
                            subjects.forEach(function (subject) {
                                options += `<option value="${subject}">${subject}</option>`;
                            });
                            $('#subject_code').html(options);
                        }
                    });
                } else {
                    $('#subject_code').html('<option value="">Select Subject</option>');
                }
            });
        });
    </script>
    </body>
    </html>
<?php endif; ?>
