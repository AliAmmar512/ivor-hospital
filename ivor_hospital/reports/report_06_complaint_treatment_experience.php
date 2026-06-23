<?php
require_once '../db_config.php';
require_once '../includes/auth_check.php';

$pageTitle = "Report 6: Complaint Treatment Experience";
$conn = get_db_connection();

$sql = "
    SELECT DISTINCT
        c.ComplaintCode,
        c.Description                                       AS ComplaintDesc,
        t.TreatmentCode,
        t.Description                                       AS TreatmentDesc,
        d.DoctorID,
        d.Name                                              AS DoctorName,
        d.Position                                          AS DoctorPosition,
        pe.SNO                                              AS ExpSNO,
        CONVERT(VARCHAR(10), pe.FromDate, 120)              AS ExpFromDate,
        CASE WHEN pe.ToDate IS NULL THEN NULL
             ELSE CONVERT(VARCHAR(10), pe.ToDate, 120)
        END                                                 AS ExpToDate,
        pe.Position                                         AS ExpPosition,
        pe.Establishment
    FROM dbo.MEDICAL_HISTORY  mh
    JOIN  dbo.COMPLAINT       c   ON mh.ComplaintCode  = c.ComplaintCode
    JOIN  dbo.TREATMENT       t   ON mh.TreatmentCode  = t.TreatmentCode
    JOIN  dbo.DOCTOR          d   ON mh.DoctorID       = d.DoctorID
    LEFT JOIN dbo.PREV_EXPERIENCE pe ON pe.DoctorID    = d.DoctorID
    ORDER BY c.ComplaintCode, t.TreatmentCode, d.DoctorID, pe.SNO
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
            <h2>Report 6 &mdash; Complaint, Treatment &amp; Doctor Experience</h2>
            <p>Complaints with their treatments, the doctors who administered each treatment, and each doctor's prior employment history.</p>
        </div>
        <span class="record-pill"><?php echo $count; ?> row<?php echo $count !== 1 ? 's' : ''; ?></span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Complaint</th>
                    <th>Treatment</th>
                    <th>Treating Doctor</th>
                    <th>Position</th>
                    <th>Exp. SNO</th>
                    <th>From Date</th>
                    <th>To Date</th>
                    <th>Previous Position</th>
                    <th>Establishment</th>
                </tr>
            </thead>
            <tbody>
            <?php if (count($rows) > 0): ?>
                <?php foreach ($rows as $r): ?>
                <tr>
                    <td>
                        <span class="badge badge-orange"><?php echo htmlspecialchars($r['ComplaintCode']); ?></span>
                        <br><small style="color:#7090a8;"><?php echo htmlspecialchars($r['ComplaintDesc']); ?></small>
                    </td>
                    <td>
                        <span class="badge badge-blue"><?php echo htmlspecialchars($r['TreatmentCode']); ?></span>
                        <br><small style="color:#7090a8;"><?php echo htmlspecialchars($r['TreatmentDesc']); ?></small>
                    </td>
                    <td><?php echo htmlspecialchars($r['DoctorName']); ?></td>
                    <td><span class="badge badge-navy"><?php echo htmlspecialchars($r['DoctorPosition']); ?></span></td>
                    <td><?php echo $r['ExpSNO']      ? htmlspecialchars($r['ExpSNO'])      : '<span style="color:#aaa;">—</span>'; ?></td>
                    <td><?php echo $r['ExpFromDate'] ? htmlspecialchars($r['ExpFromDate']) : '<span style="color:#aaa;">—</span>'; ?></td>
                    <td><?php echo $r['ExpToDate']   ? htmlspecialchars($r['ExpToDate'])   : ($r['ExpFromDate'] ? '<span class="badge badge-green">Current</span>' : '<span style="color:#aaa;">—</span>'); ?></td>
                    <td><?php echo $r['ExpPosition'] ? htmlspecialchars($r['ExpPosition']) : '<span style="color:#aaa;">—</span>'; ?></td>
                    <td><?php echo $r['Establishment'] ? htmlspecialchars($r['Establishment']) : '<span style="color:#aaa;">No experience on record</span>'; ?></td>
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
