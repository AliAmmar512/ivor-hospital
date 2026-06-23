<?php
require_once '../db_config.php';
require_once '../includes/auth_check.php';

$pageTitle = "Care Units";
$conn = get_db_connection();

$sql = "
    SELECT
        cu.CareUnitNo,
        w.WardName,
        n.NurseID     AS HeadNurseID,
        n.Name        AS HeadNurseName,
        n.Position    AS HeadNursePosition,
        (SELECT COUNT(*) FROM dbo.NURSE nx WHERE nx.CareUnitNo = cu.CareUnitNo) AS NurseCount
    FROM dbo.CARE_UNIT cu
    JOIN      dbo.WARD  w  ON cu.WardID      = w.WardID
    LEFT JOIN dbo.NURSE n  ON cu.HeadNurseID = n.NurseID
    ORDER BY cu.CareUnitNo
";
$result = sqlsrv_query($conn, $sql);

$count = 0;
$rows  = array();
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
            <h2>&#x1F4CC; Care Units</h2>
            <p>Care units with their assigned ward and head nurse.</p>
        </div>
        <span class="record-pill"><?php echo $count; ?> care unit<?php echo $count !== 1 ? 's' : ''; ?></span>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Care Unit No</th>
                    <th>Ward</th>
                    <th>Head Nurse ID</th>
                    <th>Head Nurse Name</th>
                    <th>Position</th>
                    <th>Total Nurses</th>
                </tr>
            </thead>
            <tbody>
            <?php if (count($rows) > 0): ?>
                <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['CareUnitNo']); ?></td>
                    <td><?php echo htmlspecialchars($row['WardName']); ?></td>
                    <td><?php echo $row['HeadNurseID']   ? htmlspecialchars($row['HeadNurseID'])   : '<span style="color:#aaa;">—</span>'; ?></td>
                    <td><?php echo $row['HeadNurseName'] ? htmlspecialchars($row['HeadNurseName']) : '<span style="color:#aaa;">Unassigned</span>'; ?></td>
                    <td>
                        <?php if ($row['HeadNursePosition']): ?>
                            <span class="badge badge-blue"><?php echo htmlspecialchars($row['HeadNursePosition']); ?></span>
                        <?php else: ?>
                            <span style="color:#aaa;">—</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($row['NurseCount']); ?></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="6" class="no-records">No care units found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
sqlsrv_close($conn);
include '../includes/footer.php';
?>
