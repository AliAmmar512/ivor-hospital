<?php
require_once '../db_config.php';
require_once '../includes/auth_check.php';

$pageTitle = "Report 8: Patients Grouped by Treatment";
$conn = get_db_connection();

$sql = "
    SELECT
        c.ComplaintCode,
        c.Description                                   AS ComplaintDesc,
        t.TreatmentCode,
        t.Description                                   AS TreatmentDesc,
        p.PatientNo,
        p.PatientName,
        CONVERT(VARCHAR(10), p.DateAdmitted, 120)       AS DateAdmitted,
        d.Name                                          AS TreatingDoctor,
        CONVERT(VARCHAR(10), mh.DateStarted, 120)       AS DateStarted,
        CASE WHEN mh.DateEnded IS NULL THEN NULL
             ELSE CONVERT(VARCHAR(10), mh.DateEnded, 120)
        END                                             AS DateEnded
    FROM dbo.MEDICAL_HISTORY mh
    JOIN  dbo.COMPLAINT  c  ON mh.ComplaintCode  = c.ComplaintCode
    JOIN  dbo.TREATMENT  t  ON mh.TreatmentCode  = t.TreatmentCode
    JOIN  dbo.PATIENT    p  ON mh.PatientNo       = p.PatientNo
    JOIN  dbo.DOCTOR     d  ON mh.DoctorID        = d.DoctorID
    ORDER BY c.ComplaintCode, t.TreatmentCode, p.PatientNo
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
            <h2>Report 8 &mdash; Patients Grouped by Treatment</h2>
            <p>All treatment records grouped by complaint and treatment type, showing which patients received each treatment.</p>
        </div>
        <span class="record-pill"><?php echo $count; ?> record<?php echo $count !== 1 ? 's' : ''; ?></span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Complaint</th>
                    <th>Treatment</th>
                    <th>Patient No</th>
                    <th>Patient Name</th>
                    <th>Date Admitted</th>
                    <th>Treating Doctor</th>
                    <th>Date Started</th>
                    <th>Date Ended</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
            <?php if (count($rows) > 0):
                $prevGroup = null;
                foreach ($rows as $r):
                    $group = $r['ComplaintCode'] . '|' . $r['TreatmentCode'];
                    if ($group !== $prevGroup):
                        $prevGroup = $group;
            ?>
                <tr class="group-header-row">
                    <td colspan="9" style="background:#0d2740;color:#fff;padding:8px 14px;font-size:0.78rem;letter-spacing:.05em;">
                        <span class="badge badge-orange" style="margin-right:6px;"><?php echo htmlspecialchars($r['ComplaintCode']); ?></span>
                        <?php echo htmlspecialchars($r['ComplaintDesc']); ?>
                        &nbsp;&rarr;&nbsp;
                        <span class="badge badge-blue" style="margin-right:6px;"><?php echo htmlspecialchars($r['TreatmentCode']); ?></span>
                        <?php echo htmlspecialchars($r['TreatmentDesc']); ?>
                    </td>
                </tr>
            <?php endif; ?>
                <tr>
                    <td></td>
                    <td></td>
                    <td><?php echo htmlspecialchars($r['PatientNo']); ?></td>
                    <td><?php echo htmlspecialchars($r['PatientName']); ?></td>
                    <td><?php echo htmlspecialchars($r['DateAdmitted']); ?></td>
                    <td><?php echo htmlspecialchars($r['TreatingDoctor']); ?></td>
                    <td><?php echo $r['DateStarted'] ? htmlspecialchars($r['DateStarted']) : '<span style="color:#aaa;">—</span>'; ?></td>
                    <td><?php echo $r['DateEnded']   ? htmlspecialchars($r['DateEnded'])   : '<span style="color:#aaa;">—</span>'; ?></td>
                    <td>
                        <?php if ($r['DateEnded'] === null): ?>
                            <span class="badge badge-green">Active</span>
                        <?php else: ?>
                            <span class="badge badge-gray">Done</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="9" class="no-records">No treatment records found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
sqlsrv_close($conn);
include '../includes/footer.php';
?>
