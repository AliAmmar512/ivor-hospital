<?php
require_once '../db_config.php';
require_once '../includes/auth_check.php';

$pageTitle = "Report 7: Patients with Multiple Complaints";
$conn = get_db_connection();

$sql = "
    SELECT
        p.PatientNo,
        p.PatientName,
        CONVERT(VARCHAR(10), p.DateAdmitted, 120)       AS DateAdmitted,
        w.WardName,
        d.Name                                          AS TreatingDoctor,
        hc.ComplaintCode,
        c.Description                                   AS ComplaintDesc,
        mh.TreatmentCode,
        t.Description                                   AS TreatmentDesc
    FROM dbo.PATIENT        p
    JOIN  dbo.WARD          w   ON p.WardID          = w.WardID
    JOIN  dbo.DOCTOR        d   ON p.DoctorID        = d.DoctorID
    JOIN  dbo.HAS_COMPLAINT hc  ON hc.PatientNo      = p.PatientNo
    JOIN  dbo.COMPLAINT     c   ON hc.ComplaintCode  = c.ComplaintCode
    LEFT JOIN dbo.MEDICAL_HISTORY mh ON mh.PatientNo    = p.PatientNo
                                    AND mh.ComplaintCode = hc.ComplaintCode
    LEFT JOIN dbo.TREATMENT t   ON mh.TreatmentCode  = t.TreatmentCode
    WHERE p.PatientNo IN (
        SELECT   PatientNo
        FROM     dbo.HAS_COMPLAINT
        GROUP BY PatientNo
        HAVING   COUNT(*) > 1
    )
    ORDER BY p.PatientNo, hc.ComplaintCode
";
$result = sqlsrv_query($conn, $sql);

$rows       = array();
$patientIDs = array();
if ($result) {
    while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
        $rows[] = $row;
        $patientIDs[$row['PatientNo']] = true;
    }
}
$patientCount = count($patientIDs);
$rowCount     = count($rows);

include '../includes/header.php';
?>

<div class="table-container">
    <div class="table-header">
        <div>
            <h2>Report 7 &mdash; Patients with Multiple Complaints</h2>
            <p>Patients who have been diagnosed with more than one complaint, with all their complaints and assigned treatments.</p>
        </div>
        <span class="record-pill"><?php echo $patientCount; ?> patient<?php echo $patientCount !== 1 ? 's' : ''; ?></span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Patient No</th>
                    <th>Patient Name</th>
                    <th>Date Admitted</th>
                    <th>Ward</th>
                    <th>Treating Doctor</th>
                    <th>Complaint</th>
                    <th>Treatment</th>
                </tr>
            </thead>
            <tbody>
            <?php if (count($rows) > 0): ?>
                <?php foreach ($rows as $r): ?>
                <tr>
                    <td><?php echo htmlspecialchars($r['PatientNo']); ?></td>
                    <td><?php echo htmlspecialchars($r['PatientName']); ?></td>
                    <td><?php echo htmlspecialchars($r['DateAdmitted']); ?></td>
                    <td><?php echo htmlspecialchars($r['WardName']); ?></td>
                    <td><?php echo htmlspecialchars($r['TreatingDoctor']); ?></td>
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
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="7" class="no-records">No patients with multiple complaints found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
sqlsrv_close($conn);
include '../includes/footer.php';
?>
