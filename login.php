<?php
// Start session
session_start();

// Database Configuration
$db_host = "localhost";
$db_user = "root";  // Default XAMPP username
$db_pass = "";      // Default XAMPP password (empty)
$db_name = "medicare_plus";

// Create database connection
$conn = new mysqli($db_host, $db_user, $db_pass);

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS $db_name";
if (!$conn->query($sql)) {
    die(json_encode([
        'status' => 'error',
        'message' => 'Error creating database: ' . $conn->error
    ]));
}

// Select the database
$conn->select_db($db_name);

// Create users table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if (!$conn->query($sql)) {
    die(json_encode([
        'status' => 'error',
        'message' => 'Error creating users table: ' . $conn->error
    ]));
}

// Create newsletter_subscribers table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS newsletter_subscribers (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (!$conn->query($sql)) {
    die(json_encode([
        'status' => 'error',
        'message' => 'Error creating newsletter table: ' . $conn->error
    ]));
}

// Check if user is already logged in, redirect to washroom.php
if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    header("Location: washroom.html");
    exit();
}

// Process API requests
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    // API request handling
    header('Content-Type: application/json');

    // Get the request type
    $action = '';
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
    } elseif (isset($_GET['action'])) {
        $action = $_GET['action'];
    } else {
        // Try to determine action based on form fields
        if (isset($_POST['login-email']) && isset($_POST['login-password'])) {
            $action = 'login';
        } elseif (isset($_POST['register-email']) && isset($_POST['register-password'])) {
            $action = 'register';
        } elseif (isset($_POST['newsletter_email'])) {
            $action = 'newsletter';
        }
    }

    // Handle different actions
    switch ($action) {
        case 'login':
            handleLogin($conn);
            break;
        case 'register':
            handleRegistration($conn);
            break;
        case 'newsletter':
            handleNewsletter($conn);
            break;
        default:
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid action specified'
            ]);
    }

    // Close the connection
    $conn->close();
    exit;
}

/**
 * Handle user login
 * @param mysqli $conn Database connection
 */
function handleLogin($conn) {
    // Get form data
    $email = $conn->real_escape_string($_POST['login-email']);
    $password = $_POST['login-password']; // Will be verified with password_verify
    
    // Check if user exists
    $sql = "SELECT id, first_name, password FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'User not found. Please check your email or register.'
        ]);
        return;
    }
    
    // Verify password
    $user = $result->fetch_assoc();
    if (password_verify($password, $user['password'])) {
        // Password is correct
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['first_name'];
        $_SESSION['logged_in'] = true;
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Login successful. Welcome back, ' . $user['first_name'] . '!',
            'redirect' => 'washroom.html'
        ]);
    } else {
        // Password is incorrect
        echo json_encode([
            'status' => 'error',
            'message' => 'Incorrect password. Please try again.'
        ]);
    }
    
    $stmt->close();
}

/**
 * Handle user registration
 * @param mysqli $conn Database connection
 */
function handleRegistration($conn) {
    // Get form data
    $firstName = $conn->real_escape_string($_POST['register-firstname']);
    $lastName = $conn->real_escape_string($_POST['register-lastname']);
    $email = $conn->real_escape_string($_POST['register-email']);
    $phone = $conn->real_escape_string($_POST['register-phone']);
    $password = $_POST['register-password'];
    
    // Validate data (basic validation)
    if (empty($firstName) || empty($lastName) || empty($email) || empty($phone) || empty($password)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'All fields are required.'
        ]);
        return;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid email format.'
        ]);
        return;
    }
    
    // Check if email already exists
    $sql = "SELECT id FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Email already exists. Please use a different email or try logging in.'
        ]);
        $stmt->close();
        return;
    }
    $stmt->close();
    
    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert user into database
    $sql = "INSERT INTO users (first_name, last_name, email, phone, password) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $firstName, $lastName, $email, $phone, $hashedPassword);
    
    if ($stmt->execute()) {
        // User successfully registered, set session variables
        $_SESSION['user_id'] = $conn->insert_id;
        $_SESSION['user_name'] = $firstName;
        $_SESSION['logged_in'] = true;
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Registration successful! Welcome to MediCare Plus!',
            'redirect' => 'washroom.html'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Registration failed: ' . $stmt->error
        ]);
    }
    
    $stmt->close();
}

/**
 * Handle newsletter subscription
 * @param mysqli $conn Database connection
 */
