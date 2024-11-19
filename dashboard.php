<?php
session_start();
if (!isset($_SESSION['student_name'])) {
    header("Location: index.php");
    exit();
}
?>
<html>
<head>
    <title>Dashboard</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <script src="js/script.js"></script>
</head>
<body>
<h1>Hello <?php echo $_SESSION['student_name']; ?>, Welcome to DTC Hub</h1>
<!-- Display graph and other student-specific content here -->
<div id="graph"></div>
<div id="exam-schedule"></div>
<div id="coming-soon">
    <p>Results coming soon!</p>
</div>
<a href="logout.php">Logout</a>
</body>
</html>
