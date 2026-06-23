<?php
require_once '../db_config.php';
require_once '../includes/auth_check.php';

$pageTitle = "Patient Record";
$conn = get_db_connection();

// Load all patients for the dropdown
$allPatients = sqlsrv_query($conn,
    "SELECT PatientNo, PatientName FROM dbo.PATIENT ORDER BY PatientNo"
);

$selectedNo = isset($_GET['patient_no']) ? (int)$_GET['patient_no'] : 0;
$patient    = null;
$history    = array();

if ($selectedNo > 0) {

    // ── Patient header info ─────────────────────────────────
    $pSql = "
        SELECT
            p.PatientNo,
            p.PatientName,
            CONVERT(VARCHAR(10), p.DateOfBirth,  120) AS DateOfBirth,
            CONVERT(VARCHAR(10), p.DateAdmitted, 120) AS DateAdmitted,
            w.WardName,
            p.CareUnitNo,
            p.BedNo,
            d.DoctorID,
            d.Name     AS DoctorName,
            d.Position AS DoctorPosition,
            CASE
                WHEN d.Position = 'Consultant' THEN d.Name
                ELSE ISNULL(con.Name, 'N/A')
            END AS ConsultantName,
            CASE
                WHEN d.Position = 'Consultant' THEN d.DoctorID
                ELSE ISNULL(con.DoctorID, d.DoctorID)
            END AS ConsultantID
        FROM dbo.PATIENT p
        JOIN  dbo.WARD   w   ON p.WardID    = w.WardID
        JOIN  dbo.DOCTOR d   ON p.DoctorID  = d.DoctorID
        LEFT JOIN dbo.DOCTOR con ON d.ConsultantID = con.DoctorID
        WHERE p.PatientNo = ?
    ";
    $pRes = sqlsrv_query($conn, $pSql, array($selectedNo));
    if ($pRes) {
        $patient = sqlsrv_fetch_array($pRes, SQLSRV_FETCH_ASSOC);
    }

    // ── Medical history rows ────────────────────────────────
    if ($patient) {
        $hSql = "
            SELECT
                mh.ComplaintCode,
                comp.Description  AS ComplaintDesc,
                mh.TreatmentCode,
                treat.Description AS TreatmentDesc,
                d.Name            AS DoctorName,
                mh.SNO,
                CONVERT(VARCHAR(10), mh.DateStarted, 120) AS DateStarted,
                CASE WHEN mh.DateEnded IS NULL
                     THEN NULL
                     ELSE CONVERT(VARCHAR(10), mh.DateEnded, 120)
                END AS DateEnded
            FROM dbo.MEDICAL_HISTORY mh
            JOIN dbo.COMPLAINT comp  ON mh.ComplaintCode = comp.ComplaintCode
            JOIN dbo.TREATMENT treat ON mh.TreatmentCode = treat.TreatmentCode
            JOIN dbo.DOCTOR    d     ON mh.DoctorID      = d.DoctorID
            WHERE mh.PatientNo = ?
            ORDER BY mh.ComplaintCode, mh.SNO
        ";
        $hRes = sqlsrv_query($conn, $hSql, array($selectedNo));
        if ($hRes) {
            while ($row = sqlsrv_fetch_array($hRes, SQLSRV_FETCH_ASSOC)) {
                $history[] = $row;
            }
        }
    }
}

include '../includes/header.php';
?>

