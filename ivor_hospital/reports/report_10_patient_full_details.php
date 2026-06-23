<?php
require_once '../db_config.php';
require_once '../includes/auth_check.php';

$pageTitle = "Report 10: Patient Full Details";
$conn = get_db_connection();

// Build patient list for dropdown
$patientsSql = "SELECT PatientNo, PatientName FROM dbo.PATIENT ORDER BY PatientNo";
$patientsRes  = sqlsrv_query($conn, $patientsSql);
$patients     = array();
if ($patientsRes) {
    while ($pr = sqlsrv_fetch_array($patientsRes, SQLSRV_FETCH_ASSOC)) {
        $patients[] = $pr;
    }
}

$selectedPatient = isset($_GET['patient_no']) ? trim($_GET['patient_no']) : '';
$patient         = null;
$complaints      = array();
$treatments      = array();

if ($selectedPatient !== '') {
    // Patient header
    $pSql = "
        SELECT
            p.PatientNo,
            p.PatientName,
            CONVERT(VARCHAR(10), p.DateOfBirth, 120)    AS DateOfBirth,
            CONVERT(VARCHAR(10), p.DateAdmitted, 120)   AS DateAdmitted,
            p.BedNo,
            p.CareUnitNo,
            w.WardName,
            w.Specialty                                 AS WardSpecialty,
            d.Name                                      AS DoctorName,
            d.Position                                  AS DoctorPosition,
            CASE WHEN d.Position = 'Consultant' THEN d.Name
                 ELSE ISNULL(con.Name, 'N/A')
            END                                         AS ConsultantName
        FROM dbo.PATIENT    p
        JOIN  dbo.WARD      w   ON p.WardID       = w.WardID
        JOIN  dbo.DOCTOR    d   ON p.DoctorID      = d.DoctorID
        LEFT JOIN dbo.DOCTOR con ON d.ConsultantID = con.DoctorID
        WHERE p.PatientNo = ?
    ";
    $pRes = sqlsrv_query($conn, $pSql, array($selectedPatient));
    if ($pRes) {
        $patient = sqlsrv_fetch_array($pRes, SQLSRV_FETCH_ASSOC);
    }

    // Complaints
    $cSql = "
        SELECT hc.ComplaintCode, c.Description AS ComplaintDesc
        FROM   dbo.HAS_COMPLAINT hc
        JOIN   dbo.COMPLAINT     c  ON hc.ComplaintCode = c.ComplaintCode
        WHERE  hc.PatientNo = ?
        ORDER BY hc.ComplaintCode
    ";
    $cRes = sqlsrv_query($conn, $cSql, array($selectedPatient));
    if ($cRes) {
        while ($row = sqlsrv_fetch_array($cRes, SQLSRV_FETCH_ASSOC)) {
            $complaints[] = $row;
        }
    }

    // Medical history / treatments
    $tSql = "
        SELECT
            mh.ComplaintCode,
            c.Description                               AS ComplaintDesc,
            mh.TreatmentCode,
            t.Description                               AS TreatmentDesc,
            d.Name                                      AS TreatingDoctor,
            d.Position                                  AS DoctorPosition,
            CONVERT(VARCHAR(10), mh.DateStarted, 120)   AS DateStarted,
            CASE WHEN mh.DateEnded IS NULL THEN NULL
                 ELSE CONVERT(VARCHAR(10), mh.DateEnded, 120)
            END                                         AS DateEnded
        FROM dbo.MEDICAL_HISTORY mh
        JOIN  dbo.COMPLAINT  c  ON mh.ComplaintCode  = c.ComplaintCode
        JOIN  dbo.TREATMENT  t  ON mh.TreatmentCode  = t.TreatmentCode
        JOIN  dbo.DOCTOR     d  ON mh.DoctorID        = d.DoctorID
        WHERE mh.PatientNo = ?
        ORDER BY mh.ComplaintCode, mh.DateStarted
    ";
    $tRes = sqlsrv_query($conn, $tSql, array($selectedPatient));
    if ($tRes) {
        while ($row = sqlsrv_fetch_array($tRes, SQLSRV_FETCH_ASSOC)) {
            $treatments[] = $row;
        }
    }
}

include '../includes/header.php';
?>

