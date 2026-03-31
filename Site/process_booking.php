<?php
require 'db_connect.php'; // Using the Supabase connection we just made

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Sanitize Basic Inputs
    $name = htmlspecialchars($_POST['name']);
    $phone = htmlspecialchars($_POST['phone']);
    $category = $_POST['service_category'];
    $date = $_POST['service_date'];
    $location = htmlspecialchars($_POST['location']);
    $notes = htmlspecialchars($_POST['notes']);

    // 2. Handle Dynamic Fields (Medical Waste or Fabrication)
    $extra_details = "";
    
    if ($category === "Medical Waste" && isset($_POST['waste_type'])) {
        $types = implode(", ", $_POST['waste_type']);
        $extra_details .= "Waste Types: $types. ";
    } 
    
    if ($category === "Fabrication" && isset($_POST['fabrication_type'])) {
        $fab_type = $_POST['fabrication_type'];
        $extra_details .= "Fabrication: $fab_type. ";
    }

    // Combine everything into the 'scope' field
    $full_scope = "Location: $location. " . $extra_details . "Notes: " . $notes;

    try {
        // 3. PostgreSQL SQL (No backticks!)
        $sql = "INSERT INTO bookings (client_name, client_phone, service_category, specific_requirements, booking_date) 
                VALUES (:name, :phone, :category, :specific_requirements, :date)";
        
        $stmt = $pdo->prepare($sql);
        
        $stmt->execute([
            ':name' => $name,
            ':phone' => $phone,
            ':category' => $category,
            ':scope' => $full_scope,
            ':date' => $date
        ]);

if ($result) {
    require 'email_functions.php';
    // Trigger the background alert
    sendBookingNotification($name, $category, $phone);
    
    echo "Booking Successful";
}

        // Success! Redirect to a thank you page
        header("Location: landing.html?status=success");
        exit();

    } catch (PDOException $e) {
        // Log the error for your IT eyes
        error_log("Supabase Insert Error: " . $e->getMessage());
        die("Submission failed. Please try again later.");
    }
}