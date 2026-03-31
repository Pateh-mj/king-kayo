<?php
session_start();
require 'db_connect.php'; // Our new Supabase PDO connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['username'];
    $pass = $_POST['password'];

    try {
        // PostgreSQL query - standardizing column names
        // Note: Ensure your Supabase table has: username, password_hash, role, full_name
        $stmt = $pdo->prepare("SELECT id, username, password_hash, role, full_name FROM users WHERE username = :username LIMIT 1");
        $stmt->execute([':username' => $user]);
        $account = $stmt->fetch();

        // Verify hash (Postgres stores these as TEXT, which works perfectly with password_verify)
        if ($account && password_verify($pass, $account['password_hash'])) {
            // Regeneration prevents Session Fixation attacks
            session_regenerate_id();

            $_SESSION['user_id'] = $account['id']; // This will be a UUID string from Supabase
            $_SESSION['role'] = $account['role'];
            $_SESSION['name'] = $account['full_name'];
            
            header("Location: admin.php");
            exit;
        } else {
            $error = "Invalid Credentials Access Denied.";
        }
    } catch (PDOException $e) {
        error_log("Login Error: " . $e->getMessage());
        $error = "System connectivity issue. Please contact IT.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login | King Kayo Group</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-slate-950 flex items-center justify-center min-h-screen p-6">
    <div class="max-w-md w-full bg-white p-10 shadow-2xl border-t-8 border-gold">
        <div class="mb-10 text-center">
            <h1 class="text-3xl font-black tracking-tighter text-slate-950 uppercase">King~Kayo</h1>
            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mt-2">Operational Command Access</p>
        </div>

        <?php if(isset($error)): ?>
            <div class="bg-red-50 text-red-600 p-4 mb-6 text-xs font-bold uppercase tracking-widest border-l-4 border-red-600">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-6">
            <div>
                <label class="block text-[10px] font-black uppercase tracking-widest text-slate-500 mb-2">Username</label>
                <input type="text" name="username" required class="w-full border-2 border-slate-200 px-4 py-3 text-sm font-bold focus:border-gold outline-none">
            </div>
            <div>
                <label class="block text-[10px] font-black uppercase tracking-widest text-slate-500 mb-2">Security Password</label>
                <input type="password" name="password" required class="w-full border-2 border-slate-200 px-4 py-3 text-sm font-bold focus:border-gold outline-none">
            </div>
            <button type="submit" class="w-full bg-slate-950 text-gold py-4 text-xs font-black uppercase tracking-[0.3em] hover:bg-gold hover:text-slate-950 transition-all">
                Authenticate
            </button>
        </form>
    </div>
</body>
</html>