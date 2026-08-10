<?php
session_start();
include "../config/db.php";

if(!isset($_SESSION['user']) || ($_SESSION['role'] != "admin" && $_SESSION['role'] != "teacher"))
{
    header("Location:../login.php");
    exit();
}

$message = "";
$error = "";

// Delete result handling
if(isset($_GET['delete']))
{
    $deleteId = intval($_GET['delete']);
    if($deleteId > 0)
    {
        $delStmt = $conn->prepare("DELETE FROM results WHERE id=?");
        $delStmt->bind_param("i", $deleteId);
        if($delStmt->execute())
        {
            $message = "Result record deleted successfully!";
        }
        else
        {
            $error = "Failed to delete result record.";
        }
    }
}

// Add result handling
if(isset($_POST['add_result']))
{
    $student_id = intval($_POST['student_id']);
    $subject = trim($_POST['subject']);
    $marks = intval($_POST['marks']);
    $grade = trim($_POST['grade']);

    if($student_id > 0 && !empty($subject) && $marks >= 0 && !empty($grade))
    {
        $insertStmt = $conn->prepare("INSERT INTO results (student_id, subject, marks, grade) VALUES (?, ?, ?, ?)");
        $insertStmt->bind_param("isis", $student_id, $subject, $marks, $grade);
        if($insertStmt->execute())
        {
            $message = "Student result added successfully!";
        }
        else
        {
            $error = "Failed to add student result.";
        }
    }
    else
    {
        $error = "Please fill in all fields correctly.";
    }
}

// Fetch all results with student names
$sql = "SELECT results.*, students.full_name, students.email
        FROM results
        INNER JOIN students ON results.student_id = students.id
        ORDER BY results.id DESC";
$resultQuery = $conn->query($sql);

// Fetch students list for dropdown
$studentsList = $conn->query("SELECT id, full_name FROM students ORDER BY full_name ASC");
?>

<!DOCTYPE html>
<html>
<head>
<title>Results Management | Smart Campus CRM</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/dashboard.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<style>
.form-card{
    background:white;
    padding:25px;
    border-radius:20px;
    box-shadow:0 10px 30px rgba(0,0,0,.08);
}
label{ font-weight:600; margin-bottom:6px; }
.form-control, .form-select{ padding:10px; border-radius:10px; }
</style>
</head>

<body>

<?php include "../includes/sidebar.php"; ?>

<div class="main">

<?php
$pageTitle = "Results Management";
include "../includes/topbar.php";
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 mt-2">
    <div>
        <h4 class="mb-0"><i class="fa-solid fa-square-poll-vertical text-primary"></i> Academic Results Management</h4>
        <small class="text-muted">Manage student marks and grades.</small>
    </div>
    <div class="crm-table-actions">
        <button class="btn-crm-export" onclick="exportTableToCSV('resultsTable', 'academic_results.csv')">
            <i class="fa-solid fa-file-csv"></i> Export CSV
        </button>
        <button class="btn-crm-print" onclick="printPage()">
            <i class="fa-solid fa-print"></i> Print
        </button>
    </div>
</div>

<?php if(!empty($message)){ ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fa-solid fa-circle-check"></i> <?php echo htmlspecialchars($message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php } ?>

<?php if(!empty($error)){ ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fa-solid fa-triangle-exclamation"></i> <?php echo htmlspecialchars($error); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php } ?>

<!-- Add Result Form -->
<div class="form-card mb-4">
    <h5 class="mb-3"><i class="fa-solid fa-plus text-primary"></i> Add Student Result</h5>
    <form method="POST">
        <div class="row g-3">
            <div class="col-md-3">
                <label>Student</label>
                <select name="student_id" class="form-select" required>
                    <option value="">-- Select Student --</option>
                    <?php if($studentsList){ ?>
                        <?php while($st = $studentsList->fetch_assoc()){ ?>
                            <option value="<?php echo $st['id']; ?>">
                                <?php echo htmlspecialchars($st['full_name']); ?>
                            </option>
                        <?php } ?>
                    <?php } ?>
                </select>
            </div>
            <div class="col-md-3">
                <label>Subject</label>
                <input type="text" name="subject" class="form-control" placeholder="e.g. Mathematics" required>
            </div>
            <div class="col-md-2">
                <label>Marks (0-100)</label>
                <input type="number" name="marks" class="form-control" min="0" max="100" placeholder="85" required>
            </div>
            <div class="col-md-2">
                <label>Grade</label>
                <select name="grade" class="form-select" required>
                    <option value="A+">A+</option>
                    <option value="A">A</option>
                    <option value="B+">B+</option>
                    <option value="B">B</option>
                    <option value="C">C</option>
                    <option value="D">D</option>
                    <option value="F">F</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" name="add_result" class="btn btn-primary w-100">
                    <i class="fa-solid fa-save"></i> Save Result
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Results List Table -->
<div class="table-box">
    <h5 class="mb-3">All Academic Results</h5>
    <div class="table-responsive">
        <table class="table table-hover align-middle" id="resultsTable">
            <thead class="table-dark">
                <tr>
                    <th>Student Name</th>
                    <th>Email</th>
                    <th>Subject</th>
                    <th>Score Marks</th>
                    <th>Grade</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if($resultQuery && $resultQuery->num_rows > 0){ ?>
                    <?php while($row = $resultQuery->fetch_assoc()){ ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($row['full_name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo htmlspecialchars($row['subject']); ?></td>
                            <td><strong><?php echo htmlspecialchars($row['marks']); ?> / 100</strong></td>
                            <td><span class="badge bg-primary px-3 py-2"><?php echo htmlspecialchars($row['grade']); ?></span></td>
                            <td>
                                <?php if($row['grade'] === 'F' || intval($row['marks']) < 40): ?>
                                    <span class="badge-crm-failed">Failed</span>
                                <?php else: ?>
                                    <span class="badge-crm-passed">Passed</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="results.php?delete=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this result record?');" title="Delete">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No Results Uploaded Yet</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

</div>

</body>
</html>

