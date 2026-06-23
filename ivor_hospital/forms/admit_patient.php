<?php
require_once '../db_config.php';
require_once '../includes/auth_check.php';

$pageTitle = "Admit Patient";
$conn = get_db_connection();

$success = "";
$error   = "";

// ── Handle form submission ──────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $patientName  = isset($_POST['patient_name'])   ? trim($_POST['patient_name'])  : '';
    $dateOfBirth  = isset($_POST['date_of_birth'])  ? trim($_POST['date_of_birth']) : '';
    $dateAdmitted = isset($_POST['date_admitted'])  ? trim($_POST['date_admitted']) : '';
    $wardID       = isset($_POST['ward_id'])         ? (int)$_POST['ward_id']       : 0;
    $careUnitNo   = isset($_POST['care_unit_no'])   ? (int)$_POST['care_unit_no']   : 0;
    $bedNo        = isset($_POST['bed_no'])          ? (int)$_POST['bed_no']        : 0;
    $doctorID     = isset($_POST['doctor_id'])       ? (int)$_POST['doctor_id']     : 0;

    // Server-side validation
    if ($patientName === '' || $dateOfBirth === '' || $dateAdmitted === ''
        || $wardID === 0   || $careUnitNo === 0 || $bedNo === 0 || $doctorID === 0) {
        $error = "All fields are required.";

    } elseif ($dateOfBirth >= $dateAdmitted) {
        $error = "Date of Birth must be earlier than Date Admitted.";

    } else {
        // Check bed is still available
        $chk = sqlsrv_query($conn, "SELECT 1 FROM dbo.PATIENT WHERE BedNo = ?", array($bedNo));
        if ($chk && sqlsrv_has_rows($chk)) {
            $error = "Bed #$bedNo is already occupied. Please select a different bed.";
        } else {
            $sql = "INSERT INTO dbo.PATIENT (PatientName, DateOfBirth, DateAdmitted, WardID, CareUnitNo, BedNo, DoctorID)
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $params = array($patientName, $dateOfBirth, $dateAdmitted, $wardID, $careUnitNo, $bedNo, $doctorID);
            $result = sqlsrv_query($conn, $sql, $params);

            if ($result) {
                $success = "Patient <strong>" . htmlspecialchars($patientName) . "</strong> admitted successfully.";
                // Reset POST values after success
                $_POST = array();
            } else {
                $errors = sqlsrv_errors();
                $error  = "Insert failed: " . (isset($errors[0]['message']) ? $errors[0]['message'] : 'Unknown error.');
            }
        }
    }
}

// ── Load dropdowns ──────────────────────────────────────────
$wards = sqlsrv_query($conn, "SELECT WardID, WardName FROM dbo.WARD ORDER BY WardID");

$careUnits = sqlsrv_query($conn,
    "SELECT cu.CareUnitNo, w.WardName
     FROM dbo.CARE_UNIT cu
     JOIN dbo.WARD w ON cu.WardID = w.WardID
     ORDER BY cu.CareUnitNo"
);

// Only available beds (not yet assigned to any patient)
$beds = sqlsrv_query($conn,
    "SELECT b.BedNo, w.WardName
     FROM dbo.BED b
     JOIN dbo.WARD w ON b.WardID = w.WardID
     WHERE b.BedNo NOT IN (SELECT BedNo FROM dbo.PATIENT)
     ORDER BY b.WardID, b.BedNo"
);

$doctors = sqlsrv_query($conn,
    "SELECT DoctorID, Name, Position FROM dbo.DOCTOR ORDER BY DoctorID"
);

include '../includes/header.php';
?>

