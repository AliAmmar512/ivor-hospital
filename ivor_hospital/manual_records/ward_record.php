<?php
require_once '../db_config.php';
require_once '../includes/auth_check.php';

$pageTitle = "Ward Record";
$conn = get_db_connection();

// Load all wards for the dropdown
$allWards = sqlsrv_query($conn,
    "SELECT WardID, WardName FROM dbo.WARD ORDER BY WardID"
);

$selectedWard = isset($_GET['ward_id']) ? (int)$_GET['ward_id'] : 0;
$ward         = null;
$patients     = array();

// Nurses grouped by position
$daySisters   = array();
$nightSisters = array();
$staffNurses  = array();
$nonRegNurses = array();

if ($selectedWard > 0) {

    // ── Ward info ───────────────────────────────────────────
    $wRes = sqlsrv_query($conn,
        "SELECT WardID, WardName, Specialty FROM dbo.WARD WHERE WardID = ?",
        array($selectedWard)
    );
    if ($wRes) {
        $ward = sqlsrv_fetch_array($wRes, SQLSRV_FETCH_ASSOC);
    }

    if ($ward) {

        // ── Nurses ──────────────────────────────────────────
        $nRes = sqlsrv_query($conn,
            "SELECT Name, Position, CareUnitNo FROM dbo.NURSE
             WHERE WardID = ?
             ORDER BY Position, Name",
            array($selectedWard)
        );
        if ($nRes) {
            while ($row = sqlsrv_fetch_array($nRes, SQLSRV_FETCH_ASSOC)) {
                switch ($row['Position']) {
                    case 'Day Sister':           $daySisters[]   = $row['Name']; break;
                    case 'Night Sister':         $nightSisters[] = $row['Name']; break;
                    case 'Staff Nurse':          $staffNurses[]  = $row['Name']; break;
                    case 'Non Registered Nurse': $nonRegNurses[] = $row['Name']; break;
                }
            }
        }

        // ── Patients ─────────────────────────────────────────
        $pSql = "
            SELECT
                p.PatientNo,
                p.PatientName,
                p.CareUnitNo,
                p.BedNo,
                CONVERT(VARCHAR(10), p.DateAdmitted, 120) AS DateAdmitted,
                CASE
                    WHEN d.Position = 'Consultant' THEN d.Name
                    ELSE ISNULL(con.Name, 'N/A')
                END AS ConsultantName
            FROM dbo.PATIENT p
            JOIN  dbo.DOCTOR d   ON p.DoctorID     = d.DoctorID
            LEFT JOIN dbo.DOCTOR con ON d.ConsultantID = con.DoctorID
            WHERE p.WardID = ?
            ORDER BY p.CareUnitNo, p.BedNo
        ";
        $pRes = sqlsrv_query($conn, $pSql, array($selectedWard));
        if ($pRes) {
            while ($row = sqlsrv_fetch_array($pRes, SQLSRV_FETCH_ASSOC)) {
                $patients[] = $row;
            }
        }
    }
}

// Helper: join array into readable string or show dash
function names_or_dash($arr) {
    return count($arr) > 0 ? implode(', ', array_map('htmlspecialchars', $arr)) : '<span style="color:#aaa;">None assigned</span>';
}

include '../includes/header.php';
?>

