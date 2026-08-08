<?php

if(session_status() == PHP_SESSION_NONE)
{
    session_start();
}

$titleText = isset($pageTitle) ? $pageTitle : "Dashboard";
$userName = isset($_SESSION['user']) ? htmlspecialchars($_SESSION['user']) : "User";
$userRole = isset($_SESSION['role']) ? ucfirst(htmlspecialchars($_SESSION['role'])) : "Administrator";
$collegeName = isset($_SESSION['college_name']) ? htmlspecialchars($_SESSION['college_name']) : "Smart Campus Main Institute";

// Fetch dynamic notifications if DB connection exists
$notifications = [];
if(isset($conn) && $conn) {
    $recentStus = @mysqli_query($conn, "SELECT full_name, created_at FROM students ORDER BY id DESC LIMIT 3");
    if($recentStus) {
        while($r = mysqli_fetch_assoc($recentStus)) {
            $notifications[] = [
                'title' => 'New Student Enrolled',
                'desc' => $r['full_name'] . ' joined the institute',
                'time' => isset($r['created_at']) ? date('M d, H:i', strtotime($r['created_at'])) : 'Recently',
                'icon' => 'notification-icon-blue fa-user-plus'
            ];
        }
    }
}
if(empty($notifications)) {
    $notifications = [
        ['title' => 'System Ready', 'desc' => 'Smart Campus CRM running smoothly', 'time' => 'Just now', 'icon' => 'notification-icon-green fa-circle-check'],
        ['title' => 'New Student Enrolled', 'desc' => 'A new student completed registration', 'time' => '10 mins ago', 'icon' => 'notification-icon-blue fa-user-plus'],
        ['title' => 'Attendance Updated', 'desc' => 'Attendance logs updated for today', 'time' => '1 hour ago', 'icon' => 'notification-icon-amber fa-calendar-check']
    ];
}
?>

<div class="topbar">
    <div class="d-flex align-items-center gap-3">
        <button type="button" class="mobile-sidebar-toggle" id="mobileSidebarToggle" title="Toggle Sidebar">
            <i class="fa-solid fa-bars"></i>
        </button>
        <h3><?php echo htmlspecialchars($titleText); ?></h3>
    </div>

    <!-- Global Quick Search -->
    <div class="topbar-search d-none d-md-block">
        <i class="fa-solid fa-magnifying-glass search-icon"></i>
        <input type="text" id="globalSearchInput" placeholder="Quick search CRM..." autocomplete="off">
        <div class="search-results-dropdown" id="searchResultsDropdown"></div>
    </div>

    <div class="topbar-right">
        <!-- College Badge -->
        <div class="d-none d-md-flex align-items-center">
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill" style="font-size:12px;font-weight:600;">
                <i class="fa-solid fa-building-columns me-1"></i> <?php echo $collegeName; ?>
            </span>
        </div>

        <!-- Interactive Notifications -->
        <div class="crm-dropdown-container">
            <div class="notification notification-trigger" title="Notifications">
                <i class="fa-solid fa-bell"></i>
                <span><?php echo count($notifications); ?></span>
            </div>

            <div class="crm-dropdown-menu" id="notificationDropdown">
                <div class="crm-dropdown-header">
                    <h6>Notifications</h6>
                    <small class="badge bg-primary rounded-pill"><?php echo count($notifications); ?> New</small>
                </div>
                <div class="crm-notification-list">
                    <?php foreach($notifications as $n): ?>
                        <div class="notification-item unread">
                            <div class="notification-icon-wrapper <?php echo $n['icon']; ?>">
                                <i class="fa-solid <?php echo explode(' ', $n['icon'])[1] ?? 'fa-bell'; ?>"></i>
                            </div>
                            <div class="notification-content">
                                <p><strong><?php echo htmlspecialchars($n['title']); ?></strong></p>
                                <p class="text-secondary"><?php echo htmlspecialchars($n['desc']); ?></p>
                                <small><?php echo htmlspecialchars($n['time']); ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- User Profile Dropdown -->
        <div class="crm-dropdown-container">
            <div class="profile profile-trigger" style="cursor: pointer;" title="Account Menu">
                <i class="fa-solid fa-circle-user fa-2x"></i>
                <div class="d-none d-sm-block">
                    <strong><?php echo $userName; ?></strong>
                    <br>
                    <small><?php echo $userRole; ?></small>
                </div>
            </div>

            <div class="crm-dropdown-menu" id="profileDropdown" style="width: 240px;">
                <div class="crm-dropdown-header">
                    <div>
                        <strong><?php echo $userName; ?></strong>
                        <br><small class="text-muted"><?php echo $userRole; ?></small>
                    </div>
                </div>
                <div class="p-2">
                    <a href="profile.php" class="dropdown-item p-2 rounded d-flex align-items-center gap-2">
                        <i class="fa-solid fa-id-card text-primary"></i> My Profile
                    </a>
                    <hr class="my-1">
                    <a href="../logout.php" class="dropdown-item p-2 rounded text-danger d-flex align-items-center gap-2">
                        <i class="fa-solid fa-right-from-bracket"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $scriptPath = file_exists("assets/js/crm_advanced.js") ? "assets/js/crm_advanced.js" : "../assets/js/crm_advanced.js"; ?>
<script src="<?php echo $scriptPath; ?>"></script>