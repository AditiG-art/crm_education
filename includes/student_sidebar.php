<aside class="sidebar">
    <div class="logo">
        <i class="fa-solid fa-user-graduate"></i>
        <h4>Student Panel</h4>
    </div>
    <ul>
        <li class="<?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">
            <a href="dashboard.php">
                <i class="fa-solid fa-chart-line"></i> Dashboard
            </a>
        </li>
        <li class="<?= basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : '' ?>">
            <a href="profile.php">
                <i class="fa-solid fa-user"></i> My Profile
            </a>
        </li>
        <li class="<?= basename($_SERVER['PHP_SELF']) == 'attendance.php' ? 'active' : '' ?>">
            <a href="attendance.php">
                <i class="fa-solid fa-calendar-check"></i> Attendance
            </a>
        </li>
        <li class="<?= basename($_SERVER['PHP_SELF']) == 'results.php' ? 'active' : '' ?>">
            <a href="results.php">
                <i class="fa-solid fa-file-lines"></i> Results
            </a>
        </li>
        <li>
            <a href="../logout.php">
                <i class="fa-solid fa-right-from-bracket"></i> Logout
            </a>
        </li>
    </ul>
</aside>
