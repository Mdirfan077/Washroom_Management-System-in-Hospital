<?php
// Set admin email (kept for notification purposes)
$admin_email = "rajdmish1218@gmail.com";

// Include PHPMailer classes (kept for optional email notifications)
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader
require __DIR__ . '/../../vendor/autoload.php';

// Database connection parameters for XAMPP
$db_host = 'localhost';
$db_user = 'root';     // default XAMPP username
$db_pass = '';         // default XAMPP password (empty)
$db_name = 'medicare_plus'; // updated database name

// Connect to database
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Function to sanitize form inputs
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to send email using PHPMailer (kept for notification purposes)
function send_email($to, $subject, $body, $from_email, $from_name = '') {
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';  // Your SMTP server
        $mail->SMTPAuth   = true;
        $mail->Username   = 'rajdmish1218@gmail.com';     // SMTP username
        $mail->Password   = 'pvri ovbr siqq mijm';     // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // or PHPMailer::ENCRYPTION_SMTPS
        $mail->Port       = 587;                 // TCP port to connect to, use 465 for ENCRYPTION_SMTPS
        
        // Recipients
        $mail->setFrom($from_email, $from_name);
        $mail->addAddress($to);
        
        // Content
        $mail->isHTML(false);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        // Log the error for debugging
        error_log("PHPMailer Error: {$mail->ErrorInfo}");
        return false;
    }
}

// Process contact form
if (isset($_POST['contact_submit'])) {
    // Sanitize inputs
    $name = sanitize_input($_POST['name']);
    $email = sanitize_input($_POST['email']);
    $subject = sanitize_input($_POST['subject']);
    $message = sanitize_input($_POST['message']);
    $submission_date = date('Y-m-d H:i:s');
    
    // Create SQL query to insert data
    $sql = "INSERT INTO contact_submissions (name, email, subject, message, submission_date) 
            VALUES (?, ?, ?, ?, ?)";
    
    // Prepare statement
    $stmt = $conn->prepare($sql);
    
    // Bind parameters
    $stmt->bind_param("sssss", $name, $email, $subject, $message, $submission_date);
    
    // Execute statement and check result
    $db_success = $stmt->execute();
    
    // Option: Send email notification about the new submission
    $email_body = "Name: $name\n";
    $email_body .= "Email: $email\n";
    $email_body .= "Subject: $subject\n\n";
    $email_body .= "Message:\n$message";
    
    $mail_sent = send_email($admin_email, "Contact Form Submission: $subject", $email_body, $email, $name);
    
    // Store form data in session for confirmation page
    session_start();
    $_SESSION['form_data'] = [
        'name' => $name,
        'email' => $email,
        'subject' => $subject,
        'message' => $message,
        'form_type' => 'contact',
        'success' => $db_success
    ];
    
    // Redirect to confirmation page
    header("Location: confirmation.php");
    exit();
}

// Process appointment form
if (isset($_POST['appointment_submit'])) {
    // Sanitize inputs
    $name = sanitize_input($_POST['name']);
    $email = sanitize_input($_POST['email']);
    $phone = sanitize_input($_POST['phone']);
    $department = sanitize_input($_POST['department']);
    $appointment_date = sanitize_input($_POST['appointment_date']);
    $message = sanitize_input($_POST['message']);
    $submission_date = date('Y-m-d H:i:s');
    
    // Create SQL query to insert data
    $sql = "INSERT INTO appointment_requests (name, email, phone, department, appointment_date, message, submission_date) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    // Prepare statement
    $stmt = $conn->prepare($sql);
    
    // Bind parameters
    $stmt->bind_param("sssssss", $name, $email, $phone, $department, $appointment_date, $message, $submission_date);
    
    // Execute statement and check result
    $db_success = $stmt->execute();
    
    // Option: Send email notification about the new appointment request
    $email_body = "Name: $name\n";
    $email_body .= "Email: $email\n";
    $email_body .= "Phone: $phone\n";
    $email_body .= "Department: $department\n";
    $email_body .= "Appointment Date: $appointment_date\n\n";
    $email_body .= "Message:\n$message";
    
    $mail_sent = send_email($admin_email, "New Appointment Request", $email_body, $email, $name);
    
    // Store form data in session for confirmation page
    session_start();
    $_SESSION['form_data'] = [
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'department' => $department,
        'appointment_date' => $appointment_date,
        'message' => $message,
        'form_type' => 'appointment',
        'success' => $db_success
    ];
    
    // Redirect to confirmation page
    header("Location: confirmation.php");
    exit();
}

// Process newsletter subscription
if (isset($_POST['newsletter_email'])) {
    // Sanitize input
    $email = sanitize_input($_POST['newsletter_email']);
    $subscription_date = date('Y-m-d H:i:s');
    
    // Create SQL query to insert data
    $sql = "INSERT INTO newsletter_subscriptions (email, subscription_date) VALUES (?, ?)";
    
    // Prepare statement
    $stmt = $conn->prepare($sql);
    
    // Bind parameters
    $stmt->bind_param("ss", $email, $subscription_date);
    
    // Execute statement and check result
    $db_success = $stmt->execute();
    
    // Option: Send email notification about the new subscription
    $email_body = "Email: $email\n";
    
    $mail_sent = send_email($admin_email, "New Newsletter Subscription", $email_body, $email);
    
    // Store form data in session for confirmation page
    session_start();
    $_SESSION['form_data'] = [
        'email' => $email,
        'form_type' => 'newsletter',
        'success' => $db_success
    ];
    
    // Redirect to confirmation page
    header("Location: confirmation.php");
    exit();
}

// Close database connection
$conn->close();

// If no form was submitted, redirect to homepage
header("Location: index.html");
exit();
?>