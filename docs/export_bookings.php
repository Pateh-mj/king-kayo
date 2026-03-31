<?php
session_start();
require 'db_connect.php';

// Security Check
if (!isset($_SESSION['user_id'])) {
    header('HTTP/1.1 403 Forbidden');
    exit('Unauthorized access.');
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=KingKayo_Full_Report_' . date('Y-m-d') . '.csv');

// Open the output stream
$output = fopen('php://output', 'w');

// Set CSV Headers
fputcsv($output, ['ID', 'Client Name', 'Phone', 'Service Category', 'Scope/Requirements', 'Status', 'Booking Date']);

$role = $_SESSION['role'];

try {
    if ($role === 'super_admin') {
        // Standardized to match the Supabase table columns
        $query = "SELECT id, client_name, client_phone, service_category, scope, status, booking_date 
                  FROM bookings 
                  WHERE status != 'Archived' 
                  ORDER BY created_at DESC";
        $stmt = $pdo->query($query);
    } else {
        $category_map = [
            'waste_mgr' => 'Medical Waste', 
            'fabrication_mgr' => 'Fabrication', 
            'cleaning_mgr' => 'Cleaning'
        ];
        $target_category = $category_map[$role] ?? 'Unknown';
        
        $query = "SELECT id, client_name, client_phone, service_category, scope, status, booking_date 
                  FROM bookings 
                  WHERE service_category = :category AND status != 'Archived' 
                  ORDER BY created_at DESC";
        $stmt = $pdo->prepare($query);
        $stmt->execute([':category' => $target_category]);
    }

    // Loop through results and write to CSV
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, $row);
    }

} catch (PDOException $e) {
    // Fail silently in the CSV stream if a database error occurs
    error_log("Export Error: " . $e->getMessage());
}

fclose($output);
exit;