<div class="record-page">

    <!-- ── Selection form ───────────────────────────────── -->
    <div class="select-form no-print">
        <div class="form-group">
            <label for="ward_id">Select Ward</label>
            <select id="ward_id">
                <option value="">— Choose a ward —</option>
                <?php
                if ($allWards) {
                    while ($r = sqlsrv_fetch_array($allWards, SQLSRV_FETCH_ASSOC)) {
                        $sel = ($selectedWard == $r['WardID']) ? ' selected' : '';
                        echo '<option value="' . $r['WardID'] . '"' . $sel . '>'
                           . htmlspecialchars($r['WardName']) . '</option>';
                    }
                }
                ?>
            </select>
        </div>
        <button class="btn btn-primary" onclick="viewRecord()">&#x1F50D; View Record</button>
        <?php if ($ward): ?>
            <button class="btn btn-secondary" onclick="window.print()">&#x1F5A8; Print Record</button>
        <?php endif; ?>
    </div>

    <?php if ($selectedWard > 0 && !$ward): ?>
        <div class="alert alert-error">No ward found with Ward ID <?php echo $selectedWard; ?>.</div>

    <?php elseif ($ward): ?>

    <!-- ── Official Record Card ─────────────────────────── -->
    <div class="record-card">

        <div class="record-card-header">
            <h1>IVOR PAINE MEMORIAL HOSPITAL</h1>
            <h2>WARD RECORD</h2>
        </div>

        <div class="record-card-body">

            <!-- Ward details grid -->
            <div class="record-info-grid">
                <div class="record-info-item">
                    <label>Ward ID</label>
                    <p><?php echo htmlspecialchars($ward['WardID']); ?></p>
                </div>
                <div class="record-info-item">
                    <label>Ward Name</label>
                    <p><?php echo htmlspecialchars($ward['WardName']); ?></p>
                </div>
                <div class="record-info-item">
                    <label>Specialty</label>
                    <p><?php echo htmlspecialchars($ward['Specialty']); ?></p>
                </div>
                <div class="record-info-item">
                    <label>Total Patients</label>
                    <p><?php echo count($patients); ?></p>
                </div>
            </div>

            <!-- Nursing staff summary -->
            <div class="record-section-title">Nursing Staff</div>

            <div class="record-info-grid">
                <div class="record-info-item">
                    <label>Day Sister(s)</label>
                    <p style="font-size:13px;font-weight:500;"><?php echo names_or_dash($daySisters); ?></p>
                </div>
                <div class="record-info-item">
                    <label>Night Sister(s)</label>
                    <p style="font-size:13px;font-weight:500;"><?php echo names_or_dash($nightSisters); ?></p>
                </div>
                <div class="record-info-item">
                    <label>Staff Nurses</label>
                    <p style="font-size:13px;font-weight:500;"><?php echo names_or_dash($staffNurses); ?></p>
                </div>
                <div class="record-info-item">
                    <label>Non Registered Nurses</label>
                    <p style="font-size:13px;font-weight:500;"><?php echo names_or_dash($nonRegNurses); ?></p>
                </div>
            </div>

            <!-- Patients table -->
            <div class="record-section-title">Patient Information</div>

            <?php if (count($patients) > 0): ?>
            <div class="record-table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Patient No</th>
                            <th>Patient Name</th>
                            <th>Care Unit No</th>
                            <th>Bed No</th>
                            <th>Consultant</th>
                            <th>Date Admitted</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($patients as $p): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($p['PatientNo']); ?></td>
                            <td><?php echo htmlspecialchars($p['PatientName']); ?></td>
                            <td><?php echo htmlspecialchars($p['CareUnitNo']); ?></td>
                            <td><?php echo htmlspecialchars($p['BedNo']); ?></td>
                            <td><?php echo htmlspecialchars($p['ConsultantName']); ?></td>
                            <td><?php echo htmlspecialchars($p['DateAdmitted']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
                <p style="color:#aaa;font-style:italic;padding:14px 0;">No patients currently admitted to this ward.</p>
            <?php endif; ?>

        </div><!-- end record-card-body -->
    </div><!-- end record-card -->

    <?php endif; ?>

</div><!-- end record-page -->

<script>
function viewRecord() {
    var val = document.getElementById('ward_id').value;
    if (!val) { alert('Please select a ward.'); return; }
    window.location.href = '<?php echo BASE_URL; ?>/manual_records/ward_record.php?ward_id=' + val;
}
</script>

<?php
sqlsrv_close($conn);
include '../includes/footer.php';
?>
