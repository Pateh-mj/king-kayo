<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$role = $_SESSION['role'];
$username = $_SESSION['name'] ?? 'Operator';
$today = date('Y-m-d');

// --- STATS LOGIC (PostgreSQL Syntax) ---
// We cast created_at to a date type using ::date
$reportStmt = $pdo->prepare("SELECT service_category, COUNT(*) as count FROM bookings WHERE created_at::date = ? GROUP BY service_category");
$reportStmt->execute([$today]);
$dailyStats = $reportStmt->fetchAll() ?: [];
$totalToday = array_sum(array_column($dailyStats, 'count')) ?: 0;

// --- ROLE-BASED FILTERING ---
if ($role === 'super_admin') {
    // Note: 'bookings' doesn't need backticks in Postgres
    $stmt = $pdo->query("SELECT * FROM bookings WHERE status != 'Archived' ORDER BY created_at DESC");
} else {
    $category_map = [
        'waste_mgr' => 'Medical Waste',
        'fabrication_mgr' => 'Fabrication',
        'cleaning_mgr' => 'Cleaning'
    ];
    $target_category = $category_map[$role] ?? 'Unknown';
    $stmt = $pdo->prepare("SELECT * FROM bookings WHERE service_category = ? AND status != 'Archived' ORDER BY created_at DESC");
    $stmt->execute([$target_category]);
}
$bookings = $stmt->fetchAll() ?: [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Command Center | King Kayo</title>
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
        body { font-family: 'Inter', sans-serif; background-color: #F8FAFC; color: #0F172A; overflow-x: hidden; }
        .serif-logo { font-family: 'Playfair Display', serif; }
        .glass-header { background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(12px); border-bottom: 1px solid #E2E8F0; }
        .sidebar-transition { transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        @media (max-width: 1024px) { .sidebar-hidden { transform: translateX(-100%); } }
        @media print { .no-print { display: none !important; } }
    </style>
</head>
<body class="flex min-h-screen">

    <aside id="sidebar" class="sidebar-transition sidebar-hidden lg:transform-none fixed lg:sticky top-0 left-0 w-72 h-screen bg-slate-950 text-white z-50 flex flex-col p-8 no-print shadow-2xl">
        <div class="flex items-center justify-between mb-12">
            <h1 class="serif-logo text-3xl text-gold italic uppercase tracking-tighter">King~Kayo</h1>
            <button onclick="toggleSidebar()" class="lg:hidden text-slate-400 p-2">✕</button>
        </div>
        <nav class="flex-grow space-y-3">
            <p class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] mb-4">Operations Hub</p>
            <a href="admin.php" class="flex items-center gap-3 px-5 py-3 bg-gold/10 text-gold rounded-xl font-bold text-sm border border-gold/20">Dashboard</a>
            <?php if ($role === 'super_admin'): ?>
                <a href="users.php" class="flex items-center gap-3 px-5 py-3 text-slate-400 hover:bg-white/5 hover:text-white rounded-2xl font-semibold text-sm transition">User Management</a>
            <?php endif; ?>
        </nav>
        <div class="mt-auto pt-6 border-t border-white/10">
            <div class="flex items-center gap-4 mb-8">
                <div class="w-10 h-10 rounded-full bg-gold flex items-center justify-center text-slate-950 font-black">
                    <?php echo strtoupper(substr($username, 0, 1)); ?>
                </div>
                <div class="overflow-hidden">
                    <p class="text-sm font-black truncate"><?php echo $username; ?></p>
                    <p class="text-[10px] text-slate-400 uppercase font-bold tracking-widest"><?php echo str_replace('_', ' ', $role); ?></p>
                </div>
            </div>
            <a href="logout.php" class="block w-full text-center py-4 bg-red-600/10 text-red-500 text-[11px] font-black uppercase tracking-[0.3em] rounded-xl hover:bg-red-600 hover:text-white transition">Log Out</a>
        </div>
    </aside>

    <div class="flex-1 flex flex-col min-w-0">
        <header class="h-20 glass-header flex items-center justify-between px-6 lg:px-12 sticky top-0 z-30 no-print">
            <div class="flex items-center gap-4">
                <button onclick="toggleSidebar()" class="lg:hidden p-3 bg-slate-100 rounded-xl">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16"><path d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5z"/></svg>
                </button>
                <h2 class="text-xs font-black uppercase tracking-[0.2em] text-slate-500">Live Control Panel</h2>
            </div>
            <div class="flex items-center gap-3">
                <span class="hidden md:block text-[10px] font-bold text-slate-400 uppercase">System Time: <?php echo date('H:i'); ?></span>
                <button onclick="toggleDailyReport()" class="bg-slate-900 text-gold px-5 py-2.5 text-[11px] font-black uppercase tracking-widest rounded-xl shadow-lg hover:bg-gold hover:text-white transition">Metrics</button>
                <a href="export_bookings.php" class="flex items-center space-x-2 px-4 py-2 bg-primary-green text-accent-gold border border-accent-gold/20 rounded-xl hover:bg-navy-obsidian transition-all duration-300">
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
        <path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2zM9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5v2z"/>
        <path d="M4.5 10.5A.5.5 0 0 1 5 10h3a.5.5 0 0 1 0 1H5a.5.5 0 0 1-.5-.5zm0 2A.5.5 0 0 1 5 12h3a.5.5 0 0 1 0 1H5a.5.5 0 0 1-.5-.5zm0-4A.5.5 0 0 1 5 8h6a.5.5 0 0 1 0 1H5a.5.5 0 0 1-.5-.5z"/>
    </svg>
    <span class="text-[10px] font-bold uppercase tracking-widest">Export to Excel</span>
</a>
            </div>
        </header>

        <main class="flex-1 p-4 lg:p-12 space-y-10">
            <div id="dailyReportBox" class="hidden animate-in slide-in-from-top duration-300">
                <div class="bg-white border-l-[8px] border-gold p-8 rounded-3xl shadow-xl border border-slate-200">
                    <h3 class="text-m font-black uppercase tracking-widest mb-6 text-slate-400">Daily Summary</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                        <div class="bg-slate-50 p-6 rounded-2xl border border-slate-100">
                            <p class="text-[10px] font-black text-slate-400 uppercase mb-1">Total Today</p>
                            <p class="text-3xl font-black text-slate-900"><?php echo $totalToday; ?></p>
                        </div>
                        <?php foreach($dailyStats as $stat): ?>
                        <div class="bg-slate-50 p-6 rounded-2xl border border-slate-100">
                            <p class="text-[10px] font-black text-slate-400 uppercase mb-1"><?php echo $stat['service_category']; ?></p>
                            <p class="text-3xl font-black text-gold"><?php echo $stat['count']; ?></p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="no-print flex flex-col md:flex-row gap-4 items-center">
                <input type="text" id="adminSearch" onkeyup="filterTable()" placeholder="SEARCH CLIENT OR SERVICE..." 
                       class="w-full max-w-xl bg-white border border-slate-200 px-8 py-5 text-xs font-black uppercase tracking-widest rounded-xl shadow-sm focus:ring-4 focus:ring-gold/10 focus:border-gold outline-none">
                
                <div class="flex gap-2">
                    <button onclick="filterByStatus('all')" class="px-4 py-2 text-[10px] font-bold uppercase bg-slate-900 text-white rounded-lg">All</button>
                    <button onclick="filterByStatus('Confirmed')" class="px-4 py-2 text-[10px] font-bold uppercase bg-white border border-slate-200 text-slate-500 rounded-lg hover:bg-green-50 hover:text-green-600 transition">Confirmed</button>
                    <button onclick="filterByStatus('Pending')" class="px-4 py-2 text-[10px] font-bold uppercase bg-white border border-slate-200 text-slate-500 rounded-lg hover:bg-amber-50 hover:text-amber-600 transition">Pending</button>
                </div>
            </div>

            <div class="hidden md:block bg-white rounded-2xl border border-slate-200 shadow-2xl overflow-hidden">
                <table class="w-full text-left border-collapse" id="bookingsTable">
                    <thead class="bg-slate-50/50">
                        <tr>
                            <th class="p-8 text-[10px] font-black uppercase tracking-widest text-slate-800">Client Profile</th>
                            <th class="p-8 text-[10px] font-black uppercase tracking-widest text-slate-800">Scope Preview</th>
                            <th class="p-8 text-[10px] font-black uppercase tracking-widest text-slate-800 text-center">Status</th>
                            <th class="p-8 text-[10px] font-black uppercase tracking-widest text-slate-800 text-right">Dispatch Controls</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($bookings as $row): 
                            $status = $row['status'] ?: 'Pending';
                        ?>
                        <tr id="row-<?php echo $row['id']; ?>" class="booking-row hover:bg-slate-50/50 transition-all group" data-status="<?php echo $status; ?>">
                            <td class="p-8">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 font-bold text-xs">
                                        <?php echo substr($row['client_name'], 0, 2); ?>
                                    </div>
                                    <div>
                                        <p class="font-extrabold text-sm text-slate-900 uppercase mb-1"><?php echo htmlspecialchars($row['client_name']); ?></p>
                                        <button onclick="copyToClipboard('<?php echo $row['client_phone']; ?>')" class="text-[10px] text-slate-400 font-bold hover:text-gold"><?php echo $row['client_phone']; ?> 📋</button>
                                    </div>
                                </div>
                            </td>
                            <td class="p-8">
                                <div class="cursor-pointer" onclick="openScope('<?php echo addslashes($row['client_name']); ?>', '<?php echo addslashes($row['specific_requirements']); ?>', '<?php echo $row['service_category']; ?>', '<?php echo $row['service_date']; ?>')">
                                    <p class="text-xs text-slate-500 line-clamp-1 italic italic italic">"<?php echo htmlspecialchars($row['specific_requirements']); ?>"</p>
                                    <span class="text-[9px] font-black uppercase text-gold">Details [+]</span>
                                </div>
                            </td>
                            <td class="p-8 text-center">
                                <span class="text-[9px] font-black uppercase tracking-widest px-5 py-2 rounded-full <?php echo $status == 'Confirmed' ? 'bg-green-600 text-white' : 'bg-slate-700 text-white'; ?>">
                                    <?php echo $status; ?>
                                </span>
                            </td>
                            <td class="p-8 text-right">
                                <div class="flex justify-end gap-2">
                                    <?php 
                                    // Define your message - you can include dynamic data like the client name or service
                                    $message = "Hello " . $row['client_name'] . ", this is King Kayo Group. We are reaching out regarding your inquiry for " . $row['service_category'] . ".";
                                    $encodedMessage = rawurlencode($message);
                                    $phoneNumber = preg_replace('/[^0-9]/', '', $row['client_phone']);
                                    ?>

                                    <a href="https://wa.me/<?php echo $phoneNumber; ?>?text=<?php echo $encodedMessage; ?>" 
                                    target="_blank" 
                                    class="p-3 bg-green-500 text-white rounded-xl hover:scale-110 transition shadow-md">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                                            <path d="M13.601 2.326A7.854 7.854 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.933 7.933 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.898 7.898 0 0 0 13.6 2.326z"/>
                                        </svg>
                                    </a>
                                    <button onclick="processStatus('<?php echo $row['id']; ?>', 'Confirmed')"class="px-4 py-2.5 bg-slate-800 text-white text-[9px] font-black uppercase rounded-xl hover:bg-green-600 transition">Approve</button>
                                    <button onclick="processStatus('<?php echo $row['id']; ?>', 'Archived')" class="px-4 py-2.5 bg-white border border-slate-200 text-slate-400 text-[9px] font-black uppercase rounded-xl hover:bg-red-50 hover:text-red-500 transition">Archive</button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="md:hidden divide-y divide-slate-200">
                <?php foreach ($bookings as $row): 
                    $status = $row['status'] ?: 'Pending';
                ?>
                <div class="booking-row p-6 space-y-4 bg-white mb-4 rounded-2xl shadow-sm border border-slate-100" data-status="<?php echo $status; ?>">
                    <div class="flex justify-between items-start">
                        <div>
                            <h4 class="font-black text-sm uppercase text-slate-900"><?php echo htmlspecialchars($row['client_name']); ?></h4>
                            <p class="text-[10px] text-gold font-black uppercase tracking-widest"><?php echo $row['service_category']; ?></p>
                        </div>
                        <span class="text-[9px] font-black uppercase px-3 py-1 rounded-full <?php echo $status == 'Confirmed' ? 'bg-green-600 text-white' : 'bg-slate-700 text-white'; ?>">
                            <?php echo $status; ?>
                        </span>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <button onclick="openScope('<?php echo addslashes($row['client_name']); ?>', '<?php echo addslashes($row['specific_requirements']); ?>', '<?php echo $row['service_category']; ?>', '<?php echo $row['service_date']; ?>')" class="py-3 bg-slate-50 border border-slate-200 rounded-xl text-[10px] font-black uppercase text-slate-900">Scope</button>
                        <button onclick="processStatus('<?php echo $row['id']; ?>', 'Confirmed')"class="py-3 bg-slate-800 text-white rounded-xl text-[10px] font-black uppercase">Approve</button>
                    </div>
                    <button onclick="processStatus('<?php echo $row['id']; ?>', 'Archived')" class="w-full py-3 text-slate-400 text-[9px] font-black uppercase border border-dashed border-slate-200 rounded-xl">Move to Archive</button>
                </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>

    <div id="scopeModal" class="hidden fixed inset-0 bg-slate-950/90 z-[100] flex items-center justify-center p-6 no-print">
        <div class="bg-white w-full max-w-xl rounded-3xl p-10 shadow-2xl relative animate-in zoom-in duration-200">
            <h2 id="modalTitle" class="text-2xl font-black uppercase mb-1">...</h2>
            <p id="modalCategory" class="text-[10px] font-black text-gold uppercase tracking-widest mb-6"></p>
            <div class="bg-slate-50 p-6 rounded-2xl border border-slate-100 mb-8 h-48 overflow-y-auto text-sm leading-relaxed" id="modalContent"></div>
            <div class="grid grid-cols-2 gap-4">
                <button onclick="window.print()" class="py-4 bg-slate-900 text-white rounded-xl font-black text-[10px] uppercase tracking-widest">Print Order</button>
                <button onclick="closeModal()" class="py-4 bg-slate-100 text-slate-500 rounded-xl font-black text-[10px] uppercase tracking-widest">Close</button>
            </div>
        </div>
    </div>

    <script>
        function toggleSidebar() { document.getElementById('sidebar').classList.toggle('sidebar-hidden'); }
        function toggleDailyReport() { document.getElementById('dailyReportBox').classList.toggle('hidden'); }
        function closeModal() { document.getElementById('scopeModal').classList.add('hidden'); }

        function filterTable() {
            const term = document.getElementById("adminSearch").value.toUpperCase();
            document.querySelectorAll(".booking-row").forEach(row => {
                row.style.display = row.innerText.toUpperCase().includes(term) ? "" : "none";
            });
        }

        function filterByStatus(status) {
            document.querySelectorAll(".booking-row").forEach(row => {
                row.style.display = (status === 'all' || row.getAttribute('data-status') === status) ? "" : "none";
            });
        }

        function openScope(title, content, category, date) {
            document.getElementById('modalTitle').innerText = title;
            document.getElementById('modalContent').innerText = content;
            document.getElementById('modalCategory').innerText = category + " | Est: " + date;
            document.getElementById('scopeModal').classList.remove('hidden');
        }

        function processStatus(id, status) {
            if(!confirm(`Confirm update to ${status}?`)) return;
            fetch('update_status.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `id=${id}&status=${status}`
            }).then(() => location.reload());
        }

        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => alert('Copied to clipboard!'));
        }
    </script>
</body>
</html>