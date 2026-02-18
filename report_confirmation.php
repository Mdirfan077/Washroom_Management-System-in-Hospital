<?php
// Start session to access messages
session_start();

// Database connection configuration
$dbHost = "localhost";
$dbUsername = "hospital_admin";
$dbPassword = "your_secure_password";
$dbName = "medicare_plus";

// Initialize variables
$issueDetails = null;
$issueId = null;

// Check if issue ID is provided
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $issueId = intval($_GET['id']);
    
    // Create database connection
    $conn = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName);
    
    // Check connection
    if (!$conn->connect_error) {
        // Prepare SQL to fetch issue details
        $stmt = $conn->prepare("SELECT wi.*, w.location, w.gender 
                               FROM washroom_issues wi 
                               JOIN washrooms w ON wi.washroom_id = w.washroom_id 
                               WHERE wi.id = ?");
        $stmt->bind_param("i", $issueId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $issueDetails = $result->fetch_assoc();
        }
        
        $stmt->close();
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Confirmation - MediCare Plus</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
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
                    <a href="washroom.html" class="hover:text-blue-200 border-b-2 border-white py-1">WASHROOM MANAGEMENT</a>
                    <a href="login.html" class="bg-gradient-to-r from-blue-600 to-cyan-500 text-white px-4 py-2 rounded-lg shadow-md hover:from-cyan-600 hover:to-blue-700 transition">STAFF</a>
                </div>
                <div class="md:hidden">
                    <button id="mobile-menu-button" class="text-white">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
        </div>
        <!-- Mobile Menu -->
        <div id="mobile-menu" class="hidden md:hidden bg-blue-600 pb-4 px-4">
            <div class="flex flex-col space-y-3 text-sm">
                <a href="index.html" class="hover:text-blue-200 py-1 border-b border-blue-500">HOME</a>
                <a href="service.html" class="hover:text-blue-200 py-1 border-b border-blue-500">SERVICES</a>
                <a href="doctor.html" class="hover:text-blue-200 py-1 border-b border-blue-500">DOCTORS</a>
                <a href="washroom.html" class="hover:text-blue-200 py-1 border-b border-blue-500">WASHROOM MANAGEMENT</a>
                <a href="#" class="hover:text-blue-200 py-1">CONTACT</a>
            </div>
        </div>
    </nav>

    <!-- Confirmation Content -->
    <div class="container mx-auto px-4 py-12">
        <div class="max-w-2xl mx-auto">
            <?php if ($issueDetails): ?>
                <!-- Success confirmation -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <!-- Header -->
                    <div class="bg-gradient-to-r from-green-500 to-emerald-600 p-6">
                        <div class="flex items-center justify-center">
                            <div class="bg-white rounded-full p-3 mr-4">
                                <i class="fas fa-check-circle text-green-500 text-3xl"></i>
                            </div>
                            <div>
                                <h1 class="text-white text-2xl font-bold">Report Submitted Successfully</h1>
                                <p class="text-green-100">Your washroom issue has been reported</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Issue details -->
                    <div class="p-6">
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                            <div class="flex items-center">
                                <i class="fas fa-info-circle text-green-600 mr-3 text-xl"></i>
                                <p class="text-green-800">
                                    Your issue has been assigned reference number: 
                                    <span class="font-bold">WI<?php echo $issueId; ?></span>
                                </p>
                            </div>
                        </div>
                        
                        <h2 class="text-xl font-semibold text-gray-800 mb-4">Issue Details</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h3 class="text-sm font-medium text-gray-500">Washroom Location</h3>
                                <p class="text-gray-800 font-semibold">
                                    <?php echo htmlspecialchars($issueDetails['washroom_id']); ?> - 
                                    <?php echo htmlspecialchars($issueDetails['location']); ?>
                                    (<?php echo htmlspecialchars($issueDetails['floor']); ?>)
                                </p>
                            </div>
                            
                            <div>
                                <h3 class="text-sm font-medium text-gray-500">Issue Type</h3>
                                <p class="text-gray-800 font-semibold">
                                    <?php echo htmlspecialchars($issueDetails['issue_type']); ?>
                                </p>
                            </div>
                            
                            <div>
                                <h3 class="text-sm font-medium text-gray-500">Reported By</h3>
                                <p class="text-gray-800 font-semibold">
                                    <?php echo htmlspecialchars($issueDetails['reporter_name']); ?> 
                                    (<?php echo htmlspecialchars($issueDetails['reporter_department']); ?>)
                                </p>
                            </div>
                            
                            <div>
                                <h3 class="text-sm font-medium text-gray-500">Report Date</h3>
                                <p class="text-gray-800 font-semibold">
                                    <?php echo date('F j, Y, g:i a', strtotime($issueDetails['report_date'])); ?>
                                </p>
                            </div>
                            
                            <div class="md:col-span-2">
                                <h3 class="text-sm font-medium text-gray-500">Description</h3>
                                <p class="text-gray-800">
                                    <?php echo htmlspecialchars($issueDetails['description']); ?>
                                </p>
                            </div>
                        </div>
                        
                        <!-- Status information -->
                        <div class="mt-8 border-t border-gray-200 pt-6">
                            <h2 class="text-lg font-semibold text-gray-800 mb-4">What Happens Next?</h2>
                            
                            <div class="bg-blue-50 rounded-lg p-5">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 mt-1">
                                        <i class="fas fa-clipboard-list text-blue-500 text-lg"></i>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-blue-800 mb-2">
                                            Your report has been sent to our maintenance team. Here's the expected process:
                                        </p>
                                        
                                        <ol class="list-decimal list-inside space-y-2 text-blue-700 pl-3">
                                            <li>A maintenance staff member will be assigned to your issue.</li>
                                            <li>The issue will be inspected within 
                                                <?php echo ($issueDetails['urgency'] == 'high') ? '30 minutes' : 
                                                    (($issueDetails['urgency'] == 'medium') ? '2 hours' : '24 hours'); ?>.
                                            </li>
                                            <li>The status will be updated in the system once resolved.</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Action buttons -->
                        <div class="mt-8 flex flex-col md:flex-row space-y-4 md:space-y-0 md:space-x-4">
                            <a href="washroom.html" class="flex-1 bg-gradient-to-r from-blue-600 to-cyan-500 text-white text-center font-semibold py-3 px-6 rounded-lg shadow-md hover:from-blue-700 hover:to-cyan-600 transition">
                                Return to Washroom Management
                            </a>
                            <a href="#" class="flex-1 bg-white border border-gray-300 text-gray-700 text-center font-semibold py-3 px-6 rounded-lg shadow-sm hover:bg-gray-50 transition" onclick="window.print(); return false;">
                                <i class="fas fa-print mr-2"></i> Print Report
                            </a>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <!-- Error or invalid ID -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="bg-gradient-to-r from-red-500 to-red-600 p-6">
                        <div class="flex items-center justify-center">
                            <div class="bg-white rounded-full p-3 mr-4">
                                <i class="fas fa-exclamation-triangle text-red-500 text-3xl"></i>
                            </div>
                            <h1 class="text-white text-2xl font-bold">Issue Not Found</h1>
                        </div>
                    </div>
                    
                    <div class="p-6 text-center">
                        <p class="text-gray-600 mb-6">We couldn't find the issue report you're looking for. It may have been removed or the ID is invalid.</p>
                        
                        <a href="washroom.html" class="inline-block bg-gradient-to-r from-blue-600 to-cyan-500 text-white font-semibold py-3 px-6 rounded-lg shadow-md hover:from-blue-700 hover:to-cyan-600 transition">
                            Return to Washroom Management
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-blue-900 text-white pt-16 pb-8 mt-12">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-12">
                <div>
                    <div class="flex items-center space-x-2 mb-6">
                        <i class="fas fa-heartbeat text-2xl text-cyan-400"></i>
                        <span class="text-xl font-bold">MediCare Plus</span>
                    </div>
                    <p class="text-blue-200 mb-6">Providing quality healthcare services with compassion and care for over 25 years.</p>
                </div>
                
                <!-- Footer content abbreviated for brevity -->
                <div>
                    <h3 class="text-lg font-bold mb-6">Quick Links</h3>
                    <ul class="space-y-3">
                        <li><a href="index.html" class="text-blue-200 hover:text-white transition duration-300">Home</a></li>
                        <li><a href="washroom.html" class="text-blue-200 hover:text-white transition duration-300">Washroom Management</a></li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="text-lg font-bold mb-6">Contact</h3>
                    <p class="text-blue-200">
                        <i class="fas fa-envelope mr-2"></i> support@medicareplus.com
                    </p>
                </div>
            </div>
            
            <hr class="border-blue-800 mb-8">
            
            <div class="text-center">
                <p class="text-blue-300 text-sm">&copy; 2025 MediCare Plus Hospital. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Mobile menu toggle
        document.getElementById('mobile-menu-button').addEventListener('click', function() {
            const mobileMenu = document.getElementById('mobile-menu');
            mobileMenu.classList.toggle('hidden');
        });
    </script>
</body>
</html>