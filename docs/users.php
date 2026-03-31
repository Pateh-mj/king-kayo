<?php
session_start();
require 'db_connect.php';

/**
 * 1. SECURITY LOCK: Super Admin Only
 */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'super_admin') {
    header("Location: admin.php"); 
    exit;
}

$role_session = $_SESSION['role'];
$username_session = $_SESSION['name'] ?? 'Admin';
$success = null;
$error = null;

/**
 * 2. LOGIC: Create New User (Operator)
 * Supabase handles UUID generation automatically if your schema uses gen_random_uuid()
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_user'])) {
    $name = trim($_POST['full_name']);
    $user = trim($_POST['username']);
    $pass = $_POST['password'];
    $role_to_assign = $_POST['role'];

    if (strlen($pass) < 6) {
        $error = "Security Protocol: Password must be at least 6 characters.";
    } else {
        $pass_hash = password_hash($pass, PASSWORD_DEFAULT);
        try {
            // Standardized SQL for PostgreSQL
            $stmt = $pdo->prepare("INSERT INTO users (full_name, username, password_hash, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $user, $pass_hash, $role_to_assign]);
            $success = "Operator profile for $name authorized successfully.";
        } catch (PDOException $e) {
            // Log real error for debugging, show generic for security
            error_log($e->getMessage());
            $error = "System Conflict: Username '@$user' is already registered or system is busy.";
        }
    }
}

/**
 * 3. LOGIC: Revoke Access (Delete User)
 */
if (isset($_GET['delete'])) {
    $deleteId = $_GET['delete']; // REMOVED (int) casting as IDs are now strings (UUIDs)
    
    // Prevent self-deletion: String comparison for UUIDs
    if ($deleteId !== $_SESSION['user_id']) {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$deleteId]);
        header("Location: users.php?msg=revoked");
        exit;
    }
}

/**
 * 4. FETCH DATA
 */
$stmt = $pdo->query("SELECT id, full_name, username, role FROM users ORDER BY role ASC, full_name ASC");
$allUsers = $stmt->fetchAll() ?: [];

