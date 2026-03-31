<?php
session_start();
require 'db_connect.php';

// 1. Security Check
if (!isset($_SESSION['user_id'])) {
    header('HTTP/1.1 403 Forbidden');
    exit('Unauthorized access.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect the UUID string and the status
    $id = $_POST['id'] ?? null;
    $status = $_POST['status'] ?? null;
    $valid_statuses = ['Pending', 'Confirmed', 'Archived'];

    // 2. Validation
    if ($id && in_array($status, $valid_statuses)) {
        try {
            // Postgres logic: Standard SQL (no backticks)
            $stmt = $pdo->prepare("UPDATE bookings SET status = :status WHERE id = :id");
            
            // Explicitly binding helps PDO handle the UUID string correctly
            $stmt->execute([
                ':status' => $status,
                ':id'     => $id
            ]);

            // 3. Verify that the update actually happened
            if ($stmt->rowCount() > 0) {
                echo "Success";
            } else {
                // This happens if the UUID doesn't exist in the table
                header('HTTP/1.1 404 Not Found');
                echo "Record not found.";
            }

        } catch (PDOException $e) {
            error_log("Supabase Update Error: " . $e->getMessage());
            header('HTTP/1.1 500 Internal Server Error');
            echo "Operational failure. Logged.";
        }
    } else {
        header('HTTP/1.1 400 Bad Request');
        echo "Invalid parameters.";
    }
}
?>