<div class="record-page">

    <!-- ── Selection form ───────────────────────────────── -->
    <div class="select-form no-print">
        <div class="form-group">
            <label for="patient_no">Select Patient</label>
            <select id="patient_no">
                <option value="">— Choose a patient —</option>
                <?php
                if ($allPatients) {
                    while ($r = sqlsrv_fetch_array($allPatients, SQLSRV_FETCH_ASSOC)) {
                        $sel = ($selectedNo == $r['PatientNo']) ? ' selected' : '';
                        echo '<option value="' . $r['PatientNo'] . '"' . $sel . '>'
                           . '#' . $r['PatientNo'] . ' — ' . htmlspecialchars($r['PatientName']) . '</option>';
                    }
                }
                ?>
            </select>
        </div>
        <button class="btn btn-primary" onclick="viewRecord()">&#x1F50D; View Record</button>
        <?php if ($patient): ?>
            <button class="btn btn-secondary" onclick="window.print()">&#x1F5A8; Print Record</button>
        <?php endif; ?>
    </div>

    <?php if ($selectedNo > 0 && !$patient): ?>
        <div class="alert alert-error">No patient found with Patient No. <?php echo $selectedNo; ?>.</div>

    <?php elseif ($patient): ?>

    <!-- ── Official Record Card ─────────────────────────── -->
    <div class="record-card">

        <div class="record-card-header">
            <h1>IVOR PAINE MEMORIAL HOSPITAL</h1>
            <h2>PATIENT RECORD</h2>
        </div>

        <div class="record-card-body">

            <!-- Top info grid -->
            <div class="record-info-grid">
                <div class="record-info-item">
                    <label>Patient No</label>
                    <p><?php echo htmlspecialchars($patient['PatientNo']); ?></p>
                </div>
                <div class="record-info-item">
                    <label>Patient Name</label>
                    <p><?php echo htmlspecialchars($patient['PatientName']); ?></p>
                </div>
                <div class="record-info-item">
                    <label>Date of Birth</label>
                    <p><?php echo htmlspecialchars($patient['DateOfBirth']); ?></p>
                </div>
                <div class="record-info-item">
                    <label>Date Admitted</label>
                    <p><?php echo htmlspecialchars($patient['DateAdmitted']); ?></p>
                </div>
                <div class="record-info-item">
                    <label>Ward</label>
                    <p><?php echo htmlspecialchars($patient['WardName']); ?></p>
                </div>
                <div class="record-info-item">
                    <label>Care Unit No</label>
                    <p><?php echo htmlspecialchars($patient['CareUnitNo']); ?></p>
                </div>
                <div class="record-info-item">
                    <label>Bed No</label>
                    <p><?php echo htmlspecialchars($patient['BedNo']); ?></p>
                </div>
                <div class="record-info-item">
                    <label>Doctor No</label>
                    <p><?php echo htmlspecialchars($patient['DoctorID']); ?></p>
                </div>
                <div class="record-info-item">
                    <label>Doctor Name</label>
                    <p><?php echo htmlspecialchars($patient['DoctorName']); ?></p>
                </div>
                <div class="record-info-item">
                    <label>Doctor Position</label>
                    <p><?php echo htmlspecialchars($patient['DoctorPosition']); ?></p>
                </div>
                <div class="record-info-item">
                    <label>Consultant No</label>
                    <p><?php echo htmlspecialchars($patient['ConsultantID']); ?></p>
                </div>
                <div class="record-info-item">
                    <label>Consultant Name</label>
                    <p><?php echo htmlspecialchars($patient['ConsultantName']); ?></p>
                </div>
            </div>

            <!-- Medical History table -->
            <div class="record-section-title">Medical History</div>

            <?php if (count($history) > 0): ?>
            <div class="record-table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Complaint Code</th>
                            <th>Complaint</th>
                            <th>Treatment Code</th>
                            <th>Treatment</th>
                            <th>Doctor</th>
                            <th>Date Started</th>
                            <th>Date Ended</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($history as $h): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($h['ComplaintCode']); ?></td>
                            <td><?php echo htmlspecialchars($h['ComplaintDesc']); ?></td>
                            <td><?php echo htmlspecialchars($h['TreatmentCode']); ?></td>
                            <td><?php echo htmlspecialchars($h['TreatmentDesc']); ?></td>
                            <td><?php echo htmlspecialchars($h['DoctorName']); ?></td>
                            <td><?php echo htmlspecialchars($h['DateStarted']); ?></td>
                            <td><?php echo $h['DateEnded'] ? htmlspecialchars($h['DateEnded']) : '<span style="color:#aaa;">—</span>'; ?></td>
                            <td>
                                <?php if ($h['DateEnded'] === null): ?>
                                    <span class="badge badge-green">Active</span>
                                <?php else: ?>
                                    <span class="badge badge-gray">Done</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
                <p style="color:#aaa;font-style:italic;padding:14px 0;">No medical history on record for this patient.</p>
            <?php endif; ?>

        </div><!-- end record-card-body -->
    </div><!-- end record-card -->

    <?php endif; ?>

</div><!-- end record-page -->

<script>
function viewRecord() {
    var val = document.getElementById('patient_no').value;
    if (!val) { alert('Please select a patient.'); return; }
    window.location.href = '<?php echo BASE_URL; ?>/manual_records/patient_record.php?patient_no=' + val;
}
</script>

<?php
sqlsrv_close($conn);
include '../includes/footer.php';
?>