<div class="table-container">
    <div class="table-header">
        <div>
            <h2>Report 10 &mdash; Patient Full Details</h2>
            <p>Complete profile for a selected patient: admission info, complaints, and full treatment history.</p>
        </div>
    </div>

    <!-- Filter form -->
    <div class="report-filter no-print">
        <form method="GET" action="">
            <div>
                <label for="patient_no">Patient</label>
                <select name="patient_no" id="patient_no">
                    <option value="">-- Select a patient --</option>
                    <?php foreach ($patients as $pt): ?>
                        <option value="<?php echo htmlspecialchars($pt['PatientNo']); ?>"
                            <?php echo ($pt['PatientNo'] === $selectedPatient) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($pt['PatientNo'] . ' — ' . $pt['PatientName']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn-primary">View Details</button>
            <?php if ($selectedPatient !== ''): ?>
                <a href="report_10_patient_full_details.php" class="btn-secondary">Clear</a>
                <button type="button" class="btn-secondary no-print" onclick="window.print()">&#128438; Print</button>
            <?php endif; ?>
        </form>
    </div>

    <?php if ($selectedPatient !== '' && $patient): ?>

    <!-- Patient info card -->
    <div class="record-card" style="margin:0 0 20px 0;">
        <div class="record-card-header" style="display:flex;justify-content:space-between;align-items:center;text-align:left;">
            <div>
                <span style="font-size:1.1rem;font-weight:600;"><?php echo htmlspecialchars($patient['PatientName']); ?></span>
                <span style="margin-left:12px;opacity:.75;font-size:.85rem;"><?php echo htmlspecialchars($patient['PatientNo']); ?></span>
            </div>
            <span class="badge badge-blue"><?php echo htmlspecialchars($patient['WardSpecialty']); ?></span>
        </div>
        <div class="record-card-body">
            <div class="record-info-grid">
                <div class="record-info-item"><span class="record-info-label">Date of Birth</span><p><?php echo htmlspecialchars($patient['DateOfBirth']); ?></p></div>
                <div class="record-info-item"><span class="record-info-label">Date Admitted</span><p><?php echo htmlspecialchars($patient['DateAdmitted']); ?></p></div>
                <div class="record-info-item"><span class="record-info-label">Ward</span><p><?php echo htmlspecialchars($patient['WardName']); ?></p></div>
                <div class="record-info-item"><span class="record-info-label">Care Unit</span><p><?php echo htmlspecialchars($patient['CareUnitNo']); ?></p></div>
                <div class="record-info-item"><span class="record-info-label">Bed No</span><p><?php echo htmlspecialchars($patient['BedNo']); ?></p></div>
                <div class="record-info-item"><span class="record-info-label">Treating Doctor</span><p><?php echo htmlspecialchars($patient['DoctorName']); ?> <span class="badge badge-navy" style="font-size:.7rem;"><?php echo htmlspecialchars($patient['DoctorPosition']); ?></span></p></div>
                <div class="record-info-item"><span class="record-info-label">Consultant</span><p><?php echo htmlspecialchars($patient['ConsultantName']); ?></p></div>
            </div>
        </div>
    </div>

    <!-- Complaints -->
    <div class="record-section-title">Diagnoses (<?php echo count($complaints); ?> complaint<?php echo count($complaints) !== 1 ? 's' : ''; ?>)</div>
    <div class="table-wrap" style="margin-bottom:20px;">
        <table>
            <thead>
                <tr><th>Complaint Code</th><th>Description</th></tr>
            </thead>
            <tbody>
            <?php if (count($complaints) > 0): ?>
                <?php foreach ($complaints as $c): ?>
                <tr>
                    <td><span class="badge badge-orange"><?php echo htmlspecialchars($c['ComplaintCode']); ?></span></td>
                    <td><?php echo htmlspecialchars($c['ComplaintDesc']); ?></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="2" class="no-records">No complaints recorded.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Treatments -->
    <div class="record-section-title">Treatment History (<?php echo count($treatments); ?> record<?php echo count($treatments) !== 1 ? 's' : ''; ?>)</div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Complaint</th>
                    <th>Treatment</th>
                    <th>Treating Doctor</th>
                    <th>Date Started</th>
                    <th>Date Ended</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
            <?php if (count($treatments) > 0): ?>
                <?php foreach ($treatments as $tr): ?>
                <tr>
                    <td>
                        <span class="badge badge-orange"><?php echo htmlspecialchars($tr['ComplaintCode']); ?></span>
                        <br><small style="color:#7090a8;"><?php echo htmlspecialchars($tr['ComplaintDesc']); ?></small>
                    </td>
                    <td>
                        <span class="badge badge-blue"><?php echo htmlspecialchars($tr['TreatmentCode']); ?></span>
                        <br><small style="color:#7090a8;"><?php echo htmlspecialchars($tr['TreatmentDesc']); ?></small>
                    </td>
                    <td><?php echo htmlspecialchars($tr['TreatingDoctor']); ?></td>
                    <td><?php echo htmlspecialchars($tr['DateStarted']); ?></td>
                    <td><?php echo $tr['DateEnded'] ? htmlspecialchars($tr['DateEnded']) : '<span style="color:#aaa;">—</span>'; ?></td>
                    <td>
                        <?php if ($tr['DateEnded'] === null): ?>
                            <span class="badge badge-green">Active</span>
                        <?php else: ?>
                            <span class="badge badge-gray">Done</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="6" class="no-records">No treatments on record.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php elseif ($selectedPatient !== ''): ?>
        <p style="padding:20px;color:#aaa;">Patient not found.</p>
    <?php else: ?>
        <p style="padding:20px;color:#7090a8;">Select a patient above to view their full details.</p>
    <?php endif; ?>
</div>

<?php
sqlsrv_close($conn);
include '../includes/footer.php';
?>
