<?php
require_once '../db_config.php';
require_once '../includes/auth_check.php';

$pageTitle = "Report 3: Patient Treatments";
$conn = get_db_connection();

$sql = "
    SELECT
        p.PatientNo,
        p.PatientName,
        CONVERT(VARCHAR(10), p.DateAdmitted, 120)       AS DateAdmitted,
        hc.ComplaintCode,
        c.Description                                   AS ComplaintDesc,
        mh.TreatmentCode,
        t.Description                                   AS TreatmentDesc,
        d.Name                                          AS TreatingDoctor,
        CONVERT(VARCHAR(10), mh.DateStarted, 120)       AS DateStarted,
        CASE WHEN mh.DateEnded IS NULL THEN NULL
             ELSE CONVERT(VARCHAR(10), mh.DateEnded, 120)
        END                                             AS DateEnded
    FROM dbo.PATIENT          p
    JOIN  dbo.HAS_COMPLAINT   hc  ON p.PatientNo      = hc.PatientNo
    JOIN  dbo.COMPLAINT       c   ON hc.ComplaintCode = c.ComplaintCode
    LEFT JOIN dbo.MEDICAL_HISTORY mh ON mh.PatientNo     = p.PatientNo
                                    AND mh.ComplaintCode  = hc.ComplaintCode
    LEFT JOIN dbo.TREATMENT   t   ON mh.TreatmentCode = t.TreatmentCode
    LEFT JOIN dbo.DOCTOR      d   ON mh.DoctorID      = d.DoctorID
    ORDER BY p.PatientNo, hc.ComplaintCode, mh.TreatmentCode
";
$result = sqlsrv_query($conn, $sql);

$rows  = array();
$count = 0;
if ($result) {
    while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
        $rows[] = $row;
        $count++;
    }
}

include '../includes/header.php';
?>

<div class="table-container">
    <div class="table-header">
        <div>
            <h2>Report 3 &mdash; Patient Treatments</h2>
            <p>Every patient with all their complaints, assigned treatments, and treatment dates.</p>
        </div>
        <span class="record-pill"><?php echo $count; ?> row<?php echo $count !== 1 ? 's' : ''; ?></span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Patient No</th>
                    <th>Patient Name</th>
                    <th>Date Admitted</th>
                    <th>Complaint</th>
                    <th>Treatment</th>
                    <th>Treating Doctor</th>
                    <th>Date Started</th>
                    <th>Date Ended</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
            <?php if (count($rows) > 0): ?>
                <?php foreach ($rows as $r): ?>
                <tr>
                    <td><?php echo htmlspecialchars($r['PatientNo']); ?></td>
                    <td><?php echo htmlspecialchars($r['PatientName']); ?></td>
                    <td><?php echo htmlspecialchars($r['DateAdmitted']); ?></td>
                    <td>
                        <span class="badge badge-orange"><?php echo htmlspecialchars($r['ComplaintCode']); ?></span>
                        <br><small style="color:#7090a8;"><?php echo htmlspecialchars($r['ComplaintDesc']); ?></small>
                    </td>
                    <td>
                        <?php if ($r['TreatmentCode']): ?>
                            <span class="badge badge-blue"><?php echo htmlspecialchars($r['TreatmentCode']); ?></span>
                            <br><small style="color:#7090a8;"><?php echo htmlspecialchars($r['TreatmentDesc']); ?></small>
                        <?php else: ?>
                            <span style="color:#aaa;">No treatment yet</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo $r['TreatingDoctor'] ? htmlspecialchars($r['TreatingDoctor']) : '<span style="color:#aaa;">—</span>'; ?></td>
                    <td><?php echo $r['DateStarted'] ? htmlspecialchars($r['DateStarted']) : '<span style="color:#aaa;">—</span>'; ?></td>
                    <td><?php echo $r['DateEnded']   ? htmlspecialchars($r['DateEnded'])   : '<span style="color:#aaa;">—</span>'; ?></td>
                    <td>
                        <?php if ($r['TreatmentCode'] && $r['DateEnded'] === null): ?>
                            <span class="badge badge-green">Active</span>
                        <?php elseif ($r['TreatmentCode']): ?>
                            <span class="badge badge-gray">Done</span>
                        <?php else: ?>
                            <span style="color:#aaa;">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="9" class="no-records">No data found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
sqlsrv_close($conn);
include '../includes/footer.php';
?>
