<?php
require_once '../db_config.php';
require_once '../includes/auth_check.php';

$pageTitle = "Add Patient Complaint";
$conn = get_db_connection();

$success = "";
$error   = "";

// ── Handle form submission ──────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $patientNo     = isset($_POST['patient_no'])     ? (int)trim($_POST['patient_no'])     : 0;
    $complaintCode = isset($_POST['complaint_code']) ? trim($_POST['complaint_code'])       : '';

    if ($patientNo === 0 || $complaintCode === '') {
        $error = "Both Patient and Complaint are required.";

    } else {
        // Check if this pair already exists
        $chk = sqlsrv_query($conn,
            "SELECT 1 FROM dbo.HAS_COMPLAINT WHERE PatientNo = ? AND ComplaintCode = ?",
            array($patientNo, $complaintCode)
        );
        if ($chk && sqlsrv_has_rows($chk)) {
            $error = "This patient already has that complaint on record.";
        } else {
            $sql    = "INSERT INTO dbo.HAS_COMPLAINT (PatientNo, ComplaintCode) VALUES (?, ?)";
            $params = array($patientNo, $complaintCode);
            $result = sqlsrv_query($conn, $sql, $params);

            if ($result) {
                // Get patient name for a friendly message
                $pRes = sqlsrv_query($conn,
                    "SELECT PatientName FROM dbo.PATIENT WHERE PatientNo = ?",
                    array($patientNo)
                );
                $pRow = $pRes ? sqlsrv_fetch_array($pRes, SQLSRV_FETCH_ASSOC) : null;
                $pName = $pRow ? $pRow['PatientName'] : "Patient #$patientNo";
                $success = "Complaint <strong>$complaintCode</strong> added to <strong>" . htmlspecialchars($pName) . "</strong>.";
                $_POST = array();
            } else {
                $errors = sqlsrv_errors();
                $error  = "Insert failed: " . (isset($errors[0]['message']) ? $errors[0]['message'] : 'Unknown error.');
            }
        }
    }
}

// ── Load dropdowns ──────────────────────────────────────────
$patients = sqlsrv_query($conn,
    "SELECT PatientNo, PatientName FROM dbo.PATIENT ORDER BY PatientNo"
);
$complaints = sqlsrv_query($conn,
    "SELECT ComplaintCode, Description FROM dbo.COMPLAINT ORDER BY ComplaintCode"
);

include '../includes/header.php';
?>

<div class="form-container">
    <h2>&#x1F4CB; Add Complaint to Patient</h2>

    <?php if ($success !== ""): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    <?php if ($error !== ""): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" action="" id="complaintForm"
          onsubmit="return validateRequired('complaintForm')">

        <div class="form-group">
            <label for="patient_no">Patient <span style="color:#c00;">*</span></label>
            <select id="patient_no" name="patient_no" required>
                <option value="">— Select Patient —</option>
                <?php
                $selP = isset($_POST['patient_no']) ? $_POST['patient_no'] : '';
                if ($patients) {
                    while ($r = sqlsrv_fetch_array($patients, SQLSRV_FETCH_ASSOC)) {
                        $sel = ($selP == $r['PatientNo']) ? ' selected' : '';
                        echo '<option value="' . $r['PatientNo'] . '"' . $sel . '>'
                           . '#' . $r['PatientNo'] . ' — ' . htmlspecialchars($r['PatientName']) . '</option>';
                    }
                }
                ?>
            </select>
        </div>

        <div class="form-group">
            <label for="complaint_code">Complaint <span style="color:#c00;">*</span></label>
            <select id="complaint_code" name="complaint_code" required>
                <option value="">— Select Complaint —</option>
                <?php
                $selC = isset($_POST['complaint_code']) ? $_POST['complaint_code'] : '';
                if ($complaints) {
                    while ($r = sqlsrv_fetch_array($complaints, SQLSRV_FETCH_ASSOC)) {
                        $sel = ($selC == $r['ComplaintCode']) ? ' selected' : '';
                        echo '<option value="' . htmlspecialchars($r['ComplaintCode']) . '"' . $sel . '>'
                           . htmlspecialchars($r['ComplaintCode']) . ' — ' . htmlspecialchars($r['Description']) . '</option>';
                    }
                }
                ?>
            </select>
            <small>Register this complaint for the selected patient before assigning a treatment.</small>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">&#x2795; Add Complaint</button>
            <a href="<?php echo BASE_URL; ?>/forms/assign_treatment.php" class="btn btn-secondary">Assign Treatment</a>
        </div>

    </form>
</div>

<?php
sqlsrv_close($conn);
include '../includes/footer.php';
?>