function handleNewsletter($conn) {
    // Get form data
    $email = $conn->real_escape_string($_POST['newsletter_email']);
    
    // Validate email
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Please provide a valid email address.'
        ]);
        return;
    }
    
    // Check if email already subscribed
    $sql = "SELECT id FROM newsletter_subscribers WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo json_encode([
            'status' => 'info',
            'message' => 'You are already subscribed to our newsletter.'
        ]);
        $stmt->close();
        return;
    }
    $stmt->close();
    
    // Add email to newsletter subscribers
    $sql = "INSERT INTO newsletter_subscribers (email) VALUES (?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    
    if ($stmt->execute()) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Thank you for subscribing to our newsletter!'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Subscription failed: ' . $stmt->error
        ]);
    }
    
    $stmt->close();
}

// Close the connection for non-ajax requests
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediCare Plus - Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #1a73e8;
            --primary-dark: #0d47a1;
            --secondary: #34a853;
            --light: #f8f9fa;
            --dark: #202124;
            --danger: #ea4335;
            --gray: #dadce0;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f5f5;
            color: var(--dark);
            line-height: 1.6;
        }
        
        .container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 15px;
    width: 100%; /* Ensure container takes full width */
    display: flex; /* Add this */
    justify-content: center; /* Add this */
}
        
        header {
            background-color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 15px 0;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .logo {
            color: var(--primary);
            font-size: 24px;
            font-weight: 700;
            text-decoration: none;
            display: flex;
            align-items: center;
        }
        
        .logo i {
            margin-right: 10px;
            color: var(--secondary);
        }
        
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .main {
    padding: 60px 0;
    min-height: calc(100vh - 170px);
    display: flex;
    justify-content: center; /* Add this to center horizontally */
    align-items: center;
}
        
.auth-container {
    display: flex;
    width: 100%;
    max-width: 1000px;
    margin: 0 auto;
    background: white;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

        
        .auth-image {
            flex: 1;
            background-image: url('/api/placeholder/600/800');
            background-size: cover;
            background-position: center;
            display: none;
        }
        
        .auth-forms {
            flex: 1;
            padding: 40px;
        }
        
        .tabs {
            display: flex;
            margin-bottom: 30px;
            border-bottom: 2px solid var(--gray);
        }
        
        .tab {
            padding: 10px 20px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .tab.active {
            color: var(--primary);
            border-bottom: 2px solid var(--primary);
            margin-bottom: -2px;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--gray);
            border-radius: 5px;
            font-size: 16px;
            transition: border 0.3s;
        }
        
        .form-control:focus {
            border-color: var(--primary);
            outline: none;
        }
        
        .btn {
            padding: 12px 24px;
            font-size: 16px;
            font-weight: 600;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background-color: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
        }
        
        .btn-block {
            display: block;
            width: 100%;
        }
        
        .form-footer {
            margin-top: 30px;
            text-align: center;
        }
        
        .form-footer a {
            color: var(--primary);
            text-decoration: none;
        }
        
        .form-footer a:hover {
            text-decoration: underline;
        }
        
        .newsletter {
            background-color: var(--primary-dark);
            color: white;
            padding: 60px 0;
        }
        
        .newsletter h2 {
            margin-bottom: 20px;
        }
        
        .newsletter-form {
            display: flex;
            max-width: 500px;
            margin: 0 auto;
        }
        
        .newsletter-form input {
            flex: 1;
            padding: 12px 15px;
            border: none;
            border-radius: 5px 0 0 5px;
            font-size: 16px;
        }
        
        .newsletter-form button {
            padding: 12px 20px;
            background-color: var(--secondary);
            color: white;
            font-weight: 600;
            border: none;
            border-radius: 0 5px 5px 0;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .newsletter-form button:hover {
            background-color: #2d8e47;
        }
        
        footer {
            background-color: var(--dark);
            color: white;
            padding: 30px 0;
            text-align: center;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            display: none;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .alert-info {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        
        @media (min-width: 768px) {
            .auth-image {
                display: block;
            }
        }
     
    </style>
</head>
<body>
<header>
        <div class="container">
            <nav class="navbar">
                <a href="index.php" class="logo">
                    <i class="fas fa-heartbeat"></i>
                    <span>MediCare Plus</span>
                </a>
                <ul class="homexx" style="list-style: none; margin: 0; padding: 0; position: absolute; top: 10px; right: 20px;">
                    <li><a href="index.html" style="text-decoration: none; color: white; font-weight: bold; background-color: #1A73EB; padding: 8px 15px; border-radius: 5px; display: inline-block;">Home</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="main">
        <div class="container">
            <div class="auth-container">
                <div class="auth-image"></div>
                <div class="auth-forms">
                    <div class="alert" id="alert"></div>
                    
                    <div class="tabs">
                        <div class="tab active" data-tab="login">Login</div>
                        <div class="tab" data-tab="register">Register</div>
                    </div>
                    
                    <div class="tab-content active" id="login-tab">
                        <form id="login-form">
                            <div class="form-group">
                                <label for="login-email">Email Address</label>
                                <input type="email" id="login-email" name="login-email" class="form-control" placeholder="Enter your email" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="login-password">Password</label>
                                <input type="password" id="login-password" name="login-password" class="form-control" placeholder="Enter your password" required>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-block">Login</button>
                            
                            <div class="form-footer">
                                <p>Don't have an account? <a href="#" class="tab-link" data-tab="register">Register Now</a></p>
                            </div>
                        </form>
                    </div>
                    
                    <div class="tab-content" id="register-tab">
                        <form id="register-form">
                            <div class="form-group">
                                <label for="register-firstname">First Name</label>
                                <input type="text" id="register-firstname" name="register-firstname" class="form-control" placeholder="Enter your first name" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="register-lastname">Last Name</label>
                                <input type="text" id="register-lastname" name="register-lastname" class="form-control" placeholder="Enter your last name" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="register-email">Email Address</label>
                                <input type="email" id="register-email" name="register-email" class="form-control" placeholder="Enter your email" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="register-phone">Phone Number</label>
                                <input type="tel" id="register-phone" name="register-phone" class="form-control" placeholder="Enter your phone number" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="register-password">Password</label>
                                <input type="password" id="register-password" name="register-password" class="form-control" placeholder="Create a password" required>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-block">Register</button>
                            
                            <div class="form-footer">
                                <p>Already have an account? <a href="#" class="tab-link" data-tab="login">Login</a></p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <section class="newsletter">
        <div class="container">
            <h2>Subscribe to Our Newsletter</h2>
            <p>Stay updated with our latest healthcare services, tips, and promotions.</p>
            <form id="newsletter-form" class="newsletter-form">
                <input type="email" name="newsletter_email" placeholder="Enter your email address" required>
                <button type="submit">Subscribe</button>
            </form>
        </div>
    </section>

    <footer>
        <div class="container">
            <p>&copy; 2025 MediCare Plus. All rights reserved.</p>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Tab switching
            const tabs = document.querySelectorAll('.tab');
            const tabLinks = document.querySelectorAll('.tab-link');
            const tabContents = document.querySelectorAll('.tab-content');
            
            function switchTab(tabId) {
                tabs.forEach(tab => {
                    if (tab.dataset.tab === tabId) {
                        tab.classList.add('active');
                    } else {
                        tab.classList.remove('active');
                    }
                });
                
                tabContents.forEach(content => {
                    if (content.id === tabId + '-tab') {
                        content.classList.add('active');
                    } else {
                        content.classList.remove('active');
                    }
                });
            }
            
            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    switchTab(this.dataset.tab);
                });
            });
            
            tabLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    switchTab(this.dataset.tab);
                });
            });
            
            // Alert function
            function showAlert(message, type) {
                const alert = document.getElementById('alert');
                alert.textContent = message;
                alert.className = 'alert alert-' + type;
                alert.style.display = 'block';
                
                setTimeout(() => {
                    alert.style.display = 'none';
                }, 5000);
            }
            
            // Form submissions
            document.getElementById('login-form').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                formData.append('action', 'login');
                
                fetch('login.php', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        showAlert(data.message, 'success');
                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, 1000);
                    } else {
                        showAlert(data.message, data.status);
                    }
                })
                .catch(error => {
                    showAlert('An error occurred. Please try again.', 'danger');
                    console.error('Error:', error);
                });
            });
            
            document.getElementById('register-form').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                formData.append('action', 'register');
                
                fetch('login.php', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        showAlert(data.message, 'success');
                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, 1000);
                    } else {
                        showAlert(data.message, data.status);
                    }
                })
                .catch(error => {
                    showAlert('An error occurred. Please try again.', 'danger');
                    console.error('Error:', error);
                });
            });
            
            document.getElementById('newsletter-form').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                formData.append('action', 'newsletter');
                
                fetch('login.php', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success' || data.status === 'info') {
                        showAlert(data.message, data.status);
                        this.reset();
                    } else {
                        showAlert(data.message, 'danger');
                    }
                })
                .catch(error => {
                    showAlert('An error occurred. Please try again.', 'danger');
                    console.error('Error:', error);
                });
            });
        });
    </script>
</body>
</html>