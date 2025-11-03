<?php
// Enable error reporting for development (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Set header for JSON response
header('Content-Type: application/json');

// Database configuration for InfinityFree
// Get these values from your InfinityFree Control Panel > MySQL Databases
$servername = "sql200.infinityfree.com";  // Change to your host (e.g., sql305.infinityfree.com)
$username = "if0_40320548";  // Your database username (starts with if0_)
$password = "l7fHf5cziYI1";  // Your database password - FILL THIS IN
$dbname = "if0_40320548_students";  // Your database name

// Response array
$response = array(
    'success' => false,
    'message' => '',
    'data' => array()
);

try {
    // Create database connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }
    
    // Set charset
    $conn->set_charset("utf8mb4");
    
    // Check if form is submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        
        // Validate and sanitize input data
        $firstName = sanitizeInput($_POST['firstName'] ?? '');
        $lastName = sanitizeInput($_POST['lastName'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $phone = sanitizeInput($_POST['phone'] ?? '');
        $dob = sanitizeInput($_POST['dob'] ?? '');
        $gender = sanitizeInput($_POST['gender'] ?? '');
        $address = sanitizeInput($_POST['address'] ?? '');
        $city = sanitizeInput($_POST['city'] ?? '');
        $state = sanitizeInput($_POST['state'] ?? '');
        $zipcode = sanitizeInput($_POST['zipcode'] ?? '');
        $country = sanitizeInput($_POST['country'] ?? '');
        $qualification = sanitizeInput($_POST['qualification'] ?? '');
        $comments = sanitizeInput($_POST['comments'] ?? '');
        
        // Validation
        $errors = array();
        
        if (empty($firstName)) $errors[] = "First name is required";
        if (empty($lastName)) $errors[] = "Last name is required";
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Valid email is required";
        }
        if (empty($phone) || !preg_match('/^[0-9]{10}$/', $phone)) {
            $errors[] = "Valid 10-digit phone number is required";
        }
        if (empty($dob)) $errors[] = "Date of birth is required";
        if (empty($gender)) $errors[] = "Gender is required";
        if (empty($address)) $errors[] = "Address is required";
        if (empty($city)) $errors[] = "City is required";
        if (empty($state)) $errors[] = "State is required";
        if (empty($zipcode)) $errors[] = "Zip code is required";
        if (empty($country)) $errors[] = "Country is required";
        if (empty($qualification)) $errors[] = "Qualification is required";
        
        // If validation errors exist
        if (!empty($errors)) {
            throw new Exception(implode(", ", $errors));
        }
        
        // Check if email already exists
        $checkStmt = $conn->prepare("SELECT id FROM registrations WHERE email = ?");
        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows > 0) {
            throw new Exception("Email address already registered");
        }
        $checkStmt->close();
        
        // Prepare SQL statement
        $stmt = $conn->prepare("INSERT INTO registrations (first_name, last_name, email, phone, dob, gender, address, city, state, zipcode, country, qualification, comments, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $conn->error);
        }
        
        // Bind parameters
        $stmt->bind_param("sssssssssssss", 
            $firstName, $lastName, $email, $phone, $dob, $gender, 
            $address, $city, $state, $zipcode, $country, $qualification, $comments
        );
        
        // Execute statement
        if ($stmt->execute()) {
            $insertedId = $stmt->insert_id;
            
            // Success response with data
            $response['success'] = true;
            $response['message'] = "Registration successful";
            $response['data'] = array(
                'id' => $insertedId,
                'firstName' => $firstName,
                'lastName' => $lastName,
                'email' => $email,
                'phone' => $phone,
                'dob' => $dob,
                'gender' => $gender,
                'address' => $address,
                'city' => $city,
                'state' => $state,
                'zipcode' => $zipcode,
                'country' => $country,
                'qualification' => $qualification,
                'comments' => $comments,
                'createdAt' => date('Y-m-d H:i:s')
            );
            
            // Optional: Send email notification
            // sendEmailNotification($email, $firstName, $lastName, $insertedId);
            
        } else {
            throw new Exception("Error executing query: " . $stmt->error);
        }
        
        $stmt->close();
        
    } else {
        throw new Exception("Invalid request method");
    }
    
} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
    
    // Log error to file (optional)
    error_log(date('[Y-m-d H:i:s] ') . $e->getMessage() . "\n", 3, 'errors.log');
}

// Close database connection
if (isset($conn)) {
    $conn->close();
}

// Send JSON response
echo json_encode($response);

// Function to sanitize input
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Optional: Email notification function
function sendEmailNotification($email, $firstName, $lastName, $registrationId) {
    $to = $email;
    $subject = "Registration Confirmation - Application #" . $registrationId;
    $message = "Dear " . $firstName . " " . $lastName . ",\n\n";
    $message .= "Thank you for registering with us.\n";
    $message .= "Your registration ID is: #" . $registrationId . "\n\n";
    $message .= "Best regards,\nThe Registration Team";
    $headers = "From: noreply@example.com\r\n";
    $headers .= "Reply-To: support@example.com\r\n";
    
    mail($to, $subject, $message, $headers);
}
?>