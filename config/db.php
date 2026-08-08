<?php

/*
 * Smart Campus CRM — Database Configuration
 * Supports Railway (MYSQL_URL / env vars) + local XAMPP (fallback)
 */

$host     = getenv('MYSQLHOST')     ?: getenv('MYSQL_HOST')     ?: getenv('DB_HOST')     ?: 'localhost';
$user     = getenv('MYSQLUSER')     ?: getenv('MYSQL_USER')     ?: getenv('DB_USER')     ?: 'root';
$password = getenv('MYSQLPASSWORD') ?: getenv('MYSQL_PASSWORD') ?: getenv('MYSQL_ROOT_PASSWORD') ?: getenv('DB_PASSWORD') ?: '';
$database = getenv('MYSQLDATABASE') ?: getenv('MYSQL_DATABASE') ?: getenv('DB_NAME')     ?: 'crm_education';
$port     = (int)(getenv('MYSQLPORT') ?: getenv('MYSQL_PORT') ?: getenv('DB_PORT') ?: 3306);

// Automatically parse Railway connection string if MYSQL_URL or DATABASE_URL is present
$urlStr = getenv('MYSQL_URL') ?: getenv('DATABASE_URL') ?: getenv('MYSQL_PRIVATE_URL') ?: getenv('MYSQL_PUBLIC_URL');
if($urlStr) {
    $parsed = parse_url($urlStr);
    if($parsed) {
        if(!empty($parsed['host'])) $host = $parsed['host'];
        if(!empty($parsed['user'])) $user = rawurldecode($parsed['user']);
        if(!empty($parsed['pass'])) $password = rawurldecode($parsed['pass']);
        if(!empty($parsed['port'])) $port = (int)$parsed['port'];
        if(!empty($parsed['path']) && $parsed['path'] !== '/') $database = ltrim($parsed['path'], '/');
    }
}

$isRailway = (bool)(getenv('RAILWAY_ENVIRONMENT') || getenv('MYSQLHOST') || getenv('MYSQL_HOST') || getenv('MYSQL_URL'));

// Connect directly to target database
$conn = @mysqli_connect($host, $user, $password, $database, $port);


