-- Smart Campus CRM - Database Schema & Initial Data
-- Database: crm_education

CREATE DATABASE IF NOT EXISTS `crm_education` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `crm_education`;

-- --------------------------------------------------------
-- Table structure for `colleges`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `colleges` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `college_name` VARCHAR(150) UNIQUE NOT NULL,
  `college_code` VARCHAR(30) UNIQUE NOT NULL,
  `address` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed default colleges
INSERT IGNORE INTO `colleges` (`id`, `college_name`, `college_code`, `address`) VALUES
(1, 'Smart Campus Main Institute', 'SCMI', '100 University Ave, Campus City'),
(2, 'Apex Engineering College', 'AEC', '45 Tech Park Road, Silicon Bay'),
(3, 'Global Science & Business Academy', 'GSBA', '78 Academy Boulevard, Metro West');

-- --------------------------------------------------------
-- Table structure for `users`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `college_id` INT DEFAULT 1,
  `college_name` VARCHAR(150) DEFAULT 'Smart Campus Main Institute',
  `first_name` VARCHAR(50),
  `last_name` VARCHAR(50),
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) UNIQUE NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('admin','teacher','student','parent') NOT NULL DEFAULT 'student',
  `status` VARCHAR(20) DEFAULT 'Active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed default users (Passwords: admin123, teacher123, student123, parent123)
INSERT IGNORE INTO `users` (`id`, `college_id`, `college_name`, `first_name`, `last_name`, `full_name`, `email`, `password`, `role`, `status`) VALUES
(1, 1, 'Smart Campus Main Institute', 'Admin', 'User', 'Administrator', 'admin@crm.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Active'),
(2, 1, 'Smart Campus Main Institute', 'Sarah', 'Jenkins', 'Dr. Sarah Jenkins', 'teacher@crm.com', '$2y$10$eE58.zM7p.31S4/K7yE7u.1zL1GZ.q9aL7W.8K9b0n1o2p3q4r5s6', 'teacher', 'Active'),
(3, 1, 'Smart Campus Main Institute', 'Alex', 'Rivera', 'Alex Rivera', 'student@crm.com', '$2y$10$qR6S7t8U9V0W1X2Y3Z4a5b6c7d8e9f0g1h2i3j4k5l6m7n8o9p0q1', 'student', 'Active'),
(4, 1, 'Smart Campus Main Institute', 'Carlos', 'Rivera', 'Carlos Rivera', 'parent@crm.com', '$2y$10$a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6q7r8s9t0u1v2w3x4y5z6', 'parent', 'Active');

-- --------------------------------------------------------
-- Table structure for `students`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `students` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `college_id` INT DEFAULT 1,
  `college_name` VARCHAR(150) DEFAULT 'Smart Campus Main Institute',
  `first_name` VARCHAR(50),
  `last_name` VARCHAR(50),
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20),
  `gender` VARCHAR(10),
  `date_of_birth` DATE,
  `course` VARCHAR(100),
  `address` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed default students
INSERT IGNORE INTO `students` (`id`, `college_id`, `college_name`, `first_name`, `last_name`, `full_name`, `email`, `phone`, `gender`, `date_of_birth`, `course`, `address`) VALUES
(1, 1, 'Smart Campus Main Institute', 'Alex', 'Rivera', 'Alex Rivera', 'student@crm.com', '+1 555-0144', 'Male', '2002-05-14', 'Computer Science', '123 Campus Way, Suite 4B'),
(2, 1, 'Smart Campus Main Institute', 'Sophia', 'Chen', 'Sophia Chen', 'sophia@crm.com', '+1 555-0177', 'Female', '2001-09-22', 'Data Science', '456 Academic Blvd'),
(3, 1, 'Smart Campus Main Institute', 'Marcus', 'Webb', 'Marcus Webb', 'marcus@crm.com', '+1 555-0201', 'Male', '2003-01-11', 'Business Administration', '789 College St'),
(4, 1, 'Smart Campus Main Institute', 'Priya', 'Patel', 'Priya Patel', 'priya@crm.com', '+1 555-0234', 'Female', '2002-07-30', 'Computer Science', '321 University Ave');

