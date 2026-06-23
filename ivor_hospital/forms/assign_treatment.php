<?php
require_once '../db_config.php';
require_once '../includes/auth_check.php';

$pageTitle = "Assign Treatment";
$conn = get_db_connection();

$success = "";
$error   = "";

// ── Handle form submission ──────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $patientNo     = isset($_POST['patient_no'])     ? (int)trim($_POST['patient_no'])     : 0;
    $complaintCode = isset($_POST['complaint_code']) ? trim($_POST['complaint_code'])       : '';
    $treatmentCode = isset($_POST['treatment_code']) ? trim($_POST['treatment_code'])       : '';
    $doctorID      = isset($_POST['doctor_id'])      ? (int)trim($_POST['doctor_id'])       : 0;
    $dateStarted   = isset($_POST['date_started'])   ? trim($_POST['date_started'])         : '';
    $dateEnded     = isset($_POST['date_ended'])      ? trim($_POST['date_ended'])           : '';

    if ($patientNo === 0 || $complaintCode === '' || $treatmentCode === ''
        || $doctorID === 0 || $dateStarted === '') {
        $error = "Patient, Complaint, Treatment, Doctor, and Date Started are all required.";

    } elseif ($dateEnded !== '' && $dateEnded < $dateStarted) {
        $error = "Date Ended cannot be before Date Started.";

    } else {
        // Verify patient actually has this complaint
        $chk = sqlsrv_query($conn,
            "SELECT 1 FROM dbo.HAS_COMPLAINT WHERE PatientNo = ? AND ComplaintCode = ?",
            array($patientNo, $complaintCode)
        );
        if (!$chk || !sqlsrv_has_rows($chk)) {
            $error = "Patient does not have complaint <strong>$complaintCode</strong> on record. "
                   . "Please add it via <a href='" . BASE_URL . "/forms/add_patient_complaint.php'>Add Complaint</a> first.";
        } else {
            // Auto-compute SNO for this (PatientNo, ComplaintCode, TreatmentCode, DoctorID) combination
            $snoSql = "SELECT ISNULL(MAX(SNO), 0) + 1 AS NextSNO
                       FROM dbo.MEDICAL_HISTORY
                       WHERE PatientNo = ? AND ComplaintCode = ? AND TreatmentCode = ? AND DoctorID = ?";
            $snoRes = sqlsrv_query($conn, $snoSql, array($patientNo, $complaintCode, $treatmentCode, $doctorID));
            $snoRow = $snoRes ? sqlsrv_fetch_array($snoRes, SQLSRV_FETCH_ASSOC) : null;
            $sno    = $snoRow ? (int)$snoRow['NextSNO'] : 1;

            $dateEndedVal = ($dateEnded === '') ? null : $dateEnded;

            $sql = "INSERT INTO dbo.MEDICAL_HISTORY
                        (PatientNo, ComplaintCode, TreatmentCode, DoctorID, SNO, DateStarted, DateEnded)
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $params = array($patientNo, $complaintCode, $treatmentCode, $doctorID, $sno, $dateStarted, $dateEndedVal);
            $result = sqlsrv_query($conn, $sql, $params);

            if ($result) {
                $success = "Treatment <strong>$treatmentCode</strong> assigned to patient #$patientNo "
                         . "for complaint <strong>$complaintCode</strong> (SNO: $sno).";
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
$treatments = sqlsrv_query($conn,
    "SELECT TreatmentCode, Description FROM dbo.TREATMENT ORDER BY TreatmentCode"
);
$doctors = sqlsrv_query($conn,
    "SELECT DoctorID, Name, Position FROM dbo.DOCTOR ORDER BY DoctorID"
);

include '../includes/header.php';
?>

<div class="form-container">
    <h2>&#x1F489; Assign Treatment</h2>

    <?php if ($success !== ""): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    <?php if ($error !== ""): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>

    <p style="font-size:12px;color:#7090a8;margin-bottom:18px;">
        The patient must already have the complaint registered. SNO is assigned automatically.
    </p>

    <form method="POST" action="" id="treatForm"
          onsubmit="return validateRequired('treatForm') && validateDateRange('date_started','date_ended')">

        <div class="form-row">
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
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="treatment_code">Treatment <span style="color:#c00;">*</span></label>
                <select id="treatment_code" name="treatment_code" required>
                    <option value="">— Select Treatment —</option>
                    <?php
                    $selT = isset($_POST['treatment_code']) ? $_POST['treatment_code'] : '';
                    if ($treatments) {
                        while ($r = sqlsrv_fetch_array($treatments, SQLSRV_FETCH_ASSOC)) {
                            $sel = ($selT == $r['TreatmentCode']) ? ' selected' : '';
                            echo '<option value="' . htmlspecialchars($r['TreatmentCode']) . '"' . $sel . '>'
                               . htmlspecialchars($r['TreatmentCode']) . ' — ' . htmlspecialchars($r['Description']) . '</option>';
                        }
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label for="doctor_id">Doctor <span style="color:#c00;">*</span></label>
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
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="date_started">Date Started <span style="color:#c00;">*</span></label>
                <input type="date" id="date_started" name="date_started" required
                       value="<?php echo htmlspecialchars(isset($_POST['date_started']) ? $_POST['date_started'] : date('Y-m-d')); ?>">
            </div>
            <div class="form-group">
                <label for="date_ended">Date Ended</label>
                <input type="date" id="date_ended" name="date_ended"
                       value="<?php echo htmlspecialchars(isset($_POST['date_ended']) ? $_POST['date_ended'] : ''); ?>">
                <small>Leave blank if treatment is still ongoing.</small>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">&#x1F489; Assign Treatment</button>
            <a href="<?php echo BASE_URL; ?>/views/medical_history.php" class="btn btn-secondary">View History</a>
        </div>

    </form>
</div>

<?php
sqlsrv_close($conn);
include '../includes/footer.php';
?>
