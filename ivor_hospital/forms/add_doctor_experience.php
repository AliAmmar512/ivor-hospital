<?php
require_once '../db_config.php';
require_once '../includes/auth_check.php';

$pageTitle = "Add Doctor Experience";
$conn = get_db_connection();

$success = "";
$error   = "";

// ── Handle form submission ──────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $doctorID      = isset($_POST['doctor_id'])    ? (int)trim($_POST['doctor_id'])    : 0;
    $fromDate      = isset($_POST['from_date'])    ? trim($_POST['from_date'])          : '';
    $toDate        = isset($_POST['to_date'])       ? trim($_POST['to_date'])           : '';
    $position      = isset($_POST['position'])     ? trim($_POST['position'])           : '';
    $establishment = isset($_POST['establishment'])? trim($_POST['establishment'])      : '';

    if ($doctorID === 0 || $fromDate === '' || $position === '' || $establishment === '') {
        $error = "Doctor, From Date, Position, and Establishment are all required.";

    } elseif ($toDate !== '' && $toDate < $fromDate) {
        $error = "To Date cannot be before From Date.";

    } else {
        // Auto-compute SNO for this doctor
        $snoRes = sqlsrv_query($conn,
            "SELECT ISNULL(MAX(SNO), 0) + 1 AS NextSNO FROM dbo.PREV_EXPERIENCE WHERE DoctorID = ?",
            array($doctorID)
        );
        $snoRow = $snoRes ? sqlsrv_fetch_array($snoRes, SQLSRV_FETCH_ASSOC) : null;
        $sno    = $snoRow ? (int)$snoRow['NextSNO'] : 1;

        $toDateVal = ($toDate === '') ? null : $toDate;

        $sql = "INSERT INTO dbo.PREV_EXPERIENCE (DoctorID, SNO, FromDate, ToDate, Position, Establishment)
                VALUES (?, ?, ?, ?, ?, ?)";
        $params = array($doctorID, $sno, $fromDate, $toDateVal, $position, $establishment);
        $result = sqlsrv_query($conn, $sql, $params);

        if ($result) {
            // Get doctor name for message
            $dRes  = sqlsrv_query($conn, "SELECT Name FROM dbo.DOCTOR WHERE DoctorID = ?", array($doctorID));
            $dRow  = $dRes ? sqlsrv_fetch_array($dRes, SQLSRV_FETCH_ASSOC) : null;
            $dName = $dRow ? $dRow['Name'] : "Doctor #$doctorID";
            $success = "Experience record added for <strong>" . htmlspecialchars($dName) . "</strong> (SNO: $sno).";
            $_POST = array();
        } else {
            $errors = sqlsrv_errors();
            $error  = "Insert failed: " . (isset($errors[0]['message']) ? $errors[0]['message'] : 'Unknown error.');
        }
    }
}

// ── Load dropdown ────────────────────────────────────────────
$doctors = sqlsrv_query($conn,
    "SELECT DoctorID, Name, Position FROM dbo.DOCTOR ORDER BY DoctorID"
);

include '../includes/header.php';
?>

<div class="form-container">
    <h2>&#x1F4BC; Add Doctor Previous Experience</h2>

    <?php if ($success !== ""): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    <?php if ($error !== ""): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <p style="font-size:12px;color:#7090a8;margin-bottom:18px;">
        SNO is assigned automatically per doctor.
        Leave To Date blank if this is a current or ongoing position.
    </p>

    <form method="POST" action="" id="expForm"
          onsubmit="return validateRequired('expForm') && validateDateRange('from_date','to_date')">

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

        <div class="form-row">
            <div class="form-group">
                <label for="from_date">From Date <span style="color:#c00;">*</span></label>
                <input type="date" id="from_date" name="from_date" required
                       value="<?php echo htmlspecialchars(isset($_POST['from_date']) ? $_POST['from_date'] : ''); ?>">
            </div>
            <div class="form-group">
                <label for="to_date">To Date</label>
                <input type="date" id="to_date" name="to_date"
                       value="<?php echo htmlspecialchars(isset($_POST['to_date']) ? $_POST['to_date'] : ''); ?>">
                <small>Leave blank if still currently in this role.</small>
            </div>
        </div>

        <div class="form-group">
            <label for="position">Position Held <span style="color:#c00;">*</span></label>
            <input type="text" id="position" name="position" required
                   placeholder="e.g. Junior Houseman, Registrar"
                   value="<?php echo htmlspecialchars(isset($_POST['position']) ? $_POST['position'] : ''); ?>">
        </div>

        <div class="form-group">
            <label for="establishment">Establishment <span style="color:#c00;">*</span></label>
            <input type="text" id="establishment" name="establishment" required
                   placeholder="Hospital or clinic name"
                   value="<?php echo htmlspecialchars(isset($_POST['establishment']) ? $_POST['establishment'] : ''); ?>">
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">&#x2795; Add Experience</button>
            <a href="<?php echo BASE_URL; ?>/views/previous_experience.php" class="btn btn-secondary">View All Experience</a>
        </div>

    </form>
</div>

<?php
sqlsrv_close($conn);
include '../includes/footer.php';
?>
