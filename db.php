<?php

function db_connect() {
    // Adjust connection details based on your database configuration
    $conn = new mysqli('localhost', 'root', '', 'dtc_students');
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    return $conn;
}

function fetch_student_data($enrollment) {
    // Establish connection
    $conn = db_connect();

    // Prepare and execute the query to fetch student data
    $stmt = $conn->prepare("SELECT * FROM students WHERE enrollment_number = ?");
    $stmt->bind_param("s", $enrollment);  // Use string for enrollment number
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Return student data if found
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    } else {
        return false;  // No student found
    }
    
    $conn->close();
}
?>