// UI Helper for Role Badges
$roleColors = [
    'super_admin'     => 'bg-gold text-slate-950',
    'waste_mgr'       => 'bg-slate-100 text-slate-600',
    'fabrication_mgr' => 'bg-slate-100 text-slate-600',
    'cleaning_mgr'    => 'bg-slate-100 text-slate-600'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Identity Control | King Kayo</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&family=Playfair+Display:wght@900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { gold: '#D4AF37' }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #F8FAFC; color: #0F172A; }
        .serif-logo { font-family: 'Playfair Display', serif; }
        .glass-header { background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(12px); border-bottom: 1px solid #E2E8F0; }
        .sidebar-transition { transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        @media (max-width: 1024px) { .sidebar-hidden { transform: translateX(-100%); } }
        
        .btn-glow { box-shadow: 0 10px 25px -10px rgba(212, 175, 55, 0.4); }
        .btn-glow:hover { box-shadow: 0 15px 30px -5px rgba(212, 175, 55, 0.6); transform: translateY(-1px); }
    </style>
</head>
<body class="flex min-h-screen">

    <div id="sidebarOverlay" onclick="toggleSidebar()" class="fixed inset-0 bg-slate-950/60 z-40 hidden lg:hidden"></div>

    <aside id="sidebar" class="sidebar-transition sidebar-hidden lg:transform-none fixed lg:sticky top-0 left-0 w-72 h-screen bg-slate-950 text-white z-50 flex flex-col p-8 shadow-2xl">
        <div class="flex items-center justify-between mb-12">
            <h1 class="serif-logo text-3xl text-gold italic uppercase tracking-tighter">King~Kayo</h1>
            <button onclick="toggleSidebar()" class="lg:hidden text-slate-400 p-2">✕</button>
        </div>

        <nav class="flex-grow space-y-3">
            <p class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] mb-4">Operations Hub</p>
            <a href="admin.php" class="flex items-center gap-3 px-5 py-3 text-slate-400 hover:bg-white/5 hover:text-white rounded-2xl font-semibold text-sm transition">
                Dashboard
            </a>
            <a href="users.php" class="flex items-center gap-3 px-5 py-3 bg-gold/10 text-gold rounded-2xl font-bold text-sm border border-gold/20">
                User Management
            </a>
        </nav>

        <div class="mt-auto pt-6 border-t border-white/10">
            <div class="flex items-center gap-4 mb-8">
                <div class="w-10 h-10 rounded-full bg-gold flex items-center justify-center text-slate-950 font-black shadow-lg shadow-gold/20">
                    <?php echo strtoupper(substr($username_session, 0, 1)); ?>
                </div>
                <div class="overflow-hidden">
                    <p class="text-xs font-black truncate"><?php echo $username_session; ?></p>
                    <p class="text-[10px] text-slate-500 uppercase font-bold mt-1 tracking-tighter">System Administrator</p>
                </div>
            </div>
            <a href="logout.php" class="block w-full text-center py-4 bg-red-600/10 text-red-500 text-[10px] font-black uppercase tracking-[0.3em] rounded-xl hover:bg-red-600 hover:text-white transition-all">Terminate Session</a>
        </div>
    </aside>

    <div class="flex-1 flex flex-col min-w-0">
        <header class="h-20 glass-header flex items-center justify-between px-6 lg:px-12 sticky top-0 z-30">
            <button onclick="toggleSidebar()" class="lg:hidden p-3 bg-slate-100 rounded-xl text-slate-600">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5z"/></svg>
            </button>
            <h2 class="text-sm font-black uppercase tracking-tight text-slate-900">Identity Control <span class="text-slate-400">/ Permission Matrix</span></h2>
        </header>

        <main class="flex-1 p-4 lg:p-12">
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-10">
                
                <div class="xl:col-span-1">
                    <div class="bg-white p-8 lg:p-10 rounded-[2.5rem] border border-slate-200 shadow-xl sticky top-32">
                        <h3 class="text-xl font-black uppercase tracking-tighter mb-8 italic">Register Operator</h3>
                        
                        <?php if($success || isset($_GET['msg'])): ?>
                            <div class="mb-6 p-4 bg-green-50 text-green-700 text-[10px] font-black uppercase rounded-2xl border-l-4 border-green-600">
                                <?php echo $success ?: "Operator access revoked successfully."; ?>
                            </div>
                        <?php endif; ?>

                        <?php if($error): ?>
                            <div class="mb-6 p-4 bg-red-50 text-red-600 text-[10px] font-black uppercase rounded-2xl border-l-4 border-red-600">
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" class="space-y-6">
                            <input type="hidden" name="create_user" value="1">
                            <div>
                                <label class="block text-[10px] font-black uppercase text-slate-400 mb-2 px-1">Full Legal Name</label>
                                <input type="text" name="full_name" required placeholder="e.g. John Doe" class="w-full bg-slate-50 border-none px-6 py-4 text-sm font-bold rounded-2xl focus:ring-4 focus:ring-gold/10 outline-none">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black uppercase text-slate-400 mb-2 px-1">System Username</label>
                                <input type="text" name="username" required placeholder="j.doe" class="w-full bg-slate-50 border-none px-6 py-4 text-sm font-bold rounded-2xl focus:ring-4 focus:ring-gold/10 outline-none">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black uppercase text-slate-400 mb-2 px-1">Access Password</label>
                                <input type="password" name="password" required class="w-full bg-slate-50 border-none px-6 py-4 text-sm font-bold rounded-2xl focus:ring-4 focus:ring-gold/10 outline-none">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black uppercase text-slate-400 mb-2 px-1">Assign Division</label>
                                <select name="role" class="w-full bg-slate-50 border-none px-6 py-4 text-sm font-bold rounded-2xl focus:ring-4 focus:ring-gold/10 outline-none appearance-none">
                                    <option value="waste_mgr">Medical Waste Management</option>
                                    <option value="fabrication_mgr">Engineering & Fabrication</option>
                                    <option value="cleaning_mgr">Sanitation Services</option>
                                    <option value="super_admin">Super Administrator</option>
                                </select>
                            </div>
                            <button type="submit" class="btn-glow w-full py-5 bg-slate-950 text-gold text-[10px] font-black uppercase tracking-[0.4em] rounded-2xl shadow-2xl hover:bg-gold hover:text-slate-950 transition-all duration-300">
                                Authorize Access
                            </button>
                        </form>
                    </div>
                </div>

                <div class="xl:col-span-2">
                    <div class="bg-white rounded-[2.5rem] border border-slate-200 shadow-2xl overflow-hidden">
                        <div class="p-8 border-b border-slate-100 flex justify-between items-center">
                            <h3 class="text-lg font-black uppercase tracking-tighter">Authorized Personnel</h3>
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest bg-slate-50 px-3 py-1 rounded-full">Total: <?php echo count($allUsers); ?></span>
                        </div>

                        <div class="hidden md:block">
                            <table class="w-full text-left">
                                <thead class="bg-slate-50/50">
                                    <tr>
                                        <th class="p-8 text-[10px] font-black uppercase text-slate-400">Operator Profile</th>
                                        <th class="p-8 text-[10px] font-black uppercase text-slate-400">Clearance Level</th>
                                        <th class="p-8 text-[10px] font-black uppercase text-slate-400 text-right">Security</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <?php if(empty($allUsers)): ?>
                                        <tr><td colspan="3" class="p-20 text-center text-[10px] font-black uppercase text-slate-300 tracking-[0.3em]">No operators found</td></tr>
                                    <?php endif; ?>

                                    <?php foreach($allUsers as $u): 
                                        $badgeClass = $roleColors[$u['role']] ?? 'bg-slate-100 text-slate-600';
                                    ?>
                                    <tr class="hover:bg-slate-50/50 transition-all">
                                        <td class="p-8">
                                            <p class="font-black text-sm uppercase text-slate-900"><?php echo htmlspecialchars($u['full_name']); ?></p>
                                            <p class="text-[10px] text-slate-400 font-bold mt-1 italic">UID: @<?php echo htmlspecialchars($u['username']); ?></p>
                                        </td>
                                        <td class="p-8">
                                            <span class="<?php echo $badgeClass; ?> px-4 py-1.5 text-[9px] font-black uppercase rounded-full tracking-tighter">
                                                <?php echo str_replace('_', ' ', $u['role']); ?>
                                            </span>
                                        </td>
                                        <td class="p-8 text-right">
                                            <?php if($u['id'] != $_SESSION['user_id']): ?>
                                                <a href="?delete=<?php echo $u['id']; ?>" onclick="return confirm('Revoke system access for this operator?')" class="text-[10px] font-black uppercase text-red-400 hover:text-red-600 transition">Revoke</a>
                                            <?php else: ?>
                                                <span class="text-[10px] font-black uppercase text-slate-300">Primary Admin</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="md:hidden divide-y divide-slate-100">
                            <?php foreach($allUsers as $u): ?>
                            <div class="p-8 flex justify-between items-center">
                                <div>
                                    <h4 class="font-black text-sm uppercase text-slate-950"><?php echo htmlspecialchars($u['full_name']); ?></h4>
                                    <p class="text-[10px] text-slate-400 font-black uppercase mt-1 tracking-tighter"><?php echo str_replace('_', ' ', $u['role']); ?></p>
                                </div>
                                <?php if($u['id'] != $_SESSION['user_id']): ?>
                                    <a href="?delete=<?php echo $u['id']; ?>" class="p-3 bg-red-50 text-red-500 rounded-xl" title="Revoke">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/><path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/></svg>
                                    </a>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('sidebar-hidden');
            document.getElementById('sidebarOverlay').classList.toggle('hidden');
        }
    </script>
</body>
</html>