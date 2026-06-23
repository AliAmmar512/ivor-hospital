<?php
require_once '../db_config.php';
require_once '../includes/auth_check.php';

$pageTitle = "Report 4: Junior Housemen";
$conn = get_db_connection();

$sql = "
    SELECT
        d.DoctorID                                      AS JuniorID,
        d.Name                                          AS JuniorName,
        con.Name                                        AS Consultant,
        p.PatientNo,
        p.PatientName,
        w.WardName,
        p.CareUnitNo,
        sn.Name                                         AS StaffNurseInCharge
    FROM dbo.DOCTOR     d
    JOIN  dbo.DOCTOR    con  ON d.ConsultantID    = con.DoctorID
    JOIN  dbo.PATIENT   p    ON p.DoctorID        = d.DoctorID
    JOIN  dbo.WARD      w    ON p.WardID          = w.WardID
    JOIN  dbo.CARE_UNIT cu   ON p.CareUnitNo      = cu.CareUnitNo
    LEFT JOIN dbo.NURSE sn   ON cu.HeadNurseID    = sn.NurseID
    WHERE d.Position = 'Junior Houseman'
    ORDER BY d.DoctorID, p.PatientNo
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
            <h2>Report 4 &mdash; Junior Housemen &amp; Their Patients</h2>
            <p>Junior housemen, the patients under their care, and the staff nurse responsible for each patient's care unit.</p>
        </div>
        <span class="record-pill"><?php echo $count; ?> row<?php echo $count !== 1 ? 's' : ''; ?></span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Junior ID</th>
                    <th>Junior Houseman</th>
                    <th>Consultant</th>
                    <th>Patient No</th>
                    <th>Patient Name</th>
                    <th>Ward</th>
                    <th>Care Unit</th>
                    <th>Staff Nurse in Charge</th>
                </tr>
            </thead>
            <tbody>
            <?php if (count($rows) > 0): ?>
                <?php foreach ($rows as $r): ?>
                <tr>
                    <td><?php echo htmlspecialchars($r['JuniorID']); ?></td>
                    <td><?php echo htmlspecialchars($r['JuniorName']); ?></td>
                    <td><?php echo htmlspecialchars($r['Consultant']); ?></td>
                    <td><?php echo htmlspecialchars($r['PatientNo']); ?></td>
                    <td><?php echo htmlspecialchars($r['PatientName']); ?></td>
                    <td><?php echo htmlspecialchars($r['WardName']); ?></td>
                    <td><?php echo htmlspecialchars($r['CareUnitNo']); ?></td>
                    <td><?php echo $r['StaffNurseInCharge'] ? htmlspecialchars($r['StaffNurseInCharge']) : '<span style="color:#aaa;">Unassigned</span>'; ?></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="8" class="no-records">No junior housemen found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
sqlsrv_close($conn);
include '../includes/footer.php';
?>
