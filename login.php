<?php
session_start();
include 'db.php'; // Include the database connection file

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $enrollment = trim($_POST['enrollment_number']); // Sanitize input

    // Remove leading zeros from the enrollment number
    $enrollment = ltrim($enrollment, '0');

    // Check if enrollment number is valid after stripping leading zeros
    if (empty($enrollment) || !ctype_digit($enrollment)) {
        // Display error for invalid enrollment numbers
        $error = "Invalid enrollment number. Please enter a valid number.";
    } else {
        $conn = db_connect();
        if (!$conn) {
            die("Database connection failed: " . mysqli_connect_error());
        }

        // Query to fetch student details based on enrollment number
        $sql = $conn->prepare("SELECT name, year FROM students WHERE enrollment_number = ?");
        $sql->bind_param("s", $enrollment);
        $sql->execute();
        $result = $sql->get_result();

        if ($result->num_rows == 1) {
            // Fetch student details
            $student = $result->fetch_assoc();
            $_SESSION['name'] = $student['name'];
            $_SESSION['year'] = $student['year'];

            // Redirect to index.php
            header("Location: index.php");
            exit();
        } else {
            $error = "Enrollment number not found. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h1 class="text-center">Login</h1>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    <form method="POST" action="login.php">
        <div class="form-group">
            <label for="enrollment_number">Enrollment Number:</label>
            <input type="text" id="enrollment_number" name="enrollment_number" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Login</button>
    </form>
</div>
</body>
</html>
