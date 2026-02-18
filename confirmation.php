<?php
// Start session to access form data
session_start();

// Check if form data exists
if (!isset($_SESSION['form_data'])) {
    // Redirect to home page if no form data is available
    header("Location: index.html");
    exit();
}

// Get form data
$form_data = $_SESSION['form_data'];
$form_type = $form_data['form_type'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submission Confirmed - MediCare Plus</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        .hero-pattern {
            background-color: #f0f9ff;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%230ea5e9' fill-opacity='0.1'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
    </style>
</head>
<body class="font-sans">
    <!-- Navigation -->
    <nav class="bg-gradient-to-r from-blue-600 to-cyan-500 text-white shadow-lg">
        <div class="container mx-auto px-4 py-3">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-2">
                    <i class="fas fa-heartbeat text-2xl"></i>
                    <span class="text-xl font-bold">MediCare Plus</span>
                </div>
                <div class="hidden md:flex space-x-8 text-sm">
                    <a href="index.html" class="hover:text-blue-200 py-1">HOME</a>
                    <a href="service.html" class="hover:text-blue-200 py-1">SERVICES</a>
                    <a href="doctor.html" class="hover:text-blue-200 py-1">DOCTORS</a>
                    <a href="#" class="hover:text-blue-200 py-1">WASHROOM MANAGEMENT</a>
                    <a href="#" class="hover:text-blue-200 py-1">CONTACT</a>
                </div>
                <div class="md:hidden">
                    <button id="mobile-menu-button" class="text-white">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Confirmation Section -->
    <section class="hero-pattern">
        <div class="container mx-auto px-4 py-16">
            <div class="bg-white rounded-xl shadow-lg overflow-hidden max-w-3xl mx-auto">
                <div class="p-8">
                    <?php if ($form_type === 'appointment'): ?>
                        <div class="text-center mb-8">
                            <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-4">
                                <i class="fas fa-check-circle text-3xl text-green-500"></i>
                            </div>
                            <h2 class="text-3xl font-bold text-gray-800">Appointment Request Confirmed</h2>
                            <p class="text-gray-600 mt-2">Thank you for scheduling an appointment with us.</p>
                        </div>

                        <div class="border-t border-gray-200 pt-6">
                            <h3 class="text-xl font-semibold text-blue-800 mb-4">Your Appointment Details</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <p class="text-sm text-gray-500">Full Name</p>
                                    <p class="font-medium text-gray-800"><?php echo $form_data['name']; ?></p>
                                </div>
                                
                                <div>
                                    <p class="text-sm text-gray-500">Email Address</p>
                                    <p class="font-medium text-gray-800"><?php echo $form_data['email']; ?></p>
                                </div>
                                
                                <div>
                                    <p class="text-sm text-gray-500">Phone Number</p>
                                    <p class="font-medium text-gray-800"><?php echo $form_data['phone']; ?></p>
                                </div>
                                
                                <div>
                                    <p class="text-sm text-gray-500">Department</p>
                                    <p class="font-medium text-gray-800"><?php echo $form_data['department']; ?></p>
                                </div>
                                
                                <div>
                                    <p class="text-sm text-gray-500">Appointment Date</p>
                                    <p class="font-medium text-gray-800"><?php echo $form_data['appointment_date']; ?></p>
                                </div>
                            </div>
                            
                            <?php if (!empty($form_data['message'])): ?>
                                <div class="mt-6">
                                    <p class="text-sm text-gray-500">Your Message</p>
                                    <p class="font-medium text-gray-800"><?php echo nl2br($form_data['message']); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mt-8 border-t border-gray-200 pt-6">
                            <p class="text-gray-600">Our team will review your request and contact you shortly to confirm your appointment time.</p>
                            <p class="text-gray-600 mt-2">If you have any questions, please call our appointment desk at <span class="font-semibold">(212) 555-1234</span>.</p>
                        </div>

                    <?php elseif ($form_type === 'newsletter'): ?>
                        <div class="text-center mb-8">
                            <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-4">
                                <i class="fas fa-check-circle text-3xl text-green-500"></i>
                            </div>
                            <h2 class="text-3xl font-bold text-gray-800">Newsletter Subscription Confirmed</h2>
                            <p class="text-gray-600 mt-2">Thank you for subscribing to our newsletter.</p>
                        </div>

                        <div class="border-t border-gray-200 pt-6">
                            <div class="text-center">
                                <p class="text-gray-600">Your email address <span class="font-semibold"><?php echo $form_data['email']; ?></span> has been added to our mailing list.</p>
                                <p class="text-gray-600 mt-4">You will now receive regular updates about our services, health tips, and special offers.</p>
                            </div>
                        </div>
                    
                    <?php elseif ($form_type === 'washroom_issue'): ?>
                        <div class="text-center mb-8">
                            <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-4">
                                <i class="fas fa-check-circle text-3xl text-green-500"></i>
                            </div>
                            <h2 class="text-3xl font-bold text-gray-800">Washroom Issue Reported</h2>
                            <p class="text-gray-600 mt-2">Thank you for reporting this issue.</p>
                        </div>

                        <div class="border-t border-gray-200 pt-6">
                            <h3 class="text-xl font-semibold text-blue-800 mb-4">Issue Details</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <p class="text-sm text-gray-500">Reference Number</p>
                                    <p class="font-medium text-gray-800"><?php echo $form_data['reference_id']; ?></p>
                                </div>
                                
                                <div>
                                    <p class="text-sm text-gray-500">Washroom ID</p>
                                    <p class="font-medium text-gray-800"><?php echo $form_data['washroom_id']; ?></p>
                                </div>
                                
                                <div>
                                    <p class="text-sm text-gray-500">Floor</p>
                                    <p class="font-medium text-gray-800"><?php echo $form_data['floor']; ?></p>
                                </div>
                                
                                <div>
                                    <p class="text-sm text-gray-500">Issue Type</p>
                                    <p class="font-medium text-gray-800"><?php echo $form_data['issue_type']; ?></p>
                                </div>
                                
                                <div>
                                    <p class="text-sm text-gray-500">Urgency</p>
                                    <p class="font-medium text-gray-800 <?php echo $form_data['urgency'] == 'high' ? 'text-red-600' : ''; ?>">
                                        <?php echo ucfirst($form_data['urgency']); ?>
                                    </p>
                                </div>
                                
                                <div>
                                    <p class="text-sm text-gray-500">Reported By</p>
                                    <p class="font-medium text-gray-800"><?php echo $form_data['reporter_name']; ?> (<?php echo $form_data['reporter_dept']; ?>)</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-8 border-t border-gray-200 pt-6">
                            <p class="text-gray-600">Our maintenance team has been notified and will address this issue promptly.</p>
                            <?php if ($form_data['urgency'] == 'high'): ?>
                                <p class="text-gray-600 mt-2">Since you marked this as <span class="font-semibold text-red-600">high urgency</span>, an immediate notification has been sent to our maintenance staff.</p>
                            <?php endif; ?>
                            <p class="text-gray-600 mt-2">You can track the status of this issue by referring to your reference number: <span class="font-semibold"><?php echo $form_data['reference_id']; ?></span>.</p>
                        </div>
                    <?php endif; ?>
                    
                    <div class="mt-8 text-center">
                        <a href="index.html" class="inline-block bg-gradient-to-r from-blue-600 to-cyan-500 text-white font-semibold py-3 px-8 rounded-full shadow-md hover:shadow-lg transition duration-300">
                            Return to Homepage
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-blue-900 text-white py-8">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="flex items-center space-x-2 mb-4 md:mb-0">
                    <i class="fas fa-heartbeat text-2xl text-cyan-400"></i>
                    <span class="text-xl font-bold">MediCare Plus</span>
                </div>
                <p class="text-blue-300 text-sm">&copy; 2025 MediCare Plus Hospital. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Clear session after page has loaded to prevent showing the same data if page is refreshed
        window.onload = function() {
            <?php
            // Clear the session data after displaying it
            unset($_SESSION['form_data']);
            ?>
        }
    </script>
</body>
</html>
<?php
// Clear session data after displaying it (alternative to JavaScript approach)
// unset($_SESSION['form_data']);
?>