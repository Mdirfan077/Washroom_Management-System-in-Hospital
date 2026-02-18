<?php
// Database connection configuration
$dbHost = "localhost";
$dbUsername = "root";
$dbPassword = "";
$dbName = "medicare_plus";

// Create database connection
$conn = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to sanitize input data
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect and sanitize form data
    $floor = sanitizeInput($_POST["floor"]);
    $washroomId = sanitizeInput($_POST["washroom_id"]);
    $issueType = sanitizeInput($_POST["issue_type"]);
    $description = sanitizeInput($_POST["description"]);
    $reporterName = sanitizeInput($_POST["reporter_name"]);
    $reporterDept = sanitizeInput($_POST["reporter_dept"]);
    $urgency = sanitizeInput($_POST["urgency"]);
    
    // Get current date and time
    $reportDate = date("Y-m-d H:i:s");
    
    // Set initial status
    $status = "Reported";
    
    // Prepare SQL statement to prevent SQL injection
    $stmt = $conn->prepare("INSERT INTO washroom_issues (washroom_id, floor, issue_type, description, reporter_name, reporter_department, urgency, report_date, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->bind_param("sssssssss", $washroomId, $floor, $issueType, $description, $reporterName, $reporterDept, $urgency, $reportDate, $status);
    
    // Execute query and check if successful
    if ($stmt->execute()) {
        // Get the ID of the last inserted record
        $lastInsertId = $conn->insert_id;
        
        // Log the activity
        $logAction = "New washroom issue reported for " . $washroomId . " (" . $issueType . ")";
        $logStmt = $conn->prepare("INSERT INTO activity_logs (user_name, action, timestamp) VALUES (?, ?, ?)");
        $logStmt->bind_param("sss", $reporterName, $logAction, $reportDate);
        $logStmt->execute();
        $logStmt->close();
        
        // Update washroom status in washrooms table
        $updateStmt = $conn->prepare("UPDATE washrooms SET status = 'issue' WHERE washroom_id = ?");
        $updateStmt->bind_param("s", $washroomId);
        $updateStmt->execute();
        $updateStmt->close();
        
        // Set session message for success
        session_start();
        $_SESSION['message'] = "Issue reported successfully. Reference #WI" . $lastInsertId;
        $_SESSION['message_type'] = "success";
        
        // Send notification to maintenance staff based on urgency
        if ($urgency == "high") {
            // In a real application, this would trigger immediate notifications
            // For now, we'll just log it
            $urgentLog = $conn->prepare("INSERT INTO urgent_notifications (issue_id, message, status) VALUES (?, ?, 'pending')");
            $urgentMessage = "URGENT: " . $issueType . " issue reported in " . $washroomId;
            $urgentLog->bind_param("is", $lastInsertId, $urgentMessage);
            $urgentLog->execute();
            $urgentLog->close();
        }
        
        // Close statement
        $stmt->close();
        
        // Redirect to confirmation page
        header("Location: report_confirmation.php?id=" . $lastInsertId);
        exit();
    } else {
        // Error handling
        session_start();
        $_SESSION['message'] = "Error: " . $stmt->error;
        $_SESSION['message_type'] = "error";
        
        // Redirect back to the form
        header("Location: washroom.html");
        exit();
    }
}

// If accessing directly without form submission
else {
    // Redirect to the washroom management page
    header("Location: washroom.html");
    exit();
}

// Close database connection
$conn->close();
?>