<?php
require_once '../db_config.php';
require_once '../includes/auth_check.php';

$pageTitle = "Add Performance Grade";
$conn = get_db_connection();

$success = "";
$error   = "";

// ── Handle form submission ──────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $doctorID     = isset($_POST['doctor_id'])     ? (int)trim($_POST['doctor_id'])     : 0;
    $consultantID = isset($_POST['consultant_id']) ? (int)trim($_POST['consultant_id']) : 0;
    $gradeDate    = isset($_POST['grade_date'])    ? trim($_POST['grade_date'])          : '';
    $grade        = isset($_POST['grade'])         ? trim($_POST['grade'])               : '';

    $validGrades = array('A', 'B', 'C', 'D', 'E');

    if ($doctorID === 0 || $consultantID === 0 || $gradeDate === '' || $grade === '') {
        $error = "All fields are required.";

    } elseif (!in_array($grade, $validGrades)) {
        $error = "Grade must be one of: A, B, C, D, E.";

    } elseif ($doctorID === $consultantID) {
        $error = "A consultant cannot grade themselves.";

    } else {
        // Auto-compute SNO for this doctor
        $snoRes = sqlsrv_query($conn,
            "SELECT ISNULL(MAX(SNO), 0) + 1 AS NextSNO FROM dbo.PERF_GRADE WHERE DoctorID = ?",
            array($doctorID)
        );
        $snoRow = $snoRes ? sqlsrv_fetch_array($snoRes, SQLSRV_FETCH_ASSOC) : null;
        $sno    = $snoRow ? (int)$snoRow['NextSNO'] : 1;

        $sql = "INSERT INTO dbo.PERF_GRADE (DoctorID, SNO, ConsultantID, GradeDate, Grade)
                VALUES (?, ?, ?, ?, ?)";
        $params = array($doctorID, $sno, $consultantID, $gradeDate, $grade);
        $result = sqlsrv_query($conn, $sql, $params);

        if ($result) {
            $dRes  = sqlsrv_query($conn, "SELECT Name FROM dbo.DOCTOR WHERE DoctorID = ?", array($doctorID));
            $dRow  = $dRes ? sqlsrv_fetch_array($dRes, SQLSRV_FETCH_ASSOC) : null;
            $dName = $dRow ? $dRow['Name'] : "Doctor #$doctorID";
            $success = "Grade <strong>$grade</strong> added for <strong>" . htmlspecialchars($dName) . "</strong> (SNO: $sno).";
            $_POST = array();
        } else {
            $errors = sqlsrv_errors();
            $error  = "Insert failed: " . (isset($errors[0]['message']) ? $errors[0]['message'] : 'Unknown error.');
        }
    }
}

// ── Load dropdowns ──────────────────────────────────────────
// Doctors being graded: all NON-consultant doctors
$doctors = sqlsrv_query($conn,
    "SELECT DoctorID, Name, Position FROM dbo.DOCTOR
     WHERE Position <> 'Consultant'
     ORDER BY DoctorID"
);

// Grading consultants only
$consultants = sqlsrv_query($conn,
    "SELECT d.DoctorID, d.Name, c.Specialty
     FROM dbo.DOCTOR d
     JOIN dbo.CONSULTANT c ON d.DoctorID = c.DoctorID
     ORDER BY d.DoctorID"
);

include '../includes/header.php';
?>

<div class="form-container">
    <h2>&#x2B50; Add Performance Grade</h2>

    <?php if ($success !== ""): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    <?php if ($error !== ""): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <p style="font-size:12px;color:#7090a8;margin-bottom:18px;">
        Grades are assigned by consultants to their team members. SNO is assigned automatically.
    </p>

    <form method="POST" action="" id="gradeForm"
          onsubmit="return validateRequired('gradeForm')">

        <div class="form-row">
            <div class="form-group">
                <label for="doctor_id">Doctor Being Graded <span style="color:#c00;">*</span></label>
                <select id="doctor_id" name="doctor_id" required>
                    <option value="">— Select Doctor —</option>
                    <?php
                    $selD = isset($_POST['doctor_id']) ? $_POST['doctor_id'] : '';
                    if ($doctors) {
                        while ($r = sqlsrv_fetch_array($doctors, SQLSRV_FETCH_ASSOC)) {
                            $sel = ($selD == $r['DoctorID']) ? ' selected' : '';
                            echo '<option value="' . $r['DoctorID'] . '"' . $sel . '>'
                               . htmlspecialchars($r['Name']) . ' (' . htmlspecialchars($r['Position']) . ')</option>';
                        }
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label for="consultant_id">Graded By (Consultant) <span style="color:#c00;">*</span></label>
                <select id="consultant_id" name="consultant_id" required>
                    <option value="">— Select Consultant —</option>
                    <?php
                    $selC = isset($_POST['consultant_id']) ? $_POST['consultant_id'] : '';
                    if ($consultants) {
                        while ($r = sqlsrv_fetch_array($consultants, SQLSRV_FETCH_ASSOC)) {
                            $sel = ($selC == $r['DoctorID']) ? ' selected' : '';
                            echo '<option value="' . $r['DoctorID'] . '"' . $sel . '>'
                               . htmlspecialchars($r['Name']) . ' (' . htmlspecialchars($r['Specialty']) . ')</option>';
                        }
                    }
                    ?>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="grade_date">Grade Date <span style="color:#c00;">*</span></label>
                <input type="date" id="grade_date" name="grade_date" required
                       value="<?php echo htmlspecialchars(isset($_POST['grade_date']) ? $_POST['grade_date'] : date('Y-m-d')); ?>">
            </div>
            <div class="form-group">
                <label for="grade">Grade <span style="color:#c00;">*</span></label>
                <select id="grade" name="grade" required>
                    <option value="">— Select Grade —</option>
                    <?php
                    $selG  = isset($_POST['grade']) ? $_POST['grade'] : '';
                    $gdesc = array(
                        'A' => 'A — Outstanding',
                        'B' => 'B — Good',
                        'C' => 'C — Satisfactory',
                        'D' => 'D — Needs Improvement',
                        'E' => 'E — Unsatisfactory'
                    );
                    foreach ($gdesc as $val => $label) {
                        $sel = ($selG === $val) ? ' selected' : '';
                        echo '<option value="' . $val . '"' . $sel . '>' . $label . '</option>';
                    }
                    ?>
                </select>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">&#x2B50; Add Grade</button>
            <a href="<?php echo BASE_URL; ?>/views/performance_grades.php" class="btn btn-secondary">View All Grades</a>
        </div>

    </form>
</div>

<?php
sqlsrv_close($conn);
include '../includes/footer.php';
?>