-- --------------------------------------------------------
-- Table structure for `teachers`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `teachers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `college_id` INT DEFAULT 1,
  `college_name` VARCHAR(150) DEFAULT 'Smart Campus Main Institute',
  `first_name` VARCHAR(50),
  `last_name` VARCHAR(50),
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20),
  `subject` VARCHAR(100),
  `qualification` VARCHAR(100),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed default teachers
INSERT IGNORE INTO `teachers` (`id`, `college_id`, `college_name`, `first_name`, `last_name`, `full_name`, `email`, `phone`, `subject`, `qualification`) VALUES
(1, 1, 'Smart Campus Main Institute', 'Sarah', 'Jenkins', 'Dr. Sarah Jenkins', 'teacher@crm.com', '+1 555-0192', 'Computer Science', 'Ph.D. in Computer Science'),
(2, 1, 'Smart Campus Main Institute', 'Michael', 'Brown', 'Prof. Michael Brown', 'michael@crm.com', '+1 555-0183', 'Data Science', 'M.Sc. Data Analytics');

-- --------------------------------------------------------
-- Table structure for `parents`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `parents` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `college_id` INT DEFAULT 1,
  `college_name` VARCHAR(150) DEFAULT 'Smart Campus Main Institute',
  `first_name` VARCHAR(50),
  `last_name` VARCHAR(50),
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20),
  `address` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed default parents
INSERT IGNORE INTO `parents` (`id`, `college_id`, `college_name`, `first_name`, `last_name`, `full_name`, `email`, `phone`, `address`) VALUES
(1, 1, 'Smart Campus Main Institute', 'Carlos', 'Rivera', 'Carlos Rivera', 'parent@crm.com', '+1 555-0199', '123 Campus Way, Suite 4B');

-- --------------------------------------------------------
-- Table structure for `courses`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `courses` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `college_id` INT DEFAULT 1,
  `college_name` VARCHAR(150) DEFAULT 'Smart Campus Main Institute',
  `course_name` VARCHAR(100) NOT NULL,
  `duration` VARCHAR(50),
  `fees` VARCHAR(50),
  `teacher` VARCHAR(100),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed default courses
INSERT IGNORE INTO `courses` (`id`, `college_id`, `college_name`, `course_name`, `duration`, `fees`, `teacher`) VALUES
(1, 1, 'Smart Campus Main Institute', 'Computer Science', '4 Years', '$4,500', 'Dr. Sarah Jenkins'),
(2, 1, 'Smart Campus Main Institute', 'Data Science', '2 Years', '$3,800', 'Prof. Michael Brown'),
(3, 1, 'Smart Campus Main Institute', 'Business Administration', '3 Years', '$3,200', 'Dr. Emily Davis');

-- --------------------------------------------------------
-- Table structure for `attendance`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `attendance` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT NOT NULL,
  `attendance_date` DATE NOT NULL,
  `status` ENUM('Present','Absent','Late') NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for `results`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `results` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT NOT NULL,
  `subject` VARCHAR(100) NOT NULL,
  `marks` INT NOT NULL,
  `grade` VARCHAR(10) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for `timetable`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `timetable` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `teacher_id` INT,
  `day_of_week` ENUM('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday') NOT NULL,
  `period_number` INT NOT NULL,
  `start_time` TIME NOT NULL,
  `end_time` TIME NOT NULL,
  `subject` VARCHAR(100) NOT NULL,
  `course` VARCHAR(100),
  `room` VARCHAR(50),
  `color_class` VARCHAR(30) DEFAULT 'tt-blue',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for `marks_schedule`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `marks_schedule` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `subject` VARCHAR(100) NOT NULL,
  `exam_type` ENUM('Unit Test','Mid Term','Final Exam','Assignment','Quiz') NOT NULL DEFAULT 'Unit Test',
  `exam_date` DATE NOT NULL,
  `max_marks` INT NOT NULL DEFAULT 100,
  `course` VARCHAR(100),
  `description` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for `achievements`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `achievements` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT NOT NULL,
  `title` VARCHAR(100) NOT NULL,
  `description` VARCHAR(255),
  `badge_type` ENUM('gold','silver','bronze','blue','green','purple') DEFAULT 'blue',
  `badge_icon` VARCHAR(50) DEFAULT 'fa-trophy',
  `awarded_date` DATE NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for `announcements`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `announcements` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(200) NOT NULL,
  `content` TEXT NOT NULL,
  `notice_type` ENUM('general','urgent','info','success') DEFAULT 'general',
  `created_by` VARCHAR(100),
  `expires_date` DATE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