<div class="form-container">
    <h2>&#x2795; Admit New Patient</h2>

    <?php if ($success !== ""): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    <?php if ($error !== ""): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" action="" id="admitForm"
          onsubmit="return validateRequired('admitForm') && validateDOBAdmit('date_of_birth','date_admitted')">

        <div class="form-row">
            <div class="form-group">
                <label for="patient_name">Patient Name <span style="color:#c00;">*</span></label>
                <input type="text" id="patient_name" name="patient_name" required
                       placeholder="Full name"
                       value="<?php echo htmlspecialchars(isset($_POST['patient_name']) ? $_POST['patient_name'] : ''); ?>">
            </div>
            <div class="form-group">
                <!-- placeholder column -->
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="date_of_birth">Date of Birth <span style="color:#c00;">*</span></label>
                <input type="date" id="date_of_birth" name="date_of_birth" required
                       value="<?php echo htmlspecialchars(isset($_POST['date_of_birth']) ? $_POST['date_of_birth'] : ''); ?>">
            </div>
            <div class="form-group">
                <label for="date_admitted">Date Admitted <span style="color:#c00;">*</span></label>
                <input type="date" id="date_admitted" name="date_admitted" required
                       value="<?php echo htmlspecialchars(isset($_POST['date_admitted']) ? $_POST['date_admitted'] : date('Y-m-d')); ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="ward_id">Ward <span style="color:#c00;">*</span></label>
                <select id="ward_id" name="ward_id" required>
                    <option value="">— Select Ward —</option>
                    <?php
                    $selWard = isset($_POST['ward_id']) ? $_POST['ward_id'] : '';
                    if ($wards) {
                        while ($r = sqlsrv_fetch_array($wards, SQLSRV_FETCH_ASSOC)) {
                            $sel = ($selWard == $r['WardID']) ? ' selected' : '';
                            echo '<option value="' . $r['WardID'] . '"' . $sel . '>'
                               . htmlspecialchars($r['WardName']) . '</option>';
                        }
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label for="care_unit_no">Care Unit <span style="color:#c00;">*</span></label>
                <select id="care_unit_no" name="care_unit_no" required>
                    <option value="">— Select Care Unit —</option>
                    <?php
                    $selCU = isset($_POST['care_unit_no']) ? $_POST['care_unit_no'] : '';
                    if ($careUnits) {
                        while ($r = sqlsrv_fetch_array($careUnits, SQLSRV_FETCH_ASSOC)) {
                            $sel = ($selCU == $r['CareUnitNo']) ? ' selected' : '';
                            echo '<option value="' . $r['CareUnitNo'] . '"' . $sel . '>'
                               . 'CU ' . $r['CareUnitNo'] . ' — ' . htmlspecialchars($r['WardName']) . '</option>';
                        }
                    }
                    ?>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="bed_no">Available Bed <span style="color:#c00;">*</span></label>
                <select id="bed_no" name="bed_no" required>
                    <option value="">— Select Bed —</option>
                    <?php
                    $selBed = isset($_POST['bed_no']) ? $_POST['bed_no'] : '';
                    if ($beds) {
                        while ($r = sqlsrv_fetch_array($beds, SQLSRV_FETCH_ASSOC)) {
                            $sel = ($selBed == $r['BedNo']) ? ' selected' : '';
                            echo '<option value="' . $r['BedNo'] . '"' . $sel . '>'
                               . 'Bed ' . $r['BedNo'] . ' — ' . htmlspecialchars($r['WardName']) . '</option>';
                        }
                    }
                    ?>
                </select>
                <small>Only unoccupied beds are listed.</small>
            </div>
            <div class="form-group">
                <label for="doctor_id">Assigned Doctor <span style="color:#c00;">*</span></label>
                <select id="doctor_id" name="doctor_id" required>
                    <option value="">— Select Doctor —</option>
                    <?php
                    $selDoc = isset($_POST['doctor_id']) ? $_POST['doctor_id'] : '';
                    if ($doctors) {
                        while ($r = sqlsrv_fetch_array($doctors, SQLSRV_FETCH_ASSOC)) {
                            $sel = ($selDoc == $r['DoctorID']) ? ' selected' : '';
                            echo '<option value="' . $r['DoctorID'] . '"' . $sel . '>'
                               . htmlspecialchars($r['Name']) . ' (' . htmlspecialchars($r['Position']) . ')</option>';
                        }
                    }
                    ?>
                </select>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">&#x2795; Admit Patient</button>
            <a href="<?php echo BASE_URL; ?>/views/patients.php" class="btn btn-secondary">View Patients</a>
        </div>

    </form>
</div>

<?php
sqlsrv_close($conn);
include '../includes/footer.php';
?>
