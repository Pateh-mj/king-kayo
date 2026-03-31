<?php
// Supabase Database Credentials
// Find these in: Project Settings > Database > Connection string > PHP (or URI)
$host = 'https://cvehakgzshrlpmoehino.supabase.co'; // Example host
$port = '5432';
$dbname = 'postgres';
$user = 'postgres.cvehakgzshrlpmoehino';
$password = 'KingKayoGroup321';

try {
    // Create the DSN (Data Source Name) for PostgreSQL
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;user=$user;password=$password";

    // Initialize PDO with error handling and fetch modes
    $pdo = new PDO($dsn);
    
    // Ensure errors are thrown as exceptions for better debugging
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Log the error and stop the script if connection fails
    error_log("Connection failed: " . $e->getMessage());
    die("Database connection established failure. Please check logs.");
}
?>