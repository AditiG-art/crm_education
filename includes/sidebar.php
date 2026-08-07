<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$role = isset($_SESSION['role']) ? $_SESSION['role'] : 'admin';
?>
<div class="sidebar" id="mainSidebar">

    <div class="logo">
        <i class="fa-solid fa-graduation-cap"></i>
        <span>Smart Campus<br>CRM</span>
    </div>

    <div class="menu">

        <?php if($role == 'admin'): ?>
        <span class="sidebar-label">Main</span>

        <a href="../admin/dashboard.php" class="<?= $currentPage=='dashboard.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-chart-pie"></i> Dashboard
        </a>

        <a href="../admin/students.php" class="<?= $currentPage=='students.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-user-graduate"></i> Students
        </a>

        <a href="../admin/teachers.php" class="<?= $currentPage=='teachers.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-chalkboard-user"></i> Teachers
        </a>

        <a href="../admin/courses.php" class="<?= $currentPage=='courses.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-book-open"></i> Courses
        </a>

        <span class="sidebar-label">Academic</span>

        <a href="../admin/attendance.php" class="<?= $currentPage=='attendance.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-calendar-check"></i> Attendance
        </a>

        <a href="../admin/results.php" class="<?= $currentPage=='results.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-square-poll-vertical"></i> Results
        </a>

        <a href="../admin/achievements.php" class="<?= $currentPage=='achievements.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-trophy"></i> Achievements
            <span class="badge-pill">NEW</span>
        </a>

        <span class="sidebar-label">Management</span>

        <a href="../admin/announcements.php" class="<?= $currentPage=='announcements.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-bullhorn"></i> Announcements
        </a>

        <a href="../admin/profile.php" class="<?= $currentPage=='profile.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-id-card"></i> Profile
        </a>

        <?php elseif($role == 'teacher'): ?>
        <span class="sidebar-label">Teacher Portal</span>

        <a href="../teacher/dashboard.php" class="<?= $currentPage=='dashboard.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-chart-pie"></i> Dashboard
        </a>

        <a href="../teacher/timetable.php" class="<?= $currentPage=='timetable.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-table-cells"></i> Timetable
            <span class="badge-pill">NEW</span>
        </a>

        <a href="../teacher/marks_schedule.php" class="<?= $currentPage=='marks_schedule.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-calendar-days"></i> Marks Schedule
            <span class="badge-pill">NEW</span>
        </a>

        <span class="sidebar-label">Students</span>

        <a href="../admin/students.php" class="<?= $currentPage=='students.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-user-graduate"></i> Student Roster
        </a>

        <a href="../admin/mark_attendance.php" class="<?= $currentPage=='mark_attendance.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-calendar-check"></i> Mark Attendance
        </a>

        <a href="../admin/results.php" class="<?= $currentPage=='results.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-square-poll-vertical"></i> Upload Results
        </a>

        <?php elseif($role == 'student'): ?>
        <span class="sidebar-label">Student Portal</span>

        <a href="../student/dashboard.php" class="<?= $currentPage=='dashboard.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-chart-pie"></i> Dashboard
        </a>

        <a href="../student/attendance.php" class="<?= $currentPage=='attendance.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-calendar-check"></i> My Attendance
        </a>

        <a href="../student/results.php" class="<?= $currentPage=='results.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-chart-bar"></i> My Results
        </a>

        <a href="../student/profile.php" class="<?= $currentPage=='profile.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-id-card"></i> My Profile
        </a>
        <?php endif; ?>

    </div>

    <div class="sidebar-footer">
        <a href="../logout.php">
            <i class="fa-solid fa-right-from-bracket"></i> Logout
        </a>
    </div>

</div>