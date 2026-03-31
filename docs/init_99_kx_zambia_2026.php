<?php
require 'db_connect.php';

// Credentials - Change these immediately after your first successful login
$fullName = "Patson Tembo";
$username = "admin";
$password = "KingKayo2026"; 

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

try {
    $pdo->beginTransaction();

    // 1. Wipe old admin to prevent conflicts
    $stmt1 = $pdo->prepare("DELETE FROM users WHERE username = ?");
    $stmt1->execute([$username]);

    // 2. Insert new Super Admin
    $sql = "INSERT INTO users (full_name, username, password_hash, role) VALUES (?, ?, ?, 'super_admin')";
    $stmt2 = $pdo->prepare($sql);
    $stmt2->execute([$fullName, $username, $hashedPassword]);

    $pdo->commit();

    echo "<div style='font-family:sans-serif; padding:40px; background:#f8fafc; border-radius:20px; border:1px solid #e2e8f0; max-width:500px; margin:50px auto; text-align:center;'>";
    echo "<h2 style='color:#D4AF37; margin-bottom:10px;'>System Initialized</h2>";
    echo "<p style='color:#64748b; font-size:14px;'>Super Admin access granted to <strong>$fullName</strong>.</p>";
    echo "<div style='background:#fff; padding:15px; border-radius:12px; margin:20px 0; border:1px inset #eee;'>";
    echo "<code style='color:#0f172a;'>User: $username</code><br>";
    echo "<code style='color:#0f172a;'>Pass: [HIDDEN]</code>";
    echo "</div>";
    echo "<a href='login.php' style='display:inline-block; padding:12px 24px; background:#0f172a; color:#D4AF37; text-decoration:none; border-radius:10px; font-weight:bold; font-size:12px; text-transform:uppercase;'>Enter Dashboard</a>";
    echo "</div>";
    
} catch (PDOException $e) {
    if ($pdo->inTransaction()) { $pdo->rollBack(); }
    die("Critical Setup Failure: " . $e->getMessage());
}
?>