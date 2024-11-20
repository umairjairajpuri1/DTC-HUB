<?php
session_start();
include 'db.php'; // Include the database connection file

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $enrollment = trim($_POST['enrollment_number']); // Sanitize input

    // Remove leading zeros from the enrollment number
    $enrollment = ltrim($enrollment, '0');

    // Check if enrollment number is valid
    if (empty($enrollment) || !ctype_digit($enrollment)) {
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
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="logo-container">
        <img src="lib/dtc.webp" alt="DTC Logo" class="logo">
    </div>
    <div class="container">
        <form action="" method="POST" autocomplete="off">
            <h2>Sign In</h2>
            <div class="uc"><b>DTC HUB</b></div>

            <div class="inputBox">
                <input type="text" name="enrollment_number" id="enrollment" required>
                <label for="enrollment">Enrollment Number</label>
            </div>

            <?php if (isset($error)): ?>
                <p style="color: red; text-align: center;"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>

            <input type="submit" value="Login">
            <div class="link-container">
                <a href="#">Forgot Password?</a>
            </div>
            <div class="link-container">
                <a href="#">Signup</a>
            </div>
        </form>
    </div>
</body>
</html>