// Local XAMPP fallback: if target DB does not exist yet, connect to server & create DB
if(!$conn && !$isRailway) {
    $conn = @mysqli_connect($host, $user, $password, '', $port);
    if($conn) {
        mysqli_query($conn, "CREATE DATABASE IF NOT EXISTS `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        mysqli_select_db($conn, $database);
    }
}

if(!$conn){
    die("Database Connection Failed: " . mysqli_connect_error());
}

$conn->set_charset("utf8mb4");


// Auto Table Schema Setup
$tables = [
    "CREATE TABLE IF NOT EXISTS colleges (
        id INT AUTO_INCREMENT PRIMARY KEY,
        college_name VARCHAR(150) UNIQUE NOT NULL,
        college_code VARCHAR(30) UNIQUE NOT NULL,
        address TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        college_id INT DEFAULT 1,
        college_name VARCHAR(150) DEFAULT 'Smart Campus Main Institute',
        first_name VARCHAR(50),
        last_name VARCHAR(50),
        full_name VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin','teacher','student','parent') NOT NULL DEFAULT 'student',
        status VARCHAR(20) DEFAULT 'Active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    "CREATE TABLE IF NOT EXISTS students (
        id INT AUTO_INCREMENT PRIMARY KEY,
        college_id INT DEFAULT 1,
        college_name VARCHAR(150) DEFAULT 'Smart Campus Main Institute',
        first_name VARCHAR(50),
        last_name VARCHAR(50),
        full_name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        phone VARCHAR(20),
        gender VARCHAR(10),
        date_of_birth DATE,
        course VARCHAR(100),
        address TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    "CREATE TABLE IF NOT EXISTS teachers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        college_id INT DEFAULT 1,
        college_name VARCHAR(150) DEFAULT 'Smart Campus Main Institute',
        first_name VARCHAR(50),
        last_name VARCHAR(50),
        full_name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        phone VARCHAR(20),
        subject VARCHAR(100),
        qualification VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    "CREATE TABLE IF NOT EXISTS parents (
        id INT AUTO_INCREMENT PRIMARY KEY,
        college_id INT DEFAULT 1,
        college_name VARCHAR(150) DEFAULT 'Smart Campus Main Institute',
        first_name VARCHAR(50),
        last_name VARCHAR(50),
        full_name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        phone VARCHAR(20),
        address TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    "CREATE TABLE IF NOT EXISTS courses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        college_id INT DEFAULT 1,
        college_name VARCHAR(150) DEFAULT 'Smart Campus Main Institute',
        course_name VARCHAR(100) NOT NULL,
        duration VARCHAR(50),
        fees VARCHAR(50),
        teacher VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    "CREATE TABLE IF NOT EXISTS attendance (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT NOT NULL,
        attendance_date DATE NOT NULL,
        status ENUM('Present','Absent','Late') NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    "CREATE TABLE IF NOT EXISTS results (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT NOT NULL,
        subject VARCHAR(100) NOT NULL,
        marks INT NOT NULL,
        grade VARCHAR(10) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    "CREATE TABLE IF NOT EXISTS timetable (
        id INT AUTO_INCREMENT PRIMARY KEY,
        teacher_id INT,
        day_of_week ENUM('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday') NOT NULL,
        period_number INT NOT NULL,
        start_time TIME NOT NULL,
        end_time TIME NOT NULL,
        subject VARCHAR(100) NOT NULL,
        course VARCHAR(100),
        room VARCHAR(50),
        color_class VARCHAR(30) DEFAULT 'tt-blue',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    "CREATE TABLE IF NOT EXISTS marks_schedule (
        id INT AUTO_INCREMENT PRIMARY KEY,
        subject VARCHAR(100) NOT NULL,
        exam_type ENUM('Unit Test','Mid Term','Final Exam','Assignment','Quiz') NOT NULL DEFAULT 'Unit Test',
        exam_date DATE NOT NULL,
        max_marks INT NOT NULL DEFAULT 100,
        course VARCHAR(100),
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    "CREATE TABLE IF NOT EXISTS achievements (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT NOT NULL,
        title VARCHAR(100) NOT NULL,
        description VARCHAR(255),
        badge_type ENUM('gold','silver','bronze','blue','green','purple') DEFAULT 'blue',
        badge_icon VARCHAR(50) DEFAULT 'fa-trophy',
        awarded_date DATE NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    "CREATE TABLE IF NOT EXISTS announcements (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(200) NOT NULL,
        content TEXT NOT NULL,
        notice_type ENUM('general','urgent','info','success') DEFAULT 'general',
        created_by VARCHAR(100),
        expires_date DATE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
];

foreach($tables as $sql) {
    mysqli_query($conn, $sql);
}

// Auto-migration checks for existing databases (wrapped in try-catch for strict PHP 8.1+ / MySQL error mode)
$migrations = [
    "ALTER TABLE users MODIFY COLUMN role ENUM('admin','teacher','student','parent') NOT NULL DEFAULT 'student'",
    "ALTER TABLE users ADD COLUMN college_id INT DEFAULT 1",
    "ALTER TABLE users ADD COLUMN college_name VARCHAR(150) DEFAULT 'Smart Campus Main Institute'",
    "ALTER TABLE users ADD COLUMN first_name VARCHAR(50) AFTER id",
    "ALTER TABLE users ADD COLUMN last_name VARCHAR(50) AFTER first_name",
    "ALTER TABLE students ADD COLUMN college_id INT DEFAULT 1",
    "ALTER TABLE students ADD COLUMN college_name VARCHAR(150) DEFAULT 'Smart Campus Main Institute'",
    "ALTER TABLE students ADD COLUMN first_name VARCHAR(50) AFTER id",
    "ALTER TABLE students ADD COLUMN last_name VARCHAR(50) AFTER first_name",
    "ALTER TABLE teachers ADD COLUMN college_id INT DEFAULT 1",
    "ALTER TABLE teachers ADD COLUMN college_name VARCHAR(150) DEFAULT 'Smart Campus Main Institute'",
    "ALTER TABLE teachers ADD COLUMN first_name VARCHAR(50) AFTER id",
    "ALTER TABLE teachers ADD COLUMN last_name VARCHAR(50) AFTER first_name",
    "ALTER TABLE parents ADD COLUMN college_id INT DEFAULT 1",
    "ALTER TABLE parents ADD COLUMN college_name VARCHAR(150) DEFAULT 'Smart Campus Main Institute'",
    "ALTER TABLE courses ADD COLUMN college_id INT DEFAULT 1",
    "ALTER TABLE courses ADD COLUMN college_name VARCHAR(150) DEFAULT 'Smart Campus Main Institute'",
    "UPDATE students SET first_name = 'Alex', last_name = 'Rivera' WHERE full_name = 'Alex Rivera' AND (last_name IS NULL OR last_name = '')",
    "UPDATE students SET first_name = 'Sophia', last_name = 'Chen' WHERE full_name = 'Sophia Chen' AND (last_name IS NULL OR last_name = '')",
    "UPDATE students SET first_name = 'Marcus', last_name = 'Webb' WHERE full_name = 'Marcus Webb' AND (last_name IS NULL OR last_name = '')",
    "UPDATE students SET first_name = 'Priya', last_name = 'Patel' WHERE full_name = 'Priya Patel' AND (last_name IS NULL OR last_name = '')"
];

foreach($migrations as $mSql) {
    try {
        mysqli_query($conn, $mSql);
    } catch (Throwable $e) {
        // Migration statement already applied or duplicate column ignored
    }
}

// Seed default colleges if empty
try {
    $clgCheck = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM colleges");
    if($clgCheck && mysqli_fetch_assoc($clgCheck)['cnt'] == 0) {
        mysqli_query($conn, "INSERT INTO colleges (id, college_name, college_code, address) VALUES 
            (1, 'Smart Campus Main Institute', 'SCMI', '100 University Ave, Campus City'),
            (2, 'Apex Engineering College', 'AEC', '45 Tech Park Road, Silicon Bay'),
            (3, 'Global Science & Business Academy', 'GSBA', '78 Academy Boulevard, Metro West')");
    }
} catch (Throwable $e) {}

// Seed default users if empty
$userCheck = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM users");
if($userCheck) {
    $row = mysqli_fetch_assoc($userCheck);
    if($row['cnt'] == 0) {
        $adminPass = password_hash('admin123', PASSWORD_DEFAULT);
        $teacherPass = password_hash('teacher123', PASSWORD_DEFAULT);
        $studentPass = password_hash('student123', PASSWORD_DEFAULT);
        $parentPass = password_hash('parent123', PASSWORD_DEFAULT);

        mysqli_query($conn, "INSERT INTO users (first_name, last_name, full_name, email, password, role, status) VALUES 
            ('Admin', 'User', 'Administrator', 'admin@crm.com', '$adminPass', 'admin', 'Active'),
            ('Sarah', 'Jenkins', 'Dr. Sarah Jenkins', 'teacher@crm.com', '$teacherPass', 'teacher', 'Active'),
            ('Alex', 'Rivera', 'Alex Rivera', 'student@crm.com', '$studentPass', 'student', 'Active'),
            ('Carlos', 'Rivera', 'Carlos Rivera', 'parent@crm.com', '$parentPass', 'parent', 'Active')");
    } else {
        // Ensure default parent user exists if not already present
        try {
            $parentCheck = mysqli_query($conn, "SELECT id FROM users WHERE email = 'parent@crm.com'");
            if($parentCheck && mysqli_num_rows($parentCheck) == 0) {
                $parentPass = password_hash('parent123', PASSWORD_DEFAULT);
                mysqli_query($conn, "INSERT INTO users (first_name, last_name, full_name, email, password, role, status) VALUES 
                    ('Carlos', 'Rivera', 'Carlos Rivera', 'parent@crm.com', '$parentPass', 'parent', 'Active')");
            }
        } catch (Throwable $e) {}
    }
}

// Seed default parent profile if empty
try {
    $prtCheck = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM parents");
    if($prtCheck && mysqli_fetch_assoc($prtCheck)['cnt'] == 0) {
        mysqli_query($conn, "INSERT INTO parents (first_name, last_name, full_name, email, phone, address) VALUES 
            ('Carlos', 'Rivera', 'Carlos Rivera', 'parent@crm.com', '+1 555-0199', '123 Campus Way, Suite 4B')");
    }
} catch (Throwable $e) {}

// Seed default courses if empty
$courseCheck = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM courses");
if($courseCheck && mysqli_fetch_assoc($courseCheck)['cnt'] == 0) {
    mysqli_query($conn, "INSERT INTO courses (course_name, duration, fees, teacher) VALUES 
        ('Computer Science', '4 Years', '$4,500', 'Dr. Sarah Jenkins'),
        ('Data Science', '2 Years', '$3,800', 'Prof. Michael Brown'),
        ('Business Administration', '3 Years', '$3,200', 'Dr. Emily Davis')");
}

// Seed default teachers if empty
$teacherCheck = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM teachers");
if($teacherCheck && mysqli_fetch_assoc($teacherCheck)['cnt'] == 0) {
    mysqli_query($conn, "INSERT INTO teachers (full_name, email, phone, subject, qualification) VALUES 
        ('Dr. Sarah Jenkins', 'teacher@crm.com', '+1 555-0192', 'Computer Science', 'Ph.D. in Computer Science'),
        ('Prof. Michael Brown', 'michael@crm.com', '+1 555-0183', 'Data Science', 'M.Sc. Data Analytics')");
}

// Seed default students if empty
$studentCheck = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM students");
if($studentCheck && mysqli_fetch_assoc($studentCheck)['cnt'] == 0) {
    mysqli_query($conn, "INSERT INTO students (full_name, email, phone, gender, date_of_birth, course, address) VALUES 
        ('Alex Rivera', 'student@crm.com', '+1 555-0144', 'Male', '2002-05-14', 'Computer Science', '123 Campus Way, Suite 4B'),
        ('Sophia Chen', 'sophia@crm.com', '+1 555-0177', 'Female', '2001-09-22', 'Data Science', '456 Academic Blvd'),
        ('Marcus Webb', 'marcus@crm.com', '+1 555-0201', 'Male', '2003-01-11', 'Business Administration', '789 College St'),
        ('Priya Patel', 'priya@crm.com', '+1 555-0234', 'Female', '2002-07-30', 'Computer Science', '321 University Ave')");
}

// Seed timetable if empty
$ttCheck = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM timetable");
if($ttCheck && mysqli_fetch_assoc($ttCheck)['cnt'] == 0) {
    mysqli_query($conn, "INSERT INTO timetable (day_of_week, period_number, start_time, end_time, subject, course, room, color_class) VALUES
        ('Monday',    1, '09:00','10:00', 'Data Structures',     'Computer Science', 'Lab A-101', 'tt-blue'),
        ('Monday',    2, '10:00','11:00', 'Calculus',            'Computer Science', 'Room B-202', 'tt-green'),
        ('Monday',    3, '11:30','12:30', 'Database Systems',    'Data Science',     'Lab B-105', 'tt-purple'),
        ('Tuesday',   1, '09:00','10:00', 'Web Development',     'Computer Science', 'Lab A-101', 'tt-amber'),
        ('Tuesday',   2, '10:00','11:00', 'Machine Learning',    'Data Science',     'Lab C-303', 'tt-teal'),
        ('Tuesday',   3, '11:30','12:30', 'Statistics',          'Data Science',     'Room D-404', 'tt-pink'),
        ('Wednesday', 1, '09:00','10:00', 'Data Structures',     'Computer Science', 'Lab A-101', 'tt-blue'),
        ('Wednesday', 2, '10:00','11:00', 'Economics',           'Business Administration', 'Room E-201', 'tt-green'),
        ('Thursday',  1, '09:00','10:00', 'Algorithms',          'Computer Science', 'Lab A-101', 'tt-purple'),
        ('Thursday',  2, '10:00','11:00', 'Data Visualization',  'Data Science',     'Lab C-303', 'tt-amber'),
        ('Friday',    1, '09:00','10:00', 'Software Engineering', 'Computer Science', 'Room F-101', 'tt-teal'),
        ('Friday',    2, '10:00','11:00', 'Business Analytics',  'Business Administration', 'Room E-201', 'tt-blue'),
        ('Saturday',  1, '09:00','10:00', 'Lab Practice',        'Computer Science', 'Lab A-101', 'tt-pink')");
}

// Seed marks_schedule if empty
$msCheck = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM marks_schedule");
if($msCheck && mysqli_fetch_assoc($msCheck)['cnt'] == 0) {
    $d1 = date('Y-m-d', strtotime('+5 days'));
    $d2 = date('Y-m-d', strtotime('+12 days'));
    $d3 = date('Y-m-d', strtotime('+20 days'));
    $d4 = date('Y-m-d', strtotime('+35 days'));
    $d5 = date('Y-m-d', strtotime('-3 days'));
    mysqli_query($conn, "INSERT INTO marks_schedule (subject, exam_type, exam_date, max_marks, course, description) VALUES
        ('Data Structures',    'Unit Test',   '$d1', 50,  'Computer Science',        'Chapter 1-4: Arrays, Linked Lists, Trees, Graphs'),
        ('Machine Learning',   'Mid Term',    '$d2', 100, 'Data Science',            'Regression, Classification, Clustering algorithms'),
        ('Web Development',    'Assignment',  '$d3', 30,  'Computer Science',        'Build a responsive portfolio website'),
        ('Calculus',           'Final Exam',  '$d4', 100, 'Computer Science',        'Full syllabus — Differential and Integral Calculus'),
        ('Database Systems',   'Quiz',        '$d5', 20,  'Computer Science',        'ER Diagrams and Normalization'),
        ('Business Analytics', 'Unit Test',   '$d2', 50,  'Business Administration', 'Chapters 1-5: Market Analysis and KPIs')");
}

// Seed achievements if empty
$achCheck = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM achievements");
if($achCheck && mysqli_fetch_assoc($achCheck)['cnt'] == 0) {
    $studRow = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM students LIMIT 1"));
    $studId  = $studRow ? $studRow['id'] : 1;
    $studRow2 = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM students LIMIT 1 OFFSET 1"));
    $studId2 = $studRow2 ? $studRow2['id'] : 1;
    mysqli_query($conn, "INSERT INTO achievements (student_id, title, description, badge_type, badge_icon, awarded_date) VALUES
        ($studId,  'Class Topper',          'Achieved highest marks in semester examinations', 'gold',   'fa-trophy',     '".date('Y-m-d')."'),
        ($studId,  'Perfect Attendance',    'Maintained 100% attendance for the month',         'green',  'fa-calendar-check', '".date('Y-m-d')."'),
        ($studId2, 'Most Improved',         'Showed exceptional academic improvement',           'silver', 'fa-chart-line', '".date('Y-m-d')."'),
        ($studId2, 'Subject Excellence',    'Scored full marks in Machine Learning test',        'purple', 'fa-star',       '".date('Y-m-d')."')");
}

// Seed announcements if empty
$annCheck = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM announcements");
if($annCheck && mysqli_fetch_assoc($annCheck)['cnt'] == 0) {
    $exp1 = date('Y-m-d', strtotime('+30 days'));
    $exp2 = date('Y-m-d', strtotime('+7 days'));
    mysqli_query($conn, "INSERT INTO announcements (title, content, notice_type, created_by, expires_date) VALUES
        ('Mid-Term Examination Schedule Released', 'The mid-term examination schedule has been published. Please check the Marks Schedule section for subject-wise dates and timings. All students are advised to prepare accordingly.', 'urgent', 'Administrator', '$exp2'),
        ('Library Timings Extended', 'The institute library will now remain open until 8:00 PM on weekdays to support students during the examination period. Saturday hours remain unchanged.', 'info', 'Administrator', '$exp1'),
        ('Annual Sports Day Registration Open', 'Registrations for the Annual Sports Day are now open. Students wishing to participate in athletic events should register at the administrative office before the deadline.', 'success', 'Administrator', '$exp1'),
        ('Fee Submission Deadline Reminder', 'This is a reminder that the last date for fee submission for the current semester is approaching. Late submissions will attract a penalty as per institute policy.', 'general', 'Administrator', '$exp2')");
}
