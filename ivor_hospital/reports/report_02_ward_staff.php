<?php
require_once '../db_config.php';
require_once '../includes/auth_check.php';

$pageTitle = "Report 2: Ward Staff";
$conn = get_db_connection();

$sql = "
    SELECT
        w.WardID,
        w.WardName,
        w.Specialty,
        cu.CareUnitNo,
        sn.Name        AS HeadNurse,
        sn.Position    AS HeadNursePosition,
        (SELECT TOP 1 n2.Name FROM dbo.NURSE n2
         WHERE n2.WardID = w.WardID AND n2.Position = 'Day Sister')     AS DaySister,
        (SELECT TOP 1 n2.Name FROM dbo.NURSE n2
         WHERE n2.WardID = w.WardID AND n2.Position = 'Night Sister')   AS NightSister
    FROM dbo.WARD      w
    JOIN dbo.CARE_UNIT cu ON cu.WardID      = w.WardID
    LEFT JOIN dbo.NURSE sn ON cu.HeadNurseID = sn.NurseID
    ORDER BY w.WardID, cu.CareUnitNo
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
            <h2>Report 2 &mdash; Ward Staff</h2>
            <p>Each ward with its sisters, care units, and the staff nurse in charge of each care unit.</p>
        </div>
        <span class="record-pill"><?php echo $count; ?> row<?php echo $count !== 1 ? 's' : ''; ?></span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Ward ID</th>
                    <th>Ward Name</th>
                    <th>Specialty</th>
                    <th>Day Sister</th>
                    <th>Night Sister</th>
                    <th>Care Unit No</th>
                    <th>Staff Nurse in Charge</th>
                </tr>
            </thead>
            <tbody>
            <?php if (count($rows) > 0): ?>
                <?php foreach ($rows as $r): ?>
                <tr>
                    <td><?php echo htmlspecialchars($r['WardID']); ?></td>
                    <td><?php echo htmlspecialchars($r['WardName']); ?></td>
                    <td><span class="badge badge-teal"><?php echo htmlspecialchars($r['Specialty']); ?></span></td>
                    <td><?php echo $r['DaySister']   ? htmlspecialchars($r['DaySister'])   : '<span style="color:#aaa;">None</span>'; ?></td>
                    <td><?php echo $r['NightSister'] ? htmlspecialchars($r['NightSister']) : '<span style="color:#aaa;">None</span>'; ?></td>
                    <td><?php echo htmlspecialchars($r['CareUnitNo']); ?></td>
                    <td><?php echo $r['HeadNurse'] ? htmlspecialchars($r['HeadNurse']) : '<span style="color:#aaa;">Unassigned</span>'; ?></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="7" class="no-records">No data found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
sqlsrv_close($conn);
include '../includes/footer.php';
?